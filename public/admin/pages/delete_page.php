<?php

require '../../../config/autoload.php';
require '../../../config/database.php';
require '../../../config/config.php';
require '../auth_check.php';

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
