<?php

require '../../../config/database.php';
require '../../../config/config.php';
require '../../../config/autoload.php';
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

<button onclick="window.location.href='<?= BASE_URL ?>/public/admin/posts/add_post.php'">Add New Post</button>
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
                <td><?= htmlspecialchars($singlePost['title']) ?></td>
                <td>
                    <button onclick="window.location.href='edit_post.php?id=<?= $singlePost['id'] ?>'">Edit</button>
                    <button class="btn-danger" onclick="confirmDeletion('<?= $singlePost['id'] ?>')">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Include admin footer -->
<?php include '../../../templates/footer-admin.php'; ?>