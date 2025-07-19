#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Database Management CLI
 *
 * Command line interface for managing database operations using Doctrine ORM.
 * Provides commands for migrations, schema generation, and database operations.
 *
 * @package RenalTales\Scripts
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */

// Define application root
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
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
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\RollupCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Symfony\Component\Console\Application as ConsoleApplication;

try {
    // Initialize application
    $app = new Application();
    $app->bootstrap();
    
    // Get database manager
    $databaseManager = $app->get(DatabaseManager::class);
    $entityManager = $databaseManager->getEntityManager();
    
    // Create dependency factory for migrations
    $dependencyFactory = $databaseManager->createMigrationDependencyFactory();
    
    // Create console application
    $console = new ConsoleApplication('RenalTales Database Manager', '1.0.0');
    
    // Entity Manager Provider for ORM commands
    $entityManagerProvider = new SingleManagerProvider($entityManager);
    
    // Add ORM commands
    $console->addCommands([
        new CreateCommand($entityManagerProvider),
        new DropCommand($entityManagerProvider),
        new UpdateCommand($entityManagerProvider),
        new ValidateSchemaCommand($entityManagerProvider),
        new GenerateProxiesCommand($entityManagerProvider),
        new MetadataCommand($entityManagerProvider),
        new QueryCommand($entityManagerProvider),
        new ResultCommand($entityManagerProvider),
    ]);
    
    // Add Migration commands
    $console->addCommands([
        new DiffCommand($dependencyFactory),
        new ExecuteCommand($dependencyFactory),
        new GenerateCommand($dependencyFactory),
        new LatestCommand($dependencyFactory),
        new MigrateCommand($dependencyFactory),
        new RollupCommand($dependencyFactory),
        new StatusCommand($dependencyFactory),
        new SyncMetadataCommand($dependencyFactory),
        new VersionCommand($dependencyFactory),
    ]);
    
    // Add custom commands
    $console->register('db:test-connection')
        ->setDescription('Test database connection')
        ->setCode(function ($input, $output) use ($databaseManager) {
            $output->writeln('<info>Testing database connection...</info>');
            
            if ($databaseManager->isConnected()) {
                $output->writeln('<info>✓ Database connection is working!</info>');
                return 0;
            } else {
                $output->writeln('<error>✗ Database connection failed!</error>');
                return 1;
            }
        });
    
    $console->register('db:create-database')
        ->setDescription('Create database if it doesn\'t exist')
        ->setCode(function ($input, $output) use ($databaseManager) {
            $output->writeln('<info>Creating database...</info>');
            
            try {
                $connection = $databaseManager->getConnection();
                $config = $databaseManager->getConfig();
                $connectionConfig = $config['connections'][$config['default']] ?? [];
                
                if (!$connectionConfig) {
                    throw new Exception('Database configuration not found');
                }
                
                $dbName = $connectionConfig['dbname'] ?? '';
                if (!$dbName) {
                    throw new Exception('Database name not configured');
                }
                
                // Connect to server without database name
                $serverConnection = $connectionConfig;
                unset($serverConnection['dbname']);
                
                $serverConn = \Doctrine\DBAL\DriverManager::getConnection($serverConnection);
                
                // Create database
                $platform = $serverConn->getDatabasePlatform();
                $sql = $platform->getCreateDatabaseSQL($dbName);
                
                $serverConn->executeStatement($sql);
                $output->writeln("<info>✓ Database '{$dbName}' created successfully!</info>");
                
                return 0;
            } catch (Exception $e) {
                $output->writeln("<error>✗ Failed to create database: {$e->getMessage()}</error>");
                return 1;
            }
        });
    
    $console->register('db:drop-database')
        ->setDescription('Drop database')
        ->setCode(function ($input, $output) use ($databaseManager) {
            $output->writeln('<info>Dropping database...</info>');
            
            try {
                $config = $databaseManager->getConfig();
                $connectionConfig = $config['connections'][$config['default']] ?? [];
                
                if (!$connectionConfig) {
                    throw new Exception('Database configuration not found');
                }
                
                $dbName = $connectionConfig['dbname'] ?? '';
                if (!$dbName) {
                    throw new Exception('Database name not configured');
                }
                
                // Connect to server without database name
                $serverConnection = $connectionConfig;
                unset($serverConnection['dbname']);
                
                $serverConn = \Doctrine\DBAL\DriverManager::getConnection($serverConnection);
                
                // Drop database
                $platform = $serverConn->getDatabasePlatform();
                $sql = $platform->getDropDatabaseSQL($dbName);
                
                $serverConn->executeStatement($sql);
                $output->writeln("<info>✓ Database '{$dbName}' dropped successfully!</info>");
                
                return 0;
            } catch (Exception $e) {
                $output->writeln("<error>✗ Failed to drop database: {$e->getMessage()}</error>");
                return 1;
            }
        });
    
    // Run console application
    $console->run();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
