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
        break;

    case 'image_text':
        renderInput('title', $block['title'] ?? '', 'Title');
        renderTextarea('content', $block['content'] ?? '', 'Content');
        renderFileUpload('image_path', $uploads, $block['image_path'] ?? '');
        renderInput('alt_text', $block['alt_text'] ?? '', 'Alt Text');
        renderInput('caption', $block['caption'] ?? '', 'Caption');
        break;

    case 'image':
        renderFileUpload('image_path', $uploads, $block['image_path'] ?? '');
        renderInput('alt_text', $block['alt_text'] ?? '', 'Alt Text');
        renderInput('caption', $block['caption'] ?? '', 'Caption');
        break;

    case 'cta':
        renderInput('title', $block['title'] ?? '', 'Title');
        renderInput('url1', $block['url1'] ?? '', 'URL 1');
        renderInput('cta_text1', $block['cta_text1'] ?? '', 'CTA Text 1');
        renderInput('url2', $block['url2'] ?? '', 'URL 2');
        renderInput('cta_text2', $block['cta_text2'] ?? '', 'CTA Text 2');
        break;

    case 'post_picker':
        $postObj = new Post($conn);
        $availablePosts = $postObj->getAllPosts();
        echo "<label>Select Posts: <select name='blocks[{$index}][selected_post_ids][]' multiple>";
        foreach ($availablePosts as $post) {
            $isSelected = in_array($post['id'], $block['selected_post_ids'] ?? []) ? 'selected' : '';
            echo "<option value='{$post['id']}' {$isSelected}>{$post['title']}</option>";
        }
        echo "</select></label><br>";
        break;

    case 'video':
        echo "<div class='video-url-field' id='video-url-field-{$index}' style='display:none;'>";
        renderInput('video_url', $block['video_url'] ?? '', 'Video URL', 'text');
        echo "</div>";

        echo "<div class='video-upload-field' id='video-upload-field-{$index}' style='display:none;'>";
        renderFileUpload('video_file', $uploads, $block['video_file'] ?? '');
        echo "</div>";
        renderVideoSourceSelector('video_source', $block['video_source'] ?? '');
        break;

    case 'slider_gallery':
        renderTextarea('gallery_data', $block['gallery_data'] ?? '', 'Gallery Data (JSON)');
        renderNumberInput('slider_speed', $block['slider_speed'] ?? '', 'Slider Speed');
        break;

    case 'quote':
        renderTextarea('quotes_data', $block['quotes_data'] ?? '', 'Quotes Data (JSON)');
        break;

    case 'accordion':
        renderTextarea('accordion_data', $block['accordion_data'] ?? '', 'Accordion Data (JSON)');
        break;

    case 'audio':
        renderInput('audio_url', $block['audio_url'] ?? '', 'Audio URL');
        renderFileUpload('video_url', $uploads, $block['audio_url'] ?? '');
        renderSelect('audio_source', $videoSourceOptions, $block['audio_source'] ?? '', 'Audio Source');
        break;

    case 'free_code':
        renderTextarea('free_code_content', $block['free_code_content'] ?? '', 'Free Code Content');
        break;

    case 'map':
        renderTextarea('map_embed_code', $block['map_embed_code'] ?? '', 'Map Embed Code');
        break;

    case 'form':
        renderInput('form_shortcode', $block['form_shortcode'] ?? '', 'Form Shortcode');
        break;

    case 'hero':
        renderInput('title', $block['title'] ?? '', 'Title');
        renderTextarea('content', $block['content'] ?? '', 'Content');
        renderSelect('hero_layout', $heroLayoutOptions, $block['hero_layout'] ?? '', 'Hero Layout');
        renderFileUpload('background_image_path', $uploads, $block['background_image_path'] ?? '');
        renderInput('background_video_url', $block['background_video_url'] ?? '', 'Background Video URL');
        renderSelect('background_style', $backgroundStyleOptions, $block['background_style'] ?? '', 'Background Style');
        renderColorPicker('overlay_color', $block['overlay_color'] ?? '', 'Overlay Color');
        renderColorPicker('text_color', $block['text_color'] ?? '', 'Text Color');
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