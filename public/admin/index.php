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
    <!-- !!! Need to move CSS -->
    <style> 
        body {
            font-family: Arial, sans-serif;
            padding: 2rem;
            background-color: #f4f4f4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #cccccc;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-group {
            margin-top: 20px;
        }

        .btn-group button {
            margin-right: 10px;
        }
    </style>
    <script>
        // !!! Need to move JS
        function confirmDeletion(postId) {
            if (confirm("Are you sure you want to delete this post?")) {
                window.location.href = 'posts/delete.php?id=' + postId;
            }
        }
    </script>
</head>

<body>

    <h1>Admin Dashboard</h1>

    <div class="btn-group">
        <button onclick="window.location.href='<?= BASE_URL ?>/public/admin/posts/add.php'">Add New Post</button>
        <button onclick="window.location.href='<?= BASE_URL ?>/public/admin/logout.php'">Logout</button>
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

</html>