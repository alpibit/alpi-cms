<?php
require_once __DIR__ . '/../../config/config.php';
session_start();

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    header('Location: ' . htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') . '/admin');
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') . '/admin');
    exit;
}
