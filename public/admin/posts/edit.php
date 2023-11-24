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

function renderBlockContent($block, $index)
{
    switch ($block['type']) {
        case 'text':
            echo "<textarea name='blocks[$index][content]'>{$block['content']}</textarea>";
            break;
        case 'image_text':
            // !!!
            break;
        case 'image':
            // !!!
            break;
        case 'cta':
            // !!!
            break;
    }
}

?>

<h1>Edit Post</h1>
<form action="" method="POST">
    Title: <input type="text" name="title" value="<?= isset($postData['title']) ? $postData['title'] : '' ?>"><br>
    <div id="contentBlocks">
        <?php
        if (isset($postData['blocks']) && is_array($postData['blocks'])) {
            foreach ($postData['blocks'] as $index => $block) :
                if (isset($block['type']) && isset($block['content'])) : ?>
                    <div class="block">
                        <label>Type:</label>
                        <select name="blocks[<?= $index ?>][type]">
                            <option value="text" <?= $block['type'] == 'text' ? 'selected' : '' ?>>Text</option>
                            <option value="image_text" <?= $block['type'] == 'image_text' ? 'selected' : '' ?>>Image Text</option>
                            <option value="image" <?= $block['type'] == 'image' ? 'selected' : '' ?>>Image</option>
                            <option value="cta" <?= $block['type'] == 'cta' ? 'selected' : '' ?>>CTA</option>
                        </select><br>
                        <?php
                        renderBlockContent($block, $index);
                        ?>
                        <div class="buttons">
                        </div>
                        <br>
                    </div>
        <?php endif;
            endforeach;
        } ?>
    </div>
    <input type="submit" value="Update Post">
    <button type="button" onclick="addBlock()">Add Another Block</button>
</form>

<?php
ob_end_flush();
?>

<script src="/assets/js/posts-blocks.js"></script>