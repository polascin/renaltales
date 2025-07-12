<?php

/**
 * Database Migration Script
 * Create remember_tokens table
 */

require_once __DIR__ . '/core/Database.php';

try {
    echo "Starting database migration...\n";
    
    $db = Database::getInstance();
    
    // Read the SQL file
    $sqlFile = __DIR__ . '/database/migrations/create_remember_tokens_table.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Migration file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
        }
    );
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            echo "Executing: " . substr($statement, 0, 50) . "...\n";
            $db->execute($statement);
        }
    }
    
    echo "Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

?>
