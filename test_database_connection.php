<?php
/**
 * Database Connection Test
 * Tests connectivity to the remote database
 */

// Load environment variables
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value, '"'));
    }
}

echo "=== Database Connection Test ===\n";
echo "Testing connection to remote database...\n\n";

// Database configuration
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: 'renaltales';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

echo "Host: $host\n";
echo "Port: $port\n";
echo "Database: $database\n";
echo "Username: $username\n";
echo "Password: " . (empty($password) ? 'Empty' : str_repeat('*', strlen($password))) . "\n\n";

// Test connection
try {
    $start_time = microtime(true);
    
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    $connection_time = microtime(true) - $start_time;
    
    echo "✓ Connection successful!\n";
    echo "Connection time: " . round($connection_time * 1000, 2) . " ms\n\n";
    
    // Test basic query
    $start_time = microtime(true);
    $stmt = $pdo->query("SELECT VERSION() as db_version, NOW() as db_time");
    $result = $stmt->fetch();
    $query_time = microtime(true) - $start_time;
    
    echo "✓ Basic query successful!\n";
    echo "Query time: " . round($query_time * 1000, 2) . " ms\n";
    echo "Database version: " . $result['db_version'] . "\n";
    echo "Current time: " . $result['db_time'] . "\n\n";
    
    // Test table existence
    echo "Checking table structure...\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    $expected_tables = ['users', 'stories', 'categories', 'comments', 'media', 'tags', 'user_profiles'];
    $missing_tables = [];
    
    foreach ($expected_tables as $table) {
        if (in_array($table, $tables)) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "✗ Table '$table' missing\n";
            $missing_tables[] = $table;
        }
    }
    
    if (!empty($missing_tables)) {
        echo "\nWarning: Missing tables: " . implode(', ', $missing_tables) . "\n";
    }
    
    echo "\nAll tables found: " . implode(', ', $tables) . "\n\n";
    
    // Test performance with multiple queries
    echo "Performance test (10 queries)...\n";
    $total_time = 0;
    for ($i = 0; $i < 10; $i++) {
        $start_time = microtime(true);
        $pdo->query("SELECT 1");
        $total_time += microtime(true) - $start_time;
    }
    
    echo "Average query time: " . round(($total_time / 10) * 1000, 2) . " ms\n";
    echo "Total time for 10 queries: " . round($total_time * 1000, 2) . " ms\n\n";
    
    echo "=== Database test completed successfully! ===\n";
    
} catch (PDOException $e) {
    echo "✗ Connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
    exit(1);
}
