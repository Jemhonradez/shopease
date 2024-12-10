<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: /login');
  exit();
}

$user_id = $_SESSION['user_id'];
?>

<main id="cart">
  <section class="container --first">
    <h1>Shopping Cart</h1>
    <div class="cart-container"></div>
    <div class="line"></div>
    <div class="total-container">
      <h4>Summary</h4>
      <p>Current Balance: <span id="balance">P0.00</span></p>
      <p>Total: <span id="total-price">P0.00</span></p>
    </div>
    <div class="btn-container">
      <button id="clearBtn" type="button" class="secondary-cta-btn" onclick="clearCart()">Clear all items</button>
      <button id="payBtn" type="button" class="primary-cta-btn" onclick="checkout()">Pay now</button>
    </div>
  </section>
</main>

<script>
  const userId = <?php echo json_encode($user_id); ?>;
  const payBtn = document.getElementById("payBtn");
  const clearBtn = document.getElementById("clearBtn");

  function formatCurrency(number) {
    const formattedNumber = new Intl.NumberFormat("en-US", {
      style: "currency",
      currency: "PHP",
    }).format(number);

    return formattedNumber;
  }

  async function loadBalance(userId) {
    const balanceElement = document.getElementById("balance");

    try {
      const response = await fetch(`/assets/js/utils/balance.php`);
      const data = await response.json();

      if (Array.isArray(data) && data[0]?.balance) {
        // If response is an array with a balance key
        balanceElement.textContent = formatCurrency(data[0].balance);
      } else if (data.balance) {
        // If response has a balance key directly
        balanceElement.textContent = formatCurrency(data.balance);
      } else {
        console.error("Unexpected balance response format:", data);
        balanceElement.textContent = "Error loading balance";
      }
    } catch (error) {
      console.error("Error loading balance:", error);
      balanceElement.textContent = "Error loading balance";
    }
  }

  async function loadCart(userId) {
    const ordersContainer = document.querySelector(".cart-container");
    const totalPriceElement = document.getElementById("total-price");

    const loaderDiv = document.createElement("div");
    loaderDiv.className = "loader-container";
    const loader = document.createElement("span");
    loader.className = "loader";

    // Clear the previous cart content and show loader
    ordersContainer.innerHTML = "";
    ordersContainer.appendChild(loaderDiv);
    loaderDiv.appendChild(loader);

    try {
      const response = await fetch(`/api/orders.php?user_id=${userId}`);
      const data = await response.json();

      if (data.error) {
        console.error(data.error);
        ordersContainer.innerHTML = "<p>Error loading orders. Please try again later.</p>";
        return;
      }

      loaderDiv.remove(); // Remove loader once data is loaded

      let totalAmount = 0;

      const orders = Array.isArray(data.orders) ? data.orders : [];
      if (orders.length > 0) {
        // Filter out orders with status "pending" or "complete"
        const filteredOrders = orders.filter(order => order.order_status === 'processing');

        if (filteredOrders.length > 0) {
          filteredOrders.forEach(order => {
            const orderTotal = order.item_price * order.item_qty;
            totalAmount += orderTotal;

            const orderElement = document.createElement("div");
            orderElement.classList.add("order-item");

            orderElement.dataset.orderId = order.order_id;

            orderElement.innerHTML = `
                        <div class="order-item-image">
                            <img src="${order.item_image}" alt="${order.item_name}" />
                        </div>
                        <div class="order-item-info">
                            <h4>${order.item_name}</h4>
                            <p>Quantity: ${order.item_qty}</p>
                            <p>Price: ${formatCurrency(orderTotal)}</p>
                        </div>
                    `;

            ordersContainer.appendChild(orderElement);
          });
        } else {
          ordersContainer.innerHTML = "<p>No orders found.</p>";
        }
      } else {
        ordersContainer.innerHTML = "<p>No orders found.</p>";
      }

      totalPriceElement.textContent = formatCurrency(totalAmount);

    } catch (error) {
      console.error("Error loading orders:", error);
      ordersContainer.innerHTML = "<p>Error loading orders.</p>";
    }
  }

  async function clearCart() {
    const totalAmountElement = document.getElementById("total-price");
    const ordersContainer = document.querySelector(".cart-container");
    const orderItems = ordersContainer.querySelectorAll(".order-item");

    const loader = document.createElement("span");
    loader.className = "loader";
    clearBtn.appendChild(loader);
    clearBtn.disabled = true;

    if (orderItems.length === 0) {
      alert("The cart is already empty.");
      loader.remove();
      clearBtn.disabled = false;
      return;
    }

    try {
      const response = await fetch('/api/orders.php', {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          user_id: userId
        }),
      });

      const result = await response.json();
      if (result.error) {
        console.error("Error clearing cart:", result.error);
        loader.remove();
      } else {
        console.log("All items cleared from cart.");
        totalAmountElement.textContent = formatCurrency(0);
        loadCart(userId);
        loadBalance(userId);
      }
    } catch (error) {
      console.error("Error clearing cart:", error);
    } finally {
      loader.remove(); // Ensure loader is removed after process
      clearBtn.disabled = false;
    }
  }

  async function checkout() {
    const totalAmountElement = document.getElementById("total-price");
    const totalAmount = parseFloat(totalAmountElement.textContent.replace("P", ""));

    const loader = document.createElement("span");
    loader.className = "loader";
    payBtn.appendChild(loader);
    payBtn.disabled = true;

    if (totalAmount <= 0) {
      alert("Cart is empty or total amount is invalid.");
      loader.remove();
      payBtn.disabled = false;
      return;
    }

    const orderElements = document.querySelectorAll(".order-item");
    const orderIds = Array.from(orderElements).map(order => order.dataset.orderId);

    if (orderIds.length === 0) {
      alert("No items in the cart to checkout.");
      loader.remove();
      payBtn.disabled = false;
      return;
    }

    try {
      const response = await fetch("/api/payments.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          order_ids: orderIds
        }),
      });

      const result = await response.json();

      if (result.error) {
        loader.remove();
        alert(result.error);
      } else {
        alert(result.success);
        totalAmountElement.textContent = formatCurrency(0);
        loadCart(userId);
        loadBalance(userId);
      }
    } catch (error) {
      console.error("Error during checkout:", error);
      alert("An error occurred. Please try again later.");
    } finally {
      loader.remove();
      payBtn.disabled = false;
    }
  }

  document.addEventListener("DOMContentLoaded", () => {
    loadBalance(userId);
    loadCart(userId);
  })
</script>