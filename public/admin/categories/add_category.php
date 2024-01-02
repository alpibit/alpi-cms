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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $category->addCategory($name);
    header("Location: " . BASE_URL . "/public/admin/categories/index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add New Category</title>
    <link rel="stylesheet" href="/assets/css/uploads.css">
</head>

<body class="add-category-wrap">
    <h1>Add New Category</h1>
    <form action="" method="POST">
        Name: <input type="text" name="name"><br>
        <input type="submit" value="Add Category">
    </form>
</body>

</html>
<?php ob_end_flush(); ?>