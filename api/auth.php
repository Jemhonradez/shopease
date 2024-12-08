<?php


session_start();

require_once "../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $data['action'] ?? null;

  if (!$action) {
    echo json_encode(["error" => "Action not specified."]);
    exit;
  }

  if ($action === 'login') {
    if (empty($data['username']) || empty($data['password'])) {
      echo json_encode(["error" => "Username and password are required."]);
      exit;
    }

    $username = $data['username'];
    $password = $data['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['user_id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['user_type'] = $user['user_type'];

      echo json_encode(["message" => "Login successful", "user" => $user]);
    } else {
      echo json_encode(["error" => "Invalid username or password."]);
    }
  }

  if ($action === 'register') {
    if (empty($data['name']) || empty($data['username']) || empty($data['password']) || empty($data['gender'])) {
      echo json_encode(["error" => "Missing credentials."]);
      exit;
    }

    $name = $data['name'];
    $username = $data['username'];
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $gender = $data['gender'];
    $contact_no = $data['contact_no'];
    $address = $data['address'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
      echo json_encode(["error" => "Username already exists"]);
      exit;
    }

    $stmt = $pdo->prepare("INSERT INTO users (username, password, name, gender, contact_no, address) VALUES (:username, :password, :name, :gender, :contact_no, :address)");

    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':contact_no', $contact_no);
    $stmt->bindParam(':address', $address);

    if ($stmt->execute()) {
      echo json_encode(["message" => "Successfully created your account. You may now login."]);
    } else {
      echo json_encode(["error" => "An error occurred while creating your account."]);
    }
  }
}
