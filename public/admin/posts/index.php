<?php

require '../../../config/autoload.php';
require '../../../config/database.php';
require '../../../config/config.php';
require '../auth_check.php';

// Database connection
$db = new Database();
$conn = $db->connect();
if (!$conn instanceof PDO) {
    die("Error establishing a database connection.");
}

// Fetch posts
$post = new Post($conn);
$allPosts = $post->getAllPosts();

// Include admin header
include '../../../templates/header-admin.php';
?>

<div class="alpi-admin-content">
    <div class="alpi-flex alpi-justify-between alpi-items-center alpi-mb-lg">
        <h1 class="alpi-text-primary">Manage Posts</h1>
        <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/posts/add_post.php" class="alpi-btn alpi-btn-primary">Add New Post</a>
    </div>

    <?php if (empty($allPosts)) : ?>
        <div class="alpi-alert alpi-alert-info">
            No posts found. Start by adding a new post!
        </div>
    <?php else : ?>
        <div class="alpi-card alpi-p-md">
            <table class="alpi-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allPosts as $singlePost) : ?>
                        <tr>
                            <td><?= htmlspecialchars($singlePost['title'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if (isset($singlePost['is_active'])) : ?>
                                    <span class="alpi-badge <?= $singlePost['is_active'] ? 'alpi-badge-success' : 'alpi-badge-warning' ?>">
                                        <?= $singlePost['is_active'] ? 'Published' : 'Draft' ?>
                                    </span>
                                <?php else : ?>
                                    <span class="alpi-badge alpi-badge-secondary">Unknown</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="alpi-btn-group">
                                    <a href="edit_post.php?id=<?= $singlePost['id'] ?>" class="alpi-btn alpi-btn-secondary alpi-btn-sm">Edit</a>
                                    <button class="alpi-btn alpi-btn-danger alpi-btn-sm" onclick="confirmDeletion(<?= $singlePost['id'] ?>)">Delete</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include '../../../templates/footer-admin.php'; ?>