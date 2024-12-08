<?php

switch ($request) {
  case '/api/products':
    include 'api/products.php';
    break;

  case '/api/orders':
    include 'api/orders.php';
    break;

    case '/api/customer':
    include 'api/customer.php';
    break;

    default:
    http_response_code(404);
    echo json_encode(['error' => '404 Not Found']);
    break;
}
