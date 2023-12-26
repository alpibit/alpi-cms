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
$page = new Page($conn);

$pageData = $page->getPageById($_GET['id']);
$pageData = $pageData[0];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $contentBlocks = [];

    foreach ($_POST['blocks'] as $index => $block) {
        $blockData = [
            'type' => $block['type'],
            'title' => $block['title'] ?? '',
            'style1' => $block['style1'] ?? '',
            'style2' => $block['style2'] ?? '',
            'style3' => $block['style3'] ?? '',
            'style4' => $block['style4'] ?? '',
            'style5' => $block['style5'] ?? '',
            'style6' => $block['style6'] ?? '',
            'style7' => $block['style7'] ?? '',
            'style8' => $block['style8'] ?? '',
            'background_color' => $block['background_color'] ?? '',
            'content' => $block['content'] ?? ''
        ];

        $contentBlocks[] = $blockData;
    }

    $userId = $_SESSION['user_id'] ?? 0;
    $page->updatePage($_GET['id'], $title, $contentBlocks, $userId);

    header("Location: " . BASE_URL . "/public/admin/pages/index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Page</title>
</head>

<body class="uploads-wrap">
    <h1>Edit Page</h1>
    <form action="" method="POST">
        Title: <input type="text" name="title" value="<?= isset($pageData['page_title']) ? $pageData['page_title'] : '' ?>"><br>
        <div id="contentBlocks">
        </div>
        <input type="submit" value="Update Page">
        <button type="button" onclick="addBlock()">Add Another Block</button>
    </form>
    <script src="/assets/js/posts-blocks.js"></script>
</body>

</html>
<?php ob_end_flush(); ?>