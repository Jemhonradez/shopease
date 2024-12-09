if (location.pathname === "/category/women" || location.pathname === "/category/men") {
  loadProducts();
}

if (location.pathname.includes("product.php")) {
  loadProduct();
}

async function loadProducts() {
  const itemsContainer = document.querySelector(".items-container");

  const loaderDiv = document.createElement("div");
  loaderDiv.className = "loader-container";
  const loader = document.createElement("span");
  loader.className = "loader";
  loaderDiv.appendChild(loader);
  itemsContainer.appendChild(loaderDiv);

  try {
    const response = await getData("/api/products.php");
    const items = response.items || [];
    itemsContainer.innerHTML = "";

    items.forEach((item) => {
      const productCard = document.createElement("div");
      productCard.className = "product-card";

      productCard.innerHTML = `
      <a href="${location.pathname}/product.php?item_id=${
        item.item_id
      }" class="product-link">
        <div class="item-image">  
          <img src="${item.item_image}" alt="${item.item_name}">
        </div>
        <div class="item-info">
          <h4 class="title">${item.item_name}</h4>
          <h4 class="price">P${parseFloat(item.item_price).toFixed(2)}</h4>
        </div>
      </a>
      `;

      itemsContainer.appendChild(productCard);
    });
  } catch (error) {
    console.error("Error loading products:", error);

    itemsContainer.innerHTML = `<p class="error-message">Failed to load products. Please try again later.</p>`;
  } finally {
    loaderDiv.remove();
  }
}



