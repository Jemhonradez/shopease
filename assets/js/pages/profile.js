function profileInit() {
  console.log("Profile script loaded");
  loadProfileData();
}

let user;

function loadProfileData() {
  const accountDetails = document.getElementById("account-card");

  const loaderDiv = document.createElement("div");
  loaderDiv.className = "loader-container";
  const loader = document.createElement("span");
  loader.className = "loader";

  accountDetails.innerHTML = "";
  accountDetails.appendChild(loaderDiv);
  loaderDiv.appendChild(loader);

  fetch("../api/getUserData.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.error) {
        alert(data.error);
        return;
      }

      user = data.user;

      loaderDiv.remove();

      accountDetails.innerHTML = `
        <div class="space-btwn">
          <h4>Profile & Security</h4>
          <p class="clickable" onclick="showPopup()">Edit</p>
        </div>
        <div class="item">
          <p class="title">Full Name</p>
          <p class="desc">${user.name}</p>
        </div>
        <div class="item">
          <p class="title">Balance</p>
          <p class="desc">${formatCurrency(user.balance)}</p>
        </div>
        <div class="item">
          <p class="title">Username</p>
          <p class="desc">${user.username}</p>
        </div>
        <div class="item">
          <p class="title">Phone number</p>
          <p class="desc">${user.contact_no}</p>
        </div>
        <div class="item">
          <p class="title">Address</p>
          <p class="desc">${user.address}</p>
        </div>
        <div class="item">
          <p class="title">Password</p>
          <p class="desc">*********</p>
        </div>
      `;

      document.querySelector('input[name="name"]').value = user.name;
      document.querySelector('input[name="username"]').value = user.username;
      document.querySelector('input[name="contact_no"]').value = user.contact_no;
      document.querySelector('input[name="address"]').value = user.address;
    })
    .catch((error) => {
      console.error("Error fetching profile data:", error);
    });
}

let isOpen = true;
function showPopup() {
  document.querySelector('input[name="name"]').value = user.name;
  document.querySelector('input[name="username"]').value = user.username;
  document.querySelector('input[name="contact_no"]').value = user.contact_no;
  document.querySelector('input[name="address"]').value = user.address;
  document.querySelector('input[name="currentPassword"]').value = "";
  document.querySelector('input[name="password"]').value = "";

  const popup = document.querySelector(".edit-details");
  setTimeout(() => {
    if (isOpen) {
      popup.classList.add("show");
    } else {
      popup.classList.remove("show");
    }
    isOpen = !isOpen;
  }, 50);
}

let isOpenBalance = true;
function showPopupBalance() {
  const popup = document.querySelector(".edit-balance");
  setTimeout(() => {
    if (isOpenBalance) {
      popup.classList.add("show");
    } else {
      popup.classList.remove("show");
    }
    isOpenBalance = !isOpenBalance;
  }, 50);
}

document.getElementById("editForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const submitBtn = document.querySelector("button[type='submit']");

  const loader = document.createElement("span");
  loader.className = "loader";
  submitBtn.appendChild(loader);
  submitBtn.disabled = true;

  const formData = new FormData(this);

  const data = {
    name: formData.get("name"),
    username: formData.get("username"),
    contact_no: formData.get("contact_no"),
    address: formData.get("address"),
    currentPassword: formData.get("currentPassword"),
    password: formData.get("password"),
    user_id: formData.get("user_id"),
  };

  putData("/api/users.php", data)
    .then((result) => {
      loader.remove();
      submitBtn.disabled = false;
      if (result.error) {
        alert(result.error);
      } else if (result.message) {
        alert(result.message);
        setTimeout(() => {
          location.reload();
        }, 500);
      }
    })
    .catch((error) => {
      alert(error.message);
    });
});

document.getElementById("editBalance").addEventListener("submit", function (e) {
  e.preventDefault();

  const submitBtn = document.getElementById("balancebtn");

  const loader = document.createElement("span");
  loader.className = "loader";
  submitBtn.appendChild(loader);
  submitBtn.disabled = true;

  const amount = document.querySelector("input[name='amount']").value;

  const data = {
    amount: parseFloat(amount),
  };

  postData("/api/topupBalance.php", null, data)
    .then((result) => {
      loader.remove();
      submitBtn.disabled = false;
      if (result.error) {
        alert(result.error);
      } else if (result.message) {
        alert(result.message);
        setTimeout(() => {
          location.reload();
        }, 500);
      }
    })
    .catch((error) => {
      loader.remove();
      submitBtn.disabled = false;
      alert(error.message);
    });
});

async function markCompleted(paymentId) {
  postData("/api/markOrderComplete.php", null, paymentId)
    .then((result) => {
      if (result.error) {
        alert(result.error);
      } else if (result.message) {
        alert(result.message);
        setTimeout(() => {
          location.reload();
        }, 500);
      }
    })
    .catch((error) => {
      alert(error.message);
    });
}

async function markCompleted(paymentId) {
  try {
    const response = await fetch("/api/markOrderComplete.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        payment_id: paymentId,
      }),
    });

    const result = await response.json();

    if (result.success) {
      alert(result.message);
      setTimeout(() => {
        location.reload();
      }, 500);
    } else {
      alert(result.error);
    }
  } catch (error) {
    alert("An error occurred: " + error.message);
  }
}
