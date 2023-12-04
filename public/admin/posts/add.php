<?php
ob_start();
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
    $slug = $post->generateSlug($title);
    $userId = $_SESSION['user_id'];

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

    $post->addPost($title, $contentBlocks, $slug, $userId);
    header("Location: " . BASE_URL . "/public/admin/index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add New Post</title>
    <link rel="stylesheet" href="/assets/css/uploads.css">
</head>

<body class="uploads-wrap">
    <h1>Add New Post</h1>
    <form action="" method="POST">
        Title: <input type="text" name="title"><br>
        <div id="contentBlocks">
            <div class='block'>
                <label>Type:</label>
                <select name='blocks[0][type]' onchange='loadBlockContent(this, 0)'>
                    <option value='text'>Text</option>
                    <option value='image_text'>Image + Text</option>
                    <option value='image'>Image</option>
                    <option value='cta'>Call to Action</option>
                </select><br>
                <div class='block-content'></div>
                <div class='buttons'>...</div>
            </div>
        </div>
        <button type="button" onclick="addBlock()">Add Another Block</button>
        <input type="submit" value="Add Post">
    </form>
    <script src="/assets/js/posts-blocks.js"></script>
</body>

</html>
<?php ob_end_flush(); ?>