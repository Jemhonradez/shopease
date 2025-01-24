<?php
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['payment_id'])) {
    $payment_id = $_POST['payment_id'];

    try {
      $pdo->beginTransaction();

      $updateQuery = "UPDATE payments SET payment_status = 'completed' WHERE payment_id = :payment_id";
      $stmt = $pdo->prepare($updateQuery);
      $stmt->bindParam(':payment_id', $payment_id, PDO::PARAM_INT);
      $stmt->execute();

      $updateOrderQuery = "UPDATE orders SET order_status = 'completed' WHERE order_id IN (SELECT order_id FROM payments WHERE payment_id = :payment_id)";
      $stmt = $pdo->prepare($updateOrderQuery);
      $stmt->bindParam(':payment_id', $payment_id, PDO::PARAM_INT);
      $stmt->execute();

      $pdo->commit();

      echo json_encode([
        "success" => true,
        "message" => "Order marked as complete"
      ]);
    } catch (PDOException $e) {
      $pdo->rollBack();

      echo json_encode([
        "error" => "Error: " . $e->getMessage()
      ]);
    }
  } else {
    echo json_encode([
      "error" => "No payment_id provided"
    ]);
  }
} else {
  echo json_encode([
    "error" => "Invalid request method"
  ]);
}
