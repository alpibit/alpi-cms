<?php


require '../../config/database.php';
require '../../config/config.php';
require '../../config/autoload.php';
require './auth_check.php';

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