<?php

declare(strict_types=1);

namespace RenalTales\Services;

use RenalTales\Database\DatabaseConnection;
use PDO;

/**
 * Translation Cache Service
 * 
 * Handles caching of translations for performance
 */
class TranslationCache
{
    private PDO $db;
    private array $memoryCache = [];
    private int $defaultCacheTime = 3600; // 1 hour

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }

    /**
     * Get cached translation
     */
    public function get(string $languageCode, string $cacheKey): ?array
    {
        // Check memory cache first
        $memoryKey = $languageCode . '_' . $cacheKey;
        if (isset($this->memoryCache[$memoryKey])) {
            return $this->memoryCache[$memoryKey];
        }

        // Check database cache
        $sql = "SELECT cache_data FROM translation_cache 
                WHERE language_code = :lang AND cache_key = :key AND expires_at > NOW()";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'lang' => $languageCode,
            'key' => $cacheKey
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $data = json_decode($result['cache_data'], true);
            $this->memoryCache[$memoryKey] = $data;
            return $data;
        }

        return null;
    }

    /**
     * Set cached translation
     */
    public function set(string $languageCode, string $cacheKey, array $data, int $cacheTime = null): bool
    {
        $cacheTime = $cacheTime ?? $this->defaultCacheTime;
        $expiresAt = date('Y-m-d H:i:s', time() + $cacheTime);
        
        // Store in memory cache
        $memoryKey = $languageCode . '_' . $cacheKey;
        $this->memoryCache[$memoryKey] = $data;

        // Store in database cache
        $sql = "INSERT INTO translation_cache (language_code, cache_key, cache_data, expires_at, created_at, updated_at) 
                VALUES (:lang, :key, :data, :expires, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                cache_data = :data, expires_at = :expires, updated_at = NOW()";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'lang' => $languageCode,
            'key' => $cacheKey,
            'data' => json_encode($data),
            'expires' => $expiresAt
        ]);
    }

    /**
     * Delete cached translation
     */
    public function delete(string $languageCode, string $cacheKey): bool
    {
        // Remove from memory cache
        $memoryKey = $languageCode . '_' . $cacheKey;
        unset($this->memoryCache[$memoryKey]);

        // Remove from database cache
        $sql = "DELETE FROM translation_cache 
                WHERE language_code = :lang AND cache_key = :key";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'lang' => $languageCode,
            'key' => $cacheKey
        ]);
    }

    /**
     * Clear all cache for a language
     */
    public function clearLanguageCache(string $languageCode): bool
    {
        // Clear memory cache
        foreach ($this->memoryCache as $key => $value) {
            if (strpos($key, $languageCode . '_') === 0) {
                unset($this->memoryCache[$key]);
            }
        }

        // Clear database cache
        $sql = "DELETE FROM translation_cache WHERE language_code = :lang";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['lang' => $languageCode]);
    }

    /**
     * Clear all cache
     */
    public function clearAllCache(): bool
    {
        // Clear memory cache
        $this->memoryCache = [];

        // Clear database cache
        $sql = "DELETE FROM translation_cache";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }

    /**
     * Clean expired cache entries
     */
    public function cleanExpiredCache(): bool
    {
        $sql = "DELETE FROM translation_cache WHERE expires_at < NOW()";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }

    /**
     * Get cache statistics
     */
    public function getCacheStatistics(): array
    {
        $sql = "SELECT 
                    language_code,
                    COUNT(*) as cache_count,
                    SUM(LENGTH(cache_data)) as cache_size,
                    MIN(created_at) as oldest_entry,
                    MAX(created_at) as newest_entry
                FROM translation_cache 
                WHERE expires_at > NOW()
                GROUP BY language_code
                ORDER BY language_code";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if cache exists
     */
    public function exists(string $languageCode, string $cacheKey): bool
    {
        $memoryKey = $languageCode . '_' . $cacheKey;
        if (isset($this->memoryCache[$memoryKey])) {
            return true;
        }

        $sql = "SELECT COUNT(*) FROM translation_cache 
                WHERE language_code = :lang AND cache_key = :key AND expires_at > NOW()";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'lang' => $languageCode,
            'key' => $cacheKey
        ]);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Warm up cache for a language
     */
    public function warmUpLanguageCache(string $languageCode): bool
    {
        // This would typically load all translations for a language into cache
        // Implementation depends on your specific needs
        
        $sql = "SELECT key_name, translation_text, group_name FROM translations 
                WHERE language_code = :lang AND is_active = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['lang' => $languageCode]);
        
        $translations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group translations by group_name
        $groupedTranslations = [];
        foreach ($translations as $translation) {
            $groupedTranslations[$translation['group_name']][$translation['key_name']] = $translation['translation_text'];
        }
        
        // Cache each group
        foreach ($groupedTranslations as $group => $data) {
            $this->set($languageCode, 'group_' . $group, $data);
        }
        
        return true;
    }

    /**
     * Get memory cache size
     */
    public function getMemoryCacheSize(): int
    {
        return count($this->memoryCache);
    }

    /**
     * Get memory cache data
     */
    public function getMemoryCache(): array
    {
        return $this->memoryCache;
    }

    /**
     * Set default cache time
     */
    public function setDefaultCacheTime(int $seconds): void
    {
        $this->defaultCacheTime = $seconds;
    }

    /**
     * Get default cache time
     */
    public function getDefaultCacheTime(): int
    {
        return $this->defaultCacheTime;
    }
}
