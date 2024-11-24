<?php

require '../../../config/autoload.php';
require '../../../config/database.php';
require '../../../config/config.php';
require '../auth_check.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: " . BASE_URL . "/public/admin/categories/index.php");
    exit;
}

$db = new Database();
$conn = $db->connect();
$category = new Category($conn);

$categoryId = intval($_GET['id']);
$category->deleteCategory($categoryId);

header("Location: " . BASE_URL . "/public/admin/categories/index.php");
exit;
