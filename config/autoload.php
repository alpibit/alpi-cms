<?php

if (!function_exists('alpi_autoloader')) {
    function alpi_autoloader($className)
    {
        $baseDir = __DIR__ . '/../classes/';
        $file = $baseDir . $className . '.php';

        if (file_exists($file)) {
            require $file;
        }
    }
}

spl_autoload_register('alpi_autoloader');

// Check if the database configuration exists. If not, redirect to the installation page.
if (!file_exists(__DIR__ . '/database.php')) {
    $script_name = isset($_SERVER['SCRIPT_NAME']) ? basename($_SERVER['SCRIPT_NAME']) : '';

    if ($script_name !== 'install.php') {
        $base_url = isset($_SERVER['REQUEST_SCHEME']) && isset($_SERVER['HTTP_HOST']) ? $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] : '';
        $path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $redirect_url = $base_url . $path . '/install.php';
        header('Location: ' . $redirect_url);
        exit;
    }
}
