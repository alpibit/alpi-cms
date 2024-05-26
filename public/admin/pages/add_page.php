<?php
ob_start();

require '../../../config/database.php';
require '../../../config/config.php';
require '../../../config/autoload.php';
require '../auth_check.php';

$db = new Database();
$conn = $db->connect();
$page = new Page($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'];
    $mainImagePath = $_POST['main_image_path'];
    $showMainImage = isset($_POST['show_main_image']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $contentBlocks = [];
    $slug = $page->generateSlug($title);
    $userId = $_SESSION['user_id'];

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

        if ($block['type'] == 'image' || $block['type'] == 'image_text') {
            $blockData['image_path'] = $_POST['blocks'][$index]['image_path'] ?? '';
        }

        if ($block['type'] == 'cta') {
            $blockData['url1'] = $block['url1'] ?? '';
            $blockData['cta_text1'] = $block['cta_text1'] ?? '';
            $blockData['url2'] = $block['url2'] ?? '';
            $blockData['cta_text2'] = $block['cta_text2'] ?? '';
        }

        if ($block['type'] == 'post_picker') {
            $blockData['selected_post_ids'] = implode(',', $block['selected_post_ids'] ?? []);
        }

        $contentBlocks[] = $blockData;
    }

    $page->addPage($title, $subtitle, $mainImagePath, $showMainImage, $isActive, $contentBlocks, $userId);
    header("Location: " . BASE_URL . "/public/admin/pages/index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add New Page</title>
    <link rel="stylesheet" href="/assets/css/uploads.css">
</head>

<body class="add-page-wrap">
    <h1>Add New Page</h1>
    <form action="" method="POST">
        Title: <input type="text" name="title"><br>
        Subtitle: <input type="text" name="subtitle"><br>
        Featured Image:
        <select name="main_image_path">
            <?php
            $uploads = (new Upload($conn))->listFiles();
            foreach ($uploads as $upload) {
                echo "<option value='{$upload['url']}'>{$upload['url']}</option>";
            }
            ?>
        </select><br>
        Show Main Image: <input type="checkbox" name="show_main_image"><br>
        Is Active: <input type="checkbox" name="is_active"><br>
        <div id="contentBlocks">
            <div class='block'>
                <label>Type:</label>
                <select name='blocks[0][type]' onchange='loadSelectedBlockContent(this, 0)'>
                    <option value='text'>Text</option>
                    <option value='image_text'>Image + Text</option>
                    <option value='image'>Image</option>
                    <option value='cta'>Call to Action</option>
                    <option value='post_picker'>Post Picker</option>
                </select><br>
                <div class='block-content'></div>
                <div class='buttons'>...</div>
            </div>
        </div>
        <button type="button" onclick="addBlock()">Add Another Block</button>
        <input type="submit" value="Add Page">
    </form>
    <script src="/assets/js/posts-blocks.js"></script>
</body>

</html>
<?php ob_end_flush(); ?>