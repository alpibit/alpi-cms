<?php

// allow AJAX requests to this script
header('Access-Control-Allow-Origin: *');

require '../config/database.php';
require '../config/config.php';
require '../config/autoload.php';

$blockType = $_GET['type'] ?? '';
$index = $_GET['index'] ?? 0;
$block = $_GET['blockData'] ?? [];

if (is_string($block)) {
    $block = json_decode($block, true);
}

$db = new Database();
$conn = $db->connect();

$upload = new Upload($conn);
$uploads = $upload->listFiles();

switch ($blockType) {
    case 'text':
        $content = isset($block['content']) ? htmlspecialchars($block['content']) : '';
        echo "<textarea name='blocks[$index][content]'>$content</textarea>";
        break;

    case 'image_text':
        $content = isset($block['content']) ? htmlspecialchars($block['content']) : '';
        echo "<textarea name='blocks[$index][content]'>$content</textarea><br>";
        echo "<select name='blocks[$index][image_path]'>";
        foreach ($uploads as $uploadFile) {
            $selected = (isset($block['image_path']) && $uploadFile['url'] == $block['image_path']) ? 'selected' : '';
            echo "<option value='{$uploadFile['url']}' {$selected}>{$uploadFile['url']}</option>";
        }
        echo "</select>";
        break;

    case 'image':
        echo "<select name='blocks[$index][image_path]'>";
        foreach ($uploads as $uploadFile) {
            $selected = (isset($block['image_path']) && $uploadFile['url'] == $block['image_path']) ? 'selected' : '';
            echo "<option value='{$uploadFile['url']}' {$selected}>{$uploadFile['url']}</option>";
        }
        echo "</select>";
        break;

    case 'cta':
        $ctaText = isset($block['cta_text']) ? htmlspecialchars($block['cta_text']) : '';
        $url = isset($block['url']) ? htmlspecialchars($block['url']) : '';
        echo "<input type='text' name='blocks[$index][cta_text]' value='$ctaText' /><br>";
        echo "<input type='url' name='blocks[$index][url]' value='$url' />";
        break;

    default:
        echo "Unknown block type";
}
