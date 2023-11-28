<?php

// allow AJAX requests to this script
header('Access-Control-Allow-Origin: *');

require '../config/database.php';
require '../config/config.php';
require '../config/autoload.php';

$blockType = $_GET['type'] ?? '';
$index = $_GET['index'] ?? 0;

$db = new Database();
$conn = $db->connect();

$upload = new Upload($conn);
$uploads = $upload->listFiles();

switch ($blockType) {
    case 'text':
        echo "<textarea name='blocks[$index][content]'></textarea>";
        break;
    case 'image_text':
        echo "<textarea name='blocks[$index][content]'></textarea><br>";
        echo "<input type='file' name='blocks[$index][image]' /><br>";
        // !!!
        break;
    case 'image':
        echo "<select name='blocks[$index][image_path]'>";
        foreach ($uploads as $uploadFile) {
            echo "<option value='{$uploadFile['url']}'>{$uploadFile['url']}</option>";
        }
        echo "</select>";
        break;
    case 'cta':
        echo "<input type='text' name='blocks[$index][cta_text]' /><br>";
        echo "<input type='url' name='blocks[$index][cta_link]' />";
        break;
    default:
        echo "Unknown block type";
}
