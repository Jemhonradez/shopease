<?php
session_start();

// Check if the user is logged in and the user_id exists in session
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
?>

<main>
  <section class="section-nav --first">
    <p class="clickable flex-row align-center" onclick="window.history.back();"><i class="ti ti-arrow-left"></i> Back to products</p>
  </section>
  <section class="product-container --first">
  </section>
</main>

<script>
  const userId = <?php echo json_encode($user_id); ?>;
</script>

<script>
  async function loadProduct() {
    const urlParams = new URLSearchParams(window.location.search);
    const itemId = urlParams.get("item_id");

    const productContainer = document.querySelector(".product-container");

    const loaderDiv = document.createElement("div");
    loaderDiv.className = "loader-container";
    const loader = document.createElement("span");
    loader.className = "loader";
    loaderDiv.appendChild(loader);
    productContainer.appendChild(loaderDiv);

    try {
      const product = await getData(`/api/products.php?item_id=${itemId}`);

      productContainer.innerHTML = `
        <div class="product-image">
            <img src="${product.item_image}" alt="${product.item_name}">
        </div>
        <div class="product-info">
            <h1>${product.item_name}</h1>
            <p>${product.item_desc}</p>
            <h4>P${parseFloat(product.item_price).toFixed(2)}</h4>
            <p>Stock: ${product.item_stock}</p>
            <div class="quantity-input-container">
                <label for="quantity">Quantity:</label>
                <input 
                    type="number" 
                    id="quantity" 
                    min="1" 
                    max="${product.item_stock}" 
                    value="1" 
                    oninput="handleQuantityChange(this, ${product.item_stock})"
                >
            </div>
            <button id="addToCartBtn" class="primary-cta-btn" 
                onclick="addToCart(${product.item_id}, ${userId}, parseInt(document.getElementById('quantity').value), ${product.item_price})">
                Add to Cart
            </button>
        </div>
      `;
    } catch (error) {
      console.error("Error fetching product:", error);
    } finally {
      loaderDiv.remove();
    }
  }

  async function addToCart(itemId, userId, qty, price) {
    const addToCartBtn = document.getElementById("addToCartBtn");

    const loader = document.createElement("span");
    loader.className = "loader";
    addToCartBtn.appendChild(loader);
    addToCartBtn.disabled = true;

    const requestData = {
      item_id: itemId,
      user_id: userId,
      item_qty: qty,
      item_price: price,
    };

    const response = await fetch("/api/orders.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(requestData),
    });

    const data = await response.json();

    if (data.success) {
      alert("Item added to cart");
      loader.remove();
    } else {
      alert("Error adding item to cart:", data.error);
    }
  }

  // Handle quantity input to ensure the value stays within the stock range
  function handleQuantityChange(inputElement, maxStock) {
    if (inputElement.value < 1) {
      inputElement.value = 1;
    } else if (inputElement.value > maxStock) {
      inputElement.value = maxStock;
    }
  }
</script>