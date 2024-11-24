<?php
if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../config/autoload.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../utils/helpers.php';
    require_once __DIR__ . '/../config/config.php';
    define('CONFIG_INCLUDED', true);
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');
$segments = explode('/', $path);
$pageSlug = $segments[0] ?? null;

if (!$pageSlug) {
    die('Page slug is missing.');
}

$db = new Database();
$conn = $db->connect();

if (!($conn instanceof PDO)) {
    die("Error establishing a database connection.");
}

$pageObj = new Page($conn);

$pageData = $pageObj->getPageBySlug($pageSlug);

if (!$pageData) {
    header("HTTP/1.0 404 Not Found");
    echo "Page not found.";
    exit;
}

$singlePage = $pageObj->getPageById($pageData['id']);
$blocks = $pageObj->getBlocksByPageId($pageData['id']);

$assetManager = new AssetManager();

$assetManager->addCss('global.css');
$assetManager->addJs('global.js');

function renderBlock($block, $page, $conn, $assetManager)
{
    $blockType = $block['type'];
    $blockTitle = $block['title'] ?? '';
    $blockContent = $block['content'] ?? '';
    $blockSelectedPostIds = implode(',', $block['block_data']['selected_post_ids'] ?? []);
    $blockImagePath = $block['image_path'] ?? '';
    $blockAltText = $block['alt_text'] ?? '';
    $blockCaption = $block['caption'] ?? '';
    $blockUrl1 = $block['url1'] ?? '';
    $blockCtaText1 = $block['cta_text1'] ?? '';
    $blockUrl2 = $block['url2'] ?? '';
    $blockCtaText2 = $block['cta_text2'] ?? '';
    $blockVideoUrl = $block['video_url'] ?? '';
    $blockVideoSource = $block['video_source'] ?? '';
    $blockVideoFile = $block['video_file'] ?? '';
    $blockAudioUrl = $block['audio_url'] ?? '';
    $blockAudioSource = $block['audio_source'] ?? '';
    $blockAudioFile = $block['audio_file'] ?? '';
    $blockSliderSpeed = $block['slider_speed'] ?? 0;
    $blockFreeCodeContent = $block['free_code_content'] ?? '';
    $blockMapEmbedCode = $block['map_embed_code'] ?? '';
    $blockFormShortcode = $block['form_shortcode'] ?? '';
    $blockGalleryData = $block['gallery_data'] ?? '';
    $blockQuotesData = $block['quotes_data'] ?? '';
    $blockAccordionData = $block['accordion_data'] ?? '';
    $blockTransitionSpeed = $block['transition_speed'] ?? 0;
    $blockTransitionEffect = $block['transition_effect'] ?? '';
    $blockAutoplay = $block['autoplay'] ?? false;
    $blockPauseOnHover = $block['pause_on_hover'] ?? false;
    $blockInfiniteLoop = $block['infinite_loop'] ?? false;
    $blockShowArrows = $block['show_arrows'] ?? false;
    $blockShowDots = $block['show_dots'] ?? false;
    $blockDotStyle = $block['dot_style'] ?? '';
    $blockLazyLoad = $block['lazy_load'] ?? false;
    $blockAspectRatio = $block['aspect_ratio'] ?? '';
    $blockLightboxEnabled = $block['lightbox_enabled'] ?? false;
    $blockThumbnailPath = $block['thumbnail_path'] ?? '';
    $blockBackgroundTypeDesktop = $block['background_type_desktop'] ?? '';
    $blockBackgroundTypeTablet = $block['background_type_tablet'] ?? '';
    $blockBackgroundTypeMobile = $block['background_type_mobile'] ?? '';
    $blockBackgroundImageDesktop = $block['background_image_desktop'] ?? '';
    $blockBackgroundImageTablet = $block['background_image_tablet'] ?? '';
    $blockBackgroundImageMobile = $block['background_image_mobile'] ?? '';
    $blockBackgroundVideoUrl = $block['background_video_url'] ?? '';
    $blockBackgroundColor = $block['background_color'] ?? '';
    $blockBackgroundOpacityDesktop = $block['background_opacity_desktop'] ?? 0;
    $blockBackgroundOpacityTablet = $block['background_opacity_tablet'] ?? 0;
    $blockBackgroundOpacityMobile = $block['background_opacity_mobile'] ?? 0;
    $blockBackgroundStyle = $block['background_style'] ?? 'cover';
    $blockHeroLayout = $block['hero_layout'] ?? 'center';
    $blockOverlayColor = $block['overlay_color'] ?? '';
    $blockTextColor = $block['text_color'] ?? '';
    $blockTextSizeDesktop = $block['text_size_desktop'] ?? '';
    $blockTextSizeTablet = $block['text_size_tablet'] ?? '';
    $blockTextSizeMobile = $block['text_size_mobile'] ?? '';
    $blockPaddingTopDesktop = $block['padding_top_desktop'] ?? '';
    $blockPaddingBottomDesktop = $block['padding_bottom_desktop'] ?? '';
    $blockPaddingTopTablet = $block['padding_top_tablet'] ?? '';
    $blockPaddingBottomTablet = $block['padding_bottom_tablet'] ?? '';
    $blockPaddingTopMobile = $block['padding_top_mobile'] ?? '';
    $blockPaddingBottomMobile = $block['padding_bottom_mobile'] ?? '';
    $blockMarginTopDesktop = $block['margin_top_desktop'] ?? '';
    $blockMarginBottomDesktop = $block['margin_bottom_desktop'] ?? '';
    $blockMarginTopTablet = $block['margin_top_tablet'] ?? '';
    $blockMarginBottomTablet = $block['margin_bottom_tablet'] ?? '';
    $blockMarginTopMobile = $block['margin_top_mobile'] ?? '';
    $blockMarginBottomMobile = $block['margin_bottom_mobile'] ?? '';
    $blockLayout1 = $block['layout1'] ?? '';
    $blockLayout2 = $block['layout2'] ?? '';
    $blockLayout3 = $block['layout3'] ?? '';
    $blockLayout4 = $block['layout4'] ?? '';
    $blockLayout5 = $block['layout5'] ?? '';
    $blockLayout6 = $block['layout6'] ?? '';
    $blockLayout7 = $block['layout7'] ?? '';
    $blockLayout8 = $block['layout8'] ?? '';
    $blockLayout9 = $block['layout9'] ?? '';
    $blockLayout10 = $block['layout10'] ?? '';
    $blockStyle1 = $block['style1'] ?? '';
    $blockStyle2 = $block['style2'] ?? '';
    $blockStyle3 = $block['style3'] ?? '';
    $blockStyle4 = $block['style4'] ?? '';
    $blockStyle5 = $block['style5'] ?? '';
    $blockStyle6 = $block['style6'] ?? '';
    $blockStyle7 = $block['style7'] ?? '';
    $blockStyle8 = $block['style8'] ?? '';
    $blockStyle9 = $block['style9'] ?? '';
    $blockStyle10 = $block['style10'] ?? '';
    $blockResponsiveClass = $block['responsive_class'] ?? '';
    $blockResponsiveStyle = $block['responsive_style'] ?? '';
    $blockBorderStyle = $block['border_style'] ?? '';
    $blockBorderColor = $block['border_color'] ?? '';
    $blockBorderWidth = $block['border_width'] ?? '';
    $blockAnimationType = $block['animation_type'] ?? '';
    $blockAnimationDuration = $block['animation_duration'] ?? '';
    $blockCustomCss = $block['custom_css'] ?? '';
    $blockCustomJs = $block['custom_js'] ?? '';
    $blockAriaLabel = $block['aria_label'] ?? '';
    $blockTextSize = $block['text_size'] ?? '';
    $blockClass = $block['class'] ?? '';
    $blockMetafield1 = $block['metafield1'] ?? '';
    $blockMetafield2 = $block['metafield2'] ?? '';
    $blockMetafield3 = $block['metafield3'] ?? '';
    $blockMetafield4 = $block['metafield4'] ?? '';
    $blockMetafield5 = $block['metafield5'] ?? '';
    $blockMetafield6 = $block['metafield6'] ?? '';
    $blockMetafield7 = $block['metafield7'] ?? '';
    $blockMetafield8 = $block['metafield8'] ?? '';
    $blockMetafield9 = $block['metafield9'] ?? '';
    $blockMetafield10 = $block['metafield10'] ?? '';
    $blockStatus = 'active';

    $assetManager->addCss("blocks/{$blockType}.css");
    $assetManager->addJs("blocks/{$blockType}.js");

    $blockPath = __DIR__ . '/../blocks/types/' . $blockType . '.php';

    if (file_exists($blockPath)) {
        include($blockPath);
    } else {
        echo 'Block type not found.';
    }
}


include __DIR__ . '/../templates/header.php';
?>

<main class="content">
    <h1><?php echo htmlspecialchars($singlePage['title']); ?></h1>
    <?php foreach ($blocks as $block) {
        renderBlock($block, $singlePage, $conn, $assetManager);
    } ?>
</main>

<?php include __DIR__ . '/../templates/footer.php'; ?>