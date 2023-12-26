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
$allPosts = $post->getAllPosts();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/assets/css/admin-dashboard.css">
</head>

<body class="admin-dashboard-wrap">

    <h1>Admin Dashboard</h1>

    <div class="btn-group">
        <button onclick="window.location.href='<?= BASE_URL ?>/public/admin/posts/add.php'">Add New Post</button>
        <button onclick="window.location.href='<?= BASE_URL ?>/public/admin/logout.php'">Logout</button>
        <button onclick="window.location.href='<?= BASE_URL ?>/public/admin/settings/index.php'">Settings</button>
        <button onclick="window.location.href='<?= BASE_URL ?>/public/admin/uploads/index.php'">Uploads</button>
        <button onclick="window.location.href='<?= BASE_URL ?>/public/admin/pages/index.php'">Manage Pages</button>

    </div>

    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allPosts as $singlePost) : ?>
                <tr>
                    <td><?= $singlePost['title'] ?></td>
                    <td>
                        <button onclick="window.location.href='posts/edit.php?id=<?= $singlePost['id'] ?>'">Edit</button>
                        <button class="btn-danger" onclick="confirmDeletion('<?= $singlePost['id'] ?>')">Delete</button>

                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

<script src="/assets/js/main.js"></script>

</html>