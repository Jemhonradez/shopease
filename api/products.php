<?php

require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === "GET") {
  try {
    $sql = "SELECT * FROM items ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    $items = $stmt->fetchAll();

    echo json_encode(["items" => $items]);
  } catch (PDOException $e) {
    echo json_encode(["error" => "Error:" . $e->getMessage()]);
  }
}


if ($_SERVER['REQUEST_METHOD'] === "POST") {
  $data = json_decode(file_get_contents("php://input"), true);

  if (
    empty($data['item_name']) ||
    empty($data['item_image']) ||
    empty($data['item_desc']) ||
    empty($data['item_price']) ||
    empty($data['item_stock'])
  ) {
    echo json_encode(["error" => "Missing required fields"]);
    exit();
  }

  try {
    $item_name = $data['item_name'];
    $item_image = $data['item_image'];
    $item_desc = $data['item_desc'];
    $item_price = floatval($data['item_price']);
    $item_stock = intval($data['item_stock']);

    $sql = "INSERT INTO items (item_name, item_image, item_desc, item_price, item_stock) VALUES (:item_name, :item_image, :item_desc, :item_price, :item_stock)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":item_name", $item_name);
    $stmt->bindParam(":item_image", $item_image);
    $stmt->bindParam(":item_desc", $item_desc);
    $stmt->bindParam(":item_price", $item_price);
    $stmt->bindParam(":item_stock", $item_stock);

    if ($stmt->execute()) {
      echo json_encode(["message" => "Item successfully added"]);
    } else {
      echo json_encode(["error" => "Could not add product"]);
    }
  } catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
  }
}
