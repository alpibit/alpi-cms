<?php
session_start();

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    header('Location: /public/admin/login.php');
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

require '../../config/database.php';
require '../../config/config.php';
require '../../config/autoload.php';

$db = new Database();
$conn = $db->connect();

if (!$conn instanceof PDO) {
    die("Error establishing a database connection.");
}

$post = new Post($conn);
$page = new Page($conn);
$category = new Category($conn);

$postCount = $post->countPosts();
$pageCount = $page->countPages();
$categoryCount = $category->countCategories();

?>

<?php include '../../templates/header-admin.php'; ?>

<h1>Admin Dashboard</h1>
<div>
    <p>Total Posts: <?= $postCount ?></p>
    <p>Total Pages: <?= $pageCount ?></p>
    <p>Total Categories: <?= $categoryCount ?></p>
</div>

<!-- footer file -->

<?php include '../../templates/footer-admin.php'; ?>