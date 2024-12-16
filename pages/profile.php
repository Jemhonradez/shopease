<?php
session_start();

$userId = $_SESSION["user_id"];
?>

<main>
  <div class="edit-details card">
    <h3>Edit account details</h3>
    <form id="editForm" class="form-container">
      <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
      <div class="form-item">
        <label for="name">Full name</label>
        <input type="text" name="name" />
      </div>
      <div class="form-item">
        <label for="username">Username</label>
        <input type="text" name="username" />
      </div>
      <div class="form-item">
        <label for="contact_no">Phone number</label>
        <input type="tel" name="contact_no" />
      </div>
      <div class="form-item">
        <label for="address">Address</label>
        <input type="text" name="address" />
      </div>
      <div class="form-item">
        <label for="password">Password</label>
        <div class="flex-col">
          <input type="password" name="currentPassword" placeholder="Current password" />
          <input type="password" name="password" placeholder="New password" />
        </div>
      </div>
      <div class="line"></div>
      <div class="btn-container">
        <button type="button" class="secondary-cta-btn" onclick="showPopup()">Cancel</button>
        <button type="submit" class="primary-cta-btn">Save changes</button>
      </div>
    </form>
  </div>

  <div class="edit-balance card popup">
    <h4>How much do you want to topup?</h4>
    <form id="editBalance" class="form-container">
      <input name="amount" type="number" />
      <div class="line"></div>
      <div class="btn-container">
        <button type="button" class="secondary-cta-btn" onclick="showPopupBalance()">Cancel</button>
        <button id="balancebtn" type="submit" class="primary-cta-btn">Top up</button>
      </div>
    </form>
  </div>

  <section class="profile-wrapper --first">
    <h1 class="title">Welcome back <?php echo ucfirst($_SESSION['username']) ?></h1>
    <div class="tab-menu flex-row align-center">
      <a href="?view=account" class="tab-link">Account details</a>
      <div class="vertical-line"></div>
      <a href="?view=orders" class="tab-link">Order history</a>
      <div class="vertical-line"></div>
      <p onclick="logout()" class="clickable" style="margin: 0;">Logout</p>
    </div>

    <div class="account-details view-section">
      <h3>Account Details</h3>
      <div id="account-card" class="card">
      </div>
      <button type="button" class="primary-cta-btn" style="margin-top: 15px;" onclick="showPopupBalance()">Top up balance</button>
    </div>

    <div class="order-history view-section" style="display: none;">
      <h3>Order History</h3>
      <div class="table-wrapper">
        <div class="table-header">
          <h4>Order Id</h4>
          <h4>Item Bought</h4>
          <h4>Amount</h4>
          <h4>Date</h4>
          <h4>Status</h4>
          <h4>Confirm order?</h4>
        </div>
        <div class="table-content">
        </div>
      </div>
  </section>
</main>

<script>
  const userId = <?php echo $_SESSION['user_id']; ?>;

  function formatCurrency(number) {
    const formattedNumber = new Intl.NumberFormat("en-US", {
      style: "currency",
      currency: "PHP",
    }).format(number);

    return formattedNumber;
  }

  function showView() {
    const urlParams = new URLSearchParams(window.location.search);
    const view = urlParams.get("view") || "account";

    document.querySelectorAll(".view-section").forEach((section) => {
      section.style.display = "none";
    });

    if (view === "orders") {
      document.querySelector(".order-history").style.display = "block";
    } else {
      document.querySelector(".account-details").style.display = "block";
    }

    document.querySelectorAll(".tab-link").forEach((link) => {
      link.classList.remove("active");
    });

    if (view === "orders") {
      document.querySelector('a[href="?view=orders"]').classList.add("active");
    } else {
      document.querySelector('a[href="?view=account"]').classList.add("active");
    }
  }

  // Function to load order history (payments)
  async function loadOrderHistory() {
    const table = document.querySelector(".table-content");

    table.innerHTML = "";

    const loaderDiv = document.createElement("div");
    loaderDiv.className = "loader-container";
    const loader = document.createElement("span");
    loader.className = "loader";
    table.appendChild(loaderDiv);
    loaderDiv.appendChild(loader);

    try {
      const response = await fetch(`/api/payments.php?user_id=${userId}`, {
        method: "GET",
      });

      const payments = await response.json();
      loaderDiv.remove();


      if (payments.length === 0) {
        const noProductsMessage = document.createElement("p");
        noProductsMessage.className = "no-products-message";
        noProductsMessage.textContent = "No orders found";
        table.appendChild(noProductsMessage);;
      } else {
        payments.payments.forEach((payment) => {
          const tableItem = document.createElement("div");
          tableItem.className = "table-item";

          tableItem.innerHTML = `
          <p>${payment.payment_id}</p>
          <div class="item-container">
            <div class="item-image">
              <img src="${payment.item_image}" alt="${payment.item_name}">
            </div>
            <div>
            <h4>${payment.item_name}</h4>
            </div>
          </div>
          <p>${formatCurrency(payment.amount)}</p>
          <p>${new Date(payment.payment_date).toLocaleDateString()}</p>
          <p>${payment.payment_status}</p>
          <button type="button" class="action-btn" onclick="markCompleted(${
            payment.payment_id
          })">
            Mark as complete
            <i class="ti ti-checks"></i>
          </button>
        `;

          table.appendChild(tableItem);
        });
      }
    } catch (error) {
      console.error("Error fetching payments:", error);
    }
  }

  window.onload = function() {
    showView();

    if (window.location.search.includes("view=orders")) {
      loadOrderHistory();
    }
  };
</script>