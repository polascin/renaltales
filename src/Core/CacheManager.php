<?php

declare(strict_types=1);

namespace RenalTales\Core;

use Predis\Client;
use Predis\ClientException;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Exception\InvalidArgumentException;
use Symfony\Component\Cache\CacheItem;
use Psr\Cache\CacheItemPoolInterface;
use Exception;

/**
 * Cache Manager
 *
 * Manages caching operations using Redis as the primary cache store
 * with fallback to file-based caching. Provides unified cache interface
 * for the application.
 *
 * @package RenalTales\Core
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */
class CacheManager
{
    /**
     * @var array<string, mixed> Cache configuration
     */
    private array $config;

    /**
     * @var CacheItemPoolInterface Primary cache adapter
     */
    private CacheItemPoolInterface $cache;

    /**
     * @var CacheItemPoolInterface|null Fallback cache adapter
     */
    private ?CacheItemPoolInterface $fallbackCache = null;

    /**
     * @var Client|null Redis client instance
     */
    private ?Client $redisClient = null;

    /**
     * @var Logger|null Application logger
     */
    private ?Logger $logger = null;

    /**
     * @var bool Whether Redis is available
     */
    private bool $redisAvailable = false;

    /**
     * Constructor
     *
     * @param array<string, mixed> $config Cache configuration
     * @param Logger|null $logger Application logger
     */
    public function __construct(array $config, ?Logger $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->initialize();
    }

    /**
     * Initialize the cache system
     *
     * @return void
     */
    private function initialize(): void
    {
        try {
            $this->setupRedisCache();
            $this->setupFallbackCache();
            $this->log('Cache system initialized successfully');
        } catch (Exception $e) {
            $this->log('Cache initialization failed: ' . $e->getMessage(), 'error');
            $this->setupFallbackCache();
        }
    }

    /**
     * Setup Redis cache
     *
     * @return void
     * @throws Exception If Redis setup fails
     */
    private function setupRedisCache(): void
    {
        $connection = $this->config['connections']['default'] ?? [];

        try {
            $this->redisClient = new Client([
                'scheme' => 'tcp',
                'host' => $connection['host'] ?? 'localhost',
                'port' => $connection['port'] ?? 6379,
                'password' => $connection['password'] ?? null,
                'database' => $connection['database'] ?? 0,
            ]);

            // Test connection
            $this->redisClient->ping();

            $this->cache = new RedisAdapter($this->redisClient, $this->config['prefix'] ?? 'renaltales');
            $this->redisAvailable = true;
            $this->log('Redis cache initialized successfully');
        } catch (ClientException $e) {
            $this->log('Redis connection failed: ' . $e->getMessage(), 'warning');
            $this->redisAvailable = false;
            throw new Exception('Redis setup failed: ' . $e->getMessage());
        }
    }

    /**
     * Setup fallback cache
     *
     * @return void
     */
    private function setupFallbackCache(): void
    {
        if (!$this->redisAvailable) {
            $this->cache = new FilesystemAdapter(
                'renaltales',
                0,
                $this->config['stores']['file']['path'] ?? APP_ROOT . '/storage/cache/data'
            );
            $this->log('Using file-based cache as primary cache');
        } else {
            $this->fallbackCache = new FilesystemAdapter(
                'renaltales_fallback',
                0,
                $this->config['stores']['file']['path'] ?? APP_ROOT . '/storage/cache/data'
            );
            $this->log('File-based fallback cache initialized');
        }
    }

    /**
     * Get a cache item
     *
     * @param string $key Cache key
     * @return mixed|null Cached value or null if not found
     */
    public function get(string $key)
    {
        try {
            $item = $this->cache->getItem($this->normalizeKey($key));
            return $item->isHit() ? $item->get() : null;
        } catch (InvalidArgumentException $e) {
            $this->log('Cache get failed for key: ' . $key . ' - ' . $e->getMessage(), 'error');
            return $this->getFallback($key);
        }
    }

    /**
     * Set a cache item
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int|null $ttl Time to live in seconds
     * @return bool True if successful, false otherwise
     */
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        try {
            $item = $this->cache->getItem($this->normalizeKey($key));
            $item->set($value);

            if ($ttl !== null) {
                $item->expiresAfter($ttl);
            } else {
                $item->expiresAfter($this->getDefaultTtl($key));
            }

            $result = $this->cache->save($item);

            // Also save to fallback cache if available
            if ($this->fallbackCache && $this->redisAvailable) {
                $this->setFallback($key, $value, $ttl);
            }

            return $result;
        } catch (InvalidArgumentException $e) {
            $this->log('Cache set failed for key: ' . $key . ' - ' . $e->getMessage(), 'error');
            return $this->setFallback($key, $value, $ttl);
        }
    }

    /**
     * Check if cache item exists
     *
     * @param string $key Cache key
     * @return bool True if exists, false otherwise
     */
    public function has(string $key): bool
    {
        try {
            return $this->cache->hasItem($this->normalizeKey($key));
        } catch (InvalidArgumentException $e) {
            $this->log('Cache has failed for key: ' . $key . ' - ' . $e->getMessage(), 'error');
            return $this->hasFallback($key);
        }
    }

    /**
     * Delete a cache item
     *
     * @param string $key Cache key
     * @return bool True if successful, false otherwise
     */
    public function delete(string $key): bool
    {
        try {
            $result = $this->cache->deleteItem($this->normalizeKey($key));

            // Also delete from fallback cache if available
            if ($this->fallbackCache && $this->redisAvailable) {
                $this->deleteFallback($key);
            }

            return $result;
        } catch (InvalidArgumentException $e) {
            $this->log('Cache delete failed for key: ' . $key . ' - ' . $e->getMessage(), 'error');
            return $this->deleteFallback($key);
        }
    }

    /**
     * Clear all cache items
     *
     * @return bool True if successful, false otherwise
     */
    public function clear(): bool
    {
        try {
            $result = $this->cache->clear();

            // Also clear fallback cache if available
            if ($this->fallbackCache && $this->redisAvailable) {
                $this->fallbackCache->clear();
            }

            $this->log('Cache cleared successfully');
            return $result;
        } catch (Exception $e) {
            $this->log('Cache clear failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Get multiple cache items
     *
     * @param array<string> $keys Cache keys
     * @return array<string, mixed> Array of cached values
     */
    public function getMultiple(array $keys): array
    {
        $result = [];
        $normalizedKeys = array_map([$this, 'normalizeKey'], $keys);

        try {
            $items = $this->cache->getItems($normalizedKeys);

            foreach ($items as $key => $item) {
                $originalKey = array_search($key, $normalizedKeys);
                if ($originalKey !== false && $item->isHit()) {
                    $result[$keys[$originalKey]] = $item->get();
                }
            }
        } catch (InvalidArgumentException $e) {
            $this->log('Cache getMultiple failed: ' . $e->getMessage(), 'error');
        }

        return $result;
    }

    /**
     * Set multiple cache items
     *
     * @param array<string, mixed> $values Key-value pairs to cache
     * @param int|null $ttl Time to live in seconds
     * @return bool True if successful, false otherwise
     */
    public function setMultiple(array $values, ?int $ttl = null): bool
    {
        $success = true;

        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Delete multiple cache items
     *
     * @param array<string> $keys Cache keys to delete
     * @return bool True if successful, false otherwise
     */
    public function deleteMultiple(array $keys): bool
    {
        $normalizedKeys = array_map([$this, 'normalizeKey'], $keys);

        try {
            $result = $this->cache->deleteItems($normalizedKeys);

            // Also delete from fallback cache if available
            if ($this->fallbackCache && $this->redisAvailable) {
                $this->fallbackCache->deleteItems($normalizedKeys);
            }

            return $result;
        } catch (InvalidArgumentException $e) {
            $this->log('Cache deleteMultiple failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Remember (get or set) a cache item
     *
     * @param string $key Cache key
     * @param callable $callback Callback to generate value if not cached
     * @param int|null $ttl Time to live in seconds
     * @return mixed Cached or generated value
     */
    public function remember(string $key, callable $callback, ?int $ttl = null)
    {
        $value = $this->get($key);

        if ($value === null) {
            $value = $callback();
            $this->set($key, $value, $ttl);
        }

        return $value;
    }

    /**
     * Increment a numeric cache value
     *
     * @param string $key Cache key
     * @param int $value Amount to increment
     * @return int|bool New value or false if failed
     */
    public function increment(string $key, int $value = 1)
    {
        if ($this->redisAvailable && $this->redisClient) {
            try {
                return $this->redisClient->incrby($this->normalizeKey($key), $value);
            } catch (ClientException $e) {
                $this->log('Redis increment failed: ' . $e->getMessage(), 'error');
            }
        }

        // Fallback to get/set
        $current = $this->get($key) ?? 0;
        $new = $current + $value;
        $this->set($key, $new);

        return $new;
    }

    /**
     * Decrement a numeric cache value
     *
     * @param string $key Cache key
     * @param int $value Amount to decrement
     * @return int|bool New value or false if failed
     */
    public function decrement(string $key, int $value = 1)
    {
        if ($this->redisAvailable && $this->redisClient) {
            try {
                return $this->redisClient->decrby($this->normalizeKey($key), $value);
            } catch (ClientException $e) {
                $this->log('Redis decrement failed: ' . $e->getMessage(), 'error');
            }
        }

        // Fallback to get/set
        $current = $this->get($key) ?? 0;
        $new = $current - $value;
        $this->set($key, $new);

        return $new;
    }

    /**
     * Get cache statistics
     *
     * @return array<string, mixed> Cache statistics
     */
    public function getStats(): array
    {
        $stats = [
            'driver' => $this->redisAvailable ? 'redis' : 'file',
            'redis_available' => $this->redisAvailable,
            'fallback_available' => $this->fallbackCache !== null,
        ];

        if ($this->redisAvailable && $this->redisClient) {
            try {
                $info = $this->redisClient->info();
                $stats['redis_info'] = $info;
            } catch (ClientException $e) {
                $this->log('Failed to get Redis info: ' . $e->getMessage(), 'error');
            }
        }

        return $stats;
    }

    /**
     * Get from fallback cache
     *
     * @param string $key Cache key
     * @return mixed|null Cached value or null if not found
     */
    private function getFallback(string $key)
    {
        if (!$this->fallbackCache) {
            return null;
        }

        try {
            $item = $this->fallbackCache->getItem($this->normalizeKey($key));
            return $item->isHit() ? $item->get() : null;
        } catch (InvalidArgumentException $e) {
            $this->log('Fallback cache get failed for key: ' . $key . ' - ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Set to fallback cache
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int|null $ttl Time to live in seconds
     * @return bool True if successful, false otherwise
     */
    private function setFallback(string $key, $value, ?int $ttl = null): bool
    {
        if (!$this->fallbackCache) {
            return false;
        }

        try {
            $item = $this->fallbackCache->getItem($this->normalizeKey($key));
            $item->set($value);

            if ($ttl !== null) {
                $item->expiresAfter($ttl);
            } else {
                $item->expiresAfter($this->getDefaultTtl($key));
            }

            return $this->fallbackCache->save($item);
        } catch (InvalidArgumentException $e) {
            $this->log('Fallback cache set failed for key: ' . $key . ' - ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Check if fallback cache has item
     *
     * @param string $key Cache key
     * @return bool True if exists, false otherwise
     */
    private function hasFallback(string $key): bool
    {
        if (!$this->fallbackCache) {
            return false;
        }

        try {
            return $this->fallbackCache->hasItem($this->normalizeKey($key));
        } catch (InvalidArgumentException $e) {
            $this->log('Fallback cache has failed for key: ' . $key . ' - ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Delete from fallback cache
     *
     * @param string $key Cache key
     * @return bool True if successful, false otherwise
     */
    private function deleteFallback(string $key): bool
    {
        if (!$this->fallbackCache) {
            return false;
        }

        try {
            return $this->fallbackCache->deleteItem($this->normalizeKey($key));
        } catch (InvalidArgumentException $e) {
            $this->log('Fallback cache delete failed for key: ' . $key . ' - ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Normalize cache key
     *
     * @param string $key Original key
     * @return string Normalized key
     */
    private function normalizeKey(string $key): string
    {
        // Remove or replace invalid characters
        $key = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $key);

        // Ensure key is not too long
        if (strlen($key) > 250) {
            $key = substr($key, 0, 200) . '_' . md5($key);
        }

        return $key;
    }

    /**
     * Get default TTL for a key
     *
     * @param string $key Cache key
     * @return int TTL in seconds
     */
    private function getDefaultTtl(string $key): int
    {
        $ttlConfig = $this->config['ttl'] ?? [];

        // Match key patterns to TTL configuration
        if (strpos($key, 'language') !== false) {
            return $ttlConfig['languages'] ?? 86400;
        } elseif (strpos($key, 'translation') !== false) {
            return $ttlConfig['translations'] ?? 86400;
        } elseif (strpos($key, 'config') !== false) {
            return $ttlConfig['config'] ?? 3600;
        } elseif (strpos($key, 'query') !== false) {
            return $ttlConfig['queries'] ?? 1800;
        }

        return $ttlConfig['default'] ?? 3600;
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
     * Get Redis client
     *
     * @return Client|null Redis client or null if not available
     */
    public function getRedisClient(): ?Client
    {
        return $this->redisClient;
    }

    /**
     * Check if Redis is available
     *
     * @return bool True if Redis is available, false otherwise
     */
    public function isRedisAvailable(): bool
    {
        return $this->redisAvailable;
    }

    /**
     * Close connections
     *
     * @return void
     */
    public function close(): void
    {
        if ($this->redisClient) {
            $this->redisClient->disconnect();
            $this->redisClient = null;
        }

        $this->redisAvailable = false;
        $this->log('Cache connections closed');
    }
}
