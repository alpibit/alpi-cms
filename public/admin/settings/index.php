<?php

session_start();

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    header('Location: /public/admin/login.php');
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

require '../../../config/database.php';
require '../../../config/config.php';
require '../../../config/autoload.php';

$db = new Database();
$conn = $db->connect();

if (!$conn instanceof PDO) {
    die("Error establishing a database connection.");
}

$settings = new Settings($conn);

$site_name = $settings->getSetting('site_name');
$footer_text = $settings->getSetting('footer_text');
$header_logo = $settings->getSetting('header_logo');


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $site_name = $_POST['site_name'];
    $footer_text = $_POST['footer_text'];
    $header_logo = $_POST['header_logo'];

    $settings->updateSetting('site_name', $site_name);
    $settings->updateSetting('footer_text', $footer_text);
    $settings->updateSetting('header_logo', $header_logo);
}

?>

<h1>Settings</h1>

<form action="" method="POST">
    Site Name: <input type="text" name="site_name" value="<?= $site_name ?>"><br>
    Footer Text: <input type="text" name="footer_text" value="<?= $footer_text ?>"><br>
    Header Logo: <input type="text" name="header_logo" value="<?= $header_logo ?>"><br>
    <input type="submit" value="Update">
</form>
