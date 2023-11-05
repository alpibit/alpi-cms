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
        switch ($block['type']) {
            case 'text':
                echo '<p>' . (isset($block['content']) ? nl2br(htmlspecialchars($block['content'], ENT_QUOTES, 'UTF-8')) : '') . '</p>';
                break;

            case 'image_text':
                echo '<img src="' . (isset($block['image_path']) ? htmlspecialchars($block['image_path'], ENT_QUOTES, 'UTF-8') : '') . '" alt="Image and Text Block">';
                echo '<p>' . (isset($block['content']) ? nl2br(htmlspecialchars($block['content'], ENT_QUOTES, 'UTF-8')) : '') . '</p>';
                break;

            case 'image':
                if (isset($block['image_path'])) {
                    echo '<img src="' . htmlspecialchars($block['image_path'], ENT_QUOTES, 'UTF-8') . '" alt="Image Block">';
                } else {
                    echo '<img src="" alt="Image Block">';
                }
                break;

            case 'cta':
                break;

            default:
                break;
        }
    } ?>

    <a href="/public/index.php">Back</a>
</body>

</html>