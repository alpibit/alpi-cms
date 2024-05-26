<?php

require '../../../config/database.php';
require '../../../config/config.php';
require '../../../config/autoload.php';
require '../auth_check.php';

$db = new Database();
$conn = $db->connect();

if (!$conn instanceof PDO) {
    die("Error establishing a database connection.");
}

$settings = new Settings($conn);

$site_name = htmlspecialchars($settings->getSetting('site_name'), ENT_QUOTES, 'UTF-8');
$footer_text = htmlspecialchars($settings->getSetting('footer_text'), ENT_QUOTES, 'UTF-8');
$header_logo = htmlspecialchars($settings->getSetting('header_logo'), ENT_QUOTES, 'UTF-8');

$custom_css = "/assets/css/admin/settings.css";

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF token verification
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        die("CSRF token validation failed.");
    }

    // Update settings securely
    $site_name = $_POST['site_name'];
    $footer_text = $_POST['footer_text'];
    $header_logo = $_POST['header_logo'];

    $settings->updateSetting('site_name', $site_name);
    $settings->updateSetting('footer_text', $footer_text);
    $settings->updateSetting('header_logo', $header_logo);

    // Regenerate CSRF token
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include '../../../templates/header-admin.php';
?>

<div class="settings-container">
    <h1 class="settings-title">Settings</h1>

    <form action="" method="POST" class="settings-form">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

        <div class="form-group">
            <label for="site_name" class="form-label">Site Name:</label>
            <input type="text" id="site_name" name="site_name" class="form-input" value="<?= $site_name ?>">
        </div>

        <div class="form-group">
            <label for="footer_text" class="form-label">Footer Text:</label>
            <input type="text" id="footer_text" name="footer_text" class="form-input" value="<?= $footer_text ?>">
        </div>

        <div class="form-group">
            <label for="header_logo" class="form-label">Header Logo:</label>
            <input type="text" id="header_logo" name="header_logo" class="form-input" value="<?= $header_logo ?>">
        </div>

        <input type="submit" value="Update" class="form-submit">
    </form>
</div>

<?php include '../../../templates/footer-admin.php'; ?>