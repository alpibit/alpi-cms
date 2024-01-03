<?php

$db = new Database();
$conn = $db->connect();

$settings = new Settings($conn);
$siteName = $settings->getSetting('site_name');

$pageQuery = "SELECT title, slug FROM contents WHERE content_type_id = (SELECT id FROM content_types WHERE name = 'page')";
$pages = $conn->query($pageQuery)->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories
$categoryQuery = "SELECT name, slug FROM categories";
$categories = $conn->query($categoryQuery)->fetchAll(PDO::FETCH_ASSOC);

// Fetch posts under each category
$postsByCategory = [];
foreach ($categories as $category) {
    $postQuery = "SELECT title, slug FROM contents WHERE category_id = (SELECT id FROM categories WHERE slug = :slug) AND content_type_id = (SELECT id FROM content_types WHERE name = 'post')";
    $stmt = $conn->prepare($postQuery);
    $stmt->execute(['slug' => $category['slug']]);
    $postsByCategory[$category['slug']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/header.css">
</head>

<body>
    <header class="header-wrap">
        <h1><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?></h1>

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