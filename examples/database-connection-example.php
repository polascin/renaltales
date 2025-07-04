<?php
/**
 * Example: Using the DatabaseConnection class
 * This file demonstrates how to use the new DatabaseConnection singleton
 */

require_once dirname(__DIR__) . '/bootstrap/autoload.php';

use RenalTales\Database\DatabaseConnection;

try {
    // Get database connection instance
    $pdo = DatabaseConnection::getInstance();
    
    echo "✓ Database connection established successfully!\n";
    
    // Example query to test connection
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    
    if ($result && $result['test'] === 1) {
        echo "✓ Database query test successful!\n";
    }
    
    // Example of using the connection for a prepared statement
    $stmt = $pdo->prepare("SELECT DATABASE() as current_db");
    $stmt->execute();
    $result = $stmt->fetch();
    
    echo "✓ Current database: " . ($result['current_db'] ?? 'Unknown') . "\n";
    
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// Example of reconnecting (useful for long-running scripts)
try {
    $newPdo = DatabaseConnection::reconnect();
    echo "✓ Database reconnection successful!\n";
} catch (Exception $e) {
    echo "✗ Reconnection failed: " . $e->getMessage() . "\n";
}
