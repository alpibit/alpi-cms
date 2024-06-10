<?php

require '../../../config/database.php';
require '../../../config/config.php';
require '../../../config/autoload.php';
require '../auth_check.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: " . BASE_URL . "/public/admin/posts/index.php");
    exit;
}

$db = new Database();
$conn = $db->connect();
$post = new Post($conn);

$postId = intval($_GET['id']);
$post->deletePost($postId);

header("Location: " . BASE_URL . "/public/admin/posts/index.php");
exit;
