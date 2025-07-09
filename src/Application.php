<?php

declare(strict_types=1);

namespace RenalTales;

use Monolog\Logger;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Main Application class for RenalTales
 * 
 * This class handles the core application initialization and configuration
 */
class Application
{
    private array $config;
    private ?Logger $logger;
    private static ?Application $instance = null;

    /**
     * Private constructor for singleton pattern
     */
    private function __construct()
    {
        $this->config = [];
        $this->logger = null;
    }

    /**
     * Get the singleton instance of the Application
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the application with configuration
     */
    public function initialize(array $config): void
    {
        $this->config = $config;
        
        // Initialize logging if available
        if (isset($GLOBALS['logger']) && $GLOBALS['logger'] instanceof Logger) {
            $this->logger = $GLOBALS['logger'];
        }
        
        $this->logInfo('Application initialized', [
            'name' => $this->config['app']['name'] ?? 'Unknown',
            'version' => $this->config['app']['version'] ?? '1.0.0',
            'environment' => $this->config['app']['env'] ?? 'development'
        ]);
    }

    /**
     * Get configuration value by key
     */
    public function getConfig(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $keyPart) {
            if (!isset($value[$keyPart])) {
                return $default;
            }
            $value = $value[$keyPart];
        }
        
        return $value;
    }

    /**
     * Get the application name
     */
    public function getName(): string
    {
        return $this->getConfig('app.name', 'RenalTales');
    }

    /**
     * Get the application version
     */
    public function getVersion(): string
    {
        return $this->getConfig('app.version', '1.0.0');
    }

    /**
     * Get the application environment
     */
    public function getEnvironment(): string
    {
        return $this->getConfig('app.env', 'development');
    }

    /**
     * Check if application is in debug mode
     */
    public function isDebug(): bool
    {
        return (bool) $this->getConfig('app.debug', false);
    }

    /**
     * Get database configuration
     */
    public function getDatabaseConfig(): array
    {
        return $this->getConfig('database', []);
    }

    /**
     * Log an info message
     */
    public function logInfo(string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->info($message, $context);
        }
    }

    /**
     * Log an error message
     */
    public function logError(string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->error($message, $context);
        }
    }

    /**
     * Log a debug message
     */
    public function logDebug(string $message, array $context = []): void
    {
        if ($this->logger && $this->isDebug()) {
            $this->logger->debug($message, $context);
        }
    }

    /**
     * Get the application root directory
     */
    public function getRootPath(): string
    {
        return APP_ROOT ?? __DIR__ . '/..';
    }

    /**
     * Get the storage path
     */
    public function getStoragePath(): string
    {
        return $this->getConfig('storage.path', $this->getRootPath() . '/storage');
    }

    /**
     * Get the cache path
     */
    public function getCachePath(): string
    {
        return $this->getConfig('cache.path', $this->getRootPath() . '/storage/cache');
    }

    /**
     * Get the logs path
     */
    public function getLogsPath(): string
    {
        return dirname($this->getConfig('LOG_PATH', $this->getRootPath() . '/storage/logs/app.log'));
    }
}
