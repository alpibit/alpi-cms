<?php
session_start();

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    header('Location: path_to_your_login_page/login.php');
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

require '../../../config/database.php';
require '../../../config/config.php';
require '../../../config/autoload.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: " . BASE_URL . "/public/admin/index.php");
    exit;
}

$db = new Database();
$conn = $db->connect();
$post = new Post($conn);

$postId = intval($_GET['id']);
$post->deletePost($postId);

header("Location: " . BASE_URL . "/public/admin/index.php");
exit;
