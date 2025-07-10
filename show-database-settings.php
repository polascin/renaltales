<?php
/**
 * Database Settings Display
 * Shows current database configuration from all sources
 */

echo "=== RENAL TALES DATABASE SETTINGS ===\n\n";

// 1. Load environment variables from .env file
echo "1. ENVIRONMENT VARIABLES (.env file)\n";
echo "Location: " . realpath('.env') . "\n";
echo str_repeat("-", 50) . "\n";

if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, '"');
        
        // Only show database-related settings
        if (strpos($key, 'DB_') === 0 || strpos($key, 'TEST_DB_') === 0) {
            // Mask password for security
            if (strpos($key, 'PASSWORD') !== false && !empty($value)) {
                $value = str_repeat('*', strlen($value));
            }
            echo sprintf("%-20s = %s\n", $key, $value);
        }
    }
} else {
    echo "ERROR: .env file not found!\n";
}

echo "\n";

// 2. Show configuration files
echo "2. CONFIGURATION FILES\n";
echo str_repeat("-", 50) . "\n";

$configFiles = [
    'Main Database Config' => 'config/database.php',
    'Development Config' => 'config/environments/development.php',
    'Production Config' => 'config/environments/production.php'
];

foreach ($configFiles as $name => $path) {
    echo sprintf("%-20s: %s\n", $name, realpath($path) ?: 'NOT FOUND');
}

echo "\n";

// 3. Show current effective settings (from getenv or defaults)
echo "3. CURRENT EFFECTIVE SETTINGS\n";
echo str_repeat("-", 50) . "\n";

// Load .env to set environment variables
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value, '"'));
    }
}

// Function to get environment variable (like in config files)
function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    
    // Convert boolean strings
    if (in_array(strtolower($value), ['true', 'false'])) {
        return strtolower($value) === 'true';
    }
    
    // Convert null string
    if (strtolower($value) === 'null') {
        return null;
    }
    
    return $value;
}

$dbSettings = [
    'Connection Type' => env('DB_CONNECTION', 'mysql'),
    'Host' => env('DB_HOST', 'localhost'),
    'Port' => env('DB_PORT', '3306'),
    'Database' => env('DB_DATABASE', 'renaltales'),
    'Username' => env('DB_USERNAME', 'root'),
    'Password' => env('DB_PASSWORD') ? str_repeat('*', strlen(env('DB_PASSWORD'))) : 'Empty',
    'Charset' => env('DB_CHARSET', 'utf8mb4'),
    'Collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
    'Prefix' => env('DB_PREFIX', 'None'),
];

foreach ($dbSettings as $setting => $value) {
    echo sprintf("%-20s: %s\n", $setting, $value);
}

echo "\n";

// 4. Show test database settings
echo "4. TEST DATABASE SETTINGS\n";
echo str_repeat("-", 50) . "\n";

$testDbSettings = [
    'Host' => env('TEST_DB_HOST', 'localhost'),
    'Port' => env('TEST_DB_PORT', '3306'),
    'Database' => env('TEST_DB_DATABASE', 'renaltales_test'),
    'Username' => env('TEST_DB_USERNAME', 'root'),
    'Password' => env('TEST_DB_PASSWORD') ? str_repeat('*', strlen(env('TEST_DB_PASSWORD'))) : 'Empty',
];

foreach ($testDbSettings as $setting => $value) {
    echo sprintf("%-20s: %s\n", $setting, $value);
}

echo "\n";

// 5. Test connection
echo "5. CONNECTION TEST\n";
echo str_repeat("-", 50) . "\n";

try {
    $host = env('DB_HOST', 'localhost');
    $port = env('DB_PORT', '3306');
    $database = env('DB_DATABASE', 'renaltales');
    $username = env('DB_USERNAME', 'root');
    $password = env('DB_PASSWORD', '');
    
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 5,
    ];
    
    $startTime = microtime(true);
    $pdo = new PDO($dsn, $username, $password, $options);
    $connectionTime = microtime(true) - $startTime;
    
    // Get database info
    $stmt = $pdo->query("SELECT VERSION() as version, DATABASE() as current_db, NOW() as server_time");
    $info = $stmt->fetch();
    
    echo "✓ Connection Status: SUCCESS\n";
    echo sprintf("  Connection Time: %.2f ms\n", $connectionTime * 1000);
    echo sprintf("  Database Version: %s\n", $info['version']);
    echo sprintf("  Current Database: %s\n", $info['current_db']);
    echo sprintf("  Server Time: %s\n", $info['server_time']);
    
    // Count tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo sprintf("  Tables Count: %d\n", count($tables));
    
} catch (PDOException $e) {
    echo "✗ Connection Status: FAILED\n";
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "\n";

// 6. Show file locations and permissions
echo "6. FILE LOCATIONS & PERMISSIONS\n";
echo str_repeat("-", 50) . "\n";

$files = [
    '.env' => '.env',
    'Database Config' => 'config/database.php',
    'Dev Config' => 'config/environments/development.php',
    'Prod Config' => 'config/environments/production.php',
    'Bootstrap' => 'bootstrap.php',
    'Database Class' => 'core/Database.php'
];

foreach ($files as $name => $path) {
    if (file_exists($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $size = filesize($path);
        $modified = date('Y-m-d H:i:s', filemtime($path));
        echo sprintf("%-15s: %s (Perms: %s, Size: %d bytes, Modified: %s)\n", 
            $name, realpath($path), $perms, $size, $modified);
    } else {
        echo sprintf("%-15s: NOT FOUND\n", $name);
    }
}

echo "\n";

// 7. Show current working directory and related paths
echo "7. DIRECTORY INFORMATION\n";
echo str_repeat("-", 50) . "\n";
echo "Current Directory: " . getcwd() . "\n";
echo "Script Directory: " . __DIR__ . "\n";
echo "Real Path: " . realpath('.') . "\n";

// Check if we're in the right directory
if (basename(getcwd()) === 'renaltales') {
    echo "✓ You are in the correct application directory\n";
} else {
    echo "⚠ Warning: You might not be in the correct application directory\n";
}

echo "\n=== END OF DATABASE SETTINGS ===\n";
