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

$postData = $post->getPostById($_GET['id']);
$postData = $postData[0];
$categories = $category->getAllCategories();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'];
    $mainImagePath = $_POST['main_image_path'];
    $showMainImage = isset($_POST['show_main_image']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $categoryId = $_POST['category_id'] ?? null;
    $contentBlocks = [];
    $slug = $post->generateSlug($title);

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
            'video_file' => $block['video_file'] ?? '',
            'audio_url' => $block['audio_url'] ?? '',
            'audio_source' => $block['audio_source'] ?? '',
            'audio_file' => $block['audio_file'] ?? '',
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

    $userId = $_SESSION['user_id'] ?? 0;
    $post->updatePost($_GET['id'], $title, $contentBlocks, $slug, $userId, $subtitle, $mainImagePath, $showMainImage, $isActive, $categoryId);

    header("Location: " . BASE_URL . "/public/admin/index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Post</title>
    <link rel="stylesheet" href="/assets/css/uploads.css">
</head>

<body class="edit-post-wrap">
    <h1>Edit Post</h1>
    <form action="" method="POST">
        Title: <input type="text" name="title" value="<?= isset($postData['title']) ? $postData['title'] : '' ?>"><br>
        Subtitle: <input type="text" name="subtitle" value="<?= isset($postData['subtitle']) ? $postData['subtitle'] : '' ?>"><br>
        Featured Image:
        <select name="main_image_path">
            <?php
            $uploads = (new Upload($conn))->listFiles();
            foreach ($uploads as $upload) {
                $selected = $postData['main_image_path'] == $upload['url'] ? 'selected' : '';
                echo "<option value='{$upload['url']}' {$selected}>{$upload['url']}</option>";
            }
            ?>
        </select><br>
        Show Main Image: <input type="checkbox" name="show_main_image" <?= $postData['show_main_image'] ? 'checked' : '' ?>><br>
        Is Active: <input type="checkbox" name="is_active" <?= $postData['is_active'] ? 'checked' : '' ?>><br>
        <label>Category:</label>
        <select name="category_id">
            <?php foreach ($categories as $cat) : ?>
                <?php $selected = ($postData['category_id'] == $cat['id']) ? 'selected' : ''; ?>
                <option value="<?= $cat['id'] ?>" <?= $selected ?>><?= $cat['name'] ?></option>
            <?php endforeach; ?>
        </select><br>


        <div id="contentBlocks">
            <?php
            if (isset($postData['blocks']) && is_array($postData['blocks'])) {
                foreach ($postData['blocks'] as $index => $block) {
                    echo "<div class='block'>";
                    echo "<label>Type:</label>";
                    echo "<select name='blocks[$index][type]' onchange='loadBlockContent(this, $index)'>";
                    echo "<option value='text' " . ($block['type'] == 'text' ? 'selected' : '') . ">Text</option>";
                    echo "<option value='image_text' " . ($block['type'] == 'image_text' ? 'selected' : '') . ">Image + Text</option>";
                    echo "<option value='image' " . ($block['type'] == 'image' ? 'selected' : '') . ">Image</option>";
                    echo "<option value='cta' " . ($block['type'] == 'cta' ? 'selected' : '') . ">Call to Action</option>";
                    echo "<option value='post_picker' " . ($block['type'] == 'post_picker' ? 'selected' : '') . ">Post Picker</option>";
                    echo "<option value='video' " . ($block['type'] == 'video' ? 'selected' : '') . ">Video</option>";
                    echo "<option value='slider_gallery' " . ($block['type'] == 'slider_gallery' ? 'selected' : '') . ">Slider Gallery</option>";
                    echo "<option value='quote' " . ($block['type'] == 'quote' ? 'selected' : '') . ">Quotes</option>";
                    echo "<option value='accordion' " . ($block['type'] == 'accordion' ? 'selected' : '') . ">Accordion</option>";
                    echo "<option value='audio' " . ($block['type'] == 'audio' ? 'selected' : '') . ">Audio</option>";
                    echo "<option value='free_code' " . ($block['type'] == 'free_code' ? 'selected' : '') . ">Free Code</option>";
                    echo "<option value='map' " . ($block['type'] == 'map' ? 'selected' : '') . ">Map</option>";
                    echo "<option value='form' " . ($block['type'] == 'form' ? 'selected' : '') . ">Form</option>";
                    echo "<option value='hero' " . ($block['type'] == 'hero' ? 'selected' : '') . ">Hero</option>";
                    echo "</select><br>";
                    $blockDataJson = htmlspecialchars(json_encode($block['block_data']), ENT_QUOTES, 'UTF-8');
                    echo "<div class='block-content' data-value='{$blockDataJson}'></div>";
                    echo "<div class='buttons'>...</div>";
                    echo "</div>";
                }
            }
            ?>
        </div>
        <input type="submit" value="Update Post">
        <button type="button" onclick="addBlock()">Add Another Block</button>
    </form>
    <script src="/assets/js/posts-blocks.js"></script>
</body>

</html>
<?php ob_end_flush(); ?>