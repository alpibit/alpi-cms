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
    alpiExitWithPublicErrorPage([
        'statusCode' => 404,
        'pageTitle' => 'Post not found',
        'eyebrow' => 'Post not found',
        'title' => 'We could not find the post you were looking for.',
        'message' => 'The address may be incomplete or no longer available.',
        'errorCode' => '404',
    ]);
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!($conn instanceof PDO)) {
        throw new Exception('Error establishing a database connection.');
    }

    $postObj = new Post($conn);

    $postData = $postObj->getPostBySlug($postSlug);

    if (!$postData) {
        header('HTTP/1.0 404 Not Found');
        require __DIR__ . '/404.php';
        exit;
    }

    $singlePost = $postObj->getPostById($postData['id']);
    $blocks = $postObj->getBlocksByPostId($postData['id']);

    $assetManager = new AssetManager();

    $pageTitle = isset($singlePost[0]['title']) ? $singlePost[0]['title'] : '';

    $blockRenderer = new BlockRenderer($conn, $assetManager, ['post' => $singlePost[0]]);
    $blockRenderer->preloadAssets($blocks);

    include __DIR__ . '/../templates/header.php';
} catch (Throwable $e) {
    error_log('Single post error: ' . $e->getMessage());
    alpiExitWithPublicErrorPage([
        'statusCode' => 500,
        'pageTitle' => 'Temporary issue',
        'eyebrow' => 'Temporary issue',
        'title' => 'We could not load this post right now.',
        'message' => 'Please try again in a moment.',
        'errorCode' => $e instanceof PDOException && $e->getCode() === '42S02' ? 'DB_TABLE_MIA' : null,
    ]);
}
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