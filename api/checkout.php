<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["error" => "User not authenticated."]);
  exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);

$order_ids = isset($data['order_ids']) ? $data['order_ids'] : [];

if (empty($order_ids)) {
  echo json_encode(["error" => "Order IDs are required."]);
  exit();
}

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

  foreach ($order_ids as $order_id) {
    $stmt = $pdo->prepare("SELECT order_id, item_id, item_price, item_qty FROM orders WHERE user_id = :user_id AND order_id = :order_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
      echo json_encode(["error" => "Order not found or already processed: Order ID $order_id"]);
      $pdo->rollBack();
      exit();
    }

    $orderTotal = $order['item_price'] * $order['item_qty'];

    if ($balance < $orderTotal) {
      echo json_encode(["error" => "Insufficient funds for order ID $order_id."]);
      $pdo->rollBack();
      exit();
    }

    $productQuery = $pdo->prepare("UPDATE items SET item_stock = item_stock - :item_qty WHERE item_id = :item_id AND item_stock >= :item_qty");
    $productQuery->bindParam(':item_qty', $order['item_qty']);
    $productQuery->bindParam(':item_id', $order['item_id']);
    $productQuery->execute();

    if ($productQuery->rowCount() === 0) {
      throw new Exception("Insufficient stock for product ID: {$order['item_id']}");
    }

    $balance -= $orderTotal;

    $stmt = $pdo->prepare("INSERT INTO payments (user_id, order_id, amount) VALUES (:user_id, :order_id, :amount)");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->bindParam(':amount', $orderTotal);
    $stmt->execute();

    $stmt = $pdo->prepare("DELETE FROM orders WHERE user_id = :user_id AND order_id = :order_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
  }
  $stmt = $pdo->prepare("UPDATE users SET balance = :balance WHERE user_id = :user_id");
  $stmt->bindParam(':balance', $balance);
  $stmt->bindParam(':user_id', $user_id);
  $stmt->execute();

  $pdo->commit();

  echo json_encode(["success" => "Checkout completed successfully."]);
} catch (PDOException $e) {
  $pdo->rollBack();
  echo json_encode(["error" => "Database error: " . $e->getMessage()]);
} catch (Exception $e) {
  $pdo->rollBack();
  echo json_encode(["error" => $e->getMessage()]);
}
