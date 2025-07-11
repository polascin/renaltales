<?php

/**
 * Database Connection Test Script
 * Tests database connection with detailed diagnostics
 */

// Load bootstrap first to ensure environment is loaded
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/core/Database.php';

echo "=== Database Connection Test ===\n\n";

// Test environment loading
echo "1. Environment Variables:\n";
echo "   DB_HOST: " . ($_ENV['DB_HOST'] ?? 'NOT SET') . "\n";
echo "   DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? 'NOT SET') . "\n";
echo "   DB_USERNAME: " . ($_ENV['DB_USERNAME'] ?? 'NOT SET') . "\n";
echo "   DB_PASSWORD: " . (isset($_ENV['DB_PASSWORD']) ? '[SET]' : 'NOT SET') . "\n\n";

// Test config loading
echo "2. Configuration Loading:\n";
$configFile = __DIR__ . '/config/database.php';
if (file_exists($configFile)) {
    echo "   ✓ Database config file exists\n";
    $dbConfig = require $configFile;
    $connection = $dbConfig['connections'][$dbConfig['default']] ?? null;
    if ($connection) {
        echo "   ✓ Default connection config loaded\n";
        echo "   Host: " . $connection['host'] . "\n";
        echo "   Database: " . $connection['database'] . "\n";
        echo "   Username: " . $connection['username'] . "\n";
    } else {
        echo "   ✗ No default connection found\n";
    }
} else {
    echo "   ✗ Database config file not found\n";
}
echo "\n";

// Test database connection
echo "3. Database Connection Test:\n";
try {
    $db = Database::getInstance();
    $status = $db->testConnection();
    
    if ($status['connected']) {
        echo "   ✓ Database connection successful!\n";
        echo "   Server version: " . $status['version'] . "\n";
        echo "   Host: " . $status['host'] . "\n";
        echo "   Database: " . $status['database'] . "\n";
        echo "   Username: " . $status['username'] . "\n";
        echo "   Charset: " . $status['charset'] . "\n";
        
        // Test a simple query
        echo "\n4. Database Query Test:\n";
        try {
            $result = $db->execute("SHOW TABLES");
            $tables = $result->fetchAll(PDO::FETCH_COLUMN);
            echo "   ✓ Query executed successfully\n";
            echo "   Tables found: " . count($tables) . "\n";
            if (!empty($tables)) {
                echo "   Table list: " . implode(', ', array_slice($tables, 0, 5)) . "\n";
                if (count($tables) > 5) {
                    echo "   ... and " . (count($tables) - 5) . " more\n";
                }
            }
        } catch (Exception $e) {
            echo "   ✗ Query test failed: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "   ✗ Database connection failed!\n";
        echo "   Error: " . ($status['error'] ?? 'Unknown error') . "\n";
        echo "   Host: " . $status['host'] . "\n";
        echo "   Database: " . $status['database'] . "\n";
        echo "   Username: " . $status['username'] . "\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Database initialization failed!\n";
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";

// Provide recommendations
echo "\nRecommendations:\n";
if (!file_exists(__DIR__ . '/.env')) {
    echo "- Create .env file from .env.example\n";
}

$status = $status ?? ['connected' => false];
if (!$status['connected']) {
    echo "- Check if database server is running\n";
    echo "- Verify database credentials\n";
    echo "- Ensure database exists\n";
    echo "- Check network connectivity (if using remote database)\n";
}

?>
