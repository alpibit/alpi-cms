<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/BlockRenderer.php';

// Home content ID
$homeContentId = 1;

$db = new Database();
$conn = $db->connect();

if (!($conn instanceof PDO)) {
    die("Error establishing a database connection.");
}

$pageObj = new Page($conn);
$homePage = $pageObj->getPageById($homeContentId);

$blocks = $pageObj->getBlocksByPageId($homeContentId) ?? [];

$assetManager = new AssetManager();

$pageTitle = isset($homePage['title']) ? $homePage['title'] : '';

$blockRenderer = new BlockRenderer($conn, $assetManager, ['page' => $homePage]);
$blockRenderer->preloadAssets($blocks);

include __DIR__ . '/../templates/header.php';
?>

<main class="content">
    <?php $blockRenderer->renderBlocks($blocks); ?>
</main>

<?php include __DIR__ . '/../templates/footer.php'; ?>