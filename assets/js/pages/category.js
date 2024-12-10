if (location.pathname === "/category") {
  loadProducts("all");
}

if (location.pathname === "/category/women") {
  loadProducts("women");
}

if (location.pathname === "/category/men") {
  loadProducts("men");
}

async function loadProducts(mainCategory) {
  const itemsContainer = document.querySelector(".items-container");
  const subCategoriesContainer = document.querySelector(".sub-categories");

  const loaderDiv = document.createElement("div");
  loaderDiv.className = "loader-container";
  const loader = document.createElement("span");
  loader.className = "loader";
  loaderDiv.appendChild(loader);
  itemsContainer.appendChild(loaderDiv);

  try {
    let response;
    const urlParams = new URLSearchParams(window.location.search);
    const tag = urlParams.get("tag");

    // If 'all' category, fetch all products, including categories and tags
    if (mainCategory === "all") {
      response = await getData(`/api/products.php`);
    } else {
      response = await getData(`/api/products.php?category=${mainCategory}`);
    }

    const items = response.items.map((item) => ({
      ...item,
      tags: Array.isArray(item.tags)
        ? item.tags
        : typeof item.tags === "string"
        ? item.tags.includes(",")
          ? item.tags.split(",")
          : [item.tags]
        : Object.values(item.tags || {}),
    }));

    itemsContainer.innerHTML = "";
    subCategoriesContainer.innerHTML = "";

    if (items.length === 0) {
      itemsContainer.innerHTML = `<p>No products found.</p>`;
      return;
    }

    // Get unique tags for filtering
    const uniqueTags = [...new Set(items.flatMap((item) => item.tags))].map((tag) =>
      tag.replace(/[{}]/g, "")
    );

    // Create the 'All' button for showing all items
    const allBtn = document.createElement("button");
    allBtn.className = "filter-btn base-btn primary-cta-btn";
    allBtn.textContent = "All";
    allBtn.addEventListener("click", () => {
      displayProducts(items); // Display all items when 'All' is clicked
      updateActiveFilterButton(allBtn);
    });
    subCategoriesContainer.appendChild(allBtn);

    // Create buttons for each unique tag for filtering
    uniqueTags.forEach((tag) => {
      const filterBtn = document.createElement("button");
      filterBtn.className = "filter-btn base-btn";
      filterBtn.textContent = tag;
      filterBtn.addEventListener("click", () => {
        filterProducts(tag, items);
        updateActiveFilterButton(filterBtn);
      });
      subCategoriesContainer.appendChild(filterBtn);
    });

    // Filter by the tag if it exists in the URL
    if (tag) {
      filterProducts(tag, items);
      // Find and highlight the active tag button
      const activeTagBtn = [...subCategoriesContainer.querySelectorAll('.filter-btn')].find(btn =>
        btn.textContent.toLowerCase() === tag.toLowerCase()
      );
      if (activeTagBtn) {
        updateActiveFilterButton(activeTagBtn);
      }
    } else {
      // Display all products and make sure the 'All' button is active
      displayProducts(items);
      updateActiveFilterButton(allBtn); // Make 'All' button active initially
    }
  } catch (error) {
    console.error("Error loading products:", error);
    itemsContainer.innerHTML = `<p class="error-message">Failed to load products. Please try again later.</p>`;
  } finally {
    loaderDiv.remove();
  }
}

function displayProducts(items) {
  const itemsContainer = document.querySelector(".items-container");
  itemsContainer.innerHTML = "";

  items.forEach((item) => {
    const productCard = document.createElement("div");
    productCard.className = "product-card";

    productCard.innerHTML = `
      <a href="${location.pathname}/product.php?item_id=${item.item_id}" class="product-link">
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

function filterProducts(tag, items) {
  const formattedItems = items.map((item) => {
    const formattedTags = Array.isArray(item.tags)
      ? item.tags.map((tag) => tag.replace(/[{}]/g, ""))
      : typeof item.tags === "string"
      ? item.tags.includes(",")
        ? item.tags.split(",").map((tag) => tag.replace(/[{}]/g, ""))
        : [item.tags.replace(/[{}]/g, "")]
      : Object.values(item.tags || {}).map((tag) => tag.replace(/[{}]/g, ""));

    return {
      ...item,
      tags: formattedTags,
    };
  });

  const filteredItems = formattedItems.filter((item) => item.tags.includes(tag));

  displayProducts(filteredItems);
}

function updateActiveFilterButton(activeBtn) {
  const allBtns = document.querySelectorAll(".filter-btn");
  allBtns.forEach((btn) => btn.classList.remove("primary-cta-btn"));
  activeBtn.classList.add("primary-cta-btn");
}
