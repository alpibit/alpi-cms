<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/autoload.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');
$segments = explode('/', $path);
$postSlug = $segments[1] ?? null;

if (!$postSlug) {
    die('Post slug is missing.');
}

$db = new Database();
$conn = $db->connect();

if (!($conn instanceof PDO)) {
    die("Error establishing a database connection.");
}

$postObj = new Post($conn);

$singlePost = $postObj->getPostBySlug($postSlug);

if (!$singlePost) {
    header("HTTP/1.0 404 Not Found");
    echo "Post not found.";
    exit;
}

$blocks = $postObj->getBlocksByPostId($singlePost['id']) ?? [];

function renderBlock($block, $page, $conn)
{
    $blockType = $block['type'];
    $blockTitle = $block['title'] ?? null;
    $blockContent = $block['content'] ?? null;
    $blockTitleFontSize = $block['block_data']['style6'] ?? null;
    $blockTitleColor = $block['block_data']['style7'] ?? null;
    $blockTitleAlignment = $block['block_data']['style8'] ?? null;
    $blockTextSize = $block['block_data']['style1'] ?? null;
    $blockTextColor = $block['block_data']['style2'] ?? null;
    $blockBackgroundColor = $block['block_data']['background_color'] ?? null;
    $blockTopPadding = $block['block_data']['style4'] ?? null;
    $blockBottomPadding = $block['block_data']['style5'] ?? null;
    $blockCtaText1 = $block['block_data']['cta_text1'] ?? null;
    $blockUrl1 = $block['block_data']['url1'] ?? null;
    $blockCtaText2 = $block['block_data']['cta_text2'] ?? null;
    $blockUrl2 = $block['block_data']['url2'] ?? null;
    $blockSelectedPostIds = $block['block_data']['selected_post_ids'] ?? null;
    $blockImagePath = $block['block_data']['image_path'] ?? null;
    $blockAltText = $block['block_data']['alt_text'] ?? null;
    $blockCaption = $block['block_data']['caption'] ?? null;
    $blockClass = $block['block_data']['class'] ?? null;
    $blockMetafield1 = $block['block_data']['metafield_1'] ?? null;
    $blockMetafield2 = $block['block_data']['metafield_2'] ?? null;
    $blockMetafield3 = $block['block_data']['metafield_3'] ?? null;
    $blockLayoutToggle = $block['block_data']['layout1'] ?? null;
    $blockPath = __DIR__ . '/../blocks/types/' . $blockType . '.php';

    if (file_exists($blockPath)) {
        include($blockPath);
    } else {
        echo 'Block type not found.';
    }
}


?>

<?php include __DIR__ . '/../templates/header.php'; ?>

<main class="content">
    <?php foreach ($blocks as $block) {
        renderBlock($block, $singlePost, $conn);
    } ?>
</main>

<?php include __DIR__ . '/../templates/footer.php'; ?>