<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/autoload.php';

// Home content ID
$homeContentId = 1;

$db = new Database();
$conn = $db->connect();

if (!($conn instanceof PDO)) {
    die("Error establishing a database connection.");
}

$page = new Page($conn);
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


<?php include __DIR__ . '/../templates/header.php'; ?>

<main class="content">

    <?php
    if (isset($homePage['blocks']) && is_array($homePage['blocks'])) {
        foreach ($homePage['blocks'] as $block) {
            renderBlock($block, $homePage);
        }
    }
    ?>

</main>

<?php include __DIR__ . '/../templates/footer.php'; ?>