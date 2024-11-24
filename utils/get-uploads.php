<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../config/config.php';

$db = new Database();
$conn = $db->connect();

$upload = new Upload($conn);
$files = $upload->listFiles();

$response = ['uploads' => []];

foreach ($files as $file) {
    $response['uploads'][] = ['url' => $file['url']];
}

header('Content-Type: application/json');
echo json_encode($response);
