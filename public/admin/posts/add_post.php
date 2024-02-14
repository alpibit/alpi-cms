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
$post = new Post($conn);
$category = new Category($conn);
$categories = $category->getAllCategories();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'];
    $mainImagePath = $_POST['main_image_path'];
    $showMainImage = isset($_POST['show_main_image']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $categoryId = $_POST['category_id'];
    $contentBlocks = [];
    $slug = $post->generateSlug($title);
    $userId = $_SESSION['user_id'];

    foreach ($_POST['blocks'] as $index => $block) {
        $blockData = [
            'type' => $block['type'],
            'title' => $block['title'] ?? '',
            'content' => $block['content'] ?? '',
            'selected_post_ids' => implode(',', $block['selected_post_ids'] ?? []),
            'image_path' => $block['image_path'] ?? '',
            'alt_text' => $block['alt_text'] ?? '',
            'caption' => $block['caption'] ?? '',
            'url1' => $block['url1'] ?? '',
            'cta_text1' => $block['cta_text1'] ?? '',
            'url2' => $block['url2'] ?? '',
            'cta_text2' => $block['cta_text2'] ?? '',
            'video_url' => $block['video_url'] ?? '',
            'video_source' => $block['video_source'] ?? '',
            'audio_url' => $block['audio_url'] ?? '',
            'audio_source' => $block['audio_source'] ?? '',
            'slider_speed' => $block['slider_speed'] ?? 0,
            'free_code_content' => $block['free_code_content'] ?? '',
            'map_embed_code' => $block['map_embed_code'] ?? '',
            'form_shortcode' => $block['form_shortcode'] ?? '',
            'gallery_data' => $block['gallery_data'] ?? '',
            'quotes_data' => $block['quotes_data'] ?? '',
            'accordion_data' => $block['accordion_data'] ?? '',
            'background_image_path' => $block['background_image_path'] ?? '',
            'background_video_url' => $block['background_video_url'] ?? '',
            'background_style' => $block['background_style'] ?? 'cover',
            'hero_layout' => $block['hero_layout'] ?? 'center',
            'overlay_color' => $block['overlay_color'] ?? '',
            'text_color' => $block['text_color'] ?? '',
            'layout1' => $block['layout1'] ?? '',
            'layout2' => $block['layout2'] ?? '',
            'layout3' => $block['layout3'] ?? '',
            'layout4' => $block['layout4'] ?? '',
            'layout5' => $block['layout5'] ?? '',
            'layout6' => $block['layout6'] ?? '',
            'layout7' => $block['layout7'] ?? '',
            'layout8' => $block['layout8'] ?? '',
            'layout9' => $block['layout9'] ?? '',
            'layout10' => $block['layout10'] ?? '',
            'style1' => $block['style1'] ?? '',
            'style2' => $block['style2'] ?? '',
            'style3' => $block['style3'] ?? '',
            'style4' => $block['style4'] ?? '',
            'style5' => $block['style5'] ?? '',
            'style6' => $block['style6'] ?? '',
            'style7' => $block['style7'] ?? '',
            'style8' => $block['style8'] ?? '',
            'style9' => $block['style9'] ?? '',
            'style10' => $block['style10'] ?? '',
            'responsive_class' => $block['responsive_class'] ?? '',
            'responsive_style' => $block['responsive_style'] ?? '',
            'background_color' => $block['background_color'] ?? '',
            'border_style' => $block['border_style'] ?? '',
            'border_color' => $block['border_color'] ?? '',
            'border_width' => $block['border_width'] ?? '',
            'animation_type' => $block['animation_type'] ?? '',
            'animation_duration' => $block['animation_duration'] ?? '',
            'custom_css' => $block['custom_css'] ?? '',
            'custom_js' => $block['custom_js'] ?? '',
            'aria_label' => $block['aria_label'] ?? '',
            'text_size' => $block['text_size'] ?? '',
            'class' => $block['class'] ?? '',
            'metafield1' => $block['metafield1'] ?? '',
            'metafield2' => $block['metafield2'] ?? '',
            'metafield3' => $block['metafield3'] ?? '',
            'metafield4' => $block['metafield4'] ?? '',
            'metafield5' => $block['metafield5'] ?? '',
            'metafield6' => $block['metafield6'] ?? '',
            'metafield7' => $block['metafield7'] ?? '',
            'metafield8' => $block['metafield8'] ?? '',
            'metafield9' => $block['metafield9'] ?? '',
            'metafield10' => $block['metafield10'] ?? '',
            'order_num' => $index + 1,
            'status' => 'active',
        ];

        $contentBlocks[] = $blockData;
    }

    // Call the new addPost function
    $post->addPost($title, $subtitle, $mainImagePath, $showMainImage, $isActive, $contentBlocks, $userId, $categoryId);

    header("Location: " . BASE_URL . "/public/admin/index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add New Post</title>
    <link rel="stylesheet" href="/assets/css/uploads.css">
</head>

<body class="add-post-wrap">
    <h1>Add New Post</h1>
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
        Category:
        <select name="category_id">
            <?php foreach ($categories as $category) : ?>
                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
            <?php endforeach; ?>
        </select><br>
        <div id="contentBlocks">
            <div class='block'>
                <label>Type:</label>
                <select name='blocks[0][type]' onchange='loadBlockContent(this, 0)'>
                    <option value='text'>Text</option>
                    <option value='image_text'>Image + Text</option>
                    <option value='image'>Image</option>
                    <option value='cta'>Call to Action</option>
                    <option value='post_picker'>Post Picker</option>
                    <option value='video'>Video</option>
                    <option value='slider_gallery'>Slider Gallery</option>
                    <option value='quote'>Quote</option>
                    <option value='accordion'>Accordion</option>
                    <option value='audio'>Audio</option>
                    <option value='free_code'>Free Code</option>
                    <option value='map'>Map</option>
                    <option value='form'>Form</option>
                    <option value='hero'>Hero</option>
                </select><br>
                <div class='block-content'></div>
                <div class='buttons'>...</div>
            </div>
        </div>
        <button type="button" onclick="addBlock()">Add Another Block</button>
        <input type="submit" value="Add Post">
    </form>
    <script src="/assets/js/posts-blocks.js"></script>
</body>

</html>
<?php ob_end_flush(); ?>