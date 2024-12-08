<?php

session_start();

require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === "GET") {
  if (isset($_SESSION['user_id'])) {
    try {
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = :user_id LIMIT 1');
      $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
      $stmt->execute();

      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($user) {
        echo json_encode(["user" => $user]);
      } else {
        echo json_encode(["error" => "User data not found"]);
      }
    } catch (PDOException $e) {
      echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    }
  } else {
    echo json_encode(["error" => "User not logged in"]);
    exit();
  }
}

if ($_SERVER['REQUEST_METHOD'] === "PUT") {
  $data = json_decode(file_get_contents("php://input"), true);

  $user_id = $data["user_id"];

  $fieldsToUpdate = [];
  $params = [];

  foreach (["name", "username", "contact_no", "address", "password"] as $field) {
    if (isset($data[$field]) && $data[$field] !== '') {
      if ($field === "password") {
        $data[$field] = password_hash($data[$field], PASSWORD_DEFAULT);
      }

      $fieldsToUpdate[] = "$field = ?";
      $params[] = $data[$field];
    }
  }

  if (!empty($fieldsToUpdate)) {
    $sql = "UPDATE users SET " . implode(", ", $fieldsToUpdate) . " WHERE user_id = ?";
    $params[] = $user_id;

    $stmt = $pdo->prepare($sql);

    if ($stmt->execute($params)) {
      echo json_encode(["message" => "Successfully updated account."]);
    } else {
      echo json_encode(["error" => "An error occurred while updating your account."]);
    }
  }
}
