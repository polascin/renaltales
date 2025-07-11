<?php

/**
 * Simple Environment Test Script
 */

echo "=== Environment Loading Test ===\n\n";

// Load bootstrap
require_once __DIR__ . '/bootstrap.php';

echo "After bootstrap loading:\n";
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'NOT SET') . "\n";
echo "DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? 'NOT SET') . "\n";
echo "DB_USERNAME: " . ($_ENV['DB_USERNAME'] ?? 'NOT SET') . "\n";
echo "DB_PASSWORD: " . (isset($_ENV['DB_PASSWORD']) ? '[SET]' : 'NOT SET') . "\n\n";

// Test manual .env parsing
echo "Manual .env parsing test:\n";
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    echo "✓ .env file exists\n";
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $envVars = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, '"\'');
            $envVars[$key] = $value;
        }
    }
    
    echo "Found " . count($envVars) . " variables\n";
    echo "DB_HOST: " . ($envVars['DB_HOST'] ?? 'NOT FOUND') . "\n";
    echo "DB_DATABASE: " . ($envVars['DB_DATABASE'] ?? 'NOT FOUND') . "\n";
    echo "DB_USERNAME: " . ($envVars['DB_USERNAME'] ?? 'NOT FOUND') . "\n";
    
    // Set them in $_ENV
    foreach ($envVars as $key => $value) {
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
    
    echo "\nAfter manual setting:\n";
    echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'NOT SET') . "\n";
    echo "DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? 'NOT SET') . "\n";
    echo "DB_USERNAME: " . ($_ENV['DB_USERNAME'] ?? 'NOT SET') . "\n";
    
} else {
    echo "✗ .env file not found\n";
}

?>
