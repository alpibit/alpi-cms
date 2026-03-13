<?php
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../public/admin/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Method not allowed.';
    exit;
}

$allowedBlockTypes = [
    'text',
    'image_text',
    'image',
    'cta',
    'post_picker',
    'video',
    'slider_gallery',
    'quote',
    'accordion',
    'audio',
    'free_code',
    'map',
    'form',
    'hero',
];

$blockType = trim((string) ($_GET['type'] ?? ''));
$index = filter_input(
    INPUT_GET,
    'index',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 0]]
);

if ($blockType === '' || $index === false || $index === null || !in_array($blockType, $allowedBlockTypes, true)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Invalid block request.';
    exit;
}

$block = [];

$db = new Database();
$conn = $db->connect();

if (!$conn instanceof PDO) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Unable to load block data.';
    exit;
}

$upload = new Upload($conn);
$uploads = $upload->listFiles();

header('Content-Type: text/html; charset=UTF-8');

function renderInput($name, $value, $placeholder, $type = 'text')
{
    $escapedPlaceholder = htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8');
    $escapedValue = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

    echo "<div class='alpi-form-group'>";
    echo "<label class='alpi-form-label'>{$escapedPlaceholder}: <input class='alpi-form-input' type='{$type}' name='blocks[{$GLOBALS['index']}][{$name}]' value='{$escapedValue}' placeholder='{$escapedPlaceholder}'></label>";
    echo "</div>";
}

function renderSpacingControls($block, $type)
{
    $sizes = ['desktop', 'tablet', 'mobile'];
    $properties = ['padding', 'margin'];
    $directions = ['top', 'bottom'];

    $uniqueId = uniqid($type . '-tabs-');

    echo "<div id='{$uniqueId}' class='alpi-tabs-container'>";
    echo "<div class='alpi-tabs'>";
    foreach ($sizes as $size) {
        echo "<button class='alpi-tab' onclick=\"openTab(event, '{$type}-{$size}-{$uniqueId}', '{$uniqueId}')\">" . ucfirst($size) . "</button>";
    }
    echo "</div>";

    foreach ($sizes as $size) {
        echo "<div id='{$type}-{$size}-{$uniqueId}' class='alpi-tab-content' style='display: none;'>";
        foreach ($properties as $property) {
            foreach ($directions as $direction) {
                $fullName = "{$property}_{$direction}_{$size}";
                renderInput($fullName, $block[$fullName] ?? '', ucfirst($property) . ' ' . ucfirst($direction) . ' (' . ucfirst($size) . ')');
            }
        }
        echo "</div>";
    }
    echo "</div>";
}

function renderBackgroundOptions($block, $index)
{
    $backgroundTypes = ['image' => 'Image', 'color' => 'Color'];
    $sizes = ['desktop', 'tablet', 'mobile'];

    foreach ($sizes as $size) {
        $selectedBackgroundType = $block["background_type_{$size}"] ?? 'image';

        echo "<div class='alpi-form-group'>";
        echo "<label class='alpi-form-label'>Background Type ({$size}): <select class='alpi-form-input alpi-background-type-selector' name='blocks[$index][background_type_{$size}]' data-background-size='{$size}' onchange='updateBackgroundTypeFields(this, \"$size\")'>";
        foreach ($backgroundTypes as $value => $name) {
            $isSelected = ($value == $selectedBackgroundType) ? 'selected' : '';
            echo "<option value='$value' $isSelected>$name</option>";
        }
        echo "</select></label>";
        echo "</div>";

        renderFileUpload("background_image_{$size}", $GLOBALS['uploads'], $block["background_image_{$size}"] ?? '');
    }

    renderColorPicker("background_color", $block["background_color"] ?? '', "Background Color", 'background');
}

function renderTextarea($name, $value, $placeholder)
{
    $escapedPlaceholder = htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8');

    echo "<div class='alpi-form-group'>";
    echo "<label class='alpi-form-label'>{$escapedPlaceholder}: <textarea class='alpi-form-input' name='blocks[{$GLOBALS['index']}][{$name}]'>" . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . "</textarea></label>";
    echo "</div>";
}

