<?php
header('Access-Control-Allow-Origin: *');

require '../config/database.php';
require '../config/config.php';
require '../config/autoload.php';

$blockType = $_GET['type'] ?? '';
$index = $_GET['index'] ?? 0;
$blockDataJson = $_GET['blockData'] ?? '{}';

$block = json_decode($blockDataJson, true) ?: [];

$db = new Database();
$conn = $db->connect();
$upload = new Upload($conn);
$uploads = $upload->listFiles();

function renderInput($name, $value, $placeholder, $type = 'text')
{
    echo "<div class='alpi-form-group'>";
    echo "<label class='alpi-form-label'>{$placeholder}: <input class='alpi-form-input' type='{$type}' name='blocks[{$GLOBALS['index']}][{$name}]' value='" . htmlspecialchars($value) . "' placeholder='{$placeholder}'></label>";
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
        echo "<label class='alpi-form-label'>Background Type ({$size}): <select class='alpi-form-input' name='blocks[$index][background_type_{$size}]' id='background_type_{$index}_{$size}' onchange='updateBackgroundTypeFields($index, \"$size\")'>";
        foreach ($backgroundTypes as $value => $name) {
            $isSelected = ($value == $selectedBackgroundType) ? 'selected' : '';
            echo "<option value='$value' $isSelected>$name</option>";
        }
        echo "</select></label>";
        echo "</div>";

        renderFileUpload("background_image_{$size}", $GLOBALS['uploads'], $block["background_image_{$size}"] ?? '');
    }

    renderColorPicker("background_color", $block["background_color"] ?? '', "Background Color", 'background');

    echo "<script>
        function updateBackgroundTypeFields(index, size) {
            var typeSelector = document.getElementById('background_type_' + index + '_' + size);
            var selectedType = typeSelector.value;
            var imageField = document.getElementById('background_image_' + index + '_' + size);
            if (imageField) {
                imageField.style.display = (selectedType == 'image') ? 'block' : 'none';
            }
            document.getElementById('background_color_' + index).style.display = (selectedType == 'color') ? 'block' : 'none';
        }
        " . implode('', array_map(fn($size) => "updateBackgroundTypeFields($index, '$size');", $sizes)) . "
    </script>";
}

function renderTextarea($name, $value, $placeholder)
{
    echo "<div class='alpi-form-group'>";
    echo "<label class='alpi-form-label'>{$placeholder}: <textarea class='alpi-form-input' name='blocks[{$GLOBALS['index']}][{$name}]'>" . htmlspecialchars($value) . "</textarea></label>";
    echo "</div>";
}

function renderSelect($name, $options, $selected, $label)
{
    echo "<div class='alpi-form-group'>";
    echo "<label class='alpi-form-label'>{$label}: <select class='alpi-form-input' name='blocks[{$GLOBALS['index']}][{$name}]'>";
    foreach ($options as $value => $display) {
        $isSelected = ($value == $selected) ? 'selected' : '';
        echo "<option value='{$value}' {$isSelected}>{$display}</option>";
    }
    echo "</select></label>";
    echo "</div>";
}

function renderFileUpload($name, $uploads, $selected)
{
    echo "<div class='alpi-form-group'>";
    echo "<label class='alpi-form-label'>Choose a file: <select class='alpi-form-input' name='blocks[{$GLOBALS['index']}][{$name}]'>";
    foreach ($uploads as $upload) {
        $isSelected = ($upload['url'] == $selected) ? 'selected' : '';
        echo "<option value='{$upload['url']}' {$isSelected}>{$upload['url']}</option>";
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
        echo "<div class='alpi-form-group'>";
        echo "<label class='alpi-form-label'>Select Posts: <select class='alpi-form-input' name='blocks[{$index}][selected_post_ids][]' multiple>";
        foreach ($availablePosts as $post) {
            $selected = in_array($post['id'], explode(',', $block['selected_post_ids'] ?? '')) ? 'selected' : '';
            echo "<option value='{$post['id']}' {$selected}>{$post['title']}</option>";
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
            echo "<div class='alpi-gallery-image' data-index='{$imageIndex}'>";
            renderFileUpload("gallery_data][{$imageIndex}][url", $uploads, $image['url'] ?? '');
            renderInput("gallery_data][{$imageIndex}][alt_text", $image['alt_text'] ?? '', 'Alt Text', 'text');
            renderInput("gallery_data][{$imageIndex}][caption", $image['caption'] ?? '', 'Caption', 'text');
            echo "<div class='alpi-btn-group'>";
            echo "<button type='button' class='alpi-btn alpi-btn-secondary' onclick='shiftImageUpward({$index}, {$imageIndex})'>Move Up</button>";
            echo "<button type='button' class='alpi-btn alpi-btn-secondary' onclick='shiftImageDownward({$index}, {$imageIndex})'>Move Down</button>";
            echo "<button type='button' class='alpi-btn alpi-btn-danger' onclick='removeGalleryImage({$index}, {$imageIndex})'>Delete Image</button>";
            echo "</div>";
            echo "</div>";
        }
        echo "<button type='button' class='alpi-btn alpi-btn-primary' onclick='addGalleryImage({$index})'>Add New Image</button>";
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
            echo "<button type='button' class='alpi-btn alpi-btn-secondary' onclick='shiftQuoteUpward({$index}, {$quoteIndex})'>Move Up</button>";
            echo "<button type='button' class='alpi-btn alpi-btn-secondary' onclick='shiftQuoteDownward({$index}, {$quoteIndex})'>Move Down</button>";
            echo "<button type='button' class='alpi-btn alpi-btn-danger' onclick='removeQuote({$index}, {$quoteIndex})'>Delete Quote</button>";
            echo "</div>";
            echo "</div>";
        }
        echo "<button type='button' class='alpi-btn alpi-btn-primary' onclick='addQuote({$index})'>Add New Quote</button>";
        echo "</div>";
        renderSpacingControls($block, 'quote');
        break;

    case 'accordion':
        $accordionData = json_decode($block['accordion_data'] ?? '[]', true);
        if (!is_array($accordionData)) {
            $accordionData = [];
        }

        if (!empty($accordionData)) {
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
        }

        echo "<button type='button' class='alpi-btn alpi-btn-primary' onclick='insertAccordionSection({$index})'>Add New Section</button>";
        renderSpacingControls($block, 'accordion');
        break;

    case 'audio':
        renderInput('audio_url', $block['audio_url'] ?? '', 'Audio URL');
        renderFileUpload('video_url', $uploads, $block['audio_url'] ?? '');
        renderSelect('audio_source', $videoSourceOptions, $block['audio_source'] ?? '', 'Audio Source');
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
