<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../public/admin/auth_check.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'error' => 'Method not allowed.',
        'uploads' => [],
    ]);
    exit;
}

$requestedType = trim((string) ($_GET['type'] ?? 'image'));
if (!Upload::isSupportedMediaGroup($requestedType)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Invalid uploads request.',
        'uploads' => [],
    ]);
    exit;
}

$db = new Database();
$conn = $db->connect();

if (!$conn instanceof PDO) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Unable to load uploads.',
        'uploads' => [],
    ]);
    exit;
}

$upload = new Upload($conn);
$files = $upload->listFiles([$requestedType]);

$response = ['uploads' => []];

foreach ($files as $file) {
    $response['uploads'][] = ['url' => $file['url']];
}

echo json_encode($response);
