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
