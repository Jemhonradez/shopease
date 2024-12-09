<?php
session_start();
require_once '../../../config/db.php';

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["error" => "User not authenticated."]);
  exit();
}

$user_id = $_SESSION['user_id'];

try {
  $stmt = $pdo->prepare("SELECT balance FROM users WHERE user_id = :user_id");
  $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($result) {
    echo json_encode(["balance" => $result['balance']]);
  } else {
    echo json_encode(["error" => "User not found."]);
  }
} catch (PDOException $e) {
  echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
