<?php

/**
 * Bootstrap file for RenalTales application
 * This file initializes the application environment and sets up autoloading
 *
 * @package RenalTales
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 **/

// File: /bootstrap.php

// Load environment variables (autoloader already loaded in index.php)
$envFile = APP_ROOT . DS . '.env';

if (file_exists($envFile)) {
    // Manual parsing of .env file - reliable and works in all environments
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }

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

    // Set putenv for backward compatibility
    foreach ($_ENV as $key => $value) {
        putenv("$key=$value");
    }
}

// Set up error reporting based on environment
if (filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
    // In debug mode, show all errors except deprecation warnings
    // (deprecation warnings from vendor libraries can be noisy)
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
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
function app_log($message, $context = [])
{
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
    'version' => $_ENV['APP_VERSION'] ?? '2025.v3.1.dev',
    'env' => $_ENV['APP_ENV'] ?? 'development',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
  ],
  // Database configuration removed
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

// Make config available globally
$GLOBALS['config'] = $config;

// Load translation helper functions
require_once APP_ROOT . DS . 'src' . DS . 'Helpers' . DS . 'helpers.php';

// Initialize global translation instance with language detection
use RenalTales\Helpers\Translation;

// Start session if not already started for language detection (only if headers not sent)
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

$translation = new Translation();
// Detect user's preferred language and set it
$detectedLanguage = $translation->detectLanguage('en');
$translation->setLanguage($detectedLanguage);

// Make translation instance available globally
$GLOBALS['translation'] = $translation;

// Application initialization complete
app_log('Application bootstrap completed', [
  'app_name' => $config['app']['name'],
  'environment' => $config['app']['env'],
  'debug' => $config['app']['debug'],
  'language' => $translation->getCurrentLanguage(),
]);
