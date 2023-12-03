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

    foreach ($_POST['blocks'] as $index => $block) {
        $blockData = [
            'type' => $block['type'],
            'title' => $block['title'] ?? '',
            'style1' => $block['style1'] ?? '',
            'style2' => $block['style2'] ?? '',
            'style3' => $block['style3'] ?? '',
            'style4' => $block['style4'] ?? '',
            'style5' => $block['style5'] ?? '',
            'style6' => $block['style6'] ?? '',
            'style7' => $block['style7'] ?? '',
            'style8' => $block['style8'] ?? '',
            'background_color' => $block['background_color'] ?? '',
            'content' => $block['content'] ?? ''
        ];

        if ($block['type'] == 'image' || $block['type'] == 'image_text') {
            $blockData['image_path'] = $_POST['blocks'][$index]['image_path'] ?? '';
        }

        if ($block['type'] == 'cta') {
            $blockData['url1'] = $block['url1'] ?? '';
            $blockData['cta_text1'] = $block['cta_text1'] ?? '';
            $blockData['url2'] = $block['url2'] ?? '';
            $blockData['cta_text2'] = $block['cta_text2'] ?? '';
        }

        $contentBlocks[] = $blockData;
    }

    $userId = $_SESSION['user_id'] ?? 0;
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
        <?php
        ?>
        </br>
        </br>
        Title: <input type="text" name="title" value="<?= isset($postData['post_title']) ? $postData['post_title'] : '' ?>"><br>
        <div id="contentBlocks">
            <?php
            if (isset($postData['blocks']) && is_array($postData['blocks'])) {
                foreach ($postData['blocks'] as $index => $block) {
                    echo "<div class='block'>";
                    echo "<label>Type:</label>";
                    echo "<select name='blocks[$index][type]' onchange='loadBlockContent(this, $index)'>";
                    echo "<option value='text' " . ($block['type'] == 'text' ? 'selected' : '') . ">Text</option>";
                    echo "<option value='image_text' " . ($block['type'] == 'image_text' ? 'selected' : '') . ">Image + Text</option>";
                    echo "<option value='image' " . ($block['type'] == 'image' ? 'selected' : '') . ">Image</option>";
                    echo "<option value='cta' " . ($block['type'] == 'cta' ? 'selected' : '') . ">Call to Action</option>";
                    // !!!
                    echo "</select><br>";
                    $blockDataJson = htmlspecialchars(json_encode($block['block_data']), ENT_QUOTES, 'UTF-8');
                    echo "<div class='block-content' data-value='{$blockDataJson}'></div>";
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