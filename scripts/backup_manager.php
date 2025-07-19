<?php

/**
 * Database Backup Management Script
 *
 * Command-line script for managing database backups
 *
 * Usage:
 * php backup_manager.php --type=full
 * php backup_manager.php --type=incremental
 * php backup_manager.php --type=cleanup
 * php backup_manager.php --type=status
 */

// Load application
require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/core/DatabaseConfig.php';
require_once dirname(__DIR__) . '/core/DatabaseBackupManager.php';

// Parse command line arguments
$options = getopt('', ['type:', 'connection:', 'help']);

if (isset($options['help']) || !isset($options['type'])) {
    showHelp();
    exit(0);
}

$type = $options['type'];
$connection = $options['connection'] ?? null;

try {
    // Initialize managers
    $dbConfig = DatabaseConfig::getInstance();
    $backupManager = new DatabaseBackupManager($dbConfig);

    echo "=== Database Backup Manager ===\n";
    echo "Type: $type\n";
    echo "Connection: " . ($connection ?? 'default') . "\n";
    echo "Started: " . date('Y-m-d H:i:s') . "\n\n";

    switch ($type) {
        case 'full':
            echo "Creating full backup...\n";
            $result = $backupManager->createFullBackup($connection);
            displayBackupResult($result);
            break;

        case 'schema':
            echo "Creating schema backup...\n";
            $result = $backupManager->createSchemaBackup($connection);
            displayBackupResult($result);
            break;

        case 'data':
            echo "Creating data backup...\n";
            $result = $backupManager->createDataBackup($connection);
            displayBackupResult($result);
            break;

        case 'incremental':
            echo "Creating incremental backup...\n";
            $result = $backupManager->createIncrementalBackup($connection);
            displayBackupResult($result);
            break;

        case 'cleanup':
            echo "Cleaning up old backups...\n";
            $result = $backupManager->cleanupOldBackups();
            displayCleanupResult($result);
            break;

        case 'status':
            echo "Backup Statistics:\n";
            $stats = $backupManager->getBackupStatistics();
            displayBackupStatistics($stats);
            break;

        case 'schedule':
            echo "Setting up automated backup schedule...\n";
            $schedule = $backupManager->scheduleAutomatedBackups();
            displaySchedule($schedule);
            break;

        default:
            echo "Error: Unknown backup type '$type'\n";
            showHelp();
            exit(1);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Display help information
 */
function showHelp()
{
    echo "Database Backup Manager\n\n";
    echo "Usage:\n";
    echo "  php backup_manager.php --type=TYPE [--connection=NAME]\n\n";
    echo "Types:\n";
    echo "  full        Create full database backup\n";
    echo "  schema      Create schema-only backup\n";
    echo "  data        Create data-only backup\n";
    echo "  incremental Create incremental backup\n";
    echo "  cleanup     Clean up old backups\n";
    echo "  status      Show backup statistics\n";
    echo "  schedule    Set up automated backup schedule\n\n";
    echo "Options:\n";
    echo "  --connection=NAME  Specify database connection (default: default connection)\n";
    echo "  --help            Show this help message\n\n";
    echo "Examples:\n";
    echo "  php backup_manager.php --type=full\n";
    echo "  php backup_manager.php --type=incremental --connection=mysql\n";
    echo "  php backup_manager.php --type=cleanup\n";
}

/**
 * Display backup result
 *
 * @param array $result
 */
function displayBackupResult($result)
{
    if ($result['success']) {
        echo "✓ Backup completed successfully!\n\n";
        echo "Details:\n";
        echo "  File: {$result['file_path']}\n";
        echo "  Size: " . formatFileSize($result['file_size']) . "\n";
        echo "  Started: {$result['started_at']}\n";
        echo "  Completed: {$result['completed_at']}\n";
        echo "  Compressed: " . ($result['compressed'] ? 'Yes' : 'No') . "\n";
        echo "  Encrypted: " . ($result['encrypted'] ? 'Yes' : 'No') . "\n";

        if (isset($result['tables_backed_up']) && !empty($result['tables_backed_up'])) {
            echo "  Tables: " . implode(', ', $result['tables_backed_up']) . "\n";
        }
    } else {
        echo "✗ Backup failed!\n\n";
        echo "Error: {$result['error']}\n";
        exit(1);
    }
}

/**
 * Display cleanup result
 *
 * @param array $result
 */
function displayCleanupResult($result)
{
    echo "✓ Cleanup completed!\n\n";
    echo "Results:\n";
    echo "  Files deleted: {$result['deleted_files']}\n";
    echo "  Space freed: " . formatFileSize($result['freed_space']) . "\n";

    if (!empty($result['errors'])) {
        echo "  Errors:\n";
        foreach ($result['errors'] as $error) {
            echo "    - $error\n";
        }
    }
}

/**
 * Display backup statistics
 *
 * @param array $stats
 */
function displayBackupStatistics($stats)
{
    echo "\nOverall Statistics:\n";
    echo "  Total backups: {$stats['total_backups']}\n";
    echo "  Successful: {$stats['successful_backups']}\n";
    echo "  Failed: {$stats['failed_backups']}\n";
    echo "  Total size: " . formatFileSize($stats['total_size']) . "\n";

    if ($stats['oldest_backup']) {
        echo "  Oldest backup: {$stats['oldest_backup']}\n";
    }

    if ($stats['newest_backup']) {
        echo "  Newest backup: {$stats['newest_backup']}\n";
    }

    if (!empty($stats['backup_types'])) {
        echo "\nBackup Types:\n";
        foreach ($stats['backup_types'] as $type => $count) {
            echo "  $type: $count\n";
        }
    }

    if (!empty($stats['recent_backups'])) {
        echo "\nRecent Backups:\n";
        foreach (array_reverse(array_slice($stats['recent_backups'], -5)) as $backup) {
            $status = $backup['success'] ? '✓' : '✗';
            $size = $backup['success'] ? formatFileSize($backup['file_size']) : 'N/A';
            echo "  $status {$backup['started_at']} [{$backup['type']}] ($size)\n";
        }
    }
}

/**
 * Display schedule information
 *
 * @param array $schedule
 */
function displaySchedule($schedule)
{
    echo "Automated backup schedule has been configured.\n\n";
    echo "Add these cron jobs to your system:\n\n";

    foreach ($schedule as $job) {
        echo "$job\n";
    }

    echo "\nTo install cron jobs:\n";
    echo "1. Run: crontab -e\n";
    echo "2. Add the above lines to your crontab\n";
    echo "3. Save and exit\n";
}

/**
 * Format file size
 *
 * @param int $bytes
 * @return string
 */
function formatFileSize($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, 2) . ' ' . $units[$pow];
}
