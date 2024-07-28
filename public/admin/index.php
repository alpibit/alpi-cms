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

include '../../templates/header-admin.php';
?>

<h1 class="alpi-text-primary alpi-mb-lg">Admin Dashboard</h1>

<div class="alpi-admin-dashboard">
    <div class="alpi-admin-widget">
        <h2 class="alpi-admin-widget-title">Total Posts</h2>
        <p class="alpi-admin-widget-content"><?= htmlspecialchars($postCount, ENT_QUOTES, 'UTF-8') ?></p>
        <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/posts/index.php" class="alpi-btn alpi-btn-primary alpi-mt-md">Manage Posts</a>
    </div>

    <div class="alpi-admin-widget">
        <h2 class="alpi-admin-widget-title">Total Pages</h2>
        <p class="alpi-admin-widget-content"><?= htmlspecialchars($pageCount, ENT_QUOTES, 'UTF-8') ?></p>
        <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/pages/index.php" class="alpi-btn alpi-btn-primary alpi-mt-md">Manage Pages</a>
    </div>

    <div class="alpi-admin-widget">
        <h2 class="alpi-admin-widget-title">Total Categories</h2>
        <p class="alpi-admin-widget-content"><?= htmlspecialchars($categoryCount, ENT_QUOTES, 'UTF-8') ?></p>
        <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/categories/index.php" class="alpi-btn alpi-btn-primary alpi-mt-md">Manage Categories</a>
    </div>
</div>

<div class="alpi-admin-recent-activity alpi-mt-xl alpi-mb-md ">
    <h2 class="alpi-text-primary alpi-mb-md">Recent Activity</h2>
    <div class="alpi-card">
        <p>No recent activity to display.</p>
        <!-- !!! Feed -->
    </div>
</div>

<?php include '../../templates/footer-admin.php'; ?>