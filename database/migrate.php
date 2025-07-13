<?php

namespace RenalTales\Database;

/**
 * Database Migration Runner
 * 
 * Executes migration scripts in the correct order
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

// Include the database class
require_once __DIR__ . '/../core/Database.php';

class MigrationRunner {
    
    private $db;
    private $migrationsPath;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->migrationsPath = __DIR__ . '/migrations/';
    }
    
    /**
     * Run all pending migrations
     */
    public function runMigrations() {
        echo "Starting database migrations...\n";
        
        // Create migrations table if it doesn't exist
        $this->createMigrationsTable();
        
        // Get list of migration files
        $migrationFiles = $this->getMigrationFiles();
        
        // Get executed migrations
        $executedMigrations = $this->getExecutedMigrations();
        
        $executedCount = 0;
        
        foreach ($migrationFiles as $file) {
            $migrationName = basename($file, '.sql');
            
            if (!in_array($migrationName, $executedMigrations)) {
                echo "Executing migration: {$migrationName}\n";
                
                if ($this->executeMigration($file)) {
                    $executedCount++;
                    echo "✓ Migration {$migrationName} completed successfully\n";
                } else {
                    echo "✗ Migration {$migrationName} failed\n";
                    break;
                }
            } else {
                echo "- Migration {$migrationName} already executed\n";
            }
        }
        
        echo "\nMigrations completed. Executed {$executedCount} new migrations.\n";
        return $executedCount;
    }
    
    /**
     * Create migrations tracking table
     */
    private function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `database_migrations` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `migration` VARCHAR(255) NOT NULL,
            `batch` INT UNSIGNED NOT NULL,
            `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_migration` (`migration`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        try {
            $this->db->execute($sql);
        } catch (Exception $e) {
            echo "Error creating migrations table: " . $e->getMessage() . "\n";
            return false;
        }
        
        return true;
    }
    
    /**
     * Get list of migration files
     */
    private function getMigrationFiles() {
        $files = glob($this->migrationsPath . '*.sql');
        sort($files);
        return $files;
    }
    
    /**
     * Get list of executed migrations
     */
    private function getExecutedMigrations() {
        try {
            $result = $this->db->select("SELECT migration FROM database_migrations ORDER BY id");
            return array_column($result, 'migration');
        } catch (Exception $e) {
            // Table doesn't exist yet
            return [];
        }
    }
    
    /**
     * Execute a migration file
     */
    private function executeMigration($filePath) {
        try {
            $sql = file_get_contents($filePath);
            
            if ($sql === false) {
                echo "Error reading migration file: {$filePath}\n";
                return false;
            }
            
            // Split SQL file into individual statements
            $statements = $this->splitSqlStatements($sql);
            
            $this->db->beginTransaction();
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                
                if (empty($statement) || $statement === ';') {
                    continue;
                }
                
                // Skip comments
                if (strpos($statement, '--') === 0) {
                    continue;
                }
                
                $this->db->execute($statement);
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            echo "Error executing migration: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Split SQL file into individual statements
     */
    private function splitSqlStatements($sql) {
        // Remove SQL comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        
        // Split by semicolon but handle DELIMITER statements
        $statements = [];
        $current = '';
        $delimiter = ';';
        
        $lines = explode("\n", $sql);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                continue;
            }
            
            // Check for DELIMITER statement
            if (preg_match('/^DELIMITER\s+(.+)$/i', $line, $matches)) {
                $delimiter = $matches[1];
                continue;
            }
            
            $current .= $line . "\n";
            
            // Check if line ends with delimiter
            if (substr($line, -strlen($delimiter)) === $delimiter) {
                $statements[] = substr($current, 0, -strlen($delimiter));
                $current = '';
            }
        }
        
        // Add remaining statement
        if (!empty(trim($current))) {
            $statements[] = $current;
        }
        
        return $statements;
    }
    
    /**
     * Run seeders
     */
    public function runSeeders() {
        echo "Starting database seeding...\n";
        
        $seedersPath = __DIR__ . '/seeders/';
        $seederFiles = glob($seedersPath . '*.sql');
        sort($seederFiles);
        
        $executedCount = 0;
        
        foreach ($seederFiles as $file) {
            $seederName = basename($file, '.sql');
            echo "Executing seeder: {$seederName}\n";
            
            if ($this->executeMigration($file)) {
                $executedCount++;
                echo "✓ Seeder {$seederName} completed successfully\n";
            } else {
                echo "✗ Seeder {$seederName} failed\n";
                break;
            }
        }
        
        echo "\nSeeders completed. Executed {$executedCount} seeders.\n";
        return $executedCount;
    }
    
    /**
     * Test database connection
     */
    public function testConnection() {
        try {
            $result = $this->db->selectOne("SELECT 1 as test");
            if ($result && $result['test'] === 1) {
                echo "✓ Database connection successful\n";
                return true;
            }
        } catch (Exception $e) {
            echo "✗ Database connection failed: " . $e->getMessage() . "\n";
        }
        
        return false;
    }
}

// Command line interface
if (isset($argv[1])) {
    $runner = new MigrationRunner();
    
    switch ($argv[1]) {
        case 'test':
            $runner->testConnection();
            break;
            
        case 'migrate':
            if ($runner->testConnection()) {
                $runner->runMigrations();
            }
            break;
            
        case 'seed':
            if ($runner->testConnection()) {
                $runner->runSeeders();
            }
            break;
            
        case 'fresh':
            if ($runner->testConnection()) {
                $runner->runMigrations();
                $runner->runSeeders();
            }
            break;
            
        case 'help':
        default:
            echo "Database Migration Runner\n";
            echo "Usage: php migrate.php [command]\n\n";
            echo "Commands:\n";
            echo "  test     - Test database connection\n";
            echo "  migrate  - Run pending migrations\n";
            echo "  seed     - Run seeders\n";
            echo "  fresh    - Run migrations and seeders\n";
            echo "  help     - Show this help\n";
            break;
    }
} else {
    echo "Use 'php migrate.php help' for usage information\n";
}
?>
