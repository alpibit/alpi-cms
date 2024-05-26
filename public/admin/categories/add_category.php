<?php
ob_start();

require '../../../config/database.php';
require '../../../config/config.php';
require '../../../config/autoload.php';
require '../auth_check.php';

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