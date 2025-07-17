<?php

/**
 * Database configuration for Doctrine migrations
 */

// Load environment variables
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    // Manual parsing of .env file
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
}

use Doctrine\DBAL\DriverManager;

// Database connection parameters
$params = [
    'driver' => 'pdo_mysql',
    'host' => $_ENV['DB_HOST'] ?? 'mariadb114.r6.websupport.sk',
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'user' => $_ENV['DB_USERNAME'] ?? 'by80b9pH',
    'password' => $_ENV['DB_PASSWORD'] ?? 'WsVZOl#;D07ju~0@_dF@',
    'dbname' => $_ENV['DB_DATABASE'] ?? 'SvwfeoXW',
    'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
];

return DriverManager::getConnection($params);
