<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/autoload.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');
$segments = explode('/', $path);
$postSlug = $segments[1] ?? null;

if (!$postSlug) {
    die('Post slug is missing.');
}

$db = new Database();
$conn = $db->connect();

if (!($conn instanceof PDO)) {
    die("Error establishing a database connection.");
}

$postObj = new Post($conn);

$singlePost = $postObj->getPostBySlug($postSlug);

if (!$singlePost) {
    header("HTTP/1.0 404 Not Found");
    echo "Post not found.";
    exit;
}

$blocks = $postObj->getBlocksByPostId($singlePost['id']) ?? [];

function renderBlock($block, $page, $conn)
{
    $blockType = $block['type'];
    $blockTitle = $block['title'] ?? '';
    $blockContent = $block['content'] ?? '';
    $blockSelectedPostIds = implode(',', $block['block_data']['selected_post_ids'] ?? []);
    $blockImagePath = $block['block_data']['image_path'] ?? '';
    $blockAltText = $block['block_data']['alt_text'] ?? '';
    $blockCaption = $block['block_data']['caption'] ?? '';
    $blockUrl1 = $block['block_data']['url1'] ?? '';
    $blockCtaText1 = $block['block_data']['cta_text1'] ?? '';
    $blockUrl2 = $block['block_data']['url2'] ?? '';
    $blockCtaText2 = $block['block_data']['cta_text2'] ?? '';
    $blockVideoUrl = $block['video_url'] ?? '';
    $blockVideoSource = $block['video_source'] ?? '';
    $blockVideoFile = $block['video_file'] ?? '';
    $blockAudioUrl = $block['block_data']['audio_url'] ?? '';
    $blockAudioSource = $block['block_data']['audio_source'] ?? '';
    $blockAudioFile = $block['block_data']['audio_file'] ?? '';
    $blockSliderSpeed = $block['block_data']['slider_speed'] ?? 0;
    $blockFreeCodeContent = $block['block_data']['free_code_content'] ?? '';
    $blockMapEmbedCode = $block['block_data']['map_embed_code'] ?? '';
    $blockFormShortcode = $block['block_data']['form_shortcode'] ?? '';
    $blockGalleryData = $block['block_data']['gallery_data'] ?? '';
    $blockQuotesData = $block['block_data']['quotes_data'] ?? '';
    $blockAccordionData = $block['block_data']['accordion_data'] ?? '';
    $blockBackgroundImagePath = $block['block_data']['background_image_path'] ?? '';
    $blockBackgroundVideoUrl = $block['block_data']['background_video_url'] ?? '';
    $blockBackgroundStyle = $block['block_data']['background_style'] ?? 'cover';
    $blockHeroLayout = $block['block_data']['hero_layout'] ?? 'center';
    $blockOverlayColor = $block['block_data']['overlay_color'] ?? '';
    $blockTextColor = $block['block_data']['text_color'] ?? '';
    $blockLayout1 = $block['block_data']['layout1'] ?? '';
    $blockLayout2 = $block['block_data']['layout2'] ?? '';
    $blockLayout3 = $block['block_data']['layout3'] ?? '';
    $blockLayout4 = $block['block_data']['layout4'] ?? '';
    $blockLayout5 = $block['block_data']['layout5'] ?? '';
    $blockLayout6 = $block['block_data']['layout6'] ?? '';
    $blockLayout7 = $block['block_data']['layout7'] ?? '';
    $blockLayout8 = $block['block_data']['layout8'] ?? '';
    $blockLayout9 = $block['block_data']['layout9'] ?? '';
    $blockLayout10 = $block['block_data']['layout10'] ?? '';
    $blockStyle1 = $block['block_data']['style1'] ?? '';
    $blockStyle2 = $block['block_data']['style2'] ?? '';
    $blockStyle3 = $block['block_data']['style3'] ?? '';
    $blockStyle4 = $block['block_data']['style4'] ?? '';
    $blockStyle5 = $block['block_data']['style5'] ?? '';
    $blockStyle6 = $block['block_data']['style6'] ?? '';
    $blockStyle7 = $block['block_data']['style7'] ?? '';
    $blockStyle8 = $block['block_data']['style8'] ?? '';
    $blockStyle9 = $block['block_data']['style9'] ?? '';
    $blockStyle10 = $block['block_data']['style10'] ?? '';
    $blockResponsiveClass = $block['block_data']['responsive_class'] ?? '';
    $blockResponsiveStyle = $block['block_data']['responsive_style'] ?? '';
    $blockBackgroundColor = $block['block_data']['background_color'] ?? '';
    $blockBorderStyle = $block['block_data']['border_style'] ?? '';
    $blockBorderColor = $block['block_data']['border_color'] ?? '';
    $blockBorderWidth = $block['block_data']['border_width'] ?? '';
    $blockAnimationType = $block['block_data']['animation_type'] ?? '';
    $blockAnimationDuration = $block['block_data']['animation_duration'] ?? '';
    $blockCustomCss = $block['block_data']['custom_css'] ?? '';
    $blockCustomJs = $block['block_data']['custom_js'] ?? '';
    $blockAriaLabel = $block['block_data']['aria_label'] ?? '';
    $blockTextSize = $block['block_data']['text_size'] ?? '';
    $blockClass = $block['block_data']['class'] ?? '';
    $blockMetafield1 = $block['block_data']['metafield1'] ?? '';
    $blockMetafield2 = $block['block_data']['metafield2'] ?? '';
    $blockMetafield3 = $block['block_data']['metafield3'] ?? '';
    $blockMetafield4 = $block['block_data']['metafield4'] ?? '';
    $blockMetafield5 = $block['block_data']['metafield5'] ?? '';
    $blockMetafield6 = $block['block_data']['metafield6'] ?? '';
    $blockMetafield7 = $block['block_data']['metafield7'] ?? '';
    $blockMetafield8 = $block['block_data']['metafield8'] ?? '';
    $blockMetafield9 = $block['block_data']['metafield9'] ?? '';
    $blockMetafield10 = $block['block_data']['metafield10'] ?? '';
    $blockStatus = 'active';
    $blockPath = __DIR__ . '/../blocks/types/' . $blockType . '.php';

    if (file_exists($blockPath)) {
        include($blockPath);
    } else {
        echo 'Block type not found.';
    }
}


?>

<?php include __DIR__ . '/../templates/header.php'; ?>

<main class="content">
    <?php foreach ($blocks as $block) {
        renderBlock($block, $singlePost, $conn);
    } ?>
</main>

<?php include __DIR__ . '/../templates/footer.php'; ?>