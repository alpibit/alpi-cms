<?php

if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../config/autoload.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../utils/helpers.php';
    require_once __DIR__ . '/../config/config.php';
    define('CONFIG_INCLUDED', true);
}

$pageTitle = 'Page Not Found';

alpiExitWithPublicErrorPage([
    'statusCode' => 404,
    'pageTitle' => 'Page not found',
    'eyebrow' => 'Page not found',
    'title' => 'We could not find the page you were looking for.',
    'message' => 'It may have moved, or the address may be slightly off.',
    'errorCode' => '404',
    'actions' => [
        [
            'label' => 'Go home',
            'href' => BASE_URL,
            'variant' => 'primary',
        ],
        [
            'label' => 'Try again',
            'href' => '',
            'variant' => 'secondary',
            'isButton' => true,
        ],
    ],
]);
