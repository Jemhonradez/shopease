<?php

require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === "GET") {
  if (isset($_GET['category'])) {
    $category = $_GET['category'];
    $tags = isset($_GET['tags']) ? $_GET['tags'] : null; // Optional tags parameter

    // Prepare the query with category and optional tags filter
    $sql = "SELECT * FROM items WHERE category = :category";

    // If tags are provided, add them to the SQL query
    if ($tags) {
      $sql .= " AND FIND_IN_SET(:tags, tags) > 0"; // Assuming tags are stored as comma-separated strings
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":category", $category, PDO::PARAM_STR);

    // Bind the tags parameter if it's present
    if ($tags) {
      $stmt->bindParam(":tags", $tags, PDO::PARAM_STR);
    }

    try {
      $stmt->execute();
      $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
      echo json_encode(["items" => $items]); // Return items based on category and optional tags
    } catch (PDOException $e) {
      echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
  } elseif (isset($_GET['item_id'])) {
    try {
      $item_id = intval($_GET['item_id']);
      $sql = "SELECT * FROM items WHERE item_id = :item_id";
      $stmt = $pdo->prepare($sql);
      $stmt->bindParam(":item_id", $item_id, PDO::PARAM_INT);
      $stmt->execute();
      $item = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($item) {
        echo json_encode($item); // Return the item
      } else {
        echo json_encode(["error" => "Item not found"]);
      }
    } catch (PDOException $e) {
      echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
  } else {
    try {
      $sql = "SELECT * FROM items ORDER BY created_at DESC";
      $stmt = $pdo->query($sql);
      $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
      echo json_encode(["items" => $items]); // Return all items
    } catch (PDOException $e) {
      echo json_encode(["error" => "Error: " . $e->getMessage()]);
    }
  }
}


if ($_SERVER['REQUEST_METHOD'] === "POST") {
  $data = json_decode(file_get_contents("php://input"), true);

  if (
    empty($data['item_name']) ||
    empty($data['item_image']) ||
    empty($data['item_desc']) ||
    empty($data['item_price']) ||
    empty($data['item_stock']) ||
    empty($data['category']) // Ensure category is provided
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
    $category = $data['category']; // Get category from data

    // Optional: Handle tags if provided
    if (!empty($data['tags']) && is_array($data['tags'])) {
      $tags = '{' . implode(',', $data['tags']) . '}'; // Convert array to PostgreSQL array format
    } else {
      $tags = null; // If no tags, set as null
    }

    $sql = "INSERT INTO items (item_name, item_image, item_desc, item_price, item_stock, category, tags) 
            VALUES (:item_name, :item_image, :item_desc, :item_price, :item_stock, :category, :tags)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":item_name", $item_name);
    $stmt->bindParam(":item_image", $item_image);
    $stmt->bindParam(":item_desc", $item_desc);
    $stmt->bindParam(":item_price", $item_price);
    $stmt->bindParam(":item_stock", $item_stock);
    $stmt->bindParam(":category", $category); // Bind category
    $stmt->bindParam(":tags", $tags);

    if ($stmt->execute()) {
      echo json_encode(["message" => "Item successfully added"]);
    } else {
      echo json_encode(["error" => "Could not add product"]);
    }
  } catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
  }
}


if ($_SERVER['REQUEST_METHOD'] === "PUT") {
  $data = json_decode(file_get_contents("php://input"), true);

  if (empty($data['item_id'])) {
    echo json_encode(["error" => "Missing required field: item_id"]);
    exit();
  }

  try {
    $item_id = intval($data['item_id']);
    $fields = [];
    $params = [":item_id" => $item_id];

    if (!empty($data['item_name'])) {
      $fields[] = "item_name = :item_name";
      $params[":item_name"] = $data['item_name'];
    }

    if (isset($data['item_image'])) {
      $fields[] = "item_image = :item_image";
      $params[":item_image"] = $data['item_image'];
    }

    if (!empty($data['item_desc'])) {
      $fields[] = "item_desc = :item_desc";
      $params[":item_desc"] = $data['item_desc'];
    }

    if (!empty($data['item_price'])) {
      $fields[] = "item_price = :item_price";
      $params[":item_price"] = floatval($data['item_price']);
    }

    if (!empty($data['item_stock'])) {
      $fields[] = "item_stock = :item_stock";
      $params[":item_stock"] = intval($data['item_stock']);
    }

    if (!empty($data['category'])) { // Update category if provided
      $fields[] = "category = :category";
      $params[":category"] = $data['category'];
    }

    if (!empty($data['tags']) && is_array($data['tags'])) {
      // Convert array to PostgreSQL array format
      $tags = '{' . implode(',', $data['tags']) . '}';
      $fields[] = "tags = :tags";
      $params[":tags"] = $tags;
    }

    if (empty($fields)) {
      echo json_encode(["error" => "No fields to update"]);
      exit();
    }

    $sql = "UPDATE items SET " . implode(", ", $fields) . " WHERE item_id = :item_id";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute($params)) {
      echo json_encode(["message" => "Item successfully updated"]);
    } else {
      echo json_encode(["error" => "Could not update product"]);
    }
  } catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
  }
}


if ($_SERVER['REQUEST_METHOD'] === "DELETE") {
  // Retrieve the item_id from the query parameters
  if (isset($_GET['item_id'])) {
    $item_id = intval($_GET['item_id']);

    try {
      // Prepare and execute the delete query
      $sql = "DELETE FROM items WHERE item_id = :item_id";
      $stmt = $pdo->prepare($sql);
      $stmt->bindParam(":item_id", $item_id, PDO::PARAM_INT);

      if ($stmt->execute()) {
        echo json_encode(["message" => "Item successfully deleted"]);
      } else {
        echo json_encode(["error" => "Could not delete the item"]);
      }
    } catch (PDOException $e) {
      echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
  } else {
    echo json_encode(["error" => "Missing required parameter: item_id"]);
  }
}
