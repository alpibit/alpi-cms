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
            'padding_bottom_desktop' => $block['padding_bottom_desktop'] ?? '',
            'padding_top_tablet' => $block['padding_top_tablet'] ?? '',
            'padding_bottom_tablet' => $block['padding_bottom_tablet'] ?? '',
            'padding_top_mobile' => $block['padding_top_mobile'] ?? '',
            'padding_bottom_mobile' => $block['padding_bottom_mobile'] ?? '',
            'margin_top_desktop' => $block['margin_top_desktop'] ?? '',
            'margin_bottom_desktop' => $block['margin_bottom_desktop'] ?? '',
            'margin_top_tablet' => $block['margin_top_tablet'] ?? '',
            'margin_bottom_tablet' => $block['margin_bottom_tablet'] ?? '',
            'margin_top_mobile' => $block['margin_top_mobile'] ?? '',
            'margin_bottom_mobile' => $block['margin_bottom_mobile'] ?? '',
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

    // Call the addPage function
    $page->addPage($title, $subtitle, $mainImagePath, $showMainImage, $isActive, $contentBlocks, $userId);

    header("Location: " . BASE_URL . "/public/admin/pages/index.php");
    exit;
}


include '../../../templates/header-admin.php';
?>

<div class="alpi-admin-content">
    <h1 class="alpi-text-primary alpi-mb-lg">Add New Page</h1>

    <form action="" method="POST" class="alpi-form">
        <div class="alpi-card alpi-mb-lg">
            <h2 class="alpi-card-header">Page Details</h2>
            <div class="alpi-card-body">
                <div class="alpi-form-group">
                    <label for="title" class="alpi-form-label">Title:</label>
                    <input type="text" id="title" name="title" class="alpi-form-input" placeholder="Enter page title" required>
                </div>

                <div class="alpi-form-group">
                    <label for="subtitle" class="alpi-form-label">Subtitle:</label>
                    <input type="text" id="subtitle" name="subtitle" class="alpi-form-input" placeholder="Enter page subtitle">
                </div>

                <div class="alpi-form-group">
                    <label for="main_image_path" class="alpi-form-label">Featured Image:</label>
                    <select id="main_image_path" name="main_image_path" class="alpi-form-input">
                        <option value="">Select an image</option>
                        <?php
                        $uploads = (new Upload($conn))->listFiles();
                        foreach ($uploads as $upload) {
                            echo "<option value='" . htmlspecialchars($upload['url'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($upload['url'], ENT_QUOTES, 'UTF-8') . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="alpi-form-group">
                    <label class="alpi-form-checkbox">
                        <input type="checkbox" name="show_main_image">
                        Show Main Image
                    </label>
                </div>

                <div class="alpi-form-group">
                    <label class="alpi-form-checkbox">
                        <input type="checkbox" name="is_active">
                        Is Active
                    </label>
                </div>
            </div>
        </div>

        <div class="alpi-card alpi-mb-lg">
            <h2 class="alpi-card-header">Content Blocks</h2>
            <div class="alpi-card-body">
                <fieldset id="contentBlocks">
                    <div class='alpi-block' data-index='0'>
                        <label class="alpi-form-label">Block Type:</label>
                        <select name='blocks[0][type]' class="alpi-form-input alpi-mb-sm" onchange='loadSelectedBlockContent(this, 0)'>
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
                        </select>
                        <div class='alpi-block-content alpi-mb-md'></div>
                        <div class='alpi-btn-group'>
                            <!-- Buttons will be dynamically added here by JS -->
                        </div>
                    </div>
                </fieldset>
                <button type="button" onclick="addBlock()" class="alpi-btn alpi-btn-secondary alpi-mt-md">Add Another Block</button>
            </div>
        </div>

        <div class="alpi-text-right">
            <button type="submit" class="alpi-btn alpi-btn-primary">Add Page</button>
        </div>
    </form>
</div>

<script src="/assets/js/posts-blocks.js"></script>

<?php
include '../../../templates/footer-admin.php';
ob_end_flush();
?>