#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Validate Doctrine ORM Schema
 * 
 * This script validates the Doctrine ORM schema to ensure all entities
 * and their mapping configurations are correct.
 * 
 * @package RenalTales\Scripts
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */

// Define application root
if (!defined('APP_ROOT')) {
    define('APP_ROOT', __DIR__);
}
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

// Load constants first if not already loaded
if (!defined('APP_NAME')) {
    require_once APP_ROOT . DS . 'config' . DS . 'constants.php';
}

// Load bootstrap
require_once APP_ROOT . DS . 'bootstrap.php';

use RenalTales\Core\Application;
use RenalTales\Core\DatabaseManager;
use Doctrine\ORM\Tools\SchemaValidator;
use Doctrine\ORM\Tools\SchemaTool;

try {
    echo "=== Doctrine Schema Validation ===\n";
    
    // Initialize application
    $app = new Application();
    $app->bootstrap();
    
    // Get database manager
    $databaseManager = $app->get(DatabaseManager::class);
    $entityManager = $databaseManager->getEntityManager();
    
    echo "✓ Database connection established\n";
    echo "✓ Entity Manager initialized\n";
    
    // Get all entity metadata
    $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
    echo "✓ Found " . count($metadata) . " entity(ies) to validate\n";
    
    // List entities
    foreach ($metadata as $classMetadata) {
        echo "  - Entity: " . $classMetadata->getName() . "\n";
        echo "    Table: " . $classMetadata->getTableName() . "\n";
        echo "    Fields: " . implode(', ', $classMetadata->getFieldNames()) . "\n";
    }
    
    // Create schema validator
    $validator = new SchemaValidator($entityManager);
    
    // Validate class mappings
    echo "\n--- Validating Entity Class Mappings ---\n";
    $classMappingErrors = $validator->validateMapping();
    
    if (empty($classMappingErrors)) {
        echo "✓ All entity class mappings are valid\n";
    } else {
        echo "✗ Found entity class mapping errors:\n";
        foreach ($classMappingErrors as $error) {
            echo "  - $error\n";
        }
    }
    
    // Check database schema using SchemaTool
    echo "\n--- Checking Database Schema ---\n";
    $schemaTool = new SchemaTool($entityManager);
    
    try {
        // Check if database exists and create if needed
        $connection = $entityManager->getConnection();
        
        // Try to connect to database
        if (!$connection->isConnected()) {
            $connection->connect();
        }
        
        echo "✓ Database connection is working\n";
        
        // Get the SQL to create the schema
        $sql = $schemaTool->getCreateSchemaSql($metadata);
        
        if (empty($sql)) {
            echo "✓ Database schema appears to be up to date\n";
        } else {
            echo "! Database schema needs to be created/updated\n";
            echo "  SQL statements needed:\n";
            foreach ($sql as $statement) {
                echo "  - " . substr($statement, 0, 80) . "...\n";
            }
        }
        
        // Try to get update schema SQL
        try {
            $updateSql = $schemaTool->getUpdateSchemaSql($metadata);
            if (!empty($updateSql)) {
                echo "! Database schema needs updates:\n";
                foreach ($updateSql as $statement) {
                    echo "  - " . substr($statement, 0, 80) . "...\n";
                }
            } else {
                echo "✓ Database schema is up to date\n";
            }
        } catch (Exception $e) {
            echo "! Could not check for schema updates: " . $e->getMessage() . "\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Database connection error: " . $e->getMessage() . "\n";
    }
    
    // Overall validation result
    $totalErrors = count($classMappingErrors);
    
    echo "\n=== Validation Summary ===\n";
    if ($totalErrors === 0) {
        echo "✓ Schema validation completed successfully!\n";
        echo "✓ All entities and mappings are valid\n";
        exit(0);
    } else {
        echo "✗ Schema validation failed with $totalErrors error(s)\n";
        echo "Please fix the errors above and run validation again\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
