<?php
require '../config/autoload.php';
require '../config/config.php';  
require '../config/database.php';

$postSlug = $_GET['slug'] ?? null;
if (!$postSlug) {
    die('Post slug is missing.');
}


$dbInstance = new Database();
$dbConnection = $dbInstance->connect();

if (!($dbConnection instanceof PDO)) {
    die("Error establishing a database connection."); 
}

$post = new Post($dbConnection);
$singlePost = $post->getPostBySlug($postSlug);

if (!$singlePost) {
    die('Post not found.');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($singlePost['title'], ENT_QUOTES, 'UTF-8'); ?></title>
</head>
<body>
    <h2><?= htmlspecialchars($singlePost['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
    <p><?= nl2br(htmlspecialchars($singlePost['content'], ENT_QUOTES, 'UTF-8')); ?></p>
    <a href="/public/index.php">Back</a>
</body>
</html>
