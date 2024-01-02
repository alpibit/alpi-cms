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
