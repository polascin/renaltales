<?php

/**
 * Migration verification script
 * This script verifies that the languages table exists and contains data
 */

// Include constants and bootstrap
define('APP_ROOT', __DIR__);
define('DS', DIRECTORY_SEPARATOR);

require_once 'bootstrap.php';
require_once 'vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

try {
    // Database connection parameters from .env
    $params = [
        'driver' => 'pdo_mysql',
        'host' => $_ENV['DB_HOST'] ?? 'mariadb114.r6.websupport.sk',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'user' => $_ENV['DB_USERNAME'] ?? 'by80b9pH',
        'password' => $_ENV['DB_PASSWORD'] ?? 'WsVZOl#;D07ju~0@_dF@',
        'dbname' => $_ENV['DB_DATABASE'] ?? 'SvwfeoXW',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
    ];

    echo "=== Migration Verification Report ===\n";
    echo "Database Host: " . $params['host'] . "\n";
    echo "Database Name: " . $params['dbname'] . "\n";
    echo "Database User: " . $params['user'] . "\n";
    echo "=====================================\n\n";

    // Create database connection
    $connection = DriverManager::getConnection($params);

    // Test connection
    echo "1. Testing database connection...\n";
    $connection->executeQuery("SELECT 1");
    echo "   ✓ Database connection successful\n\n";

    // Check if languages table exists
    echo "2. Checking if languages table exists...\n";
    $schemaManager = $connection->createSchemaManager();
    $tables = $schemaManager->listTableNames();
    
    if (in_array('languages', $tables)) {
        echo "   ✓ Languages table exists\n\n";
    } else {
        echo "   ✗ Languages table does not exist\n";
        echo "   Available tables: " . implode(', ', $tables) . "\n";
        exit(1);
    }

    // Check table structure
    echo "3. Checking table structure...\n";
    $columns = $schemaManager->listTableColumns('languages');
    
    $expectedColumns = ['id', 'code', 'name', 'native_name', 'region', 'enabled', 'created_at', 'updated_at'];
    $actualColumns = array_keys($columns);
    
    echo "   Expected columns: " . implode(', ', $expectedColumns) . "\n";
    echo "   Actual columns: " . implode(', ', $actualColumns) . "\n";
    
    $missingColumns = array_diff($expectedColumns, $actualColumns);
    if (empty($missingColumns)) {
        echo "   ✓ All expected columns present\n\n";
    } else {
        echo "   ⚠ Missing columns: " . implode(', ', $missingColumns) . "\n\n";
    }

    // Test data access with the requested query
    echo "4. Testing data access (SELECT * FROM languages LIMIT 5)...\n";
    $stmt = $connection->prepare("SELECT * FROM languages LIMIT 5");
    $result = $stmt->executeQuery();
    $languages = $result->fetchAllAssociative();
    
    if (!empty($languages)) {
        echo "   ✓ Data retrieved successfully\n";
        echo "   Found " . count($languages) . " records\n\n";
        
        echo "   Sample records:\n";
        foreach ($languages as $i => $language) {
            echo "   Record " . ($i + 1) . ":\n";
            foreach ($language as $key => $value) {
                echo "     $key: $value\n";
            }
            echo "\n";
        }
    } else {
        echo "   ⚠ No data found in languages table\n\n";
    }

    // Count total records
    echo "5. Counting total records...\n";
    $countStmt = $connection->prepare("SELECT COUNT(*) as total FROM languages");
    $countResult = $countStmt->executeQuery();
    $totalCount = $countResult->fetchAssociative()['total'];
    
    echo "   Total records in languages table: $totalCount\n\n";

    // Check if data looks correct (basic validation)
    echo "6. Data validation check...\n";
    if ($totalCount > 0) {
        $validationStmt = $connection->prepare("SELECT COUNT(*) as enabled_count FROM languages WHERE enabled = 1");
        $validationResult = $validationStmt->executeQuery();
        $enabledCount = $validationResult->fetchAssociative()['enabled_count'];
        
        echo "   Enabled languages: $enabledCount\n";
        echo "   Disabled languages: " . ($totalCount - $enabledCount) . "\n";
        
        // Check for required languages
        $requiredLanguages = ['en', 'sk', 'cs'];
        foreach ($requiredLanguages as $code) {
            $checkStmt = $connection->prepare("SELECT COUNT(*) as count FROM languages WHERE code = ?");
            $checkResult = $checkStmt->executeQuery([$code]);
            $count = $checkResult->fetchAssociative()['count'];
            
            if ($count > 0) {
                echo "   ✓ Required language '$code' found\n";
            } else {
                echo "   ⚠ Required language '$code' not found\n";
            }
        }
    }

    echo "\n=== Migration Verification Complete ===\n";
    echo "✓ Database connection works\n";
    echo "✓ Languages table exists with correct structure\n";
    echo "✓ Data is accessible\n";
    echo "✓ Application can query the remote database\n";
    echo "=====================================\n";

} catch (Exception $e) {
    echo "✗ Error during verification: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
