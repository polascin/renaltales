<?php

/**
 * RateLimitManager - Rate limiting for API endpoints
 * 
 * Provides rate limiting functionality to prevent abuse and DoS attacks
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

class RateLimitManager {
    
    private $config;
    private $storage;
    private $storageType;
    
    /**
     * Constructor
     * 
     * @param array $config Rate limiting configuration
     */
    public function __construct($config = []) {
        $this->config = array_merge([
            'storage' => 'file', // file, redis, database
            'default_limit' => 100, // requests per window
            'default_window' => 3600, // 1 hour in seconds
            'burst_limit' => 10, // burst requests per minute
            'burst_window' => 60, // 1 minute
            'blocked_duration' => 300, // 5 minutes block
            'storage_path' => sys_get_temp_dir() . '/rate_limits',
            'endpoints' => [
                'api/stories' => ['limit' => 60, 'window' => 3600],
                'api/upload' => ['limit' => 20, 'window' => 3600],
                'api/comments' => ['limit' => 30, 'window' => 3600],
                'login' => ['limit' => 5, 'window' => 900], // 5 attempts per 15 minutes
                'register' => ['limit' => 3, 'window' => 3600],
                'password-reset' => ['limit' => 3, 'window' => 3600]
            ]
        ], $config);
        
        $this->storageType = $this->config['storage'];
        $this->initializeStorage();
    }
    
    /**
     * Initialize storage backend
     */
    private function initializeStorage() {
        switch ($this->storageType) {
            case 'file':
                $this->storage = new FileRateLimitStorage($this->config['storage_path']);
                break;
            case 'redis':
                $this->storage = new RedisRateLimitStorage($this->config);
                break;
            case 'database':
                $this->storage = new DatabaseRateLimitStorage($this->config);
                break;
            default:
                throw new Exception('Invalid storage type: ' . $this->storageType);
        }
    }
    
    /**
     * Check if request is within rate limit
     * 
     * @param string $identifier Client identifier (IP, user ID, etc.)
     * @param string $endpoint Endpoint being accessed
     * @return array Rate limit result
     */
    public function checkRateLimit($identifier, $endpoint = 'default') {
        // Get rate limit configuration for endpoint
        $limits = $this->getEndpointLimits($endpoint);
        
        // Check if client is blocked
        if ($this->isBlocked($identifier)) {
            return [
                'allowed' => false,
                'reason' => 'blocked',
                'retry_after' => $this->getBlockedRetryAfter($identifier),
                'limit' => $limits['limit'],
                'remaining' => 0,
                'reset' => time() + $this->config['blocked_duration']
            ];
        }
        
        // Check burst limit first
        $burstResult = $this->checkBurstLimit($identifier);
        if (!$burstResult['allowed']) {
            return $burstResult;
        }
        
        // Check main rate limit
        $mainResult = $this->checkMainLimit($identifier, $endpoint, $limits);
        
        // If limit exceeded, potentially block the client
        if (!$mainResult['allowed']) {
            $this->handleLimitExceeded($identifier, $endpoint);
        }
        
        return $mainResult;
    }
    
    /**
     * Get rate limit configuration for endpoint
     * 
     * @param string $endpoint
     * @return array
     */
    private function getEndpointLimits($endpoint) {
        if (isset($this->config['endpoints'][$endpoint])) {
            return array_merge([
                'limit' => $this->config['default_limit'],
                'window' => $this->config['default_window']
            ], $this->config['endpoints'][$endpoint]);
        }
        
        return [
            'limit' => $this->config['default_limit'],
            'window' => $this->config['default_window']
        ];
    }
    
    /**
     * Check if client is blocked
     * 
     * @param string $identifier
     * @return bool
     */
    private function isBlocked($identifier) {
        $blockKey = "block:{$identifier}";
        $blockData = $this->storage->get($blockKey);
        
        if ($blockData && $blockData['expires'] > time()) {
            return true;
        }
        
        // Clean up expired block
        if ($blockData) {
            $this->storage->delete($blockKey);
        }
        
        return false;
    }
    
    /**
     * Get retry after time for blocked client
     * 
     * @param string $identifier
     * @return int
     */
    private function getBlockedRetryAfter($identifier) {
        $blockKey = "block:{$identifier}";
        $blockData = $this->storage->get($blockKey);
        
        if ($blockData && $blockData['expires'] > time()) {
            return $blockData['expires'] - time();
        }
        
        return 0;
    }
    
    /**
     * Check burst limit
     * 
     * @param string $identifier
     * @return array
     */
    private function checkBurstLimit($identifier) {
        $burstKey = "burst:{$identifier}";
        $currentTime = time();
        $windowStart = $currentTime - $this->config['burst_window'];
        
        // Get current burst data
        $burstData = $this->storage->get($burstKey);
        
        if (!$burstData) {
            $burstData = [
                'count' => 0,
                'window_start' => $currentTime,
                'requests' => []
            ];
        }
        
        // Clean old requests
        $burstData['requests'] = array_filter($burstData['requests'], function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });
        
        $burstData['count'] = count($burstData['requests']);
        
        // Check if burst limit exceeded
        if ($burstData['count'] >= $this->config['burst_limit']) {
            return [
                'allowed' => false,
                'reason' => 'burst_limit_exceeded',
                'retry_after' => $this->config['burst_window'],
                'limit' => $this->config['burst_limit'],
                'remaining' => 0,
                'reset' => $windowStart + $this->config['burst_window']
            ];
        }
        
        // Add current request
        $burstData['requests'][] = $currentTime;
        $burstData['count']++;
        
        // Store updated burst data
        $this->storage->set($burstKey, $burstData, $this->config['burst_window']);
        
        return [
            'allowed' => true,
            'limit' => $this->config['burst_limit'],
            'remaining' => $this->config['burst_limit'] - $burstData['count'],
            'reset' => $windowStart + $this->config['burst_window']
        ];
    }
    
    /**
     * Check main rate limit
     * 
     * @param string $identifier
     * @param string $endpoint
     * @param array $limits
     * @return array
     */
    private function checkMainLimit($identifier, $endpoint, $limits) {
        $limitKey = "limit:{$identifier}:{$endpoint}";
        $currentTime = time();
        $windowStart = $currentTime - $limits['window'];
        
        // Get current limit data
        $limitData = $this->storage->get($limitKey);
        
        if (!$limitData) {
            $limitData = [
                'count' => 0,
                'window_start' => $currentTime,
                'requests' => []
            ];
        }
        
        // Clean old requests
        $limitData['requests'] = array_filter($limitData['requests'], function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });
        
        $limitData['count'] = count($limitData['requests']);
        
        // Check if limit exceeded
        if ($limitData['count'] >= $limits['limit']) {
            return [
                'allowed' => false,
                'reason' => 'rate_limit_exceeded',
                'retry_after' => $limits['window'],
                'limit' => $limits['limit'],
                'remaining' => 0,
                'reset' => $windowStart + $limits['window']
            ];
        }
        
        // Add current request
        $limitData['requests'][] = $currentTime;
        $limitData['count']++;
        
        // Store updated limit data
        $this->storage->set($limitKey, $limitData, $limits['window']);
        
        return [
            'allowed' => true,
            'limit' => $limits['limit'],
            'remaining' => $limits['limit'] - $limitData['count'],
            'reset' => $windowStart + $limits['window']
        ];
    }
    
    /**
     * Handle limit exceeded
     * 
     * @param string $identifier
     * @param string $endpoint
     */
    private function handleLimitExceeded($identifier, $endpoint) {
        $violationKey = "violations:{$identifier}";
        $violationData = $this->storage->get($violationKey);
        
        if (!$violationData) {
            $violationData = [
                'count' => 0,
                'last_violation' => time(),
                'endpoints' => []
            ];
        }
        
        $violationData['count']++;
        $violationData['last_violation'] = time();
        $violationData['endpoints'][$endpoint] = ($violationData['endpoints'][$endpoint] ?? 0) + 1;
        
        // Store violation data
        $this->storage->set($violationKey, $violationData, 86400); // 24 hours
        
        // Block client if too many violations
        if ($violationData['count'] >= 5) {
            $this->blockClient($identifier);
        }
        
        // Log security event
        $this->logRateLimitViolation($identifier, $endpoint, $violationData);
    }
    
    /**
     * Block client
     * 
     * @param string $identifier
     */
    private function blockClient($identifier) {
        $blockKey = "block:{$identifier}";
        $blockData = [
            'blocked_at' => time(),
            'expires' => time() + $this->config['blocked_duration'],
            'reason' => 'rate_limit_violations'
        ];
        
        $this->storage->set($blockKey, $blockData, $this->config['blocked_duration']);
    }
    
    /**
     * Log rate limit violation
     * 
     * @param string $identifier
     * @param string $endpoint
     * @param array $violationData
     */
    private function logRateLimitViolation($identifier, $endpoint, $violationData) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => 'rate_limit_violation',
            'identifier' => $identifier,
            'endpoint' => $endpoint,
            'violation_count' => $violationData['count'],
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'request_uri' => substr($_SERVER['REQUEST_URI'] ?? '', 0, 255)
        ];
        
        $logDir = APP_DIR . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/rate_limit_violations.log';
        $logData = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
        
        file_put_contents($logFile, $logData, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get client identifier
     * 
     * @return string
     */
    public function getClientIdentifier() {
        // Use user ID if available, otherwise use IP
        if (isset($_SESSION['user_id'])) {
            return 'user:' . $_SESSION['user_id'];
        }
        
        return 'ip:' . $this->getClientIP();
    }
    
    /**
     * Get client IP address
     * 
     * @return string
     */
    private function getClientIP() {
        $ipHeaders = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Set rate limit headers
     * 
     * @param array $limitResult
     */
    public function setRateLimitHeaders($limitResult) {
        if (!headers_sent()) {
            header('X-RateLimit-Limit: ' . $limitResult['limit']);
            header('X-RateLimit-Remaining: ' . $limitResult['remaining']);
            header('X-RateLimit-Reset: ' . $limitResult['reset']);
            
            if (!$limitResult['allowed']) {
                header('Retry-After: ' . $limitResult['retry_after']);
                http_response_code(429); // Too Many Requests
            }
        }
    }
}

/**
 * File-based rate limit storage
 */
class FileRateLimitStorage {
    private $storageDir;
    
    public function __construct($storageDir) {
        $this->storageDir = $storageDir;
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }
    
    public function get($key) {
        $filename = $this->storageDir . '/' . md5($key) . '.json';
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $data = json_decode(file_get_contents($filename), true);
        
        // Check if expired
        if (isset($data['expires']) && $data['expires'] < time()) {
            unlink($filename);
            return null;
        }
        
        return $data;
    }
    
    public function set($key, $data, $ttl = 3600) {
        $filename = $this->storageDir . '/' . md5($key) . '.json';
        $data['expires'] = time() + $ttl;
        
        file_put_contents($filename, json_encode($data), LOCK_EX);
    }
    
    public function delete($key) {
        $filename = $this->storageDir . '/' . md5($key) . '.json';
        if (file_exists($filename)) {
            unlink($filename);
        }
    }
}

/**
 * Redis-based rate limit storage (placeholder)
 */
class RedisRateLimitStorage {
    private $redis;
    
    public function __construct($config) {
        // Initialize Redis connection
        // $this->redis = new Redis();
        // $this->redis->connect($config['redis_host'], $config['redis_port']);
        throw new Exception('Redis storage not implemented yet');
    }
    
    public function get($key) {
        // Implement Redis get
    }
    
    public function set($key, $data, $ttl = 3600) {
        // Implement Redis set
    }
    
    public function delete($key) {
        // Implement Redis delete
    }
}

/**
 * Database-based rate limit storage (placeholder)
 */
class DatabaseRateLimitStorage {
    private $db;
    
    public function __construct($config) {
        // Initialize database connection
        throw new Exception('Database storage not implemented yet');
    }
    
    public function get($key) {
        // Implement database get
    }
    
    public function set($key, $data, $ttl = 3600) {
        // Implement database set
    }
    
    public function delete($key) {
        // Implement database delete
    }
}
