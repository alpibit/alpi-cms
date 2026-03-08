<?php

require '../../../config/autoload.php';
require '../../../config/database.php';
require '../../../config/config.php';
require '../auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/public/admin/pages/index.php?status=error&message=" . urlencode('Invalid request method.'));
    exit;
}

if (!alpiVerifyCsrfToken($_POST['csrf_token'] ?? '')) {
    alpiRegenerateCsrfToken();
    header("Location: " . BASE_URL . "/public/admin/pages/index.php?status=error&message=" . urlencode('Invalid CSRF token. Please try again.'));
    exit;
}

if (!isset($_POST['id']) || empty($_POST['id'])) {
    header("Location: " . BASE_URL . "/public/admin/pages/index.php?status=error&message=" . urlencode('Missing page ID.'));
    exit;
}

$db = new Database();
$conn = $db->connect();
$page = new Page($conn);

$pageId = intval($_POST['id']);

try {
    $deleted = $page->deletePage($pageId);
    alpiRegenerateCsrfToken();

    if ($deleted) {
        header("Location: " . BASE_URL . "/public/admin/pages/index.php?status=success&message=" . urlencode('Page deleted successfully.'));
        exit;
    }
} catch (Exception $exception) {
    header("Location: " . BASE_URL . "/public/admin/pages/index.php?status=error&message=" . urlencode($exception->getMessage()));
    exit;
}

header("Location: " . BASE_URL . "/public/admin/pages/index.php?status=error&message=" . urlencode('Unable to delete page.'));
exit;
