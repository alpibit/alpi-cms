<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/autoload.php';

// Load utility functions (in progress)
require_once __DIR__ . '/../utils/helpers.php';


$dbInstance = new Database();
$dbConnection = $dbInstance->connect();

if ($dbConnection instanceof PDO) {
    $postObj = new Post($dbConnection);
} else {
    die("Error establishing a database connection.");
}

$postObj = new Post($dbConnection);
$latestPosts = $postObj->getLatestPosts(); 

// Include header template (in progress)
include __DIR__ . '/../templates/header.php';

foreach ($latestPosts as $post) {
    echo "<h2>{$post['title']}</h2>";
    echo "<p>{$post['content']}</p>";
    echo "<hr>";
}

include __DIR__ . '/../templates/footer.php';

?>