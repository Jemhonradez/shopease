if (location.pathname === "/category/women") {
  loadProducts("women");
}

if (location.pathname === "/category/men") {
  loadProducts("men");
}

async function loadProducts(category) {
  const itemsContainer = document.querySelector(".items-container");

  // Create and append loader
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

    // Filter items based on category (tags)
    const filteredItems = items.filter((item) => {
      // Check if tags is an array or string
      if (Array.isArray(item.tags)) {
        return item.tags.includes(category);
      } else if (typeof item.tags === "string") {
        // If it's a string, handle it as a PostgreSQL array (e.g., '{men,women}')
        const tagsArray = item.tags.replace(/[{}"]/g, "").split(",");
        return tagsArray.includes(category);
      }
      return false;
    });

    // Display filtered items
    if (filteredItems.length === 0) {
      itemsContainer.innerHTML = `<p class="no-products-message">No products found for ${category} category.</p>`;
    } else {
      filteredItems.forEach((item) => {
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
            <h4 class="price">${formatCurrency(item.item_price)}</h4>
          </div>
        </a>
        `;

        itemsContainer.appendChild(productCard);
      });
    }
  } catch (error) {
    console.error("Error loading products:", error);
    itemsContainer.innerHTML = `<p class="error-message">Failed to load products. Please try again later.</p>`;
  } finally {
    loaderDiv.remove();
  }
}
