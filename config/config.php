<?php
$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$host = $_SERVER['HTTP_HOST'];

$appBase = str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(__DIR__ . './index.php'));
$appBase = str_replace('index.php', '', $appBase); 

if (!defined('BASE_URL')) {
    define('BASE_URL', $scheme . '://' . $host . $appBase);
}
?>
