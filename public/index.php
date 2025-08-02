<?php
if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../config/autoload.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../utils/helpers.php';
    require_once __DIR__ . '/../config/config.php';
    define('CONFIG_INCLUDED', true);
}


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
            require __DIR__ . '/404.php';
            break;
    }
} catch (PDOException $e) {
    // Check if the error is about a missing table
    if ($e->getCode() === '42S02') {
        echo "Sorry, currently we are experiencing small issues. (Error: DB_TABLE_MIA)";
    } else {
        error_log("PDOException: " . $e->getMessage());
        echo "Sorry, we are currently experiencing technical difficulties. Please try again later.";
    }
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    echo "An unexpected error occurred. We are working to fix it. Please try again later.";
}
