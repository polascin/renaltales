<?php

declare(strict_types=1);

namespace RenalTales\Core;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Exception;

/**
 * Database Manager
 *
 * Manages Doctrine ORM setup, connections, and provides database operations.
 * Handles entity management, migrations, and caching configuration.
 *
 * @package RenalTales\Core
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */
class DatabaseManager
{
    /**
     * @var EntityManagerInterface|null The Doctrine entity manager
     */
    private ?EntityManagerInterface $entityManager = null;

    /**
     * @var Connection|null The database connection
     */
    private ?Connection $connection = null;

    /**
     * @var array<string, mixed> Database configuration
     */
    private array $config;

    /**
     * @var Logger|null Application logger
     */
    private ?Logger $logger = null;

    /**
     * @var bool Whether the database is connected
     */
    private bool $connected = false;

    /**
     * Constructor
     *
     * @param array<string, mixed> $config Database configuration
     * @param Logger|null $logger Application logger
     */
    public function __construct(array $config, ?Logger $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Initialize the database connection and entity manager
     *
     * @return void
     * @throws Exception If initialization fails
     */
    public function initialize(): void
    {
        if ($this->connected) {
            return;
        }

        try {
            $this->setupConnection();
            $this->setupEntityManager();
            $this->connected = true;

            $this->log('Database initialized successfully');
        } catch (Exception $e) {
            $this->log('Database initialization failed: ' . $e->getMessage(), 'error');
            throw new Exception('Database initialization failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Setup the database connection
     *
     * @return void
     * @throws DBALException If connection setup fails
     */
    private function setupConnection(): void
    {
        $connectionName = $this->config['default'] ?? 'mysql';
        $connectionConfig = $this->config['connections'][$connectionName] ?? [];

        if (empty($connectionConfig)) {
            throw new DBALException("Database connection configuration for '{$connectionName}' not found");
        }

        $this->connection = DriverManager::getConnection($connectionConfig);
    }

    /**
     * Setup the entity manager
     *
     * @return void
     * @throws ORMException If entity manager setup fails
     */
    private function setupEntityManager(): void
    {
        if (!$this->connection) {
            throw new ORMException('Database connection must be established before setting up entity manager');
        }

        // Create entity paths
        $entityPaths = $this->config['entity_paths'] ?? [];
        $this->ensureDirectoriesExist($entityPaths);

        // Setup caching
        $metadataCache = $this->createCacheAdapter('metadata');
        $queryCache = $this->createCacheAdapter('query');
        $resultCache = $this->createCacheAdapter('result');

        // Configure ORM
        $ormConfig = ORMSetup::createAttributeMetadataConfiguration(
            $entityPaths,
            $this->isDevelopmentMode(),
            $this->config['proxies']['directory'] ?? null,
            $metadataCache,
        );

        // Set query cache
        if ($queryCache) {
            $ormConfig->setQueryCache($queryCache);
        }

        // Set result cache
        if ($resultCache) {
            $ormConfig->setResultCache($resultCache);
        }

        // Configure proxies
        $proxyConfig = $this->config['proxies'] ?? [];
        if (isset($proxyConfig['directory'])) {
            $ormConfig->setProxyDir($proxyConfig['directory']);
            $this->ensureDirectoriesExist([$proxyConfig['directory']]);
        }
        if (isset($proxyConfig['namespace'])) {
            $ormConfig->setProxyNamespace($proxyConfig['namespace']);
        }
        if (isset($proxyConfig['auto_generate'])) {
            $ormConfig->setAutoGenerateProxyClasses($proxyConfig['auto_generate']);
        }

        // Database logging is disabled in this version due to deprecated SQLLogger interface
        // Will be re-implemented with newer Doctrine logging features

        // Create entity manager
        $this->entityManager = new EntityManager($this->connection, $ormConfig);
    }

    /**
     * Create cache adapter based on configuration
     *
     * @param string $type Cache type (metadata, query, result)
     * @return FilesystemAdapter|ArrayAdapter|null
     */
    private function createCacheAdapter(string $type): FilesystemAdapter|ArrayAdapter|null
    {
        $cacheConfig = $this->config['cache'][$type] ?? [];
        $cacheType = $cacheConfig['type'] ?? 'file';

        switch ($cacheType) {
            case 'file':
                $directory = $cacheConfig['directory'] ?? (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . "/storage/cache/doctrine/{$type}";
                $this->ensureDirectoriesExist([$directory]);
                return new FilesystemAdapter($type, 0, $directory);

            case 'array':
                return new ArrayAdapter();

            case 'none':
            default:
                return null;
        }
    }

    /**
     * Setup database logging
     *
     * @param \Doctrine\ORM\Configuration $config ORM configuration
     * @return void
     */
    private function setupDatabaseLogging(\Doctrine\ORM\Configuration $config): void
    {
        if ($this->logger) {
            $sqlLogger = new class ($this->logger) implements \Doctrine\DBAL\Logging\SQLLogger {
                private Logger $logger;

                public function __construct(Logger $logger)
                {
                    $this->logger = $logger;
                }

                public function startQuery($sql, ?array $params = null, ?array $types = null): void
                {
                    $this->logger->info('SQL Query executed', [
                        'sql' => $sql,
                        'params' => $params,
                        'types' => $types
                    ]);
                }
            };

            $config->setSQLLogger($sqlLogger);
        }
    }

    /**
     * Get the entity manager
     *
     * @return EntityManagerInterface
     * @throws Exception If entity manager is not initialized
     */
    public function getEntityManager(): EntityManagerInterface
    {
        if (!$this->entityManager) {
            throw new Exception('Entity manager not initialized. Call initialize() first.');
        }

        return $this->entityManager;
    }

    /**
     * Get the database connection
     *
     * @return Connection
     * @throws Exception If connection is not established
     */
    public function getConnection(): Connection
    {
        if (!$this->connection) {
            throw new Exception('Database connection not established. Call initialize() first.');
        }

        return $this->connection;
    }

    /**
     * Create migration dependency factory
     *
     * @return DependencyFactory
     * @throws Exception If dependency factory cannot be created
     */
    public function createMigrationDependencyFactory(): DependencyFactory
    {
        if (!$this->entityManager) {
            throw new Exception('Entity manager must be initialized before creating migration dependency factory');
        }

        $migrationConfig = $this->config['migrations'] ?? [];

        // Create migration configuration
        $configuration = new PhpFile((defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/config/migrations.php');

        // Create dependency factory
        return DependencyFactory::fromEntityManager(
            $configuration,
            new ExistingEntityManager($this->entityManager)
        );
    }

    /**
     * Check if database connection is active
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        if (!$this->connection) {
            return false;
        }

        try {
            $this->connection->fetchOne('SELECT 1');
            return true;
        } catch (DBALException $e) {
            $this->log('Database connection check failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Check if we're in development mode
     *
     * @return bool
     */
    private function isDevelopmentMode(): bool
    {
        return ($_ENV['APP_ENV'] ?? 'development') === 'development';
    }

    /**
     * Check if database logging is enabled
     *
     * @return bool
     */
    private function isDatabaseLoggingEnabled(): bool
    {
        return $this->config['logging']['enabled'] ?? false;
    }

    /**
     * Ensure directories exist
     *
     * @param array<string> $directories
     * @return void
     */
    private function ensureDirectoriesExist(array $directories): void
    {
        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
                    throw new Exception("Failed to create directory: {$directory}");
                }
            }
        }
    }

    /**
     * Log a message
     *
     * @param string $message Log message
     * @param string $level Log level
     * @return void
     */
    private function log(string $message, string $level = 'info'): void
    {
        if ($this->logger) {
            $this->logger->log($level, $message);
        }
    }

    /**
     * Close the database connection
     *
     * @return void
     */
    public function close(): void
    {
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }

        if ($this->connection) {
            $this->connection->close();
            $this->connection = null;
        }

        $this->connected = false;
        $this->log('Database connection closed');
    }

    /**
     * Get database configuration
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set logger
     *
     * @param Logger $logger
     * @return void
     */
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }
}
