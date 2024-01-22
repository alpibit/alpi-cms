<?php

require '../../../config/database.php';
require '../../../config/config.php';
require '../../../config/autoload.php';

$db = new Database();
$conn = $db->connect();

if (!$conn instanceof PDO) {
    die("Error establishing a database connection.");
}

$page = new Page($conn);
$pages = $page->getAllPages();

include '../../../templates/header-admin.php';
?>

<h1>Page Management</h1>

<button onclick="window.location.href='add_page.php'">Add New Page</button>

<table>
    <thead>
        <tr>
            <th>Title</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pages as $singlePage) : ?>
            <tr>
                <td><?= htmlspecialchars($singlePage['title']) ?></td>
                <td>
                    <a href="edit_page.php?id=<?= $singlePage['id'] ?>" class="edit-btn">Edit</a>
                </td>
                <td>
                    <button class="delete-btn" onclick="confirmPageDeletion('<?= $singlePage['id'] ?>')">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<!-- !!! REMOVE/MOVE all the styling and script -->

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        padding: 20px;
    }

    h1 {
        color: #333;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
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

    tr:nth-child(odd) {
        background-color: #fff;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    a {
        color: #0275d8;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
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

<script>
    function confirmPageDeletion(pageId) {
        if (confirm("Are you sure you want to delete this page?")) {
            window.location.href = 'delete_page.php?id=' + pageId;
        }
    }
</script>

<?php include '../../../templates/footer-admin.php'; ?>