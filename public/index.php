<?php
// Include necessary files and classes
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../utils/helpers.php';

// Start session
session_start();

try {
    $dbInstance = new Database();
    $dbConnection = $dbInstance->connect();

    if (!($dbConnection instanceof PDO)) {
        throw new Exception("Error establishing a database connection.");
    }

    // Parse the Request URI
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestUri = trim($requestUri, '/');

    // Routing logic
    if ($requestUri === '') {
        // Home page
        require __DIR__ . '/home.php';
    } else {
        $pageObj = new Page($dbConnection);
        $postObj = new Post($dbConnection);

        $pageContent = $pageObj->getPageBySlug($requestUri);
        $postContent = $postObj->getPostBySlug($requestUri);

        if ($pageContent) {
            // Render the page
            require __DIR__ . '/single-page.php';
        } elseif ($postContent) {
            // Render the post
            require __DIR__ . '/single-post.php';
        } else {
            // 404 Not Found
            header("HTTP/1.0 404 Not Found");
            require __DIR__ . '/404.php';
        }
    }
} catch (Exception $e) {
    die($e->getMessage());
}
