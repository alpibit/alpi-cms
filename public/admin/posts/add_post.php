<?php
ob_start();

require '../../../config/database.php';
require '../../../config/config.php';
require '../../../config/autoload.php';
require '../auth_check.php';

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
        if ($block['type'] == 'accordion') {
            $block['accordion_data'] = json_encode($block['accordion_data'] ?? []);
        }
        if ($block['type'] == 'slider_gallery') {
            $block['gallery_data'] = json_encode($block['gallery_data'] ?? []);
        }
        if ($block['type'] == 'quote') {
            $block['quotes_data'] = json_encode($block['quotes_data'] ?? []);
        }

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
            'slider_type' => $block['slider_type'] ?? 'image',
            'slider_speed' => $block['slider_speed'] ?? 0,
            'free_code_content' => $block['free_code_content'] ?? '',
            'map_embed_code' => $block['map_embed_code'] ?? '',
            'form_shortcode' => $block['form_shortcode'] ?? '',
            'gallery_data' => $block['gallery_data'] ?? '',
            'quotes_data' => $block['quotes_data'] ?? '',
            'accordion_data' => $block['accordion_data'] ?? '',
            'transition_speed' => $block['transition_speed'] ?? 0,
            'transition_effect' => $block['transition_effect'] ?? 'slide',
            'autoplay' => $block['autoplay'] ?? false,
            'pause_on_hover' => $block['pause_on_hover'] ?? false,
            'infinite_loop' => $block['infinite_loop'] ?? false,
            'show_arrows' => $block['show_arrows'] ?? false,
            'show_dots' => $block['show_dots'] ?? false,
            'dot_style' => $block['dot_style'] ?? 'classic',
            'lazy_load' => $block['lazy_load'] ?? false,
            'aspect_ratio' => $block['aspect_ratio'] ?? '16:9',
            'lightbox_enabled' => $block['lightbox_enabled'] ?? false,
            'thumbnail_path' => $block['thumbnail_path'] ?? '',
            'background_type_desktop' => $block['background_type_desktop'] ?? 'image',
            'background_type_tablet' => $block['background_type_tablet'] ?? 'image',
            'background_type_mobile' => $block['background_type_mobile'] ?? 'image',
            'background_image_desktop' => $block['background_image_desktop'] ?? '',
            'background_image_tablet' => $block['background_image_tablet'] ?? '',
            'background_image_mobile' => $block['background_image_mobile'] ?? '',
            'background_video_url' => $block['background_video_url'] ?? '',
            'background_color' => $block['background_color'] ?? '',
            'background_opacity_desktop' => $block['background_opacity_desktop'] ?? 1.0,
            'background_opacity_tablet' => $block['background_opacity_tablet'] ?? 1.0,
            'background_opacity_mobile' => $block['background_opacity_mobile'] ?? 1.0,
            'background_style' => $block['background_style'] ?? 'cover',
            'hero_layout' => $block['hero_layout'] ?? 'center',
            'overlay_color' => $block['overlay_color'] ?? '',
            'text_color' => $block['text_color'] ?? '',
            'text_size_desktop' => $block['text_size_desktop'] ?? '',
            'text_size_tablet' => $block['text_size_tablet'] ?? '',
            'text_size_mobile' => $block['text_size_mobile'] ?? '',
            'padding_top_desktop' => $block['padding_top_desktop'] ?? '',
            'padding_right_desktop' => $block['padding_right_desktop'] ?? '',
            'padding_bottom_desktop' => $block['padding_bottom_desktop'] ?? '',
            'padding_left_desktop' => $block['padding_left_desktop'] ?? '',
            'padding_top_tablet' => $block['padding_top_tablet'] ?? '',
            'padding_right_tablet' => $block['padding_right_tablet'] ?? '',
            'padding_bottom_tablet' => $block['padding_bottom_tablet'] ?? '',
            'padding_left_tablet' => $block['padding_left_tablet'] ?? '',
            'padding_top_mobile' => $block['padding_top_mobile'] ?? '',
            'padding_right_mobile' => $block['padding_right_mobile'] ?? '',
            'padding_bottom_mobile' => $block['padding_bottom_mobile'] ?? '',
            'padding_left_mobile' => $block['padding_left_mobile'] ?? '',
            'margin_top_desktop' => $block['margin_top_desktop'] ?? '',
            'margin_right_desktop' => $block['margin_right_desktop'] ?? '',
            'margin_bottom_desktop' => $block['margin_bottom_desktop'] ?? '',
            'margin_left_desktop' => $block['margin_left_desktop'] ?? '',
            'margin_top_tablet' => $block['margin_top_tablet'] ?? '',
            'margin_right_tablet' => $block['margin_right_tablet'] ?? '',
            'margin_bottom_tablet' => $block['margin_bottom_tablet'] ?? '',
            'margin_left_tablet' => $block['margin_left_tablet'] ?? '',
            'margin_top_mobile' => $block['margin_top_mobile'] ?? '',
            'margin_right_mobile' => $block['margin_right_mobile'] ?? '',
            'margin_bottom_mobile' => $block['margin_bottom_mobile'] ?? '',
            'margin_left_mobile' => $block['margin_left_mobile'] ?? '',
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

    // Call the addPost function
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
    <link rel="stylesheet" href="/assets/css/editor.css">
</head>

<body class="add-post-wrap">
    <h1>Add New Post</h1>
    <form action="" method="POST">
        <fieldset>
            <legend>Post Details</legend>
            <label>Title: <input type="text" name="title" placeholder="Title"></label><br>
            <label>Subtitle: <input type="text" name="subtitle" placeholder="Subtitle"></label><br>
            <label>Featured Image:
                <select name="main_image_path">
                    <?php
                    $uploads = (new Upload($conn))->listFiles();
                    foreach ($uploads as $upload) {
                        echo "<option value='{$upload['url']}'>{$upload['url']}</option>";
                    }
                    ?>
                </select>
            </label><br>
            <label>Show Main Image: <input type="checkbox" name="show_main_image"></label><br>
            <label>Is Active: <input type="checkbox" name="is_active"></label><br>
            <label>Category:
                <select name="category_id">
                    <?php foreach ($categories as $cat) : ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </label><br>
        </fieldset>

        <fieldset id="contentBlocks">
            <legend>Content Blocks</legend>
            <div class='block' data-index='0'>
                <label>Type:</label>
                <select name='blocks[0][type]' onchange='loadSelectedBlockContent(this, 0)'>
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
        </fieldset>

        <div class="form-buttons">
            <input type="submit" value="Add Post">
            <button type="button" onclick="addBlock()">Add Another Block</button>
        </div>
    </form>
    <script src="/assets/js/posts-blocks.js"></script>
</body>

</html>
<?php ob_end_flush(); ?>