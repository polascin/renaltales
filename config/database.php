<?php

declare(strict_types=1);

/**
 * Database Configuration
 *
 * Database connection configuration using environment variables.
 * This configuration is used by the Doctrine ORM setup.
 *
 * @package RenalTales\Config
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */

return [
    'default' => $_ENV['DB_CONNECTION'] ?? 'mysql',
    
    'connections' => [
        'mysql' => [
            'driver' => 'pdo_mysql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => (int)($_ENV['DB_PORT'] ?? 3306),
            'dbname' => $_ENV['DB_DATABASE'] ?? 'renaltales',
            'user' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
            'collation' => $_ENV['DB_COLLATION'] ?? 'utf8mb4_unicode_ci',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
            ]
        ],
        
        'sqlite' => [
            'driver' => 'pdo_sqlite',
            'path' => $_ENV['DB_DATABASE'] ?? APP_ROOT . '/storage/database/database.sqlite',
            'foreign_key_constraints' => true,
        ],
        
        'test' => [
            'driver' => 'pdo_mysql',
            'host' => $_ENV['TEST_DB_HOST'] ?? 'localhost',
            'port' => (int)($_ENV['TEST_DB_PORT'] ?? 3306),
            'dbname' => $_ENV['TEST_DB_DATABASE'] ?? 'renaltales_test',
            'user' => $_ENV['TEST_DB_USERNAME'] ?? 'root',
            'password' => $_ENV['TEST_DB_PASSWORD'] ?? '',
            'charset' => $_ENV['TEST_DB_CHARSET'] ?? 'utf8mb4',
            'collation' => $_ENV['TEST_DB_COLLATION'] ?? 'utf8mb4_unicode_ci',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
            ]
        ]
    ],
    
    'migrations' => [
        'table_storage' => [
            'table_name' => 'doctrine_migration_versions',
            'version_column_name' => 'version',
            'version_column_length' => 1024,
            'executed_at_column_name' => 'executed_at',
            'execution_time_column_name' => 'execution_time',
        ],
        
        'migrations_paths' => [
            'RenalTales\Migrations' => APP_ROOT . '/database/migrations',
        ],
        
        'all_or_nothing' => true,
        'transactional' => true,
        'check_database_platform' => true,
        'organize_migrations' => 'none',
        'connection' => null,
        'em' => null,
    ],
    
    'proxies' => [
        'directory' => APP_ROOT . '/storage/cache/doctrine/proxies',
        'auto_generate' => ($_ENV['APP_ENV'] ?? 'development') === 'development',
        'namespace' => 'RenalTales\Proxies',
    ],
    
    'cache' => [
        'metadata' => [
            'type' => $_ENV['DOCTRINE_METADATA_CACHE'] ?? 'file',
            'directory' => APP_ROOT . '/storage/cache/doctrine/metadata',
        ],
        'query' => [
            'type' => $_ENV['DOCTRINE_QUERY_CACHE'] ?? 'file',
            'directory' => APP_ROOT . '/storage/cache/doctrine/query',
        ],
        'result' => [
            'type' => $_ENV['DOCTRINE_RESULT_CACHE'] ?? 'file',
            'directory' => APP_ROOT . '/storage/cache/doctrine/result',
        ],
    ],
    
    'entity_paths' => [
        APP_ROOT . '/src/Entities',
    ],
    
    'logging' => [
        'enabled' => filter_var($_ENV['DB_LOGGING_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
        'file' => $_ENV['DB_LOG_FILE'] ?? APP_ROOT . '/storage/logs/database.log',
        'level' => $_ENV['DB_LOG_LEVEL'] ?? 'info',
    ],
];
