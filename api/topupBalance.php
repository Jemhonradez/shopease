<?php

session_start();
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
  if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $data = json_decode(file_get_contents("php://input"), true);

    $amount = isset($data['amount']) ? $data['amount'] : 0;

    if (is_numeric($amount) && $amount > 0) {
      try {
        $stmt = $pdo->prepare('SELECT balance FROM users WHERE user_id = :user_id LIMIT 1');
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
          $new_balance = $user['balance'] + $amount;

          $stmt = $pdo->prepare('UPDATE users SET balance = :balance WHERE user_id = :user_id');
          $stmt->bindParam(':balance', $new_balance, PDO::PARAM_STR);
          $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

          if ($stmt->execute()) {
            echo json_encode(["message" => "Balance updated successfully.", "new_balance" => $new_balance]);
          } else {
            echo json_encode(["error" => "Failed to update balance."]);
          }
        } else {
          echo json_encode(["error" => "User not found."]);
        }
      } catch (PDOException $e) {
        echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
      }
    } else {
      echo json_encode(["error" => "Invalid amount. Please enter a positive number."]);
    }
  } else {
    echo json_encode(["error" => "User not logged in"]);
  }
}
