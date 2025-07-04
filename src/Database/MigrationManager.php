<?php
declare(strict_types=1);

namespace RenalTales\Database;

use PDO;
use RenalTales\Core\Config;

class MigrationManager
{
    private PDO $pdo;
    private string $migrationsPath;

    public function __construct(Config $config)
    {
        $this->pdo = new PDO(
            sprintf(
                "%s:host=%s;dbname=%s;charset=%s",
                $config->get('database.driver'),
                $config->get('database.host'),
                $config->get('database.database'),
                $config->get('database.charset')
            ),
            $config->get('database.username'),
            $config->get('database.password'),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        $this->migrationsPath = dirname(__DIR__, 2) . '/database/migrations';
    }

    public function migrate(): void
    {
        // Create migrations table if it doesn't exist
        $this->createMigrationsTable();

        // Get all migration files
        $files = glob($this->migrationsPath . '/*.sql');
        sort($files);

        // Get executed migrations
        $executed = $this->getExecutedMigrations();

        foreach ($files as $file) {
            $version = basename($file, '.sql');
            
            if (!in_array($version, $executed)) {
                $sql = file_get_contents($file);
                
                try {
                    $this->pdo->beginTransaction();
                    
                    // Execute migration
                    $this->pdo->exec($sql);
                    
                    // Record migration
                    $stmt = $this->pdo->prepare(
                        "INSERT INTO migrations (version, executed_at) VALUES (?, NOW())"
                    );
                    $stmt->execute([$version]);
                    
                    $this->pdo->commit();
                    
                    echo "Migrated: {$version}\n";
                } catch (\Exception $e) {
                    $this->pdo->rollBack();
                    throw new \RuntimeException(
                        "Migration {$version} failed: " . $e->getMessage()
                    );
                }
            }
        }
    }

    private function createMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            version VARCHAR(255) NOT NULL,
            executed_at DATETIME NOT NULL,
            UNIQUE KEY unique_version (version)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $this->pdo->exec($sql);
    }

    private function getExecutedMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT version FROM migrations ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
