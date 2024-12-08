<?php

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

include 'pages/shared/header.php';

switch ($request) {
  case '/':
  case '/index.php':
    include 'pages/home.php';
    break;

  case '/login':
    include 'pages/auth/login.php';
    break;

  case '/register':
    include 'pages/auth/register.php';
    break;

  case '/cart':
    include 'pages/cart.php';
    break;

  case '/profile':
    include 'pages/profile.php';
    break;

  case '/category/women':
    include 'pages/category/women.php';
    break;

  case '/category/men':
    include 'pages/category/men.php';
    break;

  case '/admin':
    include 'pages/admin/admin.php';
    break;

  case '/admin/product-management':
    include 'pages/admin/product-management.php';
    break;

  default:
    include 'pages/404.php';
    break;
}

include 'pages/shared/footer.php';