function renderSelect($name, $options, $selected, $label)
{
    $escapedLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');

    echo "<div class='alpi-form-group'>";
    echo "<label class='alpi-form-label'>{$escapedLabel}: <select class='alpi-form-input' name='blocks[{$GLOBALS['index']}][{$name}]'>";
    foreach ($options as $value => $display) {
        $isSelected = ($value == $selected) ? 'selected' : '';
        echo "<option value='" . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . "' {$isSelected}>" . htmlspecialchars((string) $display, ENT_QUOTES, 'UTF-8') . "</option>";
    }
    echo "</select></label>";
    echo "</div>";
}

function renderFileUpload($name, $uploads, $selected)
{
    echo "<div class='alpi-form-group'>";
    echo "<label class='alpi-form-label'>Choose a file: <select class='alpi-form-input' name='blocks[{$GLOBALS['index']}][{$name}]'>";
    foreach ($uploads as $upload) {
        $uploadUrl = (string) ($upload['url'] ?? '');
        $isSelected = ($uploadUrl == $selected) ? 'selected' : '';
        $escapedUploadUrl = htmlspecialchars($uploadUrl, ENT_QUOTES, 'UTF-8');
        echo "<option value='{$escapedUploadUrl}' {$isSelected}>{$escapedUploadUrl}</option>";
    }
    echo "</select></label>";
    echo "</div>";
}

function renderVideoSourceSelector($name, $selected)
{
    $videoSourceOptions = ['url' => 'URL', 'upload' => 'Upload'];
    echo "<div class='alpi-form-group'>";
    echo "<label class='alpi-form-label'>Video Source: <select class='alpi-form-input video-source-selector' name='blocks[{$GLOBALS['index']}][{$name}]' onchange='toggleSourceField(this, \"video\")'>";
    foreach ($videoSourceOptions as $value => $display) {
        $isSelected = ($value == $selected) ? 'selected' : '';
        echo "<option value='{$value}' {$isSelected}>{$display}</option>";
    }
    echo "</select></label>";
    echo "</div>";
}

function renderAudioSourceSelector($name, $selected)
{
    $audioSourceOptions = ['url' => 'URL', 'upload' => 'Upload'];
    echo "<div class='alpi-form-group'>";
    echo "<label class='alpi-form-label'>Audio Source: <select class='alpi-form-input audio-source-selector' name='blocks[{$GLOBALS['index']}][{$name}]' onchange='toggleSourceField(this, \"audio\")'>";
    foreach ($audioSourceOptions as $value => $display) {
        $isSelected = ($value == $selected) ? 'selected' : '';
        echo "<option value='{$value}' {$isSelected}>{$display}</option>";
    }
    echo "</select></label>";
    echo "</div>";
}

function renderCheckbox($name, $checked, $label)
{
    $isChecked = $checked ? 'checked' : '';
    echo "<div class='alpi-form-group'>";
    echo "<label class='alpi-form-checkbox'><input type='checkbox' name='blocks[{$GLOBALS['index']}][{$name}]' {$isChecked}> {$label}</label>";
    echo "</div>";
}

function renderNumberInput($name, $value, $placeholder)
{
    renderInput($name, $value, $placeholder, 'number');
}

function renderColorPicker($name, $value, $placeholder, $type = 'text')
{
    $defaultValue = ($type == 'text') ? '#000000' : '#ffffff';
    $value = empty($value) ? $defaultValue : $value;
    renderInput($name, $value, $placeholder, 'color');
}

function renderDateTimeLocal($name, $value, $placeholder)
{
    renderInput($name, $value, $placeholder, 'datetime-local');
}

$videoSourceOptions = ['url' => 'URL', 'upload' => 'Upload'];
$backgroundStyleOptions = ['cover' => 'Cover', 'contain' => 'Contain', 'repeat' => 'Repeat', 'no-repeat' => 'No Repeat'];
$heroLayoutOptions = ['left' => 'Left', 'center' => 'Center', 'right' => 'Right'];

