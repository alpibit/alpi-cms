<?php
ob_start();

session_start();

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    header('Location: /public/admin/login.php');
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

require '../../../config/database.php';
require '../../../config/config.php';
require '../../../config/autoload.php';

$db = new Database();
$conn = $db->connect();
$post = new Post($conn);

$postData = $post->getPostById($_GET['id']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $slug = $post->generateSlug($title);

    if(!isset($_SESSION['user_id'])){
        die("User ID not set in session. Ensure you're setting this on login.");
    }
    $userId = $_SESSION['user_id'];

    $post->updatePost($_GET['id'], $title, $content, $slug, $userId);
    header("Location: " . BASE_URL . "/public/admin/index.php");
    exit;
}
?>

<h1>Edit Post</h1>
<form action="" method="POST">
    Title: <input type="text" name="title" value="<?= $postData['title'] ?>"><br>
    Content: <textarea name="content"><?= $postData['content'] ?></textarea><br>
    <input type="submit" value="Update Post">
</form>

<?php
ob_end_flush();
?>
