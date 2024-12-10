<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: /login');
  exit();
}

$user_id = $_SESSION['user_id'];
?>

<main id="cart">

  <div class="receipt-popup popup">
  </div>

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
  const isOpen = false;

  function formatCurrency(number) {
    const formattedNumber = new Intl.NumberFormat("en-US", {
      style: "currency",
      currency: "PHP",
    }).format(number);

    return formattedNumber;
  }

  function showPopup(order) {
    const popup = document.querySelector(".receipt-popup");

    if (!order) {
      console.error("Invalid order data for receipt.");
      popup.innerHTML = "<p>Error generating receipt. Please try again later.</p>";
      popup.classList.add("show");
      return;
    }

    popup.innerHTML = ""

    let popupContent = `
    <div class="receipt-title">
      <div class="receipt-icon">
        <i class="ti ti-check"></i>
      </div>
      <h3>Checkout successful!</h3>
    </div>
  `;

    order.orders.forEach(order => {
      popupContent += `
      

    <div class="order-details">
      <div class="space-btwn">
        <p>Order ID</p>
        <p><strong>${order.order_id}</strong></p>
      </div>
      <div class="space-btwn">
        <p>Item Name</p>
        <p><strong>${order.item_name}</strong></p>
      </div>
      <div class="space-btwn">
        <p>Quantity</p>
        <p><strong>${order.item_qty}</strong></p>
      </div>
      <div class="space-btwn">
        <p><strong>Total</strong></p>
        <p><strong>${formatCurrency(order.total_amount)}</strong></p>
      </div>
    </div>
    <div class="line"></div>
    
    `;
    });

    popupContent += `
    <div class="space-btwn">
      <p>New Balance</p>
      <p><strong>${formatCurrency(order.remaining_balance)}</strong></p>
    </div>

    <div class="btn-container">
      <button type="button" onclick="hidePopup()" class="secondary-cta-btn">Close</button>
      <button type="button" onclick="window.location.href = '/profile?view=orders'" class="primary-cta-btn">
        Previous orders
      </button>
    </div>
  `;
    popup.innerHTML = popupContent;

    popup.classList.add("show");
  }

  function hidePopup() {
    const popup = document.querySelector(".receipt-popup");
    popup.classList.remove("show");

    loadCart(userId);
  }

  async function loadBalance(userId) {
    const balance = document.getElementById("balance");

    balance.textContent = "Loading.."

    try {
      const response = await fetch(`/assets/js/utils/balance.php?user_id=${userId}`);
      const data = await response.json();

      if (data.error) {
        console.error("Error fetching balance:", data.error);
        balance.textContent = formatCurrency(0);
      } else {
        balance.textContent = formatCurrency(data.balance);
      }
    } catch (error) {
      console.error("Error fetching balance:", error);
      balance.textContent = formatCurrency(0);
    }
  }

  async function loadCart(userId) {
    const balance = document.getElementById("balance");
    const ordersContainer = document.querySelector(".cart-container");
    const totalPriceElement = document.getElementById("total-price");

    await loadBalance(userId);


    const loaderDiv = document.createElement("div");
    loaderDiv.className = "loader-container";
    const loader = document.createElement("span");
    loader.className = "loader";

    ordersContainer.innerHTML = "";
    ordersContainer.appendChild(loaderDiv);
    loaderDiv.appendChild(loader);

    try {
      const response = await fetch(`/api/orders.php?user_id=${userId}`);
      const data = await response.json();

      if (data.error) {
        throw new Error(data.error);
      }


      loaderDiv.remove();

      let totalAmount = 0;

      const orders = Array.isArray(data.orders) ? data.orders : [];

      if (orders.length > 0) {
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
      ordersContainer.innerHTML = "<p>Error loading orders. Please try again later.</p>";
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
      }
    } catch (error) {
      console.error("Error clearing cart:", error);
    } finally {
      loader.remove();
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
    console.log(orderIds);

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
        alert(result.error);
      } else if (result.success) {
        totalAmountElement.textContent = formatCurrency(0);
        showPopup(result);
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
    loadCart(userId);
  })
</script>