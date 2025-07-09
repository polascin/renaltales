<?php
/**
 * Data Backup Script
 * 
 * Creates backup of existing data before migration
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

require_once __DIR__ . '/../../core/Database.php';

class DataBackupManager
{
    private $database;
    private $backupPath;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->backupPath = __DIR__ . '/../../storage/backups/';
        
        // Create backup directory if it doesn't exist
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }
    
    /**
     * Create full database backup
     */
    public function createFullBackup(): string
    {
        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = $this->backupPath . "full_backup_$timestamp.sql";
        
        echo "Creating full database backup...\n";
        
        // Get database configuration
        $config = $this->database->getConfig();
        $dbName = $config['database'];
        
        // Create mysqldump command
        $command = sprintf(
            'mysqldump -h%s -u%s -p%s %s > %s',
            $config['host'],
            $config['username'],
            $config['password'],
            $dbName,
            $backupFile
        );
        
        // Execute backup
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            echo "✓ Full backup created: $backupFile\n";
            return $backupFile;
        } else {
            throw new Exception("Backup failed with return code: $returnCode");
        }
    }
    
    /**
     * Create table-specific backup
     */
    public function createTableBackup(string $tableName): string
    {
        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = $this->backupPath . "table_{$tableName}_$timestamp.sql";
        
        echo "Creating backup for table: $tableName\n";
        
        // Check if table exists
        if (!$this->tableExists($tableName)) {
            echo "⚠ Table '$tableName' does not exist, skipping backup\n";
            return '';
        }
        
        // Get table structure
        $createTableQuery = $this->database->selectOne("SHOW CREATE TABLE `$tableName`");
        
        // Get table data
        $tableData = $this->database->select("SELECT * FROM `$tableName`");
        
        // Create backup content
        $backupContent = "-- Backup for table: $tableName\n";
        $backupContent .= "-- Created: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Add table structure
        $backupContent .= "-- Table structure\n";
        $backupContent .= "DROP TABLE IF EXISTS `$tableName`;\n";
        $backupContent .= $createTableQuery['Create Table'] . ";\n\n";
        
        // Add table data
        if (!empty($tableData)) {
            $backupContent .= "-- Table data\n";
            $backupContent .= "INSERT INTO `$tableName` VALUES\n";
            
            $values = [];
            foreach ($tableData as $row) {
                $escapedValues = [];
                foreach ($row as $value) {
                    $escapedValues[] = $value === null ? 'NULL' : "'" . $this->database->escape($value) . "'";
                }
                $values[] = '(' . implode(', ', $escapedValues) . ')';
            }
            
            $backupContent .= implode(",\n", $values) . ";\n\n";
        }
        
        // Write backup file
        file_put_contents($backupFile, $backupContent);
        
        echo "✓ Table backup created: $backupFile\n";
        return $backupFile;
    }
    
    /**
     * Create data export for migration
     */
    public function exportDataForMigration(): array
    {
        $timestamp = date('Y-m-d_H-i-s');
        $exportData = [];
        
        echo "Exporting data for migration...\n";
        
        // Export users data
        if ($this->tableExists('users')) {
            $users = $this->database->select("SELECT * FROM users");
            $exportData['users'] = $users;
            
            $exportFile = $this->backupPath . "users_export_$timestamp.json";
            file_put_contents($exportFile, json_encode($users, JSON_PRETTY_PRINT));
            echo "✓ Users data exported: $exportFile\n";
        }
        
        // Export stories data
        if ($this->tableExists('stories')) {
            $stories = $this->database->select("SELECT * FROM stories");
            $exportData['stories'] = $stories;
            
            $exportFile = $this->backupPath . "stories_export_$timestamp.json";
            file_put_contents($exportFile, json_encode($stories, JSON_PRETTY_PRINT));
            echo "✓ Stories data exported: $exportFile\n";
        }
        
        // Export categories data
        if ($this->tableExists('categories')) {
            $categories = $this->database->select("SELECT * FROM categories");
            $exportData['categories'] = $categories;
            
            $exportFile = $this->backupPath . "categories_export_$timestamp.json";
            file_put_contents($exportFile, json_encode($categories, JSON_PRETTY_PRINT));
            echo "✓ Categories data exported: $exportFile\n";
        }
        
        // Export tags data
        if ($this->tableExists('tags')) {
            $tags = $this->database->select("SELECT * FROM tags");
            $exportData['tags'] = $tags;
            
            $exportFile = $this->backupPath . "tags_export_$timestamp.json";
            file_put_contents($exportFile, json_encode($tags, JSON_PRETTY_PRINT));
            echo "✓ Tags data exported: $exportFile\n";
        }
        
        // Export media data
        if ($this->tableExists('media')) {
            $media = $this->database->select("SELECT * FROM media");
            $exportData['media'] = $media;
            
            $exportFile = $this->backupPath . "media_export_$timestamp.json";
            file_put_contents($exportFile, json_encode($media, JSON_PRETTY_PRINT));
            echo "✓ Media data exported: $exportFile\n";
        }
        
        // Export comments data
        if ($this->tableExists('comments')) {
            $comments = $this->database->select("SELECT * FROM comments");
            $exportData['comments'] = $comments;
            
            $exportFile = $this->backupPath . "comments_export_$timestamp.json";
            file_put_contents($exportFile, json_encode($comments, JSON_PRETTY_PRINT));
            echo "✓ Comments data exported: $exportFile\n";
        }
        
        // Create complete export file
        $completeExportFile = $this->backupPath . "complete_export_$timestamp.json";
        file_put_contents($completeExportFile, json_encode($exportData, JSON_PRETTY_PRINT));
        echo "✓ Complete export created: $completeExportFile\n";
        
        return $exportData;
    }
    
    /**
     * Verify backup integrity
     */
    public function verifyBackup(string $backupFile): bool
    {
        if (!file_exists($backupFile)) {
            return false;
        }
        
        $fileSize = filesize($backupFile);
        if ($fileSize === 0) {
            return false;
        }
        
        // Check if file is readable
        $content = file_get_contents($backupFile, false, null, 0, 1024);
        if ($content === false) {
            return false;
        }
        
        // Basic SQL structure check
        if (strpos($content, 'CREATE TABLE') !== false || 
            strpos($content, 'INSERT INTO') !== false ||
            strpos($content, '{') !== false) { // JSON check
            return true;
        }
        
        return false;
    }
    
    /**
     * Restore from backup
     */
    public function restoreFromBackup(string $backupFile): bool
    {
        if (!$this->verifyBackup($backupFile)) {
            throw new Exception("Invalid backup file: $backupFile");
        }
        
        echo "Restoring from backup: $backupFile\n";
        
        // Determine file type
        if (pathinfo($backupFile, PATHINFO_EXTENSION) === 'json') {
            return $this->restoreFromJsonBackup($backupFile);
        } else {
            return $this->restoreFromSqlBackup($backupFile);
        }
    }
    
    /**
     * Restore from SQL backup
     */
    private function restoreFromSqlBackup(string $backupFile): bool
    {
        $config = $this->database->getConfig();
        
        $command = sprintf(
            'mysql -h%s -u%s -p%s %s < %s',
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database'],
            $backupFile
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            echo "✓ SQL backup restored successfully\n";
            return true;
        } else {
            echo "✗ SQL backup restore failed with return code: $returnCode\n";
            return false;
        }
    }
    
    /**
     * Restore from JSON backup
     */
    private function restoreFromJsonBackup(string $backupFile): bool
    {
        $jsonData = json_decode(file_get_contents($backupFile), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON backup file");
        }
        
        $this->database->beginTransaction();
        
        try {
            foreach ($jsonData as $table => $data) {
                if (!$this->tableExists($table)) {
                    echo "⚠ Table '$table' does not exist, skipping restore\n";
                    continue;
                }
                
                // Clear existing data
                $this->database->execute("DELETE FROM `$table`");
                
                // Insert backup data
                foreach ($data as $row) {
                    $columns = array_keys($row);
                    $placeholders = array_fill(0, count($columns), '?');
                    $values = array_values($row);
                    
                    $sql = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $placeholders) . ")";
                    $this->database->execute($sql, $values);
                }
                
                echo "✓ Restored table: $table\n";
            }
            
            $this->database->commit();
            echo "✓ JSON backup restored successfully\n";
            return true;
            
        } catch (Exception $e) {
            $this->database->rollback();
            echo "✗ JSON backup restore failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Check if table exists
     */
    private function tableExists(string $tableName): bool
    {
        try {
            $result = $this->database->selectOne("SHOW TABLES LIKE ?", [$tableName]);
            return $result !== null;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * List available backups
     */
    public function listBackups(): array
    {
        $backups = [];
        $files = glob($this->backupPath . '*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $backups[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => filesize($file),
                    'created' => date('Y-m-d H:i:s', filemtime($file)),
                    'type' => pathinfo($file, PATHINFO_EXTENSION)
                ];
            }
        }
        
        // Sort by creation time (newest first)
        usort($backups, function($a, $b) {
            return strcmp($b['created'], $a['created']);
        });
        
        return $backups;
    }
    
    /**
     * Clean old backups
     */
    public function cleanOldBackups(int $keepDays = 30): int
    {
        $cutoffTime = time() - ($keepDays * 24 * 60 * 60);
        $deletedCount = 0;
        
        $files = glob($this->backupPath . '*');
        
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoffTime) {
                if (unlink($file)) {
                    $deletedCount++;
                    echo "✓ Deleted old backup: " . basename($file) . "\n";
                }
            }
        }
        
        return $deletedCount;
    }
}

// Command line interface
if (isset($argv[1])) {
    $backupManager = new DataBackupManager();
    
    switch ($argv[1]) {
        case 'full':
            try {
                $backupFile = $backupManager->createFullBackup();
                echo "Full backup completed: $backupFile\n";
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage() . "\n";
                exit(1);
            }
            break;
            
        case 'export':
            try {
                $exportData = $backupManager->exportDataForMigration();
                echo "Data export completed\n";
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage() . "\n";
                exit(1);
            }
            break;
            
        case 'table':
            if (!isset($argv[2])) {
                echo "Usage: php backup_existing_data.php table <table_name>\n";
                exit(1);
            }
            
            try {
                $backupFile = $backupManager->createTableBackup($argv[2]);
                if ($backupFile) {
                    echo "Table backup completed: $backupFile\n";
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage() . "\n";
                exit(1);
            }
            break;
            
        case 'list':
            $backups = $backupManager->listBackups();
            if (empty($backups)) {
                echo "No backups found\n";
            } else {
                echo "Available backups:\n";
                foreach ($backups as $backup) {
                    echo sprintf("  %s (%s, %s bytes, %s)\n", 
                        $backup['name'], 
                        $backup['type'],
                        number_format($backup['size']),
                        $backup['created']
                    );
                }
            }
            break;
            
        case 'restore':
            if (!isset($argv[2])) {
                echo "Usage: php backup_existing_data.php restore <backup_file>\n";
                exit(1);
            }
            
            try {
                $success = $backupManager->restoreFromBackup($argv[2]);
                if ($success) {
                    echo "Backup restored successfully\n";
                } else {
                    echo "Backup restore failed\n";
                    exit(1);
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage() . "\n";
                exit(1);
            }
            break;
            
        case 'clean':
            $keepDays = isset($argv[2]) ? (int)$argv[2] : 30;
            $deletedCount = $backupManager->cleanOldBackups($keepDays);
            echo "Cleaned $deletedCount old backups\n";
            break;
            
        case 'help':
        default:
            echo "Data Backup Manager\n";
            echo "Usage: php backup_existing_data.php [command] [options]\n\n";
            echo "Commands:\n";
            echo "  full                    - Create full database backup\n";
            echo "  export                  - Export data for migration (JSON format)\n";
            echo "  table <table_name>      - Create backup for specific table\n";
            echo "  list                    - List available backups\n";
            echo "  restore <backup_file>   - Restore from backup\n";
            echo "  clean [days]            - Clean old backups (default: 30 days)\n";
            echo "  help                    - Show this help\n";
            break;
    }
} else {
    echo "Use 'php backup_existing_data.php help' for usage information\n";
}
?>