switch ($blockType) {
    case 'text':
        renderInput('title', $block['title'] ?? '', 'Title');
        renderTextarea('content', $block['content'] ?? '', 'Content');
        renderColorPicker('text_color', $block['text_color'] ?? '', 'Text Color', 'text');
        renderColorPicker('background_color', $block['background_color'] ?? '', 'Background Color', 'background');
        renderNumberInput('text_size_desktop', $block['text_size_desktop'] ?? '', 'Text Size (Desktop)');
        renderNumberInput('text_size_tablet', $block['text_size_tablet'] ?? '', 'Text Size (Tablet)');
        renderNumberInput('text_size_mobile', $block['text_size_mobile'] ?? '', 'Text Size (Mobile)');
        renderSpacingControls($block, 'text');
        break;

    case 'image_text':
        renderInput('title', $block['title'] ?? '', 'Title');
        renderTextarea('content', $block['content'] ?? '', 'Content');
        renderFileUpload('image_path', $uploads, $block['image_path'] ?? '');
        renderInput('alt_text', $block['alt_text'] ?? '', 'Alt Text');
        renderInput('caption', $block['caption'] ?? '', 'Caption');
        renderColorPicker('background_color', $block['background_color'] ?? '', 'Background Color', 'background');
        renderNumberInput('title_size_desktop', $block['title_size_desktop'] ?? '', 'Title Size (Desktop)');
        renderNumberInput('title_size_tablet', $block['title_size_tablet'] ?? '', 'Title Size (Tablet)');
        renderNumberInput('title_size_mobile', $block['title_size_mobile'] ?? '', 'Title Size (Mobile)');
        renderNumberInput('content_size_desktop', $block['content_size_desktop'] ?? '', 'Content Size (Desktop)');
        renderNumberInput('content_size_tablet', $block['content_size_tablet'] ?? '', 'Content Size (Tablet)');
        renderNumberInput('content_size_mobile', $block['content_size_mobile'] ?? '', 'Content Size (Mobile)');
        renderSpacingControls($block, 'image_text');
        break;

    case 'image':
        renderFileUpload('image_path', $uploads, $block['image_path'] ?? '');
        renderInput('alt_text', $block['alt_text'] ?? '', 'Alt Text');
        renderInput('caption', $block['caption'] ?? '', 'Caption');
        renderSpacingControls($block, 'image');
        break;

    case 'cta':
        renderInput('title', $block['title'] ?? '', 'Title');
        renderInput('url1', $block['url1'] ?? '', 'URL 1');
        renderInput('cta_text1', $block['cta_text1'] ?? '', 'CTA Text 1');
        renderInput('url2', $block['url2'] ?? '', 'URL 2');
        renderInput('cta_text2', $block['cta_text2'] ?? '', 'CTA Text 2');
        renderBackgroundOptions($block, $index);
        renderColorPicker('text_color', $block['text_color'] ?? '', 'Text Color', 'text');
        renderNumberInput('title_size_desktop', $block['title_size_desktop'] ?? '', 'Title Size (Desktop)');
        renderNumberInput('title_size_tablet', $block['title_size_tablet'] ?? '', 'Title Size (Tablet)');
        renderNumberInput('title_size_mobile', $block['title_size_mobile'] ?? '', 'Title Size (Mobile)');
        renderSpacingControls($block, 'cta');
        break;

    case 'post_picker':
        $postObj = new Post($conn);
        $availablePosts = $postObj->getAllPosts();
        $selectedPostIds = explode(',', (string) ($block['selected_post_ids'] ?? ''));
        echo "<div class='alpi-form-group'>";
        echo "<label class='alpi-form-label'>Select Posts: <select class='alpi-form-input' name='blocks[{$index}][selected_post_ids][]' multiple>";
        foreach ($availablePosts as $availablePost) {
            $postId = (string) ($availablePost['id'] ?? '');
            $selected = in_array($postId, $selectedPostIds, true) ? 'selected' : '';
            echo "<option value='" . htmlspecialchars($postId, ENT_QUOTES, 'UTF-8') . "' {$selected}>" . htmlspecialchars((string) ($availablePost['title'] ?? ''), ENT_QUOTES, 'UTF-8') . "</option>";
        }
        echo "</select></label>";
        echo "</div>";
        renderSpacingControls($block, 'post_picker');
        break;

    case 'video':
        echo "<div class='alpi-form-group video-url-field' id='video-url-field-{$index}' style='display:none;'>";
        renderInput('video_url', $block['video_url'] ?? '', 'Video URL', 'text');
        echo "</div>";

        echo "<div class='alpi-form-group video-upload-field' id='video-upload-field-{$index}' style='display:none;'>";
        renderFileUpload('video_file', $uploads, $block['video_file'] ?? '');
        echo "</div>";
        renderVideoSourceSelector('video_source', $block['video_source'] ?? '');
        renderSpacingControls($block, 'video');
        break;

    case 'slider_gallery':
        $galleryData = json_decode($block['gallery_data'] ?? '[]', true);
        if (!is_array($galleryData)) {
            $galleryData = [];
        }

        echo "<div class='alpi-slider-gallery' data-index='{$index}'>";
        foreach ($galleryData as $imageIndex => $image) {
            echo "<div class='alpi-gallery-image alpi-card alpi-mb-md' data-index='{$imageIndex}'>";
            echo "<div class='alpi-form-group'>";
            echo "<label class='alpi-form-label'>Image: <select class='alpi-form-input' name='blocks[{$index}][gallery_data][{$imageIndex}][url]'>";
            foreach ($uploads as $upload) {
                $uploadUrl = (string) ($upload['url'] ?? '');
                $selected = ($uploadUrl == ($image['url'] ?? '')) ? 'selected' : '';
                $escapedUploadUrl = htmlspecialchars($uploadUrl, ENT_QUOTES, 'UTF-8');
                echo "<option value='{$escapedUploadUrl}' {$selected}>{$escapedUploadUrl}</option>";
            }
            echo "</select></label>";
            echo "</div>";
            echo "<div class='alpi-form-group'>";
            echo "<label class='alpi-form-label'>Alt Text: <input class='alpi-form-input' type='text' name='blocks[{$index}][gallery_data][{$imageIndex}][alt_text]' value='" . htmlspecialchars($image['alt_text'] ?? '') . "' placeholder='Alt Text'></label>";
            echo "</div>";
            echo "<div class='alpi-form-group'>";
            echo "<label class='alpi-form-label'>Caption: <input class='alpi-form-input' type='text' name='blocks[{$index}][gallery_data][{$imageIndex}][caption]' value='" . htmlspecialchars($image['caption'] ?? '') . "' placeholder='Caption'></label>";
            echo "</div>";
            echo "<div class='alpi-btn-group'>";
            echo "<button type='button' class='alpi-btn alpi-btn-secondary' onclick='shiftImageUpward(this)'>Move Up</button>";
            echo "<button type='button' class='alpi-btn alpi-btn-secondary' onclick='shiftImageDownward(this)'>Move Down</button>";
            echo "<button type='button' class='alpi-btn alpi-btn-danger' onclick='removeGalleryImage(this)'>Delete Image</button>";
            echo "</div>";
            echo "</div>";
        }
        echo "<button type='button' class='alpi-btn alpi-btn-primary' onclick='addGalleryImage(this)'>Add New Image</button>";
        echo "</div>";
        renderSpacingControls($block, 'slider_gallery');
        break;

    case 'quote':
        $quotesData = json_decode($block['quotes_data'] ?? '[]', true);
        if (!is_array($quotesData)) {
            $quotesData = [];
        }

        echo "<div class='alpi-quote-wrapper' data-index='{$index}'>";
        foreach ($quotesData as $quoteIndex => $quote) {
            echo "<div class='alpi-quote alpi-card alpi-mb-md' data-index='{$quoteIndex}'>";
            renderTextarea("quotes_data][{$quoteIndex}][content", $quote['content'] ?? '', 'Quote Content');
            renderInput("quotes_data][{$quoteIndex}][author", $quote['author'] ?? '', 'Author', 'text');
            renderColorPicker("quotes_data][{$quoteIndex}][text_color", $quote['text_color'] ?? '', 'Text Color', 'text');
            renderColorPicker("quotes_data][{$quoteIndex}][background_color", $quote['background_color'] ?? '', 'Background Color', 'background');
            renderNumberInput("quotes_data][{$quoteIndex}][text_size_desktop", $quote['text_size_desktop'] ?? '', 'Text Size (Desktop)');
            renderNumberInput("quotes_data][{$quoteIndex}][text_size_tablet", $quote['text_size_tablet'] ?? '', 'Text Size (Tablet)');
            renderNumberInput("quotes_data][{$quoteIndex}][text_size_mobile", $quote['text_size_mobile'] ?? '', 'Text Size (Mobile)');
            echo "<div class='alpi-btn-group'>";
            echo "<button type='button' class='alpi-btn alpi-btn-secondary' onclick='shiftQuoteUpward(this)'>Move Up</button>";
            echo "<button type='button' class='alpi-btn alpi-btn-secondary' onclick='shiftQuoteDownward(this)'>Move Down</button>";
            echo "<button type='button' class='alpi-btn alpi-btn-danger' onclick='removeQuote(this)'>Delete Quote</button>";
            echo "</div>";
            echo "</div>";
        }
        echo "<button type='button' class='alpi-btn alpi-btn-primary' onclick='addQuote(this)'>Add New Quote</button>";
        echo "</div>";
        renderSpacingControls($block, 'quote');
        break;

    case 'accordion':
        $accordionData = json_decode($block['accordion_data'] ?? '[]', true);
        if (!is_array($accordionData)) {
            $accordionData = [];
        }

        if (!empty($accordionData)) {
            echo "<div class='alpi-accordion-wrapper' data-index='{$index}'>";
            foreach ($accordionData as $sectionIndex => $section) {
                $newIndex = $sectionIndex;
                $blockIndex = $index;
                echo "<div class='alpi-accordion-section alpi-card alpi-mb-md' data-index='{$newIndex}'>";
                echo "<div class='alpi-form-group'>";
                echo "<label class='alpi-form-label'>Section Title: <input type='text' class='alpi-form-input' name='blocks[{$blockIndex}][accordion_data][{$newIndex}][title]' placeholder='Section Title' value='" . htmlspecialchars($section['title']) . "'></label>";
                echo "</div>";
                echo "<div class='alpi-form-group'>";
                echo "<label class='alpi-form-label'>Section Content: <textarea class='alpi-form-input' name='blocks[{$blockIndex}][accordion_data][{$newIndex}][content]' placeholder='Section Content' rows='4'>" . htmlspecialchars($section['content']) . "</textarea></label>";
                echo "</div>";
                renderColorPicker("accordion_data][{$newIndex}][text_color", $section['text_color'] ?? '', 'Text Color', 'text');
                renderColorPicker("accordion_data][{$newIndex}][background_color", $section['background_color'] ?? '', 'Background Color', 'background');
                renderNumberInput("accordion_data][{$newIndex}][title_size_desktop", $section['title_size_desktop'] ?? '', 'Title Size (Desktop)');
                renderNumberInput("accordion_data][{$newIndex}][title_size_tablet", $section['title_size_tablet'] ?? '', 'Title Size (Tablet)');
                renderNumberInput("accordion_data][{$newIndex}][title_size_mobile", $section['title_size_mobile'] ?? '', 'Title Size (Mobile)');
                renderNumberInput("accordion_data][{$newIndex}][content_size_desktop", $section['content_size_desktop'] ?? '', 'Content Size (Desktop)');
                renderNumberInput("accordion_data][{$newIndex}][content_size_tablet", $section['content_size_tablet'] ?? '', 'Content Size (Tablet)');
                renderNumberInput("accordion_data][{$newIndex}][content_size_mobile", $section['content_size_mobile'] ?? '', 'Content Size (Mobile)');
                echo "<div class='alpi-btn-group'>";
                echo "<button type='button' class='alpi-btn alpi-btn-secondary' onclick='shiftAccordionSectionUp(this)'>Move Up</button>";
                echo "<button type='button' class='alpi-btn alpi-btn-secondary' onclick='shiftAccordionSectionDown(this)'>Move Down</button>";
                echo "<button type='button' class='alpi-btn alpi-btn-danger' onclick='removeAccordionSection(this)'>Delete</button>";
                echo "</div>";
                echo "</div>";
            }
            echo "<button type='button' class='alpi-btn alpi-btn-primary' onclick='insertAccordionSection(this)'>Add New Section</button>";
            echo "</div>";
        } else {
            echo "<div class='alpi-accordion-wrapper' data-index='{$index}'>";
            echo "<button type='button' class='alpi-btn alpi-btn-primary' onclick='insertAccordionSection(this)'>Add New Section</button>";
            echo "</div>";
        }
        renderSpacingControls($block, 'accordion');
        break;

    case 'audio':
        $selectedAudioFile = $block['audio_file'] ?? ($block['video_url'] ?? '');
        echo "<div class='alpi-form-group audio-url-field' id='audio-url-field-{$index}' style='display:none;'>";
        renderInput('audio_url', $block['audio_url'] ?? '', 'Audio URL', 'text');
        echo "</div>";

        echo "<div class='alpi-form-group audio-upload-field' id='audio-upload-field-{$index}' style='display:none;'>";
        renderFileUpload('audio_file', $uploads, $selectedAudioFile);
        echo "</div>";

        renderAudioSourceSelector('audio_source', $block['audio_source'] ?? '');
        renderSpacingControls($block, 'audio');
        break;

    case 'free_code':
        renderTextarea('free_code_content', $block['free_code_content'] ?? '', 'Free Code Content');
        renderSpacingControls($block, 'free_code');
        break;

    case 'map':
        renderTextarea('map_embed_code', $block['map_embed_code'] ?? '', 'Map Embed Code');
        renderSpacingControls($block, 'map');
        break;

    case 'form':
        renderInput('form_shortcode', $block['form_shortcode'] ?? '', 'Form Shortcode');
        renderSpacingControls($block, 'form');
        break;

    case 'hero':
        renderInput('title', $block['title'] ?? '', 'Title');
        renderTextarea('content', $block['content'] ?? '', 'Content');
        renderSelect('hero_layout', $heroLayoutOptions, $block['hero_layout'] ?? '', 'Hero Layout');
        renderBackgroundOptions($block, $index);
        renderColorPicker('text_color', $block['text_color'] ?? '', 'Text Color', 'text');
        renderColorPicker('overlay_color', $block['overlay_color'] ?? '', 'Overlay Color', 'background');
        renderNumberInput('title_size_desktop', $block['title_size_desktop'] ?? '', 'Title Size (Desktop)');
        renderNumberInput('title_size_tablet', $block['title_size_tablet'] ?? '', 'Title Size (Tablet)');
        renderNumberInput('title_size_mobile', $block['title_size_mobile'] ?? '', 'Title Size (Mobile)');
        renderNumberInput('content_size_desktop', $block['content_size_desktop'] ?? '', 'Content Size (Desktop)');
        renderNumberInput('content_size_tablet', $block['content_size_tablet'] ?? '', 'Content Size (Tablet)');
        renderNumberInput('content_size_mobile', $block['content_size_mobile'] ?? '', 'Content Size (Mobile)');
        renderSpacingControls($block, 'hero');
        break;

    default:
        echo "<p class='alpi-text-danger'>Unknown block type</p>";
}
