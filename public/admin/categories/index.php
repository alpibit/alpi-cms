<?php
require '../../../config/database.php';
require '../../../config/config.php';
require '../../../config/autoload.php';

$db = new Database();
$conn = $db->connect();

if (!$conn instanceof PDO) {
    die("Error establishing a database connection.");
}

$category = new Category($conn);
$categories = $category->getAllCategories();

include '../../../templates/header-admin.php';
?>

<h1>Category Management</h1>

<button onclick="window.location.href='add_category.php'">Add New Category</button>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $singleCategory) : ?>
            <tr>
                <td><?= htmlspecialchars($singleCategory['name']) ?></td>
                <td>
                    <a href="edit_category.php?id=<?= $singleCategory['id'] ?>" class="edit-btn">Edit</a>
                </td>
                <td>
                    <button class="delete-btn" onclick="confirmCategoryDeletion('<?= $singleCategory['id'] ?>')">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../../../templates/footer-admin.php'; ?>

<script>
    function confirmCategoryDeletion(categoryId) {
        if (confirm("Are you sure you want to delete this category?")) {
            window.location.href = 'delete_category.php?id=' + categoryId;
        }
    }
</script>



<style>
    /* !!! Remove/Move  */
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        padding: 20px;
    }

    h1 {
        color: #333;
    }

    button {
        padding: 10px 15px;
        margin-bottom: 20px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }

    th {
        background-color: #f0f0f0;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .edit-btn,
    .delete-btn {
        padding: 5px 10px;
        text-align: center;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .edit-btn {
        background-color: #4CAF50;
        color: white;
    }

    .delete-btn {
        background-color: #f44336;
        color: white;
    }
</style>