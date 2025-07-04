<?php
/**
 * Database Connection Test
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load config
$config = require_once __DIR__ . '/config/config.php';

echo "Testing database connection...\n";
echo "Host: " . $config['database']['host'] . "\n";
echo "Database: " . $config['database']['database'] . "\n";
echo "Username: " . $config['database']['username'] . "\n";
echo "Password: " . (empty($config['database']['password']) ? '(empty)' : '(set)') . "\n";
echo "Charset: " . $config['database']['charset'] . "\n\n";

try {
    $dsn = "mysql:host={$config['database']['host']};dbname={$config['database']['database']};charset={$config['database']['charset']}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    echo "Attempting to connect with DSN: $dsn\n";
    
    $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password'], $options);
    
    echo "✅ Database connection successful!\n";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "✅ Test query successful: " . $result['test'] . "\n";
    
    // Check if tables exist
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll();
    echo "\n📋 Tables in database:\n";
    $tableNames = [];
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        $tableNames[] = $tableName;
        echo "- " . $tableName . "\n";
    }
    
    // Check for Security class required tables
    echo "\n🔍 Checking for Security class tables:\n";
    $securityTables = ['rate_limits', 'login_attempts', 'security_logs', 'activity_logs'];
    foreach ($securityTables as $requiredTable) {
        if (in_array($requiredTable, $tableNames)) {
            echo "✅ $requiredTable - EXISTS\n";
        } else {
            echo "❌ $requiredTable - MISSING\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
    
    if ($e->getCode() == 1049) {
        echo "\n💡 The database 'renaltales' doesn't exist. You may need to create it first.\n";
    } elseif ($e->getCode() == 1045) {
        echo "\n💡 Access denied. Check your username and password.\n";
    } elseif ($e->getCode() == 2002) {
        echo "\n💡 Can't connect to MySQL server. Is MySQL running?\n";
    }
}
