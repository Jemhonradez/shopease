<?php
$config = include 'config.php';

$db_uri = $config['db_uri'];
$url = parse_url($db_uri);

$host = $url['host'];
$port = $url['port'];
$dbname = ltrim($url['path'], '/');
$user = $url['user'];
$password = $url['pass'];

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

try {
  $pdo = new PDO($dsn, $user, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {

  echo "Connection failed: " . $e->getMessage();
}
