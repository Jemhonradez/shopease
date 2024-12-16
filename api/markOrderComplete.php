<?php
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Check if payment_id is provided in the POST request
  if (isset($_POST['payment_id'])) {
    $payment_id = $_POST['payment_id'];

    try {
      // Begin a transaction to ensure atomicity
      $pdo->beginTransaction();

      // Query to update payment status to 'completed' based on payment_id
      $updateQuery = "UPDATE payments SET payment_status = 'completed' WHERE payment_id = :payment_id";
      $stmt = $pdo->prepare($updateQuery);
      $stmt->bindParam(':payment_id', $payment_id, PDO::PARAM_INT);
      $stmt->execute();

      // Optionally update the order status if needed
      $updateOrderQuery = "UPDATE orders SET order_status = 'completed' WHERE order_id IN (SELECT order_id FROM payments WHERE payment_id = :payment_id)";
      $stmt = $pdo->prepare($updateOrderQuery);
      $stmt->bindParam(':payment_id', $payment_id, PDO::PARAM_INT);
      $stmt->execute();

      // Commit the transaction
      $pdo->commit();

      // Return a JSON response
      echo json_encode([
        "success" => true,
        "message" => "Order marked as complete"
      ]);
    } catch (PDOException $e) {
      // Rollback transaction in case of error
      $pdo->rollBack();

      // Return a JSON response for error
      echo json_encode([
        "error" => "Error: " . $e->getMessage()
      ]);
    }
  } else {
    // Return a JSON response if no payment_id is provided
    echo json_encode([
      "error" => "No payment_id provided"
    ]);
  }
} else {
  // Return a JSON response if the request method is not POST
  echo json_encode([
    "error" => "Invalid request method"
  ]);
}
