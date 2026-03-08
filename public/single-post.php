<?php
if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../config/autoload.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../utils/helpers.php';
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../classes/BlockRenderer.php';
    define('CONFIG_INCLUDED', true);
}

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

$postData = $postObj->getPostBySlug($postSlug);

if (!$postData) {
    header("HTTP/1.0 404 Not Found");
    echo "Post not found.";
    exit;
}

$singlePost = $postObj->getPostById($postData['id']);
$blocks = $postObj->getBlocksByPostId($postData['id']);

$assetManager = new AssetManager();

$pageTitle = isset($singlePost[0]['title']) ? $singlePost[0]['title'] : '';

$blockRenderer = new BlockRenderer($conn, $assetManager, ['post' => $singlePost[0]]);
$blockRenderer->preloadAssets($blocks);

include __DIR__ . '/../templates/header.php';
?>

<main class="content">
    <article class="post">
        <header class="post-header">
            <h1><?php echo htmlspecialchars($singlePost[0]['title']); ?></h1>
            <?php if (!empty($singlePost[0]['subtitle'])): ?>
                <h2><?php echo htmlspecialchars($singlePost[0]['subtitle']); ?></h2>
            <?php endif; ?>
        </header>

        <?php if ($singlePost[0]['show_main_image'] && !empty($singlePost[0]['main_image_path'])): ?>
            <div class="post-featured-image">
                <img src="<?php echo htmlspecialchars($singlePost[0]['main_image_path']); ?>" alt="<?php echo htmlspecialchars($singlePost[0]['title']); ?>">
            </div>
        <?php endif; ?>

        <div class="post-content">
            <?php $blockRenderer->renderBlocks($blocks); ?>
        </div>
    </article>
</main>

<?php include __DIR__ . '/../templates/footer.php'; ?>