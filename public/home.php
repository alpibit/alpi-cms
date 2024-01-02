<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/autoload.php';

// Home content ID
$homeContentId = 1;

$dbInstance = new Database();
$dbConnection = $dbInstance->connect();

if (!($dbConnection instanceof PDO)) {
    die("Error establishing a database connection.");
}

$page = new Page($dbConnection);
$homePage = $page->getPageById($homeContentId);


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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($homePage['title']) ? htmlspecialchars($homePage['title'], ENT_QUOTES, 'UTF-8') : 'Home'; ?></title>
</head>

<body>
    <header>
        <h1><?= isset($homePage['title']) ? htmlspecialchars($homePage['title'], ENT_QUOTES, 'UTF-8') : ''; ?></h1>
    </header>

    <?php
    if (isset($homePage['blocks']) && is_array($homePage['blocks'])) {
        foreach ($homePage['blocks'] as $block) {
            renderBlock($block, $homePage);
        }
    }
    ?>

    <footer>
        <a href="/">Back</a>
    </footer>
</body>

</html>