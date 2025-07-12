<?php

declare(strict_types=1);

namespace RenalTales\Core;

/**
 * ConfigManager - Configuration management class
 * 
 * Handles loading and accessing configuration values
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */
class ConfigManager
{
    private static ?self $instance = null;
    private array $config = [];
    private bool $loaded = false;

    private function __construct()
    {
        // Private constructor for singleton pattern
    }

    /**
     * Get the singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load configuration from files
     */
    public function loadConfig(string $configPath = null): void
    {
        if ($this->loaded) {
            return; // Already loaded
        }

        $configPath = $configPath ?? dirname(__DIR__, 2) . '/config';

        // Load all configuration files
        $configFiles = [
            'app' => $configPath . '/app.php',
            'database' => $configPath . '/database.php',
            'security' => $configPath . '/security.php',
            'multilingual' => $configPath . '/multilingual.php',
        ];

        foreach ($configFiles as $key => $file) {
            if (file_exists($file)) {
                $this->config[$key] = require $file;
            }
        }

        $this->loaded = true;
    }

    /**
     * Get configuration value using dot notation
     * 
     * @param string $key Configuration key in dot notation (e.g., 'app.name')
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if (!$this->loaded) {
            $this->loadConfig();
        }

        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Set configuration value using dot notation
     * 
     * @param string $key Configuration key in dot notation
     * @param mixed $value Value to set
     */
    public function set(string $key, $value): void
    {
        if (!$this->loaded) {
            $this->loadConfig();
        }

        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $segment) {
            if (!is_array($config)) {
                $config = [];
            }
            $config = &$config[$segment];
        }

        $config = $value;
    }

    /**
     * Check if configuration key exists
     */
    public function has(string $key): bool
    {
        if (!$this->loaded) {
            $this->loadConfig();
        }

        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all configuration
     */
    public function all(): array
    {
        if (!$this->loaded) {
            $this->loadConfig();
        }

        return $this->config;
    }

    /**
     * Environment helper function
     */
    public static function env(string $key, $default = null)
    {
        $value = getenv($key);
        
        // Also check $_ENV array
        if ($value === false && isset($_ENV[$key])) {
            $value = $_ENV[$key];
        }
        
        if ($value === false) {
            return $default;
        }
        
        // Convert boolean strings
        if (in_array(strtolower($value), ['true', 'false'])) {
            return strtolower($value) === 'true';
        }
        
        // Convert null string
        if (strtolower($value) === 'null') {
            return null;
        }
        
        return $value;
    }
}

/**
 * Global helper function for accessing configuration
 */
if (!function_exists('config')) {
    function config(string $key = null, $default = null)
    {
        $configManager = \RenalTales\Core\ConfigManager::getInstance();
        
        if ($key === null) {
            return $configManager->all();
        }
        
        return $configManager->get($key, $default);
    }
}

/**
 * Global helper function for environment variables
 */
if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        return \RenalTales\Core\ConfigManager::env($key, $default);
    }
}
