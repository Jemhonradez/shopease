function profileInit() {
  console.log("Profile script loaded");
  loadProfileData();
}

let user;

function loadProfileData() {
  fetch("../api/getUserData.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.error) {
        alert(data.error);
        return;
      }

      user = data.user;

      document.querySelector('.desc[name="name"]').textContent = user.name;
      document.querySelector('.desc[name="username"]').textContent = user.username;
      document.querySelector('.desc[name="contact_no"]').textContent = user.contact_no;
      document.querySelector('.desc[name="address"]').textContent = user.address;

      document.querySelector('input[name="name"]').value = user.name;
      document.querySelector('input[name="username"]').value = user.username;
      document.querySelector('input[name="contact_no"]').value = user.contact_no;
      document.querySelector('input[name="address"]').value = user.address;
    })
    .catch((error) => {
      console.error("Error fetching profile data:", error);
      alert("There was an error loading your profile data");
    });
}

let isOpen = true;
function showPopup() {
  document.querySelector('input[name="name"]').value = user.name;
  document.querySelector('input[name="username"]').value = user.username;
  document.querySelector('input[name="contact_no"]').value = user.contact_no;
  document.querySelector('input[name="address"]').value = user.address;

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

document.getElementById("editForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  const data = {
    name: formData.get("name"),
    username: formData.get("username"),
    contact_no: formData.get("contact_no"),
    address: formData.get("address"),
    password: formData.get("password"),
    user_id: formData.get("user_id"),
  };

  putData("/api/users.php", data)
    .then((result) => {
      if (result.error) {
        alert(result.error);
      } else if (result.message) {
        alert(result.message);
        setTimeout(() => {
          location.reload()
        }, 500);
      }
    })
    .catch((error) => {
      alert(error.message );
    });
});
