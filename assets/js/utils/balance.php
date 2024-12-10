<?php

require_once '../../../config/db.php';

// Ensure user_id is passed and is a valid integer
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
  echo json_encode(["error" => "Invalid or missing user_id parameter."]);
  exit;
}

$user_id = (int)$_GET['user_id'];

try {
  // Prepare and execute the query
  $stmt = $pdo->prepare("SELECT balance FROM users WHERE user_id = :user_id");
  $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
  $stmt->execute();

  // Check if the user exists and return the balance
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($result) {
    echo json_encode(["balance" => $result['balance']]);
  } else {
    echo json_encode(["error" => "User not found."]);
  }
} catch (PDOException $e) {
  // Handle database connection errors
  echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
