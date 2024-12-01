<?php
require_once __DIR__ . '/../../config/config.php';

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header('Location: ' . htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') . '/admin?expired=1');
    exit;
}

$_SESSION['last_activity'] = time();

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    header('Location: ' . htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') . '/admin');
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') . '/admin');
    exit;
}

if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}
