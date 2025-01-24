<?php

session_start();
require_once "../config/db.php";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not authenticated."]);
    exit();
  }

  $user_id = $_SESSION['user_id'];
  $data = json_decode(file_get_contents("php://input"), true);

  if (!isset($data['order_ids']) || !is_array($data['order_ids']) || empty($data['order_ids'])) {
    echo json_encode(["error" => "Order IDs are required."]);
    exit();
  }

  $order_ids = $data['order_ids'];

  try {
    $pdo->beginTransaction();

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
    $orderDetails = [];

    foreach ($order_ids as $order_id) {
      $stmt = $pdo->prepare("SELECT order_id, item_id, item_price, item_qty, order_status, item_name FROM orders 
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

      if ($balance < $orderTotal) {
        throw new Exception("Insufficient funds for order ID $order_id.");
      }

      $productQuery = $pdo->prepare("UPDATE items SET item_stock = item_stock - :item_qty 
                                    WHERE item_id = :item_id AND item_stock >= :item_qty");
      $productQuery->bindParam(':item_qty', $order['item_qty']);
      $productQuery->bindParam(':item_id', $order['item_id']);
      $productQuery->execute();

      if ($productQuery->rowCount() === 0) {
        throw new Exception("Insufficient stock for product ID: {$order['item_id']}");
      }

      $balance -= $orderTotal;
      $totalAmount += $orderTotal;

      $stmt = $pdo->prepare("INSERT INTO payments (user_id, order_id, amount, payment_status) 
                            VALUES (:user_id, :order_id, :amount, 'pending')");
      $stmt->bindParam(':user_id', $user_id);
      $stmt->bindParam(':order_id', $order_id);
      $stmt->bindParam(':amount', $orderTotal);
      $stmt->execute();

      $stmt = $pdo->prepare("UPDATE orders SET order_status = 'pending' 
                            WHERE user_id = :user_id AND order_id = :order_id");
      $stmt->bindParam(':user_id', $user_id);
      $stmt->bindParam(':order_id', $order_id);
      $stmt->execute();

      // Collect order details for response
      $orderDetails[] = [
        "order_id" => $order_id,
        "item_name" => $order['item_name'],
        "item_qty" => $order['item_qty'],
        "status" => 'pending',
        "total_amount" => $orderTotal
      ];
    }

    $stmt = $pdo->prepare("UPDATE users SET balance = :balance WHERE user_id = :user_id");
    $stmt->bindParam(':balance', $balance);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    $pdo->commit();

    echo json_encode([
      "success" => "Checkout completed successfully.",
      "orders" => $orderDetails,
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


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $payments = [];

  if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    try {
      $query = "SELECT p.*, o.order_id, o.item_id, o.item_qty, o.item_name, o.item_price, i.item_image
                FROM payments p
                JOIN orders o ON p.order_id = o.order_id
                JOIN items i ON o.item_id = i.item_id
                WHERE p.user_id = :user_id
                ORDER BY p.payment_date DESC";
      $stmt = $pdo->prepare($query);
      $stmt->bindParam(':user_id', $user_id);
      $stmt->execute();

      $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
      echo json_encode(["payments" => $payments]);
    } catch (PDOException $e) {
      echo json_encode(["error" => "Error: " . $e->getMessage()]);
    }
  } elseif (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    try {
      $query = "SELECT p.*, o.order_id, o.item_id, o.item_qty, o.item_name, o.item_price, i.item_image
                FROM payments p
                JOIN orders o ON p.order_id = o.order_id
                JOIN items i ON o.item_id = i.item_id
                WHERE p.order_id = :order_id
                ORDER BY p.payment_date DESC";
      $stmt = $pdo->prepare($query);
      $stmt->bindParam(':order_id', $order_id);
      $stmt->execute();

      $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
      echo json_encode(["payments" => $payments]);
    } catch (PDOException $e) {
      echo json_encode(["error" => "Error: " . $e->getMessage()]);
    }
  } else {
    try {
      $query = "SELECT p.*, o.order_id, o.item_id, o.item_qty, o.item_name, o.item_price, i.item_image
                FROM payments p
                JOIN orders o ON p.order_id = o.order_id
                JOIN items i ON o.item_id = i.item_id
                ORDER BY p.payment_date DESC";
      $stmt = $pdo->prepare($query);
      $stmt->execute();

      $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
      echo json_encode(["payments" => $payments]);
    } catch (PDOException $e) {
      echo json_encode(["error" => "Error: " . $e->getMessage()]);
    }
  }
}


if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
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

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
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
}
