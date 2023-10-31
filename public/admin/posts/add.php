<?php
session_start();

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    header('Location: path_to_your_login_page/login.php');
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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $title = $_POST['title'];
    $contentBlocks = [];
    $userId = $_SESSION['user_id'];

    if (isset($_POST['blockType']) && isset($_POST['blockContent'])) {
        for ($i = 0; $i < count($_POST['blockType']); $i++) {
            $contentBlocks[] = [
                'type' => $_POST['blockType'][$i],
                'content' => $_POST['blockContent'][$i]
            ];
        }
    }

    $post->addPost($title, $contentBlocks, $userId);
    header("Location: " . BASE_URL . "/public/admin/index.php");
    exit;
}
?>

<h1>Add New Post</h1>
<form action="" method="POST">
    Title: <input type="text" name="title"><br>

    <!-- Allow multiple content blocks -->
    <div id="contentBlocks">
        <div class="block">
            <label>Type:</label>
            <select name="blockType[]">
                <option value="text">Text</option>
                <option value="image_text">Image Text</option>
                <option value="image">Image</option>
                <option value="cta">CTA</option>
            </select><br>
            <textarea name="blockContent[]"></textarea><br>
            <div class="buttons">
                <button type="button" onclick="moveUp(this)">Move Up</button>
                <button type="button" onclick="moveDown(this)">Move Down</button>
                <button type="button" onclick="deleteBlock(this)">Delete</button>
            </div>
            <br>
        </div>
    </div>
    <button type="button" onclick="addBlock()">Add Another Block</button>

    <input type="submit" value="Add Post">
</form>


<script src="/assets/js/posts-blocks.js"></script>