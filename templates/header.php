<?php

$db = new Database();
$conn = $db->connect();

if (!$conn instanceof PDO) {
    die("Error establishing a database connection.");
}

$settings = new Settings($conn);
$siteName = $settings->getSetting('site_name');


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
        <div class="header-menu-container">
            <button>Posts</button>
            <div class="header-dropdown-content">
                <?php foreach ($latestPosts as $post) : ?>
                    <a href="/public/single-post.php?slug=<?php echo htmlspecialchars($post['slug'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </header>