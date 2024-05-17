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
    echo "<label>{$placeholder}: <input type='{$type}' name='blocks[{$GLOBALS['index']}][{$name}]' value='" . htmlspecialchars($value) . "' placeholder='{$placeholder}'></label><br>";
}

function renderSpacingControls($block, $type)
{
    $sizes = ['desktop', 'tablet', 'mobile'];
    $properties = ['padding', 'margin'];
    $directions = ['top', 'right', 'bottom', 'left'];

    foreach ($sizes as $size) {
        foreach ($properties as $property) {
            foreach ($directions as $direction) {
                $fullName = "{$property}_{$direction}_{$size}";
                renderInput($fullName, $block[$fullName] ?? '', ucfirst($property) . ' ' . ucfirst($direction) . ' (' . ucfirst($size) . ')');
            }
        }
    }
}

function renderBackgroundOptions($block, $index)
{
    $backgroundTypes = ['image' => 'Image', 'video' => 'Video', 'color' => 'Color'];
    $selectedBackgroundType = $block['background_type_desktop'] ?? 'image';

    echo "<label>Background Type: <select name='blocks[$index][background_type_desktop]' id='background_type_$index' onchange='updateBackgroundTypeFields($index)'>";
    foreach ($backgroundTypes as $value => $name) {
        $isSelected = ($value == $selectedBackgroundType) ? 'selected' : '';
        echo "<option value='$value' $isSelected>$name</option>";
    }
    echo "</select></label><br>";

    renderColorPicker("background_color", $block['background_color'] ?? '', 'Background Color');

    renderFileUpload("background_image_desktop", $GLOBALS['uploads'], $block['background_image_desktop'] ?? '');

    renderInput("background_video_url", $block['background_video_url'] ?? '', 'Background Video URL');

    echo "<script>
        function updateBackgroundTypeFields(index) {
            var typeSelector = document.getElementById('background_type_' + index);
            var selectedType = typeSelector.value;
            document.getElementById('background_color_' + index).style.display = (selectedType == 'color') ? 'block' : 'none';
            document.getElementById('background_image_desktop_' + index).style.display = (selectedType == 'image') ? 'block' : 'none';
            document.getElementById('background_video_url_' + index).style.display = (selectedType == 'video') ? 'block' : 'none';
        }
        updateBackgroundTypeFields($index); 
    </script>";
}


function renderTextarea($name, $value, $placeholder)
{
    echo "<label>{$placeholder}: <textarea name='blocks[{$GLOBALS['index']}][{$name}]'>" . htmlspecialchars($value) . "</textarea></label><br>";
}

function renderSelect($name, $options, $selected, $label)
{
    echo "<label>{$label}: <select name='blocks[{$GLOBALS['index']}][{$name}]'>";
    foreach ($options as $value => $display) {
        $isSelected = ($value == $selected) ? 'selected' : '';
        echo "<option value='{$value}' {$isSelected}>{$display}</option>";
    }
    echo "</select></label><br>";
}

function renderFileUpload($name, $uploads, $selected)
{
    echo "<label>Choose a file: <select name='blocks[{$GLOBALS['index']}][{$name}]'>";
    foreach ($uploads as $upload) {
        $isSelected = ($upload['url'] == $selected) ? 'selected' : '';
        echo "<option value='{$upload['url']}' {$isSelected}>{$upload['url']}</option>";
    }
    echo "</select></label><br>";
}

function renderVideoSourceSelector($name, $selected)
{
    $videoSourceOptions = ['url' => 'URL', 'upload' => 'Upload'];
    echo "<label>Video Source: <select class='video-source-selector' name='blocks[{$GLOBALS['index']}][{$name}]' onchange='toggleSourceField(this, \"video\")'>";
    foreach ($videoSourceOptions as $value => $display) {
        $isSelected = ($value == $selected) ? 'selected' : '';
        echo "<option value='{$value}' {$isSelected}>{$display}</option>";
    }
    echo "</select></label><br>";
}

function renderCheckbox($name, $checked, $label)
{
    $isChecked = $checked ? 'checked' : '';
    echo "<label>{$label}: <input type='checkbox' name='blocks[{$GLOBALS['index']}][{$name}]' {$isChecked}></label><br>";
}

function renderNumberInput($name, $value, $placeholder)
{
    renderInput($name, $value, $placeholder, 'number');
}

function renderColorPicker($name, $value, $placeholder)
{
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
        renderColorPicker('text_color', $block['text_color'] ?? '', 'Text Color');
        renderColorPicker('background_color', $block['background_color'] ?? '', 'Background Color');
        renderNumberInput('text_size', $block['text_size'] ?? '', 'Text Size');
        renderSpacingControls($block, 'text');
        break;

    case 'image_text':
        renderInput('title', $block['title'] ?? '', 'Title');
        renderTextarea('content', $block['content'] ?? '', 'Content');
        renderFileUpload('image_path', $uploads, $block['image_path'] ?? '');
        renderInput('alt_text', $block['alt_text'] ?? '', 'Alt Text');
        renderInput('caption', $block['caption'] ?? '', 'Caption');
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
        renderSpacingControls($block, 'cta');
        break;

    case 'post_picker':
        $postObj = new Post($conn);
        $availablePosts = $postObj->getAllPosts();
        echo "<label>Select Posts: <select name='blocks[{$index}][selected_post_ids][]' multiple>";
        foreach ($availablePosts as $post) {
            $selected = in_array($post['id'], explode(',', $block['selected_post_ids'] ?? '')) ? 'selected' : '';
            echo "<option value='{$post['id']}' {$selected}>{$post['title']}</option>";
        }
        echo "</select></label><br>";
        renderSpacingControls($block, 'post_picker');
        break;

    case 'video':
        echo "<div class='video-url-field' id='video-url-field-{$index}' style='display:none;'>";
        renderInput('video_url', $block['video_url'] ?? '', 'Video URL', 'text');
        echo "</div>";

        echo "<div class='video-upload-field' id='video-upload-field-{$index}' style='display:none;'>";
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

        echo "<div class='slider-gallery' data-index='{$index}'>";
        foreach ($galleryData as $imageIndex => $image) {
            echo "<div class='gallery-image' data-index='{$imageIndex}'>";
            renderFileUpload("gallery_data][{$imageIndex}][url", $uploads, $image['url'] ?? ''); // Changed
            renderInput("gallery_data][{$imageIndex}][alt_text", $image['alt_text'] ?? '', 'Alt Text', 'text'); // Changed
            renderInput("gallery_data][{$imageIndex}][caption", $image['caption'] ?? '', 'Caption', 'text'); // Changed
            echo "<div class='buttons'>";
            echo "<button type='button' onclick='shiftImageUpward({$index}, {$imageIndex})'>Move Up</button>";
            echo "<button type='button' onclick='shiftImageDownward({$index}, {$imageIndex})'>Move Down</button>";
            echo "<button type='button' onclick='removeGalleryImage({$index}, {$imageIndex})'>Delete Image</button>";
            echo "</div>";
            echo "</div>";
        }
        echo "<button type='button' onclick='addGalleryImage({$index})'>Add New Image</button>";
        echo "</div>";
        renderSpacingControls($block, 'slider_gallery');
        break;



    case 'quote':
        $quotesData = json_decode($block['quotes_data'] ?? '[]', true);
        if (!is_array($quotesData)) {
            $quotesData = [];
        }

        echo "<div class='quote-wrapper' data-index='{$index}'>";
        foreach ($quotesData as $quoteIndex => $quote) {
            echo "<div class='quote' data-index='{$quoteIndex}'>";
            renderTextarea("quotes_data][{$quoteIndex}][content", $quote['content'] ?? '', 'Quote Content');
            renderInput("quotes_data][{$quoteIndex}][author", $quote['author'] ?? '', 'Author', 'text'); 
            echo "<div class='buttons'>";
            echo "<button type='button' onclick='shiftQuoteUpward({$index}, {$quoteIndex})'>Move Up</button>";
            echo "<button type='button' onclick='shiftQuoteDownward({$index}, {$quoteIndex})'>Move Down</button>";
            echo "<button type='button' onclick='removeQuote({$index}, {$quoteIndex})'>Delete Quote</button>";
            echo "</div>";
            echo "</div>";
        }
        echo "<button type='button' onclick='addQuote({$index})'>Add New Quote</button>";
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
                $newSectionHtml = "<div class='accordion-section' data-index='{$newIndex}'>";
                $newSectionHtml .= "<label>Section Title: <input type='text' name='blocks[{$blockIndex}][accordion_data][{$newIndex}][title]' placeholder='Section Title' value='{$section['title']}'></label><br>";
                $newSectionHtml .= "<label>Section Content: <textarea name='blocks[{$blockIndex}][accordion_data][{$newIndex}][content]' placeholder='Section Content' rows='4'>{$section['content']}</textarea></label><br>";
                $newSectionHtml .= "<div class='buttons'>";
                $newSectionHtml .= "<button type='button' onclick='shiftAccordionSectionUp(this)'>Move Up</button>";
                $newSectionHtml .= "<button type='button' onclick='shiftAccordionSectionDown(this)'>Move Down</button>";
                $newSectionHtml .= "<button type='button' onclick='removeAccordionSection(this)'>Delete</button>";
                $newSectionHtml .= "</div>";
                $newSectionHtml .= "</div>";
                echo $newSectionHtml;
            }
        }

        echo "<button type='button' onclick='insertAccordionSection({$index})'>Add New Section</button>";
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
        renderSpacingControls($block, 'hero');
        break;

    default:
        echo "Unknown block type";
}
?>

<style>
    .block-wrapper {
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
    }
</style>