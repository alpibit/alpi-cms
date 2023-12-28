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
    $blockSelectedPostIds = $block['selected_post_ids'] ?? null;
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
        <a href="/public/index.php">Back</a>
    </footer>
</body>

</html>