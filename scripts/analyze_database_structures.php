#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Database Structure Analysis Script
 *
 * This script analyzes:
 * 1. Local database structure (what exists now)
 * 2. Migration files (what should exist after migrations)
 * 3. Identifies differences and provides recommendations
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

class DatabaseStructureAnalyzer
{
    private array $localTables = [];
    private array $migrationTables = [];
    private array $recommendations = [];
    private array $errors = [];
    private PDO $pdo;

    public function __construct()
    {
        // Initialize local database connection
        $this->initializeLocalConnection();
    }

    /**
     * Initialize local database connection
     */
    private function initializeLocalConnection(): void
    {
        try {
            // Use configuration from rules: host=localhost, database=renaltales, username=root, charset=utf8mb4
            $dsn = "mysql:host=localhost;dbname=renaltales;charset=utf8mb4";
            $this->pdo = new PDO($dsn, 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);

            echo "✅ Successfully connected to local database\n";
        } catch (PDOException $e) {
            $this->errors[] = "Failed to connect to local database: " . $e->getMessage();
            throw new Exception("Cannot connect to local database: " . $e->getMessage());
        }
    }

    /**
     * Run complete analysis
     */
    public function runAnalysis(): void
    {
        echo "🔍 Starting database structure analysis...\n\n";

        try {
            $this->analyzeLocalDatabase();
            $this->analyzeMigrationFiles();
            $this->compareStructures();
            $this->generateRecommendations();
            $this->displayResults();
        } catch (Exception $e) {
            echo "❌ Analysis failed: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Analyze local database structure
     */
    private function analyzeLocalDatabase(): void
    {
        echo "📊 Analyzing local database structure...\n";

        try {
            // Get all tables in the local database
            $stmt = $this->pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                $this->localTables[$table] = $this->getTableStructure($table);
                echo "  - Found table: {$table}\n";
            }

            echo "✅ Found " . count($this->localTables) . " tables in local database\n\n";
        } catch (PDOException $e) {
            $this->errors[] = "Error analyzing local database: " . $e->getMessage();
            throw new Exception("Failed to analyze local database: " . $e->getMessage());
        }
    }

    /**
     * Get table structure
     */
    private function getTableStructure(string $tableName): array
    {
        $structure = [];

        try {
            // Get column information
            $stmt = $this->pdo->query("DESCRIBE `{$tableName}`");
            $columns = $stmt->fetchAll();

            foreach ($columns as $column) {
                $structure['columns'][] = [
                    'name' => $column['Field'],
                    'type' => $column['Type'],
                    'null' => $column['Null'] === 'YES',
                    'key' => $column['Key'],
                    'default' => $column['Default'],
                    'extra' => $column['Extra']
                ];
            }

            // Get indexes
            $stmt = $this->pdo->query("SHOW INDEX FROM `{$tableName}`");
            $indexes = $stmt->fetchAll();

            foreach ($indexes as $index) {
                $structure['indexes'][] = [
                    'name' => $index['Key_name'],
                    'column' => $index['Column_name'],
                    'unique' => $index['Non_unique'] == 0,
                    'type' => $index['Index_type']
                ];
            }

            // Get row count
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM `{$tableName}`");
            $result = $stmt->fetch();
            $structure['row_count'] = $result['count'];

        } catch (PDOException $e) {
            $this->errors[] = "Error getting structure for table {$tableName}: " . $e->getMessage();
            $structure = ['error' => $e->getMessage()];
        }

        return $structure;
    }

    /**
     * Analyze migration files
     */
    private function analyzeMigrationFiles(): void
    {
        echo "📋 Analyzing migration files...\n";

        $migrationPath = APP_ROOT . DS . 'database' . DS . 'migrations';

        if (!is_dir($migrationPath)) {
            $this->errors[] = "Migration directory not found: {$migrationPath}";
            return;
        }

        $migrationFiles = glob($migrationPath . DS . '*.php');

        foreach ($migrationFiles as $file) {
            echo "  - Analyzing migration: " . basename($file) . "\n";
            $this->analyzeMigrationFile($file);
        }

        echo "✅ Analyzed " . count($migrationFiles) . " migration files\n\n";
    }

    /**
     * Analyze individual migration file
     */
    private function analyzeMigrationFile(string $filePath): void
    {
        $content = file_get_contents($filePath);

        // Extract CREATE TABLE statements
        preg_match_all('/CREATE TABLE (\w+) \(([^)]*)\)/i', $content, $createMatches);

        for ($i = 0; $i < count($createMatches[1]); $i++) {
            $tableName = $createMatches[1][$i];
            $tableDefinition = $createMatches[2][$i];

            $this->migrationTables[$tableName] = [
                'action' => 'create',
                'definition' => $tableDefinition,
                'file' => basename($filePath)
            ];
        }

        // Extract DROP TABLE statements
        preg_match_all('/DROP TABLE ([^;]+);/i', $content, $dropMatches);

        foreach ($dropMatches[1] as $tableName) {
            $tableName = trim($tableName);
            $this->migrationTables[$tableName] = [
                'action' => 'drop',
                'file' => basename($filePath)
            ];
        }
    }

    /**
     * Compare local and migration structures
     */
    private function compareStructures(): void
    {
        echo "🔄 Comparing local database with migration expectations...\n";

        $localTableNames = array_keys($this->localTables);
        $migrationTableNames = array_keys($this->migrationTables);

        // Tables that exist locally but not in migrations
        $extraLocalTables = array_diff($localTableNames, $migrationTableNames);

        // Tables that should exist after migrations but don't exist locally
        $missingLocalTables = [];
        foreach ($this->migrationTables as $tableName => $info) {
            if ($info['action'] === 'create' && !in_array($tableName, $localTableNames)) {
                $missingLocalTables[] = $tableName;
            }
        }

        // Tables that should be dropped
        $tablesToDrop = [];
        foreach ($this->migrationTables as $tableName => $info) {
            if ($info['action'] === 'drop' && in_array($tableName, $localTableNames)) {
                $tablesToDrop[] = $tableName;
            }
        }

        echo "  - Local tables not in migrations: " . count($extraLocalTables) . "\n";
        echo "  - Tables to be created: " . count($missingLocalTables) . "\n";
        echo "  - Tables to be dropped: " . count($tablesToDrop) . "\n\n";

        // Store results for recommendations
        $this->recommendations['extra_local'] = $extraLocalTables;
        $this->recommendations['missing_local'] = $missingLocalTables;
        $this->recommendations['to_drop'] = $tablesToDrop;
    }

    /**
     * Generate recommendations
     */
    private function generateRecommendations(): void
    {
        echo "💡 Generating recommendations...\n\n";

        // Recommendations for extra local tables
        if (!empty($this->recommendations['extra_local'])) {
            echo "⚠️  Tables exist locally but not in migrations:\n";
            foreach ($this->recommendations['extra_local'] as $table) {
                echo "  - {$table} (contains {$this->localTables[$table]['row_count']} rows)\n";
            }
            echo "  → These tables may need to be included in migrations if they're needed remotely\n\n";
        }

        // Recommendations for missing local tables
        if (!empty($this->recommendations['missing_local'])) {
            echo "📋 Tables that will be created by migrations:\n";
            foreach ($this->recommendations['missing_local'] as $table) {
                $migrationInfo = $this->migrationTables[$table];
                echo "  - {$table} (from {$migrationInfo['file']})\n";
            }
            echo "  → These tables will be created when migrations are run on the remote database\n\n";
        }

        // Recommendations for tables to drop
        if (!empty($this->recommendations['to_drop'])) {
            echo "🗑️  Tables that will be dropped by migrations:\n";
            foreach ($this->recommendations['to_drop'] as $table) {
                $migrationInfo = $this->migrationTables[$table];
                echo "  - {$table} (contains {$this->localTables[$table]['row_count']} rows) - will be dropped by {$migrationInfo['file']}\n";
            }
            echo "  → ⚠️  WARNING: These tables and their data will be lost when migrations run!\n\n";
        }
    }

    /**
     * Display detailed results
     */
    private function displayResults(): void
    {
        echo str_repeat("=", 70) . "\n";
        echo "📊 DATABASE STRUCTURE ANALYSIS REPORT\n";
        echo str_repeat("=", 70) . "\n\n";

        // Local database summary
        echo "🏠 LOCAL DATABASE STRUCTURE:\n";
        echo "  Database: renaltales\n";
        echo "  Tables: " . count($this->localTables) . "\n";
        foreach ($this->localTables as $tableName => $structure) {
            $columnCount = isset($structure['columns']) ? count($structure['columns']) : 0;
            $rowCount = $structure['row_count'] ?? 0;
            echo "    - {$tableName}: {$columnCount} columns, {$rowCount} rows\n";
        }
        echo "\n";

        // Migration analysis
        echo "📋 MIGRATION ANALYSIS:\n";
        echo "  Migration files analyzed: " . count(glob(APP_ROOT . DS . 'database' . DS . 'migrations' . DS . '*.php')) . "\n";
        echo "  Tables to create: " . count($this->recommendations['missing_local']) . "\n";
        echo "  Tables to drop: " . count($this->recommendations['to_drop']) . "\n";
        echo "\n";

        // Critical warnings
        if (!empty($this->recommendations['to_drop'])) {
            echo "⚠️  CRITICAL WARNINGS:\n";
            echo "  - Migrations will DROP existing tables with data!\n";
            echo "  - Ensure you have backups before running migrations remotely\n";
            echo "  - Tables to be dropped: " . implode(', ', $this->recommendations['to_drop']) . "\n";
            echo "\n";
        }

        // Safe operation recommendations
        echo "✅ SAFE OPERATION RECOMMENDATIONS:\n";
        echo "  1. Create backup of remote database before running migrations\n";
        echo "  2. Test migrations on a copy of the remote database first\n";
        echo "  3. Review migration files to ensure they match your intentions\n";
        echo "  4. Consider creating separate migrations for new tables only\n";
        echo "\n";

        // Next steps
        echo "🚀 NEXT STEPS:\n";
        echo "  1. Connect to remote database and run: SHOW TABLES;\n";
        echo "  2. Compare remote table list with this analysis\n";
        echo "  3. Create focused migrations for missing tables only\n";
        echo "  4. Run migrations carefully with proper backups\n";
        echo "\n";

        // Display any errors
        if (!empty($this->errors)) {
            echo "❌ ERRORS ENCOUNTERED:\n";
            foreach ($this->errors as $error) {
                echo "  - {$error}\n";
            }
            echo "\n";
        }

        echo "Analysis completed at: " . date('Y-m-d H:i:s') . "\n";
    }
}

// Main execution
try {
    $analyzer = new DatabaseStructureAnalyzer();
    $analyzer->runAnalysis();
} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}
