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
    </div>

    <div class="order-history view-section" style="display: none;">
      <h3>Order History</h3>
      <div id="history-card" class="card">
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
    const card = document.getElementById("history-card");

    const loaderDiv = document.createElement("div");
    loaderDiv.className = "loader-container";
    const loader = document.createElement("span");
    loader.className = "loader";

    // Clear the previous cart content and show loader
    card.innerHTML = "";
    card.appendChild(loaderDiv);
    loaderDiv.appendChild(loader);

    try {
      const response = await fetch(`/api/payments.php?user_id=${userId}`, {
        method: "GET",
      });

      const payments = await response.json();

      const orderHistorySection = document.querySelector(".order-history .card");
      orderHistorySection.innerHTML = "";

      loaderDiv.remove();

      if (payments.length === 0) {
        orderHistorySection.innerHTML = "<p>No payments found.</p>";
      } else {
        payments.payments.forEach((payment) => {
          const paymentElement = document.createElement("div");
          paymentElement.classList.add("payment-item");

          paymentElement.innerHTML = `
          <p><strong>Payment ID:</strong> ${payment.payment_id}</p>
          <p><strong>Order ID:</strong> ${payment.order_id}</p>
          <p><strong>Amount:</strong> ${formatCurrency(payment.amount)}</p>
          <p><strong>Date:</strong> ${payment.payment_date}</p>
          <p><strong>Status:</strong> ${payment.payment_status}</p>
        `;

          orderHistorySection.appendChild(paymentElement);
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