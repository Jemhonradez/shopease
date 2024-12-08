<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: /login');
  exit();
}

$username = $_SESSION['username'];
$user_type = $_SESSION['user_type'];
?>

<main>

</main>