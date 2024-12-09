document.addEventListener("DOMContentLoaded", () => {
  loadProducts();
});

let isOpen = true;
function showPopup() {
  const popup = document.querySelector(".create-product");
  setTimeout(() => {
    if (isOpen) {
      popup.classList.add("show");
    } else {
      popup.classList.remove("show");
    }
    isOpen = !isOpen;
  }, 50);
}

document.getElementById("createForm").addEventListener("submit", async function (e) {
  e.preventDefault();

  const submitBtn = document.querySelector('button[type="submit"]');
  const loader = document.createElement("span");
  loader.className = "loader";
  submitBtn.appendChild(loader);
  submitBtn.disabled = true;

  const formData = new FormData(this);

  let data = {
    item_name: formData.get("item_name"),
    item_image: formData.get("item_image"),
    item_desc: formData.get("item_desc"),
    item_price: formData.get("item_price"),
    item_stock: formData.get("item_stock"),
  };

  const file = data.item_image;

  try {
    await uploadFile(file);

    const imageUrl = await getFileUrl(file.name);

    data = {
      ...data,
      item_image: imageUrl,
    };

    postData("/api/products.php", null, data)
      .then((result) => {
        if (result.error) {
          alert(result.error);
        } else if (result.message) {
          alert(result.message);
          setTimeout(() => {
            showPopup();
            loadProducts();
          }, 500);
        }
      })
      .catch((error) => {
        alert(error.message);
      })
      .finally(() => {
        loader.remove();
        submitBtn.disabled = false;
      });
  } catch (error) {
    console.error("Error:", error);
  }
});

async function loadProducts() {
  const table = document.querySelector(".table-content");

  const loaderDiv = document.createElement("div");
  loaderDiv.className = "loader-container";
  const loader = document.createElement("span");
  loader.className = "loader";
  table.appendChild(loaderDiv);
  loaderDiv.appendChild(loader);

  try {
    const response = await getData("/api/products.php");
    const items = response.items || [];

    items.forEach((item) => {
      const tableItem = document.createElement("div");
      tableItem.className = "table-item";

      tableItem.innerHTML = `
        <p>${item.item_id}</p>
        <div class="item-container">
          <div class="item-image">
            <img src="${item.item_image}" alt="${item.item_name}">
          </div>
          <div>
            <h4>${item.item_name}</h4>
            <p>Stock: ${item.item_stock}</p>
          </div>
        </div>
        <p>${item.item_desc}</p>
        <p>P${parseFloat(item.item_price).toFixed(2)}</p>
        <p>${new Date(item.created_at).toLocaleDateString()}</p>
        <button type="button" class="action-btn">
          <i class="ti ti-trash "></i>
        </button>
      `;

      table.appendChild(tableItem);
    });
  } catch (error) {
    console.error("Error loading products:", error);
  } finally {
    loaderDiv.remove();
  }
}
