<?php
include_once "../config/db.php";


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $sql = "SELECT * FROM orders";
  $result = $pdo->query($sql);

  $orders = $result->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($orders);
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
  $data = json_decode(file_get_contents("php://input"), true);

  $item_id = $data['item_id'];
  $item_qty = $data['item_qty'];
  $cus_id = $data['cus_id'];

  $sql = "INSERT INTO orders (item_id, item_qty, cus_id) VALUES (?, ?, ?)";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$item_id, $item_qty, $cus_id]);

  echo json_encode(['message' => 'Order created successfully!']);
}
