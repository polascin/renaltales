<?php

/**
 * Bootstrap file for RenalTales application
 * This file initializes the application environment and sets up autoloading
 *
 * @package RenalTales
 * @version 2025.v3.0dev
 * @author Ľubomír Polaščín
**/

// File: /bootstrap.php

// Load classes
require_once APP_DIR . DS . 'loader.php';

// Load environment variables
$envFile = APP_ROOT . DS . '.env';

if (file_exists($envFile)) {
    // Manual parsing of .env file - reliable and works in all environments
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;

        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, "\"' ");
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
} else {
    // Create basic environment if no .env file exists
    $_ENV['APP_ENV'] = 'development';
    $_ENV['APP_DEBUG'] = 'true';
    $_ENV['DB_HOST'] = 'localhost';
    $_ENV['DB_DATABASE'] = 'renaltales';
    $_ENV['DB_USERNAME'] = 'root';
    $_ENV['DB_PASSWORD'] = '';
    $_ENV['DB_CHARSET'] = 'utf8mb4';

    // Set putenv for backward compatibility
    foreach ($_ENV as $key => $value) {
        putenv("$key=$value");
    }
}

// Set up error reporting based on environment
if (filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

// Simple file logger (no Composer/Monolog)
$logPath = APP_ROOT . DS . (isset($_ENV['LOG_PATH']) && $_ENV['LOG_PATH'] ? $_ENV['LOG_PATH'] : 'storage/logs/app.log');
$logDir = dirname($logPath);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
/**
 * Simple file logger
 *
 * @param string $message
 * @param array $context
 */
function app_log($message, $context = []) {
    global $logPath;
    $date = date('Y-m-d H:i:s');
    $ctx = $context ? ' | ' . json_encode($context) : '';
    file_put_contents($logPath, "[$date] $message$ctx\n", FILE_APPEND);
}
$GLOBALS['logger'] = function ($msg, $ctx = []) {
    app_log($msg, $ctx);
};

// Basic configuration array
$config = [
    'app' => [
        'name' => $_ENV['APP_NAME'] ?? 'RenalTales',
        'version' => $_ENV['APP_VERSION'] ?? '2025.v2.0',
        'env' => $_ENV['APP_ENV'] ?? 'development',
        'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'url' => $_ENV['APP_URL'] ?? 'http://localhost',
        'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
    ],
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'name' => $_ENV['DB_DATABASE'] ?? 'renaltales',
        'user' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
        'collation' => $_ENV['DB_COLLATION'] ?? 'utf8mb4_unicode_ci',
    ],
    'security' => [
        'app_secret' => $_ENV['APP_SECRET'] ?? 'your-secret-key-here',
        'jwt_secret' => $_ENV['JWT_SECRET'] ?? 'your-jwt-secret-here',
        'bcrypt_rounds' => $_ENV['BCRYPT_ROUNDS'] ?? 10,
    ],
    'storage' => [
        'driver' => $_ENV['STORAGE_DRIVER'] ?? 'local',
        'path' => APP_ROOT . DS . ($_ENV['STORAGE_PATH'] ?? 'storage/files'),
    ],
    'cache' => [
        'driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
        'path' => APP_ROOT . DS . ($_ENV['CACHE_PATH'] ?? 'storage/cache'),
    ],
    'session' => [
        'driver' => $_ENV['SESSION_DRIVER'] ?? 'file',
        'path' => APP_ROOT . DS . ($_ENV['SESSION_PATH'] ?? 'storage/sessions'),
        'lifetime' => $_ENV['SESSION_LIFETIME'] ?? 120,
    ],
];

// Define additional constants
define('LANGUAGE_PATH', APP_ROOT . '/resources/lang/');

// Make config available globally
$GLOBALS['config'] = $config;

// Application initialization complete
app_log('Application bootstrap completed', [
    'app_name' => $config['app']['name'],
    'environment' => $config['app']['env'],
    'debug' => $config['app']['debug'],
]);
