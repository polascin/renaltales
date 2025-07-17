<?php

/**
 * Simple query script to execute the exact requested query
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

    // Create database connection
    $connection = DriverManager::getConnection($params);

    // Execute the exact query as requested
    echo "Executing: SELECT * FROM languages LIMIT 5\n";
    echo "==========================================\n";
    
    $stmt = $connection->prepare("SELECT * FROM languages LIMIT 5");
    $result = $stmt->executeQuery();
    $languages = $result->fetchAllAssociative();
    
    if (!empty($languages)) {
        echo "Query executed successfully. Found " . count($languages) . " records:\n\n";
        
        foreach ($languages as $i => $language) {
            echo "Record " . ($i + 1) . ":\n";
            foreach ($language as $key => $value) {
                echo "  $key: $value\n";
            }
            echo "\n";
        }
    } else {
        echo "Query executed successfully, but no records found.\n";
        echo "The languages table is empty.\n";
    }
    
} catch (Exception $e) {
    echo "Error executing query: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
