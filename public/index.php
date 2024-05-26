<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../utils/helpers.php';

session_start();

define('ROUTER_ACCESS', true);

try {
    $db = new Database();
    $conn = $db->connect();

    if (!($conn instanceof PDO)) {
        throw new Exception("Error establishing a database connection.");
    }

    $router = new Router($conn);
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $route = $router->getRoute($requestUri);

    switch ($route['type']) {
        case 'home':
            require __DIR__ . '/home.php';
            break;

        case 'page':
            require __DIR__ . '/single-page.php';
            break;

        case 'post':
            require __DIR__ . '/single-post.php';
            break;

        case 'category':
            require __DIR__ . '/category.php';
            break;

        case 'admin':
            require __DIR__ . '/admin/login.php';
            break;

        case '404':
        default:
            header("HTTP/1.0 404 Not Found");
            require __DIR__ . '/404.php';
            break;
    }
} catch (Exception $e) {
    die($e->getMessage());
}
