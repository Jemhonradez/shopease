<?php

session_start();

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["error" => "User not logged in"]);
  exit();
}

require_once '../config/db.php';

try {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id LIMIT 1");
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
