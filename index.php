<?php
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (strpos($request, '/api/') === 0) {
    include 'routes/api.php';
} else {
    include 'routes/web.php';
}
