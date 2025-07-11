<?php

declare(strict_types=1);

/**
 * Modern Cache Manager for Renal Tales Application
 * 
 * Provides a unified interface for cache management with support for
 * file-based cache, APCu, and future cache backends
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

class CacheManager {
    
    private string $cacheDir;
    private string $tempDir;
    private bool $apcuAvailable;
    private bool $opcacheAvailable;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->cacheDir = __DIR__ . '/../storage/cache/';
        $this->tempDir = __DIR__ . '/../storage/temp/';
        $this->apcuAvailable = $this->checkApcuAvailability();
        $this->opcacheAvailable = function_exists('opcache_reset') && function_exists('opcache_get_status');
        
        // Ensure cache directories exist
        $this->ensureDirectoryExists($this->cacheDir);
        $this->ensureDirectoryExists($this->tempDir);
    }
    
    /**
     * Check if APCu is available and enabled
     */
    private function checkApcuAvailability(): bool {
        if (!extension_loaded('apcu')) {
            return false;
        }
        
        // Check for required functions
        $requiredFunctions = ['apcu_enabled', 'apcu_store', 'apcu_fetch', 'apcu_delete', 'apcu_clear_cache'];
        foreach ($requiredFunctions as $function) {
            if (!function_exists($function)) {
                return false;
            }
        }
        
        // Check if APCu is enabled - use call_user_func to avoid static analysis warnings
        try {
            return call_user_func('apcu_enabled');
        } catch (Throwable $e) {
            return false;
        }
    }
    
    /**
     * Store data in cache
     * 
     * @param string $key Cache key
     * @param mixed $data Data to cache
     * @param int $ttl Time to live in seconds (default: 1 hour)
     * @return bool Success status
     */
    public function set(string $key, $data, int $ttl = 3600): bool {
        $success = $this->setFileCache($key, $data, $ttl);
        
        // Also store in APCu if available for faster access
        if ($this->apcuAvailable) {
            try {
                call_user_func('apcu_store', $key, $data, $ttl);
            } catch (Throwable $e) {
                // Log error but don't fail the operation
                error_log("APCu cache store error: " . $e->getMessage());
            }
        }
        
        return $success;
    }
    
    /**
     * Retrieve data from cache
     * 
     * @param string $key Cache key
     * @return mixed|null Cached data or null if not found/expired
     */
    public function get(string $key) {
        // Try APCu first for speed
        if ($this->apcuAvailable) {
            try {
                $success = false;
                $data = call_user_func_array('apcu_fetch', [$key, &$success]);
                if ($success) {
                    return $data;
                }
            } catch (Throwable $e) {
                error_log("APCu cache fetch error: " . $e->getMessage());
            }
        }
        
        // Fallback to file cache
        return $this->getFileCache($key);
    }
    
    /**
     * Delete specific cache entry
     * 
     * @param string $key Cache key
     * @return bool Success status
     */
    public function delete(string $key): bool {
        $fileSuccess = $this->deleteFileCache($key);
        
        // Also delete from APCu if available
        if ($this->apcuAvailable) {
            try {
                call_user_func('apcu_delete', $key);
            } catch (Throwable $e) {
                error_log("APCu cache delete error: " . $e->getMessage());
            }
        }
        
        return $fileSuccess;
    }
    
    /**
     * Clear all cache
     * 
     * @return array Results of cache clearing operations
     */
    public function clearAll(): array {
        $results = [
            'file_cache' => $this->clearFileCache(),
            'temp_files' => $this->clearTempFiles(),
            'apcu_cache' => $this->clearApcuCache(),
            'opcache' => $this->clearOpcache(),
            'sessions' => $this->clearOldSessions()
        ];
        
        return $results;
    }
    
    /**
     * Get cache statistics
     * 
     * @return array Cache statistics
     */
    public function getStats(): array {
        $stats = [
            'file_cache' => $this->getFileCacheStats(),
            'apcu_available' => $this->apcuAvailable,
            'opcache_available' => $this->opcacheAvailable
        ];
        
        if ($this->apcuAvailable) {
            try {
                $stats['apcu_info'] = call_user_func('apcu_cache_info');
            } catch (Throwable $e) {
                $stats['apcu_info'] = ['error' => $e->getMessage()];
            }
        }
        
        if ($this->opcacheAvailable) {
            try {
                $stats['opcache_info'] = opcache_get_status();
            } catch (Throwable $e) {
                $stats['opcache_info'] = ['error' => $e->getMessage()];
            }
        }
        
        return $stats;
    }
    
    /**
     * Store data in file cache
     */
    private function setFileCache(string $key, $data, int $ttl): bool {
        $filename = $this->getCacheFilename($key);
        $cacheData = [
            'data' => $data,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        try {
            $serialized = serialize($cacheData);
            return file_put_contents($filename, $serialized, LOCK_EX) !== false;
        } catch (Throwable $e) {
            error_log("File cache store error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Retrieve data from file cache
     */
    private function getFileCache(string $key) {
        $filename = $this->getCacheFilename($key);
        
        if (!file_exists($filename)) {
            return null;
        }
        
        try {
            $contents = file_get_contents($filename);
            if ($contents === false) {
                return null;
            }
            
            $cacheData = unserialize($contents);
            if (!is_array($cacheData) || !isset($cacheData['expires'], $cacheData['data'])) {
                // Invalid cache file, delete it
                unlink($filename);
                return null;
            }
            
            if (time() > $cacheData['expires']) {
                // Expired, delete and return null
                unlink($filename);
                return null;
            }
            
            return $cacheData['data'];
            
        } catch (Throwable $e) {
            error_log("File cache read error: " . $e->getMessage());
            // Try to delete corrupted file
            if (file_exists($filename)) {
                @unlink($filename);
            }
            return null;
        }
    }
    
    /**
     * Delete specific file cache entry
     */
    private function deleteFileCache(string $key): bool {
        $filename = $this->getCacheFilename($key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true; // Consider non-existent as successfully deleted
    }
    
    /**
     * Clear all file cache
     */
    private function clearFileCache(): array {
        $result = ['count' => 0, 'errors' => []];
        
        if (!is_dir($this->cacheDir)) {
            return $result;
        }
        
        $files = glob($this->cacheDir . '*');
        
        foreach ($files as $file) {
            if (is_file($file) && basename($file) !== '.gitkeep') {
                if (unlink($file)) {
                    $result['count']++;
                } else {
                    $result['errors'][] = basename($file);
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Clear temporary files
     */
    private function clearTempFiles(): array {
        $result = ['count' => 0, 'errors' => []];
        
        if (!is_dir($this->tempDir)) {
            return $result;
        }
        
        $files = glob($this->tempDir . '*');
        
        foreach ($files as $file) {
            if (is_file($file) && basename($file) !== '.gitkeep') {
                if (unlink($file)) {
                    $result['count']++;
                } else {
                    $result['errors'][] = basename($file);
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Clear APCu cache
     */
    private function clearApcuCache(): array {
        $result = ['success' => false, 'message' => ''];
        
        if (!$this->apcuAvailable) {
            $result['message'] = 'APCu not available';
            return $result;
        }
        
        try {
            $result['success'] = call_user_func('apcu_clear_cache');
            $result['message'] = $result['success'] ? 'APCu cache cleared' : 'APCu clear failed';
        } catch (Throwable $e) {
            $result['message'] = 'APCu error: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Clear OPcache
     */
    private function clearOpcache(): array {
        $result = ['success' => false, 'message' => ''];
        
        if (!$this->opcacheAvailable) {
            $result['message'] = 'OPcache not available';
            return $result;
        }
        
        try {
            $result['success'] = opcache_reset();
            $result['message'] = $result['success'] ? 'OPcache cleared' : 'OPcache clear failed';
        } catch (Throwable $e) {
            $result['message'] = 'OPcache error: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Clear old session files (older than 24 hours)
     */
    private function clearOldSessions(): array {
        $result = ['count' => 0, 'errors' => []];
        $sessionPath = __DIR__ . '/../storage/sessions/';
        
        if (!is_dir($sessionPath)) {
            return $result;
        }
        
        $files = glob($sessionPath . 'sess_*');
        $cutoffTime = time() - (24 * 60 * 60); // 24 hours ago
        
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoffTime) {
                if (unlink($file)) {
                    $result['count']++;
                } else {
                    $result['errors'][] = basename($file);
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Get file cache statistics
     */
    private function getFileCacheStats(): array {
        $stats = ['count' => 0, 'total_size' => 0, 'oldest' => null, 'newest' => null];
        
        if (!is_dir($this->cacheDir)) {
            return $stats;
        }
        
        $files = glob($this->cacheDir . '*');
        
        foreach ($files as $file) {
            if (is_file($file) && basename($file) !== '.gitkeep') {
                $stats['count']++;
                $stats['total_size'] += filesize($file);
                
                $mtime = filemtime($file);
                if ($stats['oldest'] === null || $mtime < $stats['oldest']) {
                    $stats['oldest'] = $mtime;
                }
                if ($stats['newest'] === null || $mtime > $stats['newest']) {
                    $stats['newest'] = $mtime;
                }
            }
        }
        
        return $stats;
    }
    
    /**
     * Generate cache filename from key
     */
    private function getCacheFilename(string $key): string {
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
        return $this->cacheDir . 'cache_' . $safeKey . '.dat';
    }
    
    /**
     * Ensure directory exists
     */
    private function ensureDirectoryExists(string $dir): void {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
