<?php

/**
 * Application configuration file.
 * Central location for all application settings and autoloading.
 */

// Define base path for the application
define('BASE_PATH', dirname(__DIR__));

// Autoload classes using PSR-4 standard
spl_autoload_register(function (string $class) {
    // Only autoload classes from the App namespace
    if (strpos($class, 'App\\') !== 0) {
        return;
    }

    // Replace namespace separators with directory separators
    $classPath = str_replace('App\\', '', $class);
    $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $classPath);
    
    // Build full file path
    $file = BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . $classPath . '.php';

    // Require the file if it exists
    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize the application
// Start session management
\App\Core\SessionManager::start();

// Application constant definitions
define('APP_NAME', 'School Encoding Module');
define('DEFAULT_TIMEZONE', 'UTC');
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set(DEFAULT_TIMEZONE);
}
