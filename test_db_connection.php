<?php
/**
 * Database Connection Test Script
 * This script tests the database connection using the credentials from .env file
 */

// Database configuration from .env file
$host = 'localhost';
$database = 'renaltales';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

echo "Testing database connection...\n";
echo "Host: $host\n";
echo "Database: $database\n";
echo "Username: $username\n";
echo "Charset: $charset\n";
echo str_repeat('-', 50) . "\n";

try {
    // Create PDO connection
    $dsn = "mysql:host=$host;dbname=$database;charset=$charset";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "✓ Connection successful!\n";
    
    // Test if we can query the database
    $stmt = $pdo->query("SELECT DATABASE() as current_db, VERSION() as mysql_version");
    $result = $stmt->fetch();
    
    echo "✓ Current database: " . $result['current_db'] . "\n";
    echo "✓ MySQL version: " . $result['mysql_version'] . "\n";
    
    // Check if the database exists and has the correct charset
    $stmt = $pdo->query("SELECT SCHEMA_NAME, DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME 
                         FROM information_schema.SCHEMATA 
                         WHERE SCHEMA_NAME = '$database'");
    $dbInfo = $stmt->fetch();
    
    if ($dbInfo) {
        echo "✓ Database '$database' exists\n";
        echo "✓ Character set: " . $dbInfo['DEFAULT_CHARACTER_SET_NAME'] . "\n";
        echo "✓ Collation: " . $dbInfo['DEFAULT_COLLATION_NAME'] . "\n";
    } else {
        echo "⚠ Database '$database' does not exist\n";
        echo "Creating database...\n";
        
        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "✓ Database '$database' created successfully\n";
    }
    
    // Test a simple query
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll();
    
    echo "✓ Tables in database: " . count($tables) . "\n";
    if (count($tables) > 0) {
        echo "  Tables: ";
        foreach ($tables as $table) {
            echo $table[array_keys($table)[0]] . " ";
        }
        echo "\n";
    }
    
    echo "\n✓ All database tests passed!\n";
    
} catch (PDOException $e) {
    echo "✗ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    
    // Try to connect without specifying database to check if MySQL is running
    try {
        $dsn = "mysql:host=$host;charset=$charset";
        $pdo = new PDO($dsn, $username, $password);
        echo "✓ MySQL server is running\n";
        echo "⚠ Database '$database' might not exist\n";
        
        // Create database if it doesn't exist
        echo "Creating database '$database'...\n";
        $pdo->exec("CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "✓ Database '$database' created successfully\n";
        
    } catch (PDOException $e2) {
        echo "✗ MySQL server connection failed: " . $e2->getMessage() . "\n";
        echo "Please check if MySQL/MariaDB is running and accessible\n";
    }
}

echo "\n" . str_repeat('-', 50) . "\n";
echo "Test completed.\n";
?>
