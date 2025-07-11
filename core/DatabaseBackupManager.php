<?php

/**
 * Database Backup Manager
 * 
 * Automated database backup system for Renal Tales
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

class DatabaseBackupManager {
    
    private $dbConfig;
    private $backupPath;
    private $backupRetention = 30; // days
    private $compressionEnabled = true;
    private $encryptionEnabled = false;
    private $encryptionKey = null;
    
    /**
     * Constructor
     * 
     * @param DatabaseConfig $dbConfig
     */
    public function __construct(DatabaseConfig $dbConfig) {
        $this->dbConfig = $dbConfig;
        $this->loadConfiguration();
        $this->ensureBackupDirectory();
    }
    
    /**
     * Load backup configuration
     */
    private function loadConfiguration() {
        $this->backupPath = $_ENV['DB_BACKUP_PATH'] ?? dirname(__DIR__) . '/storage/backups';
        $this->backupRetention = (int)($_ENV['DB_BACKUP_RETENTION_DAYS'] ?? 30);
        $this->compressionEnabled = ($_ENV['DB_BACKUP_COMPRESSION'] ?? 'true') === 'true';
        $this->encryptionEnabled = ($_ENV['DB_BACKUP_ENCRYPTION'] ?? 'false') === 'true';
        $this->encryptionKey = $_ENV['DB_BACKUP_ENCRYPTION_KEY'] ?? null;
    }
    
    /**
     * Ensure backup directory exists
     */
    private function ensureBackupDirectory() {
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
        
        // Create subdirectories for different backup types
        $subdirs = ['full', 'incremental', 'schema', 'data'];
        foreach ($subdirs as $subdir) {
            $path = $this->backupPath . '/' . $subdir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }
    
    /**
     * Create full database backup
     * 
     * @param string $connectionName
     * @param array $options
     * @return array Backup result
     */
    public function createFullBackup($connectionName = null, $options = []) {
        $connectionName = $connectionName ?? $this->dbConfig->getDefaultConnection();
        $config = $this->dbConfig->getConnection($connectionName);
        
        $backupInfo = [
            'type' => 'full',
            'connection' => $connectionName,
            'timestamp' => date('Y-m-d_H-i-s'),
            'started_at' => date('Y-m-d H:i:s'),
            'completed_at' => null,
            'file_path' => null,
            'file_size' => 0,
            'compressed' => $this->compressionEnabled,
            'encrypted' => $this->encryptionEnabled,
            'success' => false,
            'error' => null
        ];
        
        try {
            $filename = "full_backup_{$connectionName}_{$backupInfo['timestamp']}.sql";
            if ($this->compressionEnabled) {
                $filename .= '.gz';
            }
            
            $backupFile = $this->backupPath . '/full/' . $filename;
            $backupInfo['file_path'] = $backupFile;
            
            // Create mysqldump command
            $command = $this->buildMysqldumpCommand($config, $backupFile, $options);
            
            $this->log("Starting full backup for connection: $connectionName");
            $this->log("Command: " . $this->maskSensitiveInfo($command));
            
            // Execute backup
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($backupFile)) {
                $backupInfo['file_size'] = filesize($backupFile);
                $backupInfo['success'] = true;
                $backupInfo['completed_at'] = date('Y-m-d H:i:s');
                
                // Apply encryption if enabled
                if ($this->encryptionEnabled) {
                    $encryptedFile = $this->encryptBackup($backupFile);
                    if ($encryptedFile) {
                        unlink($backupFile); // Remove unencrypted file
                        $backupInfo['file_path'] = $encryptedFile;
                        $backupInfo['file_size'] = filesize($encryptedFile);
                    }
                }
                
                $this->log("Full backup completed successfully: " . $backupInfo['file_path']);
                $this->log("Backup size: " . $this->formatFileSize($backupInfo['file_size']));
                
                // Store backup metadata
                $this->storeBackupMetadata($backupInfo);
                
            } else {
                $backupInfo['error'] = "Backup command failed with return code: $returnCode. Output: " . implode("\n", $output);
                $this->log("Full backup failed: " . $backupInfo['error']);
            }
            
        } catch (Exception $e) {
            $backupInfo['error'] = $e->getMessage();
            $this->log("Full backup error: " . $backupInfo['error']);
        }
        
        return $backupInfo;
    }
    
    /**
     * Create schema-only backup
     * 
     * @param string $connectionName
     * @return array Backup result
     */
    public function createSchemaBackup($connectionName = null) {
        return $this->createFullBackup($connectionName, ['schema_only' => true]);
    }
    
    /**
     * Create data-only backup
     * 
     * @param string $connectionName
     * @return array Backup result
     */
    public function createDataBackup($connectionName = null) {
        return $this->createFullBackup($connectionName, ['data_only' => true]);
    }
    
    /**
     * Create incremental backup (simplified version)
     * 
     * @param string $connectionName
     * @return array Backup result
     */
    public function createIncrementalBackup($connectionName = null) {
        // For MySQL, incremental backups would typically use binary logs
        // This is a simplified implementation that backs up specific tables based on timestamps
        
        $connectionName = $connectionName ?? $this->dbConfig->getDefaultConnection();
        $config = $this->dbConfig->getConnection($connectionName);
        
        $backupInfo = [
            'type' => 'incremental',
            'connection' => $connectionName,
            'timestamp' => date('Y-m-d_H-i-s'),
            'started_at' => date('Y-m-d H:i:s'),
            'completed_at' => null,
            'file_path' => null,
            'file_size' => 0,
            'success' => false,
            'error' => null,
            'tables_backed_up' => []
        ];
        
        try {
            // Get last backup time
            $lastBackupTime = $this->getLastBackupTime($connectionName);
            
            // Find tables with changes since last backup
            $changedTables = $this->findChangedTables($config, $lastBackupTime);
            
            if (empty($changedTables)) {
                $backupInfo['success'] = true;
                $backupInfo['completed_at'] = date('Y-m-d H:i:s');
                $backupInfo['error'] = 'No changes detected since last backup';
                return $backupInfo;
            }
            
            $filename = "incremental_backup_{$connectionName}_{$backupInfo['timestamp']}.sql";
            if ($this->compressionEnabled) {
                $filename .= '.gz';
            }
            
            $backupFile = $this->backupPath . '/incremental/' . $filename;
            $backupInfo['file_path'] = $backupFile;
            $backupInfo['tables_backed_up'] = $changedTables;
            
            // Create backup of changed tables
            $options = ['tables' => $changedTables];
            $command = $this->buildMysqldumpCommand($config, $backupFile, $options);
            
            $this->log("Starting incremental backup for connection: $connectionName");
            $this->log("Tables to backup: " . implode(', ', $changedTables));
            
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($backupFile)) {
                $backupInfo['file_size'] = filesize($backupFile);
                $backupInfo['success'] = true;
                $backupInfo['completed_at'] = date('Y-m-d H:i:s');
                
                $this->log("Incremental backup completed successfully: " . $backupInfo['file_path']);
                $this->storeBackupMetadata($backupInfo);
            } else {
                $backupInfo['error'] = "Incremental backup failed with return code: $returnCode";
                $this->log("Incremental backup failed: " . $backupInfo['error']);
            }
            
        } catch (Exception $e) {
            $backupInfo['error'] = $e->getMessage();
            $this->log("Incremental backup error: " . $backupInfo['error']);
        }
        
        return $backupInfo;
    }
    
    /**
     * Build mysqldump command
     * 
     * @param array $config
     * @param string $outputFile
     * @param array $options
     * @return string
     */
    private function buildMysqldumpCommand($config, $outputFile, $options = []) {
        $command = 'mysqldump';
        
        // Connection parameters
        $command .= ' -h ' . escapeshellarg($config['host']);
        $command .= ' -P ' . escapeshellarg($config['port']);
        $command .= ' -u ' . escapeshellarg($config['username']);
        
        if (!empty($config['password'])) {
            $command .= ' -p' . escapeshellarg($config['password']);
        }
        
        // Backup options
        $command .= ' --single-transaction';
        $command .= ' --routines';
        $command .= ' --triggers';
        $command .= ' --lock-tables=false';
        
        if (isset($options['schema_only']) && $options['schema_only']) {
            $command .= ' --no-data';
        } elseif (isset($options['data_only']) && $options['data_only']) {
            $command .= ' --no-create-info';
        }
        
        // Database name
        $command .= ' ' . escapeshellarg($config['database']);
        
        // Specific tables if specified
        if (isset($options['tables']) && is_array($options['tables'])) {
            $command .= ' ' . implode(' ', array_map('escapeshellarg', $options['tables']));
        }
        
        // Output handling
        if ($this->compressionEnabled) {
            $command .= ' | gzip > ' . escapeshellarg($outputFile);
        } else {
            $command .= ' > ' . escapeshellarg($outputFile);
        }
        
        return $command;
    }
    
    /**
     * Find tables that have changed since last backup
     * 
     * @param array $config
     * @param string $lastBackupTime
     * @return array
     */
    private function findChangedTables($config, $lastBackupTime) {
        // This is a simplified implementation
        // In a real system, you might track table modification times or use binary logs
        
        $changedTables = [];
        
        try {
            $pdo = new PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}",
                $config['username'],
                $config['password']
            );
            
            // Check tables with UPDATE_TIME information
            $query = "
                SELECT TABLE_NAME 
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = ? 
                AND UPDATE_TIME > ? 
                AND TABLE_TYPE = 'BASE TABLE'
            ";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([$config['database'], $lastBackupTime]);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $changedTables[] = $row['TABLE_NAME'];
            }
            
        } catch (Exception $e) {
            $this->log("Error finding changed tables: " . $e->getMessage());
        }
        
        return $changedTables;
    }
    
    /**
     * Get last backup time
     * 
     * @param string $connectionName
     * @return string
     */
    private function getLastBackupTime($connectionName) {
        $metadataFile = $this->backupPath . '/metadata.json';
        
        if (!file_exists($metadataFile)) {
            return '1970-01-01 00:00:00'; // Very old date if no previous backups
        }
        
        $metadata = json_decode(file_get_contents($metadataFile), true);
        $lastBackup = '1970-01-01 00:00:00';
        
        foreach ($metadata as $backup) {
            if ($backup['connection'] === $connectionName && 
                $backup['success'] && 
                $backup['completed_at'] > $lastBackup) {
                $lastBackup = $backup['completed_at'];
            }
        }
        
        return $lastBackup;
    }
    
    /**
     * Store backup metadata
     * 
     * @param array $backupInfo
     */
    private function storeBackupMetadata($backupInfo) {
        $metadataFile = $this->backupPath . '/metadata.json';
        $metadata = [];
        
        if (file_exists($metadataFile)) {
            $metadata = json_decode(file_get_contents($metadataFile), true) ?: [];
        }
        
        $metadata[] = $backupInfo;
        
        // Keep only recent metadata (last 1000 backups)
        if (count($metadata) > 1000) {
            $metadata = array_slice($metadata, -1000);
        }
        
        file_put_contents($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT));
    }
    
    /**
     * Encrypt backup file
     * 
     * @param string $filePath
     * @return string|false Encrypted file path or false on failure
     */
    private function encryptBackup($filePath) {
        if (!$this->encryptionKey) {
            return false;
        }
        
        $encryptedFile = $filePath . '.enc';
        
        try {
            $data = file_get_contents($filePath);
            $encryptedData = openssl_encrypt($data, 'AES-256-CBC', $this->encryptionKey, 0, substr($this->encryptionKey, 0, 16));
            
            if ($encryptedData !== false) {
                file_put_contents($encryptedFile, $encryptedData);
                return $encryptedFile;
            }
            
        } catch (Exception $e) {
            $this->log("Encryption failed: " . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Clean old backups based on retention policy
     * 
     * @return array Cleanup results
     */
    public function cleanupOldBackups() {
        $results = [
            'deleted_files' => 0,
            'freed_space' => 0,
            'errors' => []
        ];
        
        $cutoffDate = date('Y-m-d', strtotime("-{$this->backupRetention} days"));
        $this->log("Cleaning up backups older than: $cutoffDate");
        
        $backupDirs = ['full', 'incremental', 'schema', 'data'];
        
        foreach ($backupDirs as $dir) {
            $dirPath = $this->backupPath . '/' . $dir;
            
            if (!is_dir($dirPath)) {
                continue;
            }
            
            $files = glob($dirPath . '/*');
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    $fileDate = date('Y-m-d', filemtime($file));
                    
                    if ($fileDate < $cutoffDate) {
                        $fileSize = filesize($file);
                        
                        if (unlink($file)) {
                            $results['deleted_files']++;
                            $results['freed_space'] += $fileSize;
                            $this->log("Deleted old backup: " . basename($file));
                        } else {
                            $results['errors'][] = "Failed to delete: " . basename($file);
                        }
                    }
                }
            }
        }
        
        $this->log("Cleanup completed. Deleted {$results['deleted_files']} files, freed " . $this->formatFileSize($results['freed_space']));
        
        return $results;
    }
    
    /**
     * Schedule automated backups
     * 
     * @param array $schedule Configuration for automated backups
     */
    public function scheduleAutomatedBackups($schedule = []) {
        $defaultSchedule = [
            'full_backup' => 'daily', // daily, weekly, monthly
            'incremental_backup' => 'hourly', // hourly, every_6_hours, every_12_hours
            'cleanup' => 'weekly'
        ];
        
        $schedule = array_merge($defaultSchedule, $schedule);
        
        // Generate cron job commands
        $cronJobs = $this->generateCronJobs($schedule);
        
        $this->log("Automated backup schedule configured:");
        foreach ($cronJobs as $job) {
            $this->log("  " . $job);
        }
        
        return $cronJobs;
    }
    
    /**
     * Generate cron job commands
     * 
     * @param array $schedule
     * @return array
     */
    private function generateCronJobs($schedule) {
        $phpPath = PHP_BINARY;
        $scriptPath = dirname(__DIR__) . '/scripts/backup_manager.php';
        $jobs = [];
        
        // Full backup schedule
        switch ($schedule['full_backup']) {
            case 'daily':
                $jobs[] = "0 2 * * * $phpPath $scriptPath --type=full";
                break;
            case 'weekly':
                $jobs[] = "0 2 * * 0 $phpPath $scriptPath --type=full";
                break;
            case 'monthly':
                $jobs[] = "0 2 1 * * $phpPath $scriptPath --type=full";
                break;
        }
        
        // Incremental backup schedule
        switch ($schedule['incremental_backup']) {
            case 'hourly':
                $jobs[] = "0 * * * * $phpPath $scriptPath --type=incremental";
                break;
            case 'every_6_hours':
                $jobs[] = "0 */6 * * * $phpPath $scriptPath --type=incremental";
                break;
            case 'every_12_hours':
                $jobs[] = "0 */12 * * * $phpPath $scriptPath --type=incremental";
                break;
        }
        
        // Cleanup schedule
        if ($schedule['cleanup'] === 'weekly') {
            $jobs[] = "0 3 * * 0 $phpPath $scriptPath --type=cleanup";
        }
        
        return $jobs;
    }
    
    /**
     * Get backup statistics
     * 
     * @return array
     */
    public function getBackupStatistics() {
        $stats = [
            'total_backups' => 0,
            'successful_backups' => 0,
            'failed_backups' => 0,
            'total_size' => 0,
            'backup_types' => [],
            'recent_backups' => [],
            'oldest_backup' => null,
            'newest_backup' => null
        ];
        
        $metadataFile = $this->backupPath . '/metadata.json';
        
        if (!file_exists($metadataFile)) {
            return $stats;
        }
        
        $metadata = json_decode(file_get_contents($metadataFile), true) ?: [];
        
        foreach ($metadata as $backup) {
            $stats['total_backups']++;
            
            if ($backup['success']) {
                $stats['successful_backups']++;
                $stats['total_size'] += $backup['file_size'];
            } else {
                $stats['failed_backups']++;
            }
            
            // Count backup types
            $type = $backup['type'];
            $stats['backup_types'][$type] = ($stats['backup_types'][$type] ?? 0) + 1;
            
            // Track oldest and newest backups
            if ($stats['oldest_backup'] === null || $backup['started_at'] < $stats['oldest_backup']) {
                $stats['oldest_backup'] = $backup['started_at'];
            }
            
            if ($stats['newest_backup'] === null || $backup['started_at'] > $stats['newest_backup']) {
                $stats['newest_backup'] = $backup['started_at'];
            }
        }
        
        // Get recent backups (last 10)
        $stats['recent_backups'] = array_slice($metadata, -10);
        
        return $stats;
    }
    
    /**
     * Restore database from backup
     * 
     * @param string $backupFile
     * @param string $connectionName
     * @return array Restore result
     */
    public function restoreFromBackup($backupFile, $connectionName = null) {
        $connectionName = $connectionName ?? $this->dbConfig->getDefaultConnection();
        $config = $this->dbConfig->getConnection($connectionName);
        
        $restoreInfo = [
            'backup_file' => $backupFile,
            'connection' => $connectionName,
            'started_at' => date('Y-m-d H:i:s'),
            'completed_at' => null,
            'success' => false,
            'error' => null
        ];
        
        try {
            if (!file_exists($backupFile)) {
                throw new Exception("Backup file not found: $backupFile");
            }
            
            $this->log("Starting database restore from: $backupFile");
            
            // Determine if file is compressed
            $isCompressed = pathinfo($backupFile, PATHINFO_EXTENSION) === 'gz';
            
            // Build restore command
            $command = $this->buildRestoreCommand($config, $backupFile, $isCompressed);
            
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0) {
                $restoreInfo['success'] = true;
                $restoreInfo['completed_at'] = date('Y-m-d H:i:s');
                $this->log("Database restore completed successfully");
            } else {
                $restoreInfo['error'] = "Restore failed with return code: $returnCode. Output: " . implode("\n", $output);
                $this->log("Database restore failed: " . $restoreInfo['error']);
            }
            
        } catch (Exception $e) {
            $restoreInfo['error'] = $e->getMessage();
            $this->log("Database restore error: " . $restoreInfo['error']);
        }
        
        return $restoreInfo;
    }
    
    /**
     * Build restore command
     * 
     * @param array $config
     * @param string $backupFile
     * @param bool $isCompressed
     * @return string
     */
    private function buildRestoreCommand($config, $backupFile, $isCompressed) {
        if ($isCompressed) {
            $command = 'gunzip -c ' . escapeshellarg($backupFile) . ' | ';
        } else {
            $command = '';
        }
        
        $command .= 'mysql';
        $command .= ' -h ' . escapeshellarg($config['host']);
        $command .= ' -P ' . escapeshellarg($config['port']);
        $command .= ' -u ' . escapeshellarg($config['username']);
        
        if (!empty($config['password'])) {
            $command .= ' -p' . escapeshellarg($config['password']);
        }
        
        $command .= ' ' . escapeshellarg($config['database']);
        
        if (!$isCompressed) {
            $command .= ' < ' . escapeshellarg($backupFile);
        }
        
        return $command;
    }
    
    /**
     * Format file size for human reading
     * 
     * @param int $bytes
     * @return string
     */
    private function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Mask sensitive information in commands
     * 
     * @param string $command
     * @return string
     */
    private function maskSensitiveInfo($command) {
        return preg_replace('/-p[^\s]+/', '-p[MASKED]', $command);
    }
    
    /**
     * Log backup events
     * 
     * @param string $message
     */
    private function log($message) {
        $logEntry = '[' . date('Y-m-d H:i:s') . '] DB Backup Manager: ' . $message . PHP_EOL;
        
        $logFile = dirname(__DIR__) . '/storage/logs/db_backup.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        if (isset($GLOBALS['logger'])) {
            $GLOBALS['logger']->info('DB Backup Manager: ' . $message);
        }
    }
}
