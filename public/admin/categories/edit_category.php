<?php
ob_start();
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
$category = new Category($conn);

$categoryData = $category->getCategoryById($_GET['id']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $category->updateCategory($_GET['id'], $name);
    header("Location: " . BASE_URL . "/public/admin/categories/index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Category</title>
    <link rel="stylesheet" href="/assets/css/uploads.css">
</head>

<body class="edit-category-wrap">
    <h1>Edit Category</h1>
    <form action="" method="POST">
        Name: <input type="text" name="name" value="<?= isset($categoryData['name']) ? htmlspecialchars($categoryData['name']) : '' ?>"><br>
        <input type="submit" value="Update Category">
    </form>
</body>

</html>
<?php ob_end_flush(); ?>