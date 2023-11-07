<?php
require '../config/autoload.php';
require '../config/config.php';
require '../config/database.php';

$postSlug = $_GET['slug'] ?? null;
if (!$postSlug) {
    die('Post slug is missing.');
}

$dbInstance = new Database();
$dbConnection = $dbInstance->connect();

if (!($dbConnection instanceof PDO)) {
    die("Error establishing a database connection.");
}

$post = new Post($dbConnection);
$singlePost = $post->getPostBySlug($postSlug);

$blocks = $post->getBlocksByPostId($singlePost['id']);

usort($blocks, function ($a, $b) {
    return $a['order_num'] <=> $b['order_num'];
});

function renderBlock($block) {
    $blockType = $block['type'];
    $blockTitle = $block['title'] ?? null;
    $blockContent = $block['content'] ?? null;
    $blockImagePath = $block['image_path'] ?? null;
    $blockAltText = $block['alt_text'] ?? null;
    $blockCaption = $block['caption'] ?? null;
    $blockUrl = $block['url'] ?? null;
    $blockClass = $block['class'] ?? null;
    $blockMetafield1 = $block['metafield_1'] ?? null;
    $blockMetafield2 = $block['metafield_2'] ?? null;
    $blockMetafield3 = $block['metafield_3'] ?? null;
    $blockCtaText = $block['cta_text'] ?? null;
    $blockStatus = $block['status'] ?? null;
    $blockPath = __DIR__ . '/../blocks/types/' . $blockType . '.php';
    if (file_exists($blockPath)) {
        include($blockPath);
    } else {
        echo 'Block type not found.';
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($singlePost['title']) ? htmlspecialchars($singlePost['title'], ENT_QUOTES, 'UTF-8') : ''; ?></title>
</head>

<body>
    <h2><?= isset($singlePost['title']) ? htmlspecialchars($singlePost['title'], ENT_QUOTES, 'UTF-8') : ''; ?></h2>

    <!-- Display blocks -->
    <?php foreach ($blocks as $block) {
        renderBlock($block);
    } ?>

    <a href="/public/index.php">Back</a>
</body>

</html>
