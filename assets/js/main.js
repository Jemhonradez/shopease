document.addEventListener("DOMContentLoaded", () => {
  const page = document.body.getAttribute("data-page");
  if (page && typeof window[page + "Init"] === "function") {
    window[page + "Init"]();
  }

  if (window.location.pathname === "/login" || window.location.pathname === "/register") {
    const script = document.createElement("script");
    script.src = "/assets/js/pages/auth.js";
    document.body.appendChild(script);
  }
});

async function uploadFile(file) {
  const { data, error } = await supabaseClient.storage
    .from("items")
    .upload(`${file.name}`, file);

  if (error) {
    console.error("Upload failed:", error.message);
    return;
  }
  console.log("File uploaded successfully:", data);
}

async function getFileUrl(filePath) {
  const { data, error } = await supabaseClient.storage
    .from("items")
    .getPublicUrl(filePath);

  if (error) {
    console.error("Error fetching file URL:", error.message);
    return;
  }

  return data.publicUrl;
}

async function deleteFile(filePath) {
  const { error } = await supabaseClient.storage.from("items").remove([filePath]);

  if (error) {
    console.error("Error deleting file:", error.message);
    return;
  }
  console.log("File deleted successfully");
}

function logout() {
  window.location.href = "/assets/js/utils/logout.php";
}

async function getData(filepath) {
  const response = await fetch(filepath, {
    method: "GET",
  });
  return await response.json();
}

async function postData(filepath, action, data) {
  const response = await fetch(filepath, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      action: action,
      ...data,
    }),
  });
  return await response.json();
}

async function putData(filepath, data) {
  const response = await fetch(filepath, {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  });
  return await response.json();
}
