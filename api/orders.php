<?php
include_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['user_id'])) {
        echo json_encode(["error" => "Missing user_id parameter"]);
        exit();
    }

    $user_id = $_GET['user_id'];

    try {
        // Fetch all orders for the given user_id, joining with the items table to include item details (like item_image)
        $sql = "
            SELECT orders.order_id, orders.item_id, orders.item_qty, items.item_name, items.item_image, items.item_price
            FROM orders
            JOIN items ON orders.item_id = items.item_id
            WHERE orders.user_id = :user_id
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $orders = $stmt->fetchAll();

        if ($orders) {
            echo json_encode(["orders" => $orders]);
        } else {
            echo json_encode(["message" => "No orders found for this user"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $data = json_decode(file_get_contents("php://input"), true);

    // Check if required fields are provided
    if (empty($data['item_id']) || empty($data['user_id']) || empty($data['item_qty'])) {
        echo json_encode(["error" => "Missing required fields"]);
        exit();
    }

    // Get values from request
    $item_id = $data['item_id'];
    $user_id = $data['user_id'];
    $item_price = intval($data['item_price']);
    $item_qty = intval($data['item_qty']);

    try {
        // Insert the order into the orders table
        $sql = "INSERT INTO orders (item_price, item_id, user_id, item_qty) VALUES (:item_price, :item_id, :user_id, :item_qty)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':item_price', $item_price);
        $stmt->bindParam(':item_id', $item_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':item_qty', $item_qty);

        if ($stmt->execute()) {
            echo json_encode(["success" => "Item added to cart"]);
        } else {
            echo json_encode(["error" => "Failed to add item to cart"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Error: " . $e->getMessage()]);
    }
}


// PUT request - Update an existing order (e.g., change item quantity)
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Get the raw POST data
    parse_str(file_get_contents("php://input"), $data);

    // Validate input
    if (empty($data['order_id']) || empty($data['item_qty'])) {
        echo json_encode(["error" => "Missing required fields"]);
        exit();
    }

    $order_id = $data['order_id'];
    $item_qty = intval($data['item_qty']);

    try {
        // Update the item quantity in the orders table
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


// DELETE request - Remove an order from the cart
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Get the raw POST data
    $data = json_decode(file_get_contents("php://input"), true);

    // Check if we're removing a single order or all orders
    if (isset($data['order_id'])) {
        // Deleting a single order
        $order_id = $data['order_id'];

        if (empty($order_id)) {
            echo json_encode(["error" => "Missing required fields"]);
            exit();
        }

        try {
            // Delete the specific order from the orders table
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
        // Deleting all orders for a user
        $user_id = (int) $data['user_id'];

        if (empty($user_id)) {
            echo json_encode(["error" => "Missing required fields"]);
            exit();
        }

        try {
            // Delete all orders for the user from the orders table
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
