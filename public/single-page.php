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
$pageSlug = $segments[0] ?? null;

if (!$pageSlug) {
    alpiExitWithPublicErrorPage([
        'statusCode' => 404,
        'pageTitle' => 'Page not found',
        'eyebrow' => 'Page not found',
        'title' => 'We could not find the page you were looking for.',
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

    $pageObj = new Page($conn);

    $pageData = $pageObj->getPageBySlug($pageSlug);

    if (!$pageData) {
        header('HTTP/1.0 404 Not Found');
        require __DIR__ . '/404.php';
        exit;
    }

    $singlePage = $pageObj->getPageById($pageData['id']);
    $blocks = $pageObj->getBlocksByPageId($pageData['id']);

    $assetManager = new AssetManager();

    $pageTitle = isset($singlePage['title']) ? $singlePage['title'] : '';

    $assetManager->addCss('global.css');
    $assetManager->addJs('global.js');

    $blockRenderer = new BlockRenderer($conn, $assetManager, ['page' => $singlePage]);
    $blockRenderer->preloadAssets($blocks);

    include __DIR__ . '/../templates/header.php';
} catch (Throwable $e) {
    error_log('Single page error: ' . $e->getMessage());
    alpiExitWithPublicErrorPage([
        'statusCode' => 500,
        'pageTitle' => 'Temporary issue',
        'eyebrow' => 'Temporary issue',
        'title' => 'We could not load this page right now.',
        'message' => 'Please try again in a moment.',
        'errorCode' => $e instanceof PDOException && $e->getCode() === '42S02' ? 'DB_TABLE_MIA' : null,
    ]);
}
?>

<main class="content">
    <h1><?php echo htmlspecialchars($singlePage['title']); ?></h1>
    <?php $blockRenderer->renderBlocks($blocks); ?>
</main>

<?php include __DIR__ . '/../templates/footer.php'; ?>