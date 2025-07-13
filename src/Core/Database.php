<?php

declare(strict_types=1);

namespace RenalTales\Core;

use PDO;
use PDOException;
use PDOStatement;
use InvalidArgumentException;
use RuntimeException;
use RenalTales\Core\Contracts\DatabaseInterface;

/**
 * Database Connection Manager
 *
 * This class manages database connections and provides methods for executing queries
 * safely with proper error handling and connection pooling.
 */
class Database implements DatabaseInterface
{
    private static ?self $instance = null;
    private ?PDO $connection = null;
    private array $config;
    private string $environment;
    private bool $isConnected = false;

    /**
     * Database constructor
     */
    private function __construct(array $config = [], string $environment = 'default')
    {
        $this->config = $config;
        $this->environment = $environment;
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(array $config = [], string $environment = 'default'): self
    {
        if (self::$instance === null) {
            self::$instance = new self($config, $environment);
        }
        return self::$instance;
    }

    /**
     * Create a new database connection
     */
    public function connect(): PDO
    {
        if ($this->isConnected && $this->connection !== null) {
            return $this->connection;
        }

        try {
            $connectionConfig = $this->getConnectionConfig();
            
            $dsn = $this->buildDsn($connectionConfig);
            $username = $connectionConfig['username'] ?? '';
            $password = $connectionConfig['password'] ?? '';
            $options = $connectionConfig['options'] ?? $this->getDefaultOptions();

            $this->connection = new PDO($dsn, $username, $password, $options);
            $this->isConnected = true;

            // Set charset if specified
            if (isset($connectionConfig['charset'])) {
                $this->connection->exec("SET NAMES {$connectionConfig['charset']}");
            }

            return $this->connection;
        } catch (PDOException $e) {
            throw new RuntimeException("Database connection failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get the current PDO connection
     */
    public function getConnection(): PDO
    {
        if (!$this->isConnected || $this->connection === null) {
            return $this->connect();
        }
        return $this->connection;
    }

    /**
     * Execute a prepared statement with parameters
     */
    public function execute(string $query, array $params = []): PDOStatement
    {
        try {
            $connection = $this->getConnection();
            $statement = $connection->prepare($query);
            
            if (!$statement) {
                throw new RuntimeException("Failed to prepare statement: " . implode(', ', $connection->errorInfo()));
            }

            $success = $statement->execute($params);
            
            if (!$success) {
                throw new RuntimeException("Failed to execute statement: " . implode(', ', $statement->errorInfo()));
            }

            return $statement;
        } catch (PDOException $e) {
            throw new RuntimeException("Database query failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Fetch all rows from a query
     */
    public function fetchAll(string $query, array $params = []): array
    {
        $statement = $this->execute($query, $params);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $result !== false ? $result : [];
    }

    /**
     * Fetch one row from a query
     */
    public function fetchOne(string $query, array $params = []): ?array
    {
        $statement = $this->execute($query, $params);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    /**
     * Fetch a single column value
     */
    public function fetchColumn(string $query, array $params = []): mixed
    {
        $statement = $this->execute($query, $params);
        return $statement->fetchColumn();
    }

    /**
     * Get the number of affected rows from last statement
     */
    public function getAffectedRows(): int
    {
        if ($this->connection === null) {
            return 0;
        }
        return $this->connection->lastInsertId() ? 1 : 0;
    }

    /**
     * Get the last inserted ID
     */
    public function getLastInsertId(): string|false
    {
        if ($this->connection === null) {
            return false;
        }
        return $this->connection->lastInsertId();
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }

    /**
     * Rollback a transaction
     */
    public function rollback(): bool
    {
        return $this->getConnection()->rollback();
    }

    /**
     * Check if currently in a transaction
     */
    public function inTransaction(): bool
    {
        return $this->getConnection()->inTransaction();
    }

    /**
     * Execute a transaction with automatic rollback on failure
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();
        
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Quote a string for use in a query
     */
    public function quote(string $value): string
    {
        return $this->getConnection()->quote($value);
    }

    /**
     * Check if the database connection is healthy
     */
    public function isHealthy(): bool
    {
        try {
            $this->execute('SELECT 1');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get database information
     */
    public function getInfo(): array
    {
        try {
            $connection = $this->getConnection();
            return [
                'server_version' => $connection->getAttribute(PDO::ATTR_SERVER_VERSION),
                'client_version' => $connection->getAttribute(PDO::ATTR_CLIENT_VERSION),
                'driver_name' => $connection->getAttribute(PDO::ATTR_DRIVER_NAME),
                'connection_status' => $connection->getAttribute(PDO::ATTR_CONNECTION_STATUS),
                'server_info' => $connection->getAttribute(PDO::ATTR_SERVER_INFO),
            ];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Close the database connection
     */
    public function close(): void
    {
        $this->connection = null;
        $this->isConnected = false;
    }

    /**
     * Get connection configuration
     */
    private function getConnectionConfig(): array
    {
        if (!empty($this->config)) {
            return $this->config;
        }

        // Load configuration from file
        $configFile = __DIR__ . '/../../config/database.php';
        if (file_exists($configFile)) {
            $config = require $configFile;
            $connectionName = $config['default'] ?? 'mysql';
            
            if (isset($config['connections'][$connectionName])) {
                return $config['connections'][$connectionName];
            }
        }

        // Fallback to environment variables
        return $this->getEnvironmentConfig();
    }

    /**
     * Get configuration from environment variables
     */
    private function getEnvironmentConfig(): array
    {
        return [
            'driver' => $_ENV['DB_DRIVER'] ?? 'mysql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'database' => $_ENV['DB_DATABASE'] ?? 'renaltales',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
            'collation' => $_ENV['DB_COLLATION'] ?? 'utf8mb4_unicode_ci',
            'options' => $this->getDefaultOptions(),
        ];
    }

    /**
     * Build DSN string from configuration
     */
    private function buildDsn(array $config): string
    {
        $driver = $config['driver'] ?? 'mysql';
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? '3306';
        $database = $config['database'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';

        switch ($driver) {
            case 'mysql':
                return "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
            case 'pgsql':
                return "pgsql:host={$host};port={$port};dbname={$database}";
            case 'sqlite':
                return "sqlite:{$database}";
            default:
                throw new InvalidArgumentException("Unsupported database driver: {$driver}");
        }
    }

    /**
     * Get default PDO options
     */
    private function getDefaultOptions(): array
    {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 30,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
        ];
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->close();
    }
}
