<?php
// This is global bootstrap for autoloading

// Include the project's main configuration
if (file_exists(__DIR__ . '/../Config.php')) {
    require_once __DIR__ . '/../Config.php';
}

// Include composer autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}
