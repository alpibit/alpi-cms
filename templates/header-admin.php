<?php

// Admin Header

$settingsAdmin = new Settings($conn);
$adminSiteName = $settingsAdmin->getSetting('site_title') . ' Admin';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($adminSiteName, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/assets/css/admin/admin-global.css">
    <?php if (!empty($custom_css)) : ?>
        <link rel="stylesheet" href="<?= htmlspecialchars(BASE_URL . $custom_css, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
</head>

<body class="alpi-admin-body">
    <header class="alpi-admin-header">
        <div class="alpi-container alpi-flex alpi-items-center alpi-justify-between">
            <h1 class="alpi-admin-title"><?= htmlspecialchars($adminSiteName, ENT_QUOTES, 'UTF-8') ?></h1>
            <nav class="alpi-admin-nav">
                <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/index.php" class="alpi-nav-link">Dashboard</a>
                <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/posts/index.php" class="alpi-nav-link">Manage Posts</a>
                <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/pages/index.php" class="alpi-nav-link">Manage Pages</a>
                <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/categories/index.php" class="alpi-nav-link">Manage Categories</a>
                <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/settings/index.php" class="alpi-nav-link">Settings</a>
                <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/settings/change_password.php" class="alpi-nav-link">Change Password</a>
                <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/settings/data-management.php" class="alpi-nav-link">Data Management</a>
                <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/uploads/index.php" class="alpi-nav-link">Uploads</a>
                <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/logout.php" class="alpi-nav-link">Logout</a>
            </nav>
        </div>
    </header>
    <main class="alpi-admin-main">
        <div class="alpi-container">