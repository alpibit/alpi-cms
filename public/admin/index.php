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

<h1>Admin Dashboard</h1>
<a href="<?= BASE_URL ?>/public/admin/posts/add.php">Add New Post</a>
<table>
    <thead>
        <tr>
            <th>Title</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($allPosts as $singlePost): ?>
            <tr>
                <td><?= $singlePost['title'] ?></td>
                <td>
                    <a href="posts/edit.php?id=<?= $singlePost['id'] ?>">Edit</a> |
                    <a href="posts/delete.php?id=<?= $singlePost['id'] ?>">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
