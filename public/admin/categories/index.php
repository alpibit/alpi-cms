<?php
require '../../../config/database.php';
require '../../../config/config.php';
require '../../../config/autoload.php';
require '../auth_check.php';

$db = new Database();
$conn = $db->connect();

if (!$conn instanceof PDO) {
    die("Error establishing a database connection.");
}

$category = new Category($conn);
$categories = $category->getAllCategories();

include '../../../templates/header-admin.php';
?>

<div class="alpi-admin-content">
    <div class="alpi-flex alpi-justify-between alpi-items-center alpi-mb-lg">
        <h1 class="alpi-text-primary">Category Management</h1>
        <a href="add_category.php" class="alpi-btn alpi-btn-primary">Add New Category</a>
    </div>

    <div class="alpi-card alpi-p-md">
        <table class="alpi-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $singleCategory) : ?>
                    <tr>
                        <td><?= htmlspecialchars($singleCategory['name']) ?></td>
                        <td>
                            <div class="alpi-btn-group">
                                <a href="edit_category.php?id=<?= $singleCategory['id'] ?>" class="alpi-btn alpi-btn-secondary alpi-btn-sm">Edit</a>
                                <button class="alpi-btn alpi-btn-danger alpi-btn-sm" onclick="confirmCategoryDeletion('<?= $singleCategory['id'] ?>')">Delete</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../../templates/footer-admin.php'; ?>

<script>
    function confirmCategoryDeletion(categoryId) {
        if (confirm("Are you sure you want to delete this category?")) {
            window.location.href = 'delete_category.php?id=' + categoryId;
        }
    }
</script>