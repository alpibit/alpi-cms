<?php
ob_start();
require '../../../config/autoload.php';
require '../../../config/database.php';
require '../../../config/config.php';
require '../auth_check.php';

$db = new Database();
$conn = $db->connect();
$category = new Category($conn);

$categoryData = $category->getCategoryById($_GET['id']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $category->updateCategory($_GET['id'], $name);
    header("Location: " . BASE_URL . "/public/admin/categories/index.php");
    exit;
}

include '../../../templates/header-admin.php';
?>

<div class="alpi-admin-content">
    <h1 class="alpi-text-primary alpi-mb-lg">Edit Category</h1>
    <div class="alpi-card alpi-p-lg">
        <form action="" method="POST" class="alpi-form">
            <div class="alpi-form-group">
                <label for="category-name" class="alpi-form-label">Name:</label>
                <input type="text" id="category-name" name="name" class="alpi-form-input" value="<?= isset($categoryData['name']) ? htmlspecialchars($categoryData['name']) : '' ?>" required>
            </div>
            <div class="alpi-text-right">
                <button type="submit" class="alpi-btn alpi-btn-primary">Update Category</button>
            </div>
        </form>
    </div>
</div>

<?php
include '../../../templates/footer-admin.php';
ob_end_flush();
?>