<?php
include_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];

        try {
            $sql = "
            SELECT orders.order_id, orders.item_id, orders.item_qty, orders.order_status, 
                    items.item_name, items.item_image, items.item_price
            FROM orders
            JOIN items ON orders.item_id = items.item_id
            WHERE orders.user_id = :user_id
        ";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($orders) {
                echo json_encode(["orders" => $orders]);
            } else {
                echo json_encode(["message" => "No orders found for this user"]);
            }
        } catch (PDOException $e) {
            echo json_encode(["error" => "Database error: " . $e->getMessage()]);
        }
    } else {
        try {
            $sql = "
            SELECT orders.order_id, orders.item_id, orders.user_id, orders.item_qty, orders.order_status, 
                    items.item_name, items.item_image, items.item_price
            FROM orders
            JOIN items ON orders.item_id = items.item_id
        ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($orders) {
                echo json_encode(["orders" => $orders]);
            } else {
                echo json_encode(["message" => "No orders found"]);
            }
        } catch (PDOException $e) {
            echo json_encode(["error" => "Database error: " . $e->getMessage()]);
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['item_id']) || empty($data['user_id']) || empty($data['item_qty'])) {
        echo json_encode(["error" => "Missing required fields"]);
        exit();
    }

    $item_id = $data['item_id'];
    $user_id = $data['user_id'];
    $item_name = $data['item_name'];
    $item_price = intval($data['item_price']);
    $item_qty = intval($data['item_qty']);
    $order_status = 'processing';

    try {
        $sql = "INSERT INTO orders (item_price, item_id, user_id, item_name, item_qty, order_status) 
                VALUES (:item_price, :item_id, :user_id, :item_name, :item_qty, :order_status)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':item_price', $item_price);
        $stmt->bindParam(':item_id', $item_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':item_name', $item_name);
        $stmt->bindParam(':item_qty', $item_qty);
        $stmt->bindParam(':order_status', $order_status);

        if ($stmt->execute()) {
            echo json_encode(["success" => "Item added to cart"]);
        } else {
            echo json_encode(["error" => "Failed to add item to cart"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Error: " . $e->getMessage()]);
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $data);

    if (empty($data['order_id']) || empty($data['item_qty'])) {
        echo json_encode(["error" => "Missing required fields"]);
        exit();
    }

    $order_id = $data['order_id'];
    $item_qty = intval($data['item_qty']);

    try {
        $sql = "UPDATE orders SET item_qty = :item_qty WHERE order_id = :order_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':item_qty', $item_qty);
        $stmt->bindParam(':order_id', $order_id);

        if ($stmt->execute()) {
            echo json_encode(["success" => "Order quantity updated"]);
        } else {
            echo json_encode(["error" => "Failed to update order"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['order_id'])) {
        $order_id = $data['order_id'];

        if (empty($order_id)) {
            echo json_encode(["error" => "Missing required fields"]);
            exit();
        }

        try {
            $sql = "DELETE FROM orders WHERE order_id = :order_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':order_id', $order_id);

            if ($stmt->execute()) {
                echo json_encode(["success" => "Order removed from cart"]);
            } else {
                echo json_encode(["error" => "Failed to remove order"]);
            }
        } catch (PDOException $e) {
            echo json_encode(["error" => "Database error: " . $e->getMessage()]);
        }
    } elseif (isset($data['user_id'])) {
        $user_id = (int) $data['user_id'];

        if (empty($user_id)) {
            echo json_encode(["error" => "Missing required fields"]);
            exit();
        }

        try {
            $sql = "DELETE FROM orders WHERE user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $user_id);

            if ($stmt->execute()) {
                echo json_encode(["success" => "All items removed from cart"]);
            } else {
                echo json_encode(["error" => "Failed to remove items"]);
            }
        } catch (PDOException $e) {
            echo json_encode(["error" => "Database error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["error" => "Missing required userId or itemId"]);
    }
}
