<?php
if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '../../config/autoload.php';
    require_once __DIR__ . '../../config/database.php';
    require_once __DIR__ . '../../utils/helpers.php';
    require_once __DIR__ . '../../config/config.php';
    define('CONFIG_INCLUDED', true);
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    die("Error establishing a database connection.");
}

$settings = new Settings($conn);
$rawSiteTitle = $settings->getSetting('site_title');
if (mb_strlen($rawSiteTitle) > 30) {
    $rawSiteTitle = mb_substr($rawSiteTitle, 0, 30) . '...';
}
$siteName = htmlspecialchars($rawSiteTitle, ENT_QUOTES, 'UTF-8');
$siteDescription = htmlspecialchars($settings->getSetting('site_description'), ENT_QUOTES, 'UTF-8');
$siteLogo = htmlspecialchars($settings->getSetting('site_logo'), ENT_QUOTES, 'UTF-8');
$siteFavicon = htmlspecialchars($settings->getSetting('site_favicon'), ENT_QUOTES, 'UTF-8');
$defaultLanguage = htmlspecialchars($settings->getSetting('default_language'), ENT_QUOTES, 'UTF-8');
$timezone = htmlspecialchars($settings->getSetting('timezone'), ENT_QUOTES, 'UTF-8');
$googleAnalyticsCode = $settings->getSetting('google_analytics_code');
$customCss = $settings->getSetting('custom_css');
$headerScripts = $settings->getSetting('header_scripts');
$footerScripts = $settings->getSetting('footer_scripts');
$footerText = htmlspecialchars($settings->getSetting('footer_text'), ENT_QUOTES, 'UTF-8');

try {
    // Fetch pages
    $stmt = $conn->query("SELECT title, slug FROM contents WHERE content_type_id = (SELECT id FROM content_types WHERE name = 'page')");
    $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch categories
    $stmt = $conn->query("SELECT name, slug FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch posts under each category
    $postsByCategory = [];
    foreach ($categories as $category) {
        $postQuery = "SELECT title, slug FROM contents WHERE category_id = (SELECT id FROM categories WHERE slug = :slug) AND content_type_id = (SELECT id FROM content_types WHERE name = 'post')";
        $stmt = $conn->prepare($postQuery);
        $stmt->execute(['slug' => $category['slug']]);
        $postsByCategory[$category['slug']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Error fetching data from the database.");
}

if (!isset($pageTitle) || !is_string($pageTitle) || trim($pageTitle) === '') {
    $pageTitle = $siteName;
}

$pageTitle = htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="<?= $defaultLanguage ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= $siteName ?></title>
    <meta name="description" content="<?= $siteDescription ?>">
    <?php if ($siteFavicon) : ?>
        <link rel="icon" href="<?= $siteFavicon ?>" type="image/x-icon">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/header.css">
    <?php if ($customCss) : ?>
        <style>
            <?= $customCss ?>
        </style>
    <?php endif; ?>
    <!-- Dynamically injected CSS files -->
    <?php
    global $assetManager;
    if (!isset($assetManager)) {
        $assetManager = new AssetManager();
    }
    echo $assetManager->getCssLinks();
    ?>
    <?= $headerScripts ?>
    <?php if ($googleAnalyticsCode) : ?>
        <!-- Google Analytics -->
        <?= $googleAnalyticsCode ?>
    <?php endif; ?>

    <?php
    if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true) {
        echo '<link rel="stylesheet" href="' . BASE_URL . '/assets/css/admin-overlay.css">';
    }
    ?>
</head>

<body>
    <?php
    if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true) {
        $username = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
        echo '<div class="admin-overlay-container">
                <span>Welcome, ' . $username . '!</span>
                <a href="' . BASE_URL . '/public/admin/index.php">Dashboard</a>
                <a href="' . BASE_URL . '/public/admin/logout.php">Logout</a>
              </div>';
    }
    ?>
    <header class="header-wrap" role="banner">
        <div class="site-branding">
            <?php if ($siteLogo) : ?>
                <a href="<?= BASE_URL ?>" class="site-logo-link" aria-label="Go to homepage">
                    <img src="<?= $siteLogo ?>" alt="<?= $siteName ?>" class="site-logo">
                </a>
            <?php endif; ?>
            <span class="site-title" title="<?= $pageTitle ?>"><?= $pageTitle ?></span>
        </div>

        <nav class="header-nav" aria-label="Main navigation">
            <!-- Pages Dropdown -->
            <div class="header-menu-container">
                <button type="button" aria-haspopup="true" aria-expanded="false">Pages</button>
                <div class="header-dropdown-content" role="menu">
                    <?php foreach ($pages as $page) : ?>
                        <a role="menuitem" href="/<?= htmlspecialchars($page['slug'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Categories and Posts Dropdown -->
            <?php foreach ($categories as $category) : ?>
                <div class="header-menu-container">
                    <button type="button" aria-haspopup="true" aria-expanded="false"><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></button>
                    <div class="header-dropdown-content" role="menu">
                        <?php foreach ($postsByCategory[$category['slug']] as $post) : ?>
                            <a role="menuitem" href="/<?= htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars($post['slug'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </nav>
    </header>