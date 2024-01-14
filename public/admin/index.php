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

<?php include '../../templates/header-admin.php'; ?>

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
                        <button onclick="window.location.href='posts/edit_post.php?id=<?= $singlePost['id'] ?>'">Edit</button>
                        <button class="btn-danger" onclick="confirmDeletion('<?= $singlePost['id'] ?>')">Delete</button>

                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<!-- footer file -->

<?php include '../../templates/footer-admin.php'; ?>