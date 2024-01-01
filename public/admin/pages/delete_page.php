<?php
session_start();

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    header('Location: path_to_your_login_page/login.php');
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

require '../../../config/database.php';
require '../../../config/config.php';
require '../../../config/autoload.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: " . BASE_URL . "/public/admin/pages/index.php");
    exit;
}

$db = new Database();
$conn = $db->connect();
$page = new Page($conn);

$pageId = intval($_GET['id']);
$page->deletePage($pageId);

header("Location: " . BASE_URL . "/public/admin/pages/index.php");
exit;
