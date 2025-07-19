<?php

declare(strict_types=1);

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;

/**
 * Doctrine Migrations Configuration
 *
 * Configuration file for Doctrine Migrations.
 * This file defines migration paths, table storage, and other migration settings.
 *
 * @package RenalTales\Config
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */

$databaseConfig = require __DIR__ . '/database.php';
$migrationConfig = $databaseConfig['migrations'];

return [
    'table_storage' => [
        'table_name' => $migrationConfig['table_storage']['table_name'],
        'version_column_name' => $migrationConfig['table_storage']['version_column_name'],
        'version_column_length' => $migrationConfig['table_storage']['version_column_length'],
        'executed_at_column_name' => $migrationConfig['table_storage']['executed_at_column_name'],
        'execution_time_column_name' => $migrationConfig['table_storage']['execution_time_column_name'],
    ],

    'migrations_paths' => $migrationConfig['migrations_paths'],

    'all_or_nothing' => $migrationConfig['all_or_nothing'],
    'transactional' => $migrationConfig['transactional'],
    'check_database_platform' => $migrationConfig['check_database_platform'],
    'organize_migrations' => $migrationConfig['organize_migrations'],
    'connection' => $migrationConfig['connection'],
    'em' => $migrationConfig['em'],
];
