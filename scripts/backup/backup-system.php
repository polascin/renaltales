<?php

namespace RenalTales\Scripts\Backup;

/**
 * RenalTales Backup System
 *
 * Comprehensive backup and restore system for the RenalTales application
 * Handles database backups, file backups, and restoration procedures
 *
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';

class BackupSystem
{
    private $config;
    private $logger;
    private $backupDir;
    private $dbConfig;

    public function __construct()
    {
        $this->config = require dirname(__DIR__, 2) . '/config/app.php';
        $this->logger = new Logger('backup');
        $this->backupDir = dirname(__DIR__, 2) . '/storage/backups';
        $this->dbConfig = $this->config['database']['connections']['mysql'];

        // Ensure backup directory exists
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    /**
     * Perform full system backup
     */
    public function performFullBackup(): bool
    {
        $this->logger->info('Starting full system backup');

        try {
            $timestamp = date('Y-m-d_H-i-s');
            $backupPath = $this->backupDir . '/full_backup_' . $timestamp;

            if (!mkdir($backupPath, 0755, true)) {
                throw new Exception('Failed to create backup directory');
            }

            // Database backup
            $this->logger->info('Starting database backup');
            $dbBackupFile = $backupPath . '/database.sql';
            if (!$this->backupDatabase($dbBackupFile)) {
                throw new Exception('Database backup failed');
            }

            // Files backup
            $this->logger->info('Starting files backup');
            $filesBackupFile = $backupPath . '/files.tar.gz';
            if (!$this->backupFiles($filesBackupFile)) {
                throw new Exception('Files backup failed');
            }

            // Configuration backup
            $this->logger->info('Starting configuration backup');
            $configBackupFile = $backupPath . '/config.tar.gz';
            if (!$this->backupConfiguration($configBackupFile)) {
                throw new Exception('Configuration backup failed');
            }

            // Create backup manifest
            $this->createBackupManifest($backupPath, $timestamp);

            // Cleanup old backups
            $this->cleanupOldBackups();

            $this->logger->info('Full system backup completed successfully', ['path' => $backupPath]);
            return true;

        } catch (Exception $e) {
            $this->logger->error('Full backup failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Backup database
     */
    public function backupDatabase(string $outputFile): bool
    {
        try {
            $host = $this->dbConfig['host'];
            $port = $this->dbConfig['port'];
            $database = $this->dbConfig['database'];
            $username = $this->dbConfig['username'];
            $password = $this->dbConfig['password'];

            // Build mysqldump command
            $command = sprintf(
                'mysqldump -h%s -P%s -u%s %s %s > %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                !empty($password) ? '-p' . escapeshellarg($password) : '',
                escapeshellarg($database),
                escapeshellarg($outputFile)
            );

            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new Exception('mysqldump failed with return code: ' . $returnCode);
            }

            // Compress the backup
            $compressedFile = $outputFile . '.gz';
            if (file_exists($outputFile)) {
                $this->compressFile($outputFile, $compressedFile);
                unlink($outputFile);
            }

            $this->logger->info('Database backup completed', ['file' => $compressedFile]);
            return true;

        } catch (Exception $e) {
            $this->logger->error('Database backup failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Backup files (uploads, storage, etc.)
     */
    public function backupFiles(string $outputFile): bool
    {
        try {
            $baseDir = dirname(__DIR__, 2);
            $filesToBackup = [
                'storage/uploads',
                'storage/logs',
                'storage/cache',
                'storage/sessions',
                'public/assets',
                'resources/lang'
            ];

            $tempDir = sys_get_temp_dir() . '/renaltales_backup_' . time();
            mkdir($tempDir, 0755, true);

            foreach ($filesToBackup as $path) {
                $sourcePath = $baseDir . '/' . $path;
                $targetPath = $tempDir . '/' . $path;

                if (is_dir($sourcePath)) {
                    $this->copyDirectory($sourcePath, $targetPath);
                } elseif (is_file($sourcePath)) {
                    $targetDir = dirname($targetPath);
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0755, true);
                    }
                    copy($sourcePath, $targetPath);
                }
            }

            // Create tar archive
            $command = sprintf(
                'cd %s && tar -czf %s .',
                escapeshellarg($tempDir),
                escapeshellarg($outputFile)
            );

            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            // Cleanup temp directory
            $this->removeDirectory($tempDir);

            if ($returnCode !== 0) {
                throw new Exception('tar command failed with return code: ' . $returnCode);
            }

            $this->logger->info('Files backup completed', ['file' => $outputFile]);
            return true;

        } catch (Exception $e) {
            $this->logger->error('Files backup failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Backup configuration files
     */
    public function backupConfiguration(string $outputFile): bool
    {
        try {
            $baseDir = dirname(__DIR__, 2);
            $configFiles = [
                '.env',
                'config/',
                'composer.json',
                'composer.lock',
                '.htaccess'
            ];

            $tempDir = sys_get_temp_dir() . '/renaltales_config_' . time();
            mkdir($tempDir, 0755, true);

            foreach ($configFiles as $file) {
                $sourcePath = $baseDir . '/' . $file;
                $targetPath = $tempDir . '/' . $file;

                if (is_dir($sourcePath)) {
                    $this->copyDirectory($sourcePath, $targetPath);
                } elseif (is_file($sourcePath)) {
                    $targetDir = dirname($targetPath);
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0755, true);
                    }
                    copy($sourcePath, $targetPath);
                }
            }

            // Create tar archive
            $command = sprintf(
                'cd %s && tar -czf %s .',
                escapeshellarg($tempDir),
                escapeshellarg($outputFile)
            );

            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            // Cleanup temp directory
            $this->removeDirectory($tempDir);

            if ($returnCode !== 0) {
                throw new Exception('tar command failed with return code: ' . $returnCode);
            }

            $this->logger->info('Configuration backup completed', ['file' => $outputFile]);
            return true;

        } catch (Exception $e) {
            $this->logger->error('Configuration backup failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Restore from backup
     */
    public function restoreFromBackup(string $backupPath): bool
    {
        try {
            $this->logger->info('Starting restore from backup', ['path' => $backupPath]);

            if (!is_dir($backupPath)) {
                throw new Exception('Backup path does not exist');
            }

            // Read backup manifest
            $manifest = $this->readBackupManifest($backupPath);
            if (!$manifest) {
                throw new Exception('Invalid backup manifest');
            }

            // Restore database
            $dbBackupFile = $backupPath . '/database.sql.gz';
            if (file_exists($dbBackupFile)) {
                $this->logger->info('Restoring database');
                if (!$this->restoreDatabase($dbBackupFile)) {
                    throw new Exception('Database restore failed');
                }
            }

            // Restore files
            $filesBackupFile = $backupPath . '/files.tar.gz';
            if (file_exists($filesBackupFile)) {
                $this->logger->info('Restoring files');
                if (!$this->restoreFiles($filesBackupFile)) {
                    throw new Exception('Files restore failed');
                }
            }

            // Restore configuration
            $configBackupFile = $backupPath . '/config.tar.gz';
            if (file_exists($configBackupFile)) {
                $this->logger->info('Restoring configuration');
                if (!$this->restoreConfiguration($configBackupFile)) {
                    throw new Exception('Configuration restore failed');
                }
            }

            $this->logger->info('Restore completed successfully');
            return true;

        } catch (Exception $e) {
            $this->logger->error('Restore failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Restore database from backup
     */
    private function restoreDatabase(string $backupFile): bool
    {
        try {
            $host = $this->dbConfig['host'];
            $port = $this->dbConfig['port'];
            $database = $this->dbConfig['database'];
            $username = $this->dbConfig['username'];
            $password = $this->dbConfig['password'];

            // Decompress the backup
            $tempSqlFile = sys_get_temp_dir() . '/restore_' . time() . '.sql';
            $this->decompressFile($backupFile, $tempSqlFile);

            // Restore database
            $command = sprintf(
                'mysql -h%s -P%s -u%s %s %s < %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                !empty($password) ? '-p' . escapeshellarg($password) : '',
                escapeshellarg($database),
                escapeshellarg($tempSqlFile)
            );

            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            unlink($tempSqlFile);

            if ($returnCode !== 0) {
                throw new Exception('mysql restore failed with return code: ' . $returnCode);
            }

            return true;

        } catch (Exception $e) {
            $this->logger->error('Database restore failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Restore files from backup
     */
    private function restoreFiles(string $backupFile): bool
    {
        try {
            $baseDir = dirname(__DIR__, 2);

            // Extract files
            $command = sprintf(
                'cd %s && tar -xzf %s',
                escapeshellarg($baseDir),
                escapeshellarg($backupFile)
            );

            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new Exception('tar extract failed with return code: ' . $returnCode);
            }

            return true;

        } catch (Exception $e) {
            $this->logger->error('Files restore failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Restore configuration from backup
     */
    private function restoreConfiguration(string $backupFile): bool
    {
        try {
            $baseDir = dirname(__DIR__, 2);

            // Extract configuration
            $command = sprintf(
                'cd %s && tar -xzf %s',
                escapeshellarg($baseDir),
                escapeshellarg($backupFile)
            );

            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new Exception('tar extract failed with return code: ' . $returnCode);
            }

            return true;

        } catch (Exception $e) {
            $this->logger->error('Configuration restore failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Create backup manifest
     */
    private function createBackupManifest(string $backupPath, string $timestamp): void
    {
        $manifest = [
            'timestamp' => $timestamp,
            'version' => $this->config['app']['version'],
            'environment' => $this->config['app']['environment'],
            'php_version' => PHP_VERSION,
            'database' => $this->dbConfig['database'],
            'files' => [
                'database' => file_exists($backupPath . '/database.sql.gz'),
                'files' => file_exists($backupPath . '/files.tar.gz'),
                'config' => file_exists($backupPath . '/config.tar.gz'),
            ],
            'checksums' => $this->calculateChecksums($backupPath),
        ];

        file_put_contents($backupPath . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
    }

    /**
     * Read backup manifest
     */
    private function readBackupManifest(string $backupPath): ?array
    {
        $manifestFile = $backupPath . '/manifest.json';
        if (!file_exists($manifestFile)) {
            return null;
        }

        $manifest = json_decode(file_get_contents($manifestFile), true);
        return $manifest;
    }

    /**
     * Calculate checksums for backup files
     */
    private function calculateChecksums(string $backupPath): array
    {
        $checksums = [];
        $files = ['database.sql.gz', 'files.tar.gz', 'config.tar.gz'];

        foreach ($files as $file) {
            $filepath = $backupPath . '/' . $file;
            if (file_exists($filepath)) {
                $checksums[$file] = md5_file($filepath);
            }
        }

        return $checksums;
    }

    /**
     * List available backups
     */
    public function listBackups(): array
    {
        $backups = [];
        $dirs = glob($this->backupDir . '/full_backup_*', GLOB_ONLYDIR);

        foreach ($dirs as $dir) {
            $manifest = $this->readBackupManifest($dir);
            if ($manifest) {
                $backups[] = [
                    'path' => $dir,
                    'timestamp' => $manifest['timestamp'],
                    'version' => $manifest['version'],
                    'environment' => $manifest['environment'],
                    'size' => $this->getDirectorySize($dir),
                ];
            }
        }

        // Sort by timestamp (newest first)
        usort($backups, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return $backups;
    }

    /**
     * Cleanup old backups
     */
    private function cleanupOldBackups(): void
    {
        $backups = $this->listBackups();
        $keepCount = 10; // Keep last 10 backups

        if (count($backups) > $keepCount) {
            $toDelete = array_slice($backups, $keepCount);

            foreach ($toDelete as $backup) {
                $this->removeDirectory($backup['path']);
                $this->logger->info('Removed old backup', ['path' => $backup['path']]);
            }
        }
    }

    /**
     * Utility methods
     */
    private function compressFile(string $inputFile, string $outputFile): bool
    {
        $input = fopen($inputFile, 'rb');
        $output = gzopen($outputFile, 'wb9');

        if (!$input || !$output) {
            return false;
        }

        while (!feof($input)) {
            gzwrite($output, fread($input, 8192));
        }

        fclose($input);
        gzclose($output);

        return true;
    }

    private function decompressFile(string $inputFile, string $outputFile): bool
    {
        $input = gzopen($inputFile, 'rb');
        $output = fopen($outputFile, 'wb');

        if (!$input || !$output) {
            return false;
        }

        while (!gzeof($input)) {
            fwrite($output, gzread($input, 8192));
        }

        gzclose($input);
        fclose($output);

        return true;
    }

    private function copyDirectory(string $source, string $destination): bool
    {
        if (!is_dir($source)) {
            return false;
        }

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();

            if ($item->isDir()) {
                mkdir($target, 0755, true);
            } else {
                copy($item, $target);
            }
        }

        return true;
    }

    private function removeDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getRealPath());
            } else {
                unlink($item->getRealPath());
            }
        }

        return rmdir($dir);
    }

    private function getDirectorySize(string $dir): int
    {
        $size = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            $size += $file->getSize();
        }

        return $size;
    }
}

// CLI Usage
if (php_sapi_name() === 'cli') {
    $backup = new BackupSystem();

    $command = $argv[1] ?? 'help';

    switch ($command) {
        case 'full':
            echo "Starting full backup...\n";
            $success = $backup->performFullBackup();
            echo $success ? "Backup completed successfully!\n" : "Backup failed!\n";
            break;

        case 'database':
            echo "Starting database backup...\n";
            $outputFile = $argv[2] ?? 'storage/backups/database_' . date('Y-m-d_H-i-s') . '.sql';
            $success = $backup->backupDatabase($outputFile);
            echo $success ? "Database backup completed: $outputFile\n" : "Database backup failed!\n";
            break;

        case 'list':
            echo "Available backups:\n";
            $backups = $backup->listBackups();
            foreach ($backups as $backup) {
                printf(
                    "- %s (%s, %s, %s)\n",
                    $backup['timestamp'],
                    $backup['version'],
                    $backup['environment'],
                    $this->formatBytes($backup['size'])
                );
            }
            break;

        case 'restore':
            $backupPath = $argv[2] ?? null;
            if (!$backupPath) {
                echo "Usage: php backup-system.php restore <backup-path>\n";
                exit(1);
            }
            echo "Starting restore from: $backupPath\n";
            $success = $backup->restoreFromBackup($backupPath);
            echo $success ? "Restore completed successfully!\n" : "Restore failed!\n";
            break;

        case 'help':
        default:
            echo "RenalTales Backup System\n";
            echo "Usage: php backup-system.php <command> [options]\n\n";
            echo "Commands:\n";
            echo "  full                 - Perform full system backup\n";
            echo "  database [file]      - Backup database only\n";
            echo "  list                 - List available backups\n";
            echo "  restore <path>       - Restore from backup\n";
            echo "  help                 - Show this help message\n";
            break;
    }
}
