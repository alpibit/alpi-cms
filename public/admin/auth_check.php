<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../utils/helpers.php';

if (session_status() == PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => alpiIsHttpsRequest(),
        'samesite' => 'Strict',
    ]);
    session_start();
}

alpiGetCsrfToken();

$timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    alpiRejectAjaxOrRedirect(BASE_URL . '/admin?expired=1', 'Session expired. Please sign in again.', 401);
}

$_SESSION['last_activity'] = time();

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    alpiRejectAjaxOrRedirect(BASE_URL . '/admin', 'You must sign in to continue.', 401);
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    alpiRejectAjaxOrRedirect(BASE_URL . '/admin', 'You are not authorized to access this area.', 403);
}

if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}
