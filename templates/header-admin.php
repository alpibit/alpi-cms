<?php

// Admin Header

$settingsAdmin = new Settings($conn);
$adminSiteName = $settingsAdmin->getSetting('site_name') . ' Admin';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($adminSiteName, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/assets/css/admin-dashboard.css">
    <?php if (!empty($custom_css)) : ?>
        <link rel="stylesheet" href="<?= htmlspecialchars(BASE_URL . $custom_css, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
</head>

<body class="admin-dashboard-wrap">

    <header class="admin-header">
        <h1><?= htmlspecialchars($adminSiteName, ENT_QUOTES, 'UTF-8') ?></h1>
        <nav class="admin-nav">
            <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/index.php">Dashboard</a>
            <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/posts/index.php">Manage Posts</a>
            <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/pages/index.php">Manage Pages</a>
            <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/categories/index.php">Manage Categories</a>
            <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/settings/index.php">Settings</a>
            <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/uploads/index.php">Uploads</a>
            <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/logout.php">Logout</a>
        </nav>
    </header>