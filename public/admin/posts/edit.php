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
            'type' => $block['type'],
            'content' => $block['content'],
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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Post</title>
    <link rel="stylesheet" href="/assets/css/uploads.css">
</head>

<body class="uploads-wrap">
    <h1>Edit Post</h1>
    <form action="" method="POST">
        Title: <input type="text" name="title" value="<?= isset($postData['title']) ? $postData['title'] : '' ?>"><br>
        <div id="contentBlocks">
            <?php
            if (isset($postData['blocks']) && is_array($postData['blocks'])) {
                foreach ($postData['blocks'] as $index => $block) {
                    echo "<div class='block'>";
                    echo "<label>Type:</label>";
                    echo "<select name='blocks[$index][type]' onchange='loadBlockContent(this, $index)'>";
                    // Populate the select options here
                    echo "</select><br>";
                    echo "<div class='block-content'></div>"; // Placeholder for block content
                    echo "<div class='buttons'>...</div>";
                    echo "</div>";
                }
            }
            ?>
        </div>
        <input type="submit" value="Update Post">
        <button type="button" onclick="addBlock()">Add Another Block</button>
    </form>
    <script src="/assets/js/posts-blocks.js"></script>
</body>

</html>
<?php ob_end_flush(); ?>