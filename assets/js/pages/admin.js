let isOpen = true;
let isOpenActionEdit = false;
let isOpenActionDelete = false;

async function saveChanges(itemId, formData) {
  const popup = document.querySelector(".action-popup-edit");

  const submitBtn = document.querySelector('button[type="submit"]');
  const loader = document.createElement("span");
  loader.className = "loader";
  submitBtn.appendChild(loader);
  submitBtn.disabled = true;

  let data = {
    item_name: formData.get("item_name"),
    item_desc: formData.get("item_desc"),
    item_price: formData.get("item_price"),
    item_stock: formData.get("item_stock"),
    tags: formData
      .get("tags")
      .split(",")
      .map((tag) => tag.trim()),
  };

  const file = formData.get("item_image");
  let imageUrl = null;

  try {
    if (file && file.size > 0) {
      const productResponse = await fetch(`/api/products.php?item_id=${itemId}`);
      const product = await productResponse.json();

      if (product.item_image) {
        const currentFilePath = product.item_image.split("/").pop();
        await deleteFile(currentFilePath);
      }

      await uploadFile(file);
      imageUrl = await getFileUrl(file.name);
    }

    data = {
      ...data,
      item_id: itemId,
      item_image: imageUrl,
    };

    await putData(`/api/products.php?item_id=${itemId}`, data)
      .then((result) => {
        if (result.error) {
          alert(result.error);
        } else if (result.message) {
          alert(result.message);
          setTimeout(() => {
            togglePopup(popup, "edit");
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
    console.error("Error updating product:", error);
    alert("Failed to update product.");
  } finally {
    loader.remove();
    submitBtn.disabled = false;
  }
}

async function confirmDelete(itemId) {
  const deleteBtn = document.querySelector(".delete-btn");
  const loader = document.createElement("span");
  loader.className = "loader";
  deleteBtn.appendChild(loader);
  deleteBtn.disabled = true;

  try {
    await fetch(`/api/products.php?item_id=${itemId}`, {
      method: "DELETE",
    });

    alert("Item deleted successfully!");
    setTimeout(() => {
      togglePopup(document.querySelector(".action-popup-delete"), "delete");
      loadProducts();
      loader.remove();
    }, 500);
  } catch (error) {
    alert("An error occurred while deleting the item.");
    console.error(error);
    loader.remove();
  } finally {
    loader.remove();
    deleteBtn.disabled = false;
  }
}

async function editItem(itemId) {
  const popup = document.querySelector(".action-popup-edit");

  popup.innerHTML = `
    <h4>Loading...</h4>
    <div class="loader"></div>
  `;
  togglePopup(popup, "edit");

  try {
    const response = await fetch(`/api/products.php?item_id=${itemId}`, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
      },
    });
    const item = await response.json();
    console.log(item);

    const imageUrl = item.item_image ? item.item_image : "";
    const tags = item.tags ? item.tags.replace(/[{}]/g, "").split(",") : [];

    popup.innerHTML = `
      <h4>Edit Item ${itemId}</h4>
      <form class="form-container" id="editForm">
        <div class="form-item">
          <label for="name">Name</label>
          <input type="text" name="item_name" value="${item.item_name}" required />
        </div>
        <div class="form-item">
          <label for="item_image">Product Image</label>
          <input type="file" name="item_image" value="${imageUrl}"/>
        </div>
        <div class="form-item">
          <label for="description">Description</label>
          <textarea name="item_desc" required>${item.item_desc}</textarea>
        </div>
        <div class="form-item">
          <label for="price">Price</label>
          <input type="number" name="item_price" value="${item.item_price}" required />
        </div>
        <div class="form-item">
          <label for="stock">Stock</label>
          <input type="number" name="item_stock" value="${item.item_stock}" required />
        </div>
        <div class="form-item">
          <label for="tags">Tags</label>
          <input type="text" name="tags" value="${tags}" required />
        </div>
        <div class="line"></div>
        <div class="btn-container">
          <button type="button" class="secondary-cta-btn" onclick="togglePopup(document.querySelector('.action-popup-edit'), 'edit')">Cancel</button>
          <button type="submit" class="primary-cta-btn">Save Changes</button>
        </div>
      </form>
    `;

    const form = document.getElementById("editForm");
    form.addEventListener("submit", (event) => {
      event.preventDefault();
      saveChanges(itemId, new FormData(form));
    });
  } catch (error) {
    popup.innerHTML = `
      <h4>Error loading data</h4>
      <p>Unable to fetch data for item ${itemId}. Please try again later.</p>
      <div class="btn-container">
        <button type="button" class="secondary-cta-btn" onclick="togglePopup(document.querySelector('.action-popup-edit'), 'edit')">Close</button>
      </div>
    `;
    console.error(error);
  }
}

function deleteItem(itemId) {
  const popup = document.querySelector(".action-popup-delete");

  popup.innerHTML = `
  <h4>Are you sure you want to delete item ${itemId}?</h4>
  <div class="btn-container">
    <button type="button" class="action-cta-btn" onclick="togglePopup(document.querySelector('.action-popup-delete'), 'delete')">Cancel</button>
    <button type="button" class="delete-btn action-cta-btn flex-row" onclick="confirmDelete(${itemId})">
      <i class="ti ti-trash"></i> Delete
    </button>
  </div>
`;

  togglePopup(popup, "delete");
}

function togglePopup(popup, type) {
  if (type === "edit") {
    if (isOpenActionEdit) {
      popup.classList.remove("show");
    } else {
      popup.classList.add("show");
    }
    isOpenActionEdit = !isOpenActionEdit;
  } else if (type === "delete") {
    if (isOpenActionDelete) {
      popup.classList.remove("show");
    } else {
      popup.classList.add("show");
    }
    isOpenActionDelete = !isOpenActionDelete;
  }
}

function toggleEditPopup(state) {
  const popup = document.querySelector(".action-popup-edit");
  popup.classList.remove("show");
  isOpenActionEdit = state;
}

function toggleDeletePopup(state) {
  const popup = document.querySelector(".action-popup-delete");
  popup.classList.remove("show");
  isOpenActionDelete = state;
}

let isOpenAction = false;
function showActionPopup(event, itemId) {
  const popup = document.querySelector(".action-popup");

  popup.innerHTML = `
  <button type="button" class="action-cta-btn flex-row" onclick="editItem(${itemId})">
    <i class="ti ti-edit"></i>
    Edit
  </button>
  <button type="button" class="action-cta-btn flex-row" onclick="deleteItem(${itemId})">
    <i class="ti ti-trash"></i>
    Delete
  </button>
`;

  const rect = event.currentTarget.getBoundingClientRect();
  const offsetX = window.pageXOffset;
  const offsetY = window.pageYOffset;

  popup.style.top = `${rect.bottom + offsetY + 10}px`;
  popup.style.left = `${rect.left + offsetX - 50}px`;

  if (isOpenAction) {
    popup.classList.remove("show");
    document.removeEventListener("click", handleOutsideClick);
  } else {
    popup.classList.add("show");
    document.addEventListener("click", handleOutsideClick);
  }

  isOpenAction = !isOpenAction;
}

async function loadProducts() {
  const table = document.querySelector(".table-content");

  table.innerHTML = "";

  const loaderDiv = document.createElement("div");
  loaderDiv.className = "loader-container";
  const loader = document.createElement("span");
  loader.className = "loader";
  table.appendChild(loaderDiv);
  loaderDiv.appendChild(loader);

  try {
    const response = await getData("/api/products.php");
    const items = response.items || [];

    if (items.length === 0) {
      const noProductsMessage = document.createElement("p");
      noProductsMessage.className = "no-products-message";
      noProductsMessage.textContent = "No products found";
      table.appendChild(noProductsMessage);
    } else {
      items.forEach((item) => {
        const tableItem = document.createElement("div");
        tableItem.className = "table-item";

        let tags = "No tags";
        if (item.tags) {
          if (Array.isArray(item.tags)) {
            tags = item.tags.length > 0 ? item.tags.join(", ") : "No tags";
          } else if (typeof item.tags === "string") {
            tags = item.tags.replace(/[{}"]/g, "").split(",").join(", ");
          }
        }

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
        <p>${formatCurrency(item.item_price)}</p>
        <p>${item.item_desc}</p>
        <p>${tags}</p>
        <p>${new Date(item.created_at).toLocaleDateString()}</p>
        <button type="button" class="action-btn" onclick="showActionPopup(event, ${
          item.item_id
        })">
          <i class="ti ti-dots-vertical"></i>
        </button>
      `;

        table.appendChild(tableItem);
      });
    }
  } catch (error) {
    console.error("Error loading products:", error);
  } finally {
    loaderDiv.remove();
  }
}

if (window.location.pathname === "/admin/product-management") {
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

  function handleOutsideClick(event) {
    const popup = document.querySelector(".action-popup");
    if (!popup.contains(event.target) && !event.target.closest(".action-btn")) {
      popup.classList.remove("show");
      isOpenAction = false;
      document.removeEventListener("click", handleOutsideClick);
    }
  }

  const createForm = document.getElementById("createForm");

  createForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    const submitBtn = document.getElementById("create-submit");
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
      tags: formData.get("tags")
        ? formData
            .get("tags")
            .split(",")
            .map((tag) => tag.trim())
        : [],
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
            createForm.reset();
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

  loadProducts();
}

if (window.location.pathname === "/admin") {
  async function loadReports() {
    const reports = document.querySelector(".reports");

    reports.innerHTML = "";

    const loaderDiv = document.createElement("div");
    loaderDiv.className = "loader-container";
    const loader = document.createElement("span");
    loader.className = "loader";
    reports.appendChild(loaderDiv);
    loaderDiv.appendChild(loader);

    try {
      const itemsResponse = await fetch("/api/products.php");
      const ordersResponse = await fetch("/api/orders.php");
      const paymentsResponse = await fetch("/api/payments.php");

      const dataItems = await itemsResponse.json();
      const dataOrders = await ordersResponse.json();
      const dataPayments = await paymentsResponse.json();

      const items = dataItems.items;
      const orders = dataOrders.orders;
      const payments = dataPayments.payments;

      console.log(items)
      console.log(orders)
      console.log(payments)

      loaderDiv.remove();

      reports.innerHTML = `
      <div class="report-card">
        <h3>Items Inventory</h3>
        <canvas id="itemsChart"></canvas>
      </div>

      <div class="report-card">
        <h3>Top-selling Products</h3>
        <canvas id="topSellingChart"></canvas>
      </div>

      <div class="report-card">
        <h3>Orders Overview</h3>
        <canvas id="ordersChart"></canvas>
      </div>

      <div class="report-card">
        <h3>Payments Overview</h3>
        <canvas id="paymentsChart"></canvas>
      </div>
      `;

      const itemsInventoryData = items.map((item) => item.item_name);
      const itemsStockData = items.map((item) => item.item_stock);

      const itemsChart = new Chart(document.getElementById("itemsChart"), {
        type: "bar",
        data: {
          labels: itemsInventoryData,
          datasets: [
            {
              label: "Items in Stock",
              data: itemsStockData,
              backgroundColor: "rgba(75, 192, 192, 0.6)",
              borderColor: "rgba(75, 192, 192, 1)",
              borderWidth: 1,
            },
          ],
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
            },
          },
        },
      });

      const itemSalesData = getItemSalesData(orders);

      const topSellingProducts = getTopSellingProducts(itemSalesData);
      const topSellingNames = topSellingProducts.map((item) => item.name);
      const topSellingSales = topSellingProducts.map((item) => item.sales);

      const topSellingChart = new Chart(document.getElementById("topSellingChart"), {
        type: "bar",
        data: {
          labels: topSellingNames,
          datasets: [
            {
              label: "Top-selling Products",
              data: topSellingSales,
              backgroundColor: "rgba(153, 102, 255, 0.6)",
              borderColor: "rgba(153, 102, 255, 1)",
              borderWidth: 1,
            },
          ],
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
            },
          },
        },
      });

      const orderStatuses = ["processing", "pending", "completed"];
      const orderStatusCount = getOrderStatusCount(orders);

      const ordersChart = new Chart(document.getElementById("ordersChart"), {
        type: "bar",
        data: {
          labels: orderStatuses,
          datasets: [
            {
              label: "Orders by Status",
              data: orderStatusCount,
              backgroundColor: "rgba(255, 159, 64, 0.6)",
              borderColor: "rgba(255, 159, 64, 1)",
              borderWidth: 1,
            },
          ],
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
            },
          },
        },
      });

      const paymentsByDate = getPaymentsByDate(payments);
      const paymentDates = Object.keys(paymentsByDate);
      const dailyTotals = Object.values(paymentsByDate);

      const paymentsOverTimeChart = new Chart(document.getElementById("paymentsChart"), {
        type: "line",
        data: {
          labels: paymentDates,
          datasets: [
            {
              label: "Total Payments by Date",
              data: dailyTotals,
              backgroundColor: "rgba(75, 192, 192, 0.2)",
              borderColor: "rgba(75, 192, 192, 1)",
              borderWidth: 2,
              tension: 0.4,
            },
          ],
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
            },
          },
        },
      });
    } catch (error) {
      console.error("Error loading reports:", error);
    }
  }

  function getTopSellingProducts(items) {
    return items
      .filter((item) => item.item_sales > 0)
      .sort((a, b) => b.item_sales - a.item_sales)
      .slice(0, 5)
      .map((item) => ({ name: item.item_name, sales: item.item_sales }));
  }

  function getOrderStatusCount(orders) {
    const statusCount = { processing: 0, pending: 0, completed: 0 };
    orders.forEach((order) => {
      statusCount[order.order_status]++;
    });
    return [statusCount["processing"], statusCount["pending"], statusCount["completed"]];
  }

  function getPaymentsByDate(payments) {
    const dateTotals = {};
    payments.forEach((payment) => {
      const date = payment.payment_date.split(" ")[0];
      const amount = parseFloat(payment.amount);
      if (!dateTotals[date]) {
        dateTotals[date] = 0;
      }
      dateTotals[date] += amount;
    });
    return dateTotals;
  }

  function getItemSalesData(orders) {
    const salesData = {};

    orders.forEach((order) => {
      const itemId = order.item_id;
      const itemName = order.item_name;
      const itemPrice = parseFloat(order.item_price);
      const itemQty = order.item_qty;
      const salesAmount = itemQty * itemPrice;

      if (!salesData[itemId]) {
        salesData[itemId] = {
          item_id: itemId,
          item_name: itemName,
          item_sales: 0,
        };
      }
      salesData[itemId].item_sales += salesAmount;
    });

    return Object.values(salesData);
  }

  loadReports();
}
