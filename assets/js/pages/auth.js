if (location.pathname === "/login") {
  document.getElementById("loginForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const submitBtn = document.querySelector('button[type="submit"]');

    const loader = document.createElement("span");
    loader.className = "loader";
    submitBtn.appendChild(loader);
    submitBtn.disabled = true;

    const username = document.querySelector('input[name="username"]').value;
    const password = document.querySelector('input[name="password"]').value;

    postData("/api/auth.php", "login", { username, password })
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
      })
      .finally(() => {
        loader.remove();
        submitBtn.disabled = false;
      });
  });
}

if (location.pathname === "/register") {
  document.getElementById("registerForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const submitBtn = document.querySelector('button[type="submit"]');

    const loader = document.createElement("span");
    loader.className = "loader";
    submitBtn.appendChild(loader);
    submitBtn.disabled = true;

    const name = document.querySelector('input[name="name"]').value;
    const username = document.querySelector('input[name="username"]').value;
    const password = document.querySelector('input[name="password"]').value;
    const gender = document.querySelector('select[name="gender"]').value;
    const contact_no = document.querySelector('input[name="contact_no"]').value;
    const address = document.querySelector('input[name="address"]').value;

    postData("/api/auth.php", "register", {
      name,
      username,
      password,
      gender,
      contact_no,
      address,
    })
      .then((result) => {
        if (result.error) {
          alert(result.error);
        } else if (result.message) {
          alert(result.message);
          setTimeout(() => {
            window.location.href = "/login";
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
  });
}
