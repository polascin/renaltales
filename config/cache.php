<?php

declare(strict_types=1);

/**
 * Cache Configuration
 *
 * Configuration for caching systems including Redis and file-based caching.
 * This configuration supports multiple cache stores with different drivers.
 *
 * @package RenalTales\Config
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */

return [
    'default' => $_ENV['CACHE_DRIVER'] ?? 'redis',

    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'prefix' => $_ENV['CACHE_PREFIX'] ?? 'renaltales',
        ],

        'file' => [
            'driver' => 'file',
            'path' => APP_ROOT . '/storage/cache/data',
        ],

        'array' => [
            'driver' => 'array',
        ],
    ],

    'connections' => [
        'default' => [
            'host' => $_ENV['REDIS_HOST'] ?? 'localhost',
            'port' => (int)($_ENV['REDIS_PORT'] ?? 6379),
            'password' => $_ENV['REDIS_PASSWORD'] ?? null,
            'database' => (int)($_ENV['REDIS_DB'] ?? 0),
        ],

        'cache' => [
            'host' => $_ENV['REDIS_HOST'] ?? 'localhost',
            'port' => (int)($_ENV['REDIS_PORT'] ?? 6379),
            'password' => $_ENV['REDIS_PASSWORD'] ?? null,
            'database' => (int)($_ENV['REDIS_CACHE_DB'] ?? 1),
        ],

        'sessions' => [
            'host' => $_ENV['REDIS_HOST'] ?? 'localhost',
            'port' => (int)($_ENV['REDIS_PORT'] ?? 6379),
            'password' => $_ENV['REDIS_PASSWORD'] ?? null,
            'database' => (int)($_ENV['REDIS_SESSION_DB'] ?? 2),
        ],
    ],

    'prefix' => $_ENV['CACHE_PREFIX'] ?? 'renaltales',

    'ttl' => [
        'default' => 3600, // 1 hour
        'languages' => 86400, // 24 hours
        'translations' => 86400, // 24 hours
        'config' => 3600, // 1 hour
        'queries' => 1800, // 30 minutes
    ],
];
