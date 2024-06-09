<?php


require '../../config/database.php';
require '../../config/config.php';
require '../../config/autoload.php';
require './auth_check.php';


try {
    $db = new Database();
    $conn = $db->connect();
    if (!$conn instanceof PDO) {
        throw new Exception("Error establishing a database connection.");
    }
} catch (Exception $e) {
    die($e->getMessage());
}

$post = new Post($conn);
$page = new Page($conn);
$category = new Category($conn);

try {
    $postCount = $post->countPosts();
    $pageCount = $page->countPages();
    $categoryCount = $category->countCategories();
} catch (Exception $e) {
    die("Error retrieving data: " . $e->getMessage());
}
?>

<?php include '../../templates/header-admin.php'; ?>

<h1>Admin Dashboard</h1>
<div>
    <p>Total Posts: <?= htmlspecialchars($postCount, ENT_QUOTES, 'UTF-8') ?></p>
    <p>Total Pages: <?= htmlspecialchars($pageCount, ENT_QUOTES, 'UTF-8') ?></p>
    <p>Total Categories: <?= htmlspecialchars($categoryCount, ENT_QUOTES, 'UTF-8') ?></p>
</div>

<?php include '../../templates/footer-admin.php'; ?>