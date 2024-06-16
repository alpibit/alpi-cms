<?php
if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '../../config/config.php';
    require_once __DIR__ . '../../config/database.php';
    require_once __DIR__ . '../../config/autoload.php';
    require_once __DIR__ . '../../utils/helpers.php';
    define('CONFIG_INCLUDED', true);
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    die("Error establishing a database connection.");
}

$settings = new Settings($conn);
$siteName = htmlspecialchars($settings->getSetting('site_name'), ENT_QUOTES, 'UTF-8');

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $siteName ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/header.css">
    <!-- Dynamically injected CSS files -->
    <?php global $assetManager;

    if (!isset($assetManager)) {
        $assetManager = new AssetManager();
    }
    
    echo $assetManager->getCssLinks(); ?>
</head>

<body>
    <header class="header-wrap">
        <h1><?= $siteName ?></h1>

        <!-- Pages Dropdown -->
        <div class="header-menu-container">
            <button>Pages</button>
            <div class="header-dropdown-content">
                <?php foreach ($pages as $page) : ?>
                    <a href="/<?= htmlspecialchars($page['slug'], ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Categories and Posts Dropdown -->
        <?php foreach ($categories as $category) : ?>
            <div class="header-menu-container">
                <button><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></button>
                <div class="header-dropdown-content">
                    <?php foreach ($postsByCategory[$category['slug']] as $post) : ?>
                        <a href="/<?= htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars($post['slug'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </header>