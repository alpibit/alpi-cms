<?php
require '../../../config/autoload.php';
require '../../../config/database.php';
require '../../../config/config.php';
require '../auth_check.php';

$db = new Database();
$conn = $db->connect();

if (!$conn instanceof PDO) {
    die("Error establishing a database connection.");
}

$page = new Page($conn);
$pages = $page->getAllPages();

include '../../../templates/header-admin.php';
?>

<div class="alpi-admin-content">
    <div class="alpi-flex alpi-justify-between alpi-items-center alpi-mb-lg">
        <h1 class="alpi-text-primary">Page Management</h1>
        <a href="add_page.php" class="alpi-btn alpi-btn-primary">Add New Page</a>
    </div>

    <?php if (isset($_GET['status'], $_GET['message'])) : ?>
        <div class="alpi-alert <?= $_GET['status'] === 'success' ? 'alpi-alert-success' : 'alpi-alert-danger' ?> alpi-mb-md">
            <?= htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (empty($pages)) : ?>
        <div class="alpi-alert alpi-alert-info">
            No pages found. Start by adding a new page!
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
                    <?php foreach ($pages as $singlePage) : ?>
                        <tr>
                            <td><?= htmlspecialchars($singlePage['title'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="alpi-badge <?= $singlePage['is_active'] ? 'alpi-badge-success' : 'alpi-badge-warning' ?>">
                                    <?= $singlePage['is_active'] ? 'Published' : 'Draft' ?>
                                </span>
                            </td>
                            <td>
                                <div class="alpi-btn-group">
                                    <a href="edit_page.php?id=<?= $singlePage['id'] ?>" class="alpi-btn alpi-btn-secondary alpi-btn-sm">Edit</a>
                                    <form method="POST" action="delete_page.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this page?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(alpiGetCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="id" value="<?= (int) $singlePage['id'] ?>">
                                        <button type="submit" class="alpi-btn alpi-btn-danger alpi-btn-sm">Delete</button>
                                    </form>
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