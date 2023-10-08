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
$postData = $postData[0];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $contentBlocks = [];
    $slug = $post->generateSlug($title);


    foreach ($_POST['blocks'] as $block) {
        $contentBlocks[] = [
            'block_type' => $block['type'],
            'block_content' => $block['content'],
        ];
    }

    if (!isset($_SESSION['user_id'])) {
        die("User ID not set in session. Ensure you're setting this on login.");
    }
    $userId = $_SESSION['user_id'];

    $post->updatePost($_GET['id'], $title, $contentBlocks, $slug, $userId);
    header("Location: " . BASE_URL . "/public/admin/index.php");
    exit;
}

?>

<h1>Edit Post</h1>
<form action="" method="POST">
    Title: <input type="text" name="title" value="<?= isset($postData['title']) ? $postData['title'] : '' ?>"><br>
    <?php
    if (isset($postData['blocks']) && is_array($postData['blocks'])) {
        foreach ($postData['blocks'] as $index => $block) :
            if (isset($block['type']) && isset($block['content'])) : ?>
                <label><?= ucfirst($block['type']) ?> Block:</label>
                <textarea name="blocks[<?= $index ?>][content]"><?= $block['content'] ?></textarea>
                <input type="hidden" name="blocks[<?= $index ?>][type]" value="<?= $block['type'] ?>">
                <br>
    <?php endif;
        endforeach;
    } ?>
    <input type="submit" value="Update Post">
</form>


<?php
ob_end_flush();
?>