<?php

session_start();
require_once "../config/db.php";

// Handle different request methods
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
  // Check if the user is authenticated
  if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not authenticated."]);
    exit();
  }

  $user_id = $_SESSION['user_id'];
  $data = json_decode(file_get_contents("php://input"), true);

  // Validate required data
  if (!isset($data['order_ids']) || !is_array($data['order_ids']) || empty($data['order_ids'])) {
    echo json_encode(["error" => "Order IDs are required."]);
    exit();
  }

  $order_ids = $data['order_ids'];

  try {
    // Start transaction
    $pdo->beginTransaction();

    // Fetch user balance
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
      echo json_encode(["error" => "User not found."]);
      $pdo->rollBack();
      exit();
    }

    $balance = $user['balance'];
    $totalAmount = 0;

    foreach ($order_ids as $order_id) {
      // Fetch order details
      $stmt = $pdo->prepare("SELECT order_id, item_id, item_price, item_qty, order_status FROM orders 
                            WHERE user_id = :user_id AND order_id = :order_id");
      $stmt->bindParam(':user_id', $user_id);
      $stmt->bindParam(':order_id', $order_id);
      $stmt->execute();
      $order = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$order) {
        throw new Exception("Order not found or already processed: Order ID $order_id");
      }

      if ($order['order_status'] === 'complete') {
        throw new Exception("Order ID $order_id is already marked as complete.");
      }

      $orderTotal = $order['item_price'] * $order['item_qty'];

      // Check if balance is sufficient
      if ($balance < $orderTotal) {
        throw new Exception("Insufficient funds for order ID $order_id.");
      }

      // Deduct stock from the product
      $productQuery = $pdo->prepare("UPDATE items SET item_stock = item_stock - :item_qty 
                                    WHERE item_id = :item_id AND item_stock >= :item_qty");
      $productQuery->bindParam(':item_qty', $order['item_qty']);
      $productQuery->bindParam(':item_id', $order['item_id']);
      $productQuery->execute();

      if ($productQuery->rowCount() === 0) {
        throw new Exception("Insufficient stock for product ID: {$order['item_id']}");
      }

      // Deduct from balance and increment total amount
      $balance -= $orderTotal;
      $totalAmount += $orderTotal;

      // Create payment record
      $stmt = $pdo->prepare("INSERT INTO payments (user_id, order_id, amount, payment_status) 
                            VALUES (:user_id, :order_id, :amount, 'pending')");
      $stmt->bindParam(':user_id', $user_id);
      $stmt->bindParam(':order_id', $order_id);
      $stmt->bindParam(':amount', $orderTotal);
      $stmt->execute();

      // Update the order status
      $stmt = $pdo->prepare("UPDATE orders SET order_status = 'pending' 
                            WHERE user_id = :user_id AND order_id = :order_id");
      $stmt->bindParam(':user_id', $user_id);
      $stmt->bindParam(':order_id', $order_id);
      $stmt->execute();
    }


    // Update user's balance
    $stmt = $pdo->prepare("UPDATE users SET balance = :balance WHERE user_id = :user_id");
    $stmt->bindParam(':balance', $balance);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    // Commit transaction
    $pdo->commit();

    echo json_encode([
      "success" => "Checkout completed successfully.",
      "total_amount" => $totalAmount,
      "remaining_balance" => $balance
    ]);
  } catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
  } catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["error" => $e->getMessage()]);
  }
}


// Read payments (GET)
elseif ($method == 'GET') {
  $payments = [];
  if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    try {
      $query = "SELECT * FROM payments WHERE user_id = :user_id ORDER BY payment_date DESC";
      $stmt = $pdo->prepare($query);
      $stmt->bindParam(':user_id', $user_id);
      $stmt->execute();

      $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
      echo json_encode($payments);
    } catch (PDOException $e) {
      echo json_encode(["error" => "Error: " . $e->getMessage()]);
    }
  } elseif (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    try {
      $query = "SELECT * FROM payments WHERE order_id = :order_id ORDER BY payment_date DESC";
      $stmt = $pdo->prepare($query);
      $stmt->bindParam(':order_id', $order_id);
      $stmt->execute();

      $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
      echo json_encode($payments);
    } catch (PDOException $e) {
      echo json_encode(["error" => "Error: " . $e->getMessage()]);
    }
  } else {
    echo json_encode(["message" => "No user_id or order_id provided"]);
  }
}

// Update payment status (PUT)
elseif ($method == 'PUT') {
  parse_str(file_get_contents("php://input"), $put_vars);

  if (isset($put_vars['payment_id'], $put_vars['payment_status'])) {
    $payment_id = $put_vars['payment_id'];
    $payment_status = $put_vars['payment_status'];

    try {
      $query = "UPDATE payments SET payment_status = :payment_status WHERE payment_id = :payment_id";
      $stmt = $pdo->prepare($query);
      $stmt->bindParam(':payment_status', $payment_status);
      $stmt->bindParam(':payment_id', $payment_id);
      $stmt->execute();

      echo json_encode(["message" => "Payment status updated successfully!"]);
    } catch (PDOException $e) {
      echo json_encode(["error" => "Error: " . $e->getMessage()]);
    }
  } else {
    echo json_encode(["error" => "Payment ID or status missing"]);
  }
}

// Delete a payment (DELETE)
elseif ($method == 'DELETE') {
  parse_str(file_get_contents("php://input"), $delete_vars);

  if (isset($delete_vars['payment_id'])) {
    $payment_id = $delete_vars['payment_id'];

    try {
      $query = "DELETE FROM payments WHERE payment_id = :payment_id";
      $stmt = $pdo->prepare($query);
      $stmt->bindParam(':payment_id', $payment_id);
      $stmt->execute();

      echo json_encode(["message" => "Payment deleted successfully!"]);
    } catch (PDOException $e) {
      echo json_encode(["error" => "Error: " . $e->getMessage()]);
    }
  } else {
    echo json_encode(["error" => "Payment ID missing"]);
  }
} else {
  echo json_encode(["message" => "Invalid request method"]);
}
