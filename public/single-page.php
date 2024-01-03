<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/autoload.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');
$segments = explode('/', $path);
$pageSlug = $segments[0] ?? null;

if (!$pageSlug) {
    die('Page slug is missing.');
}

$dbInstance = new Database();
$dbConnection = $dbInstance->connect();

if (!($dbConnection instanceof PDO)) {
    die("Error establishing a database connection.");
}

$pageObj = new Page($dbConnection);

$pageData = $pageObj->getPageBySlug($pageSlug);

if (!$pageData) {
    header("HTTP/1.0 404 Not Found");
    echo "Page not found.";
    exit;
}

$singlePage = $pageObj->getPageById($pageData['id']);

$blocks = $singlePage['blocks'] ?? [];

function renderBlock($block, $page)
{
    $blockType = $block['type'];
    $blockTitle = $block['title'] ?? null;
    $blockContent = $block['content'] ?? null;
    $blockSelectedPostIds = $block['block_data']['selected_post_ids'] ?? null;
    $blockImagePath = $block['block_data']['image_path'] ?? null;
    $blockAltText = $block['block_data']['alt_text'] ?? null;
    $blockCaption = $block['block_data']['caption'] ?? null;
    $blockUrl = $block['block_data']['url'] ?? null;
    $blockClass = $block['block_data']['class'] ?? null;
    $blockMetafield1 = $block['block_data']['metafield_1'] ?? null;
    $blockMetafield2 = $block['block_data']['metafield_2'] ?? null;
    $blockMetafield3 = $block['block_data']['metafield_3'] ?? null;
    $blockCtaText = $block['block_data']['cta_text'] ?? null;
    $blockStatus = $block['block_data']['status'] ?? null;
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
        renderBlock($block, $singlePage);
    } ?>
</main>

<?php include __DIR__ . '/../templates/footer.php'; ?>