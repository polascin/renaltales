<?php

declare(strict_types=1);

namespace RenalTales\Core;

use PDO;
use PDOException;
use Exception;

require_once __DIR__ . '/CacheManager.php';

/**
 * Database - Database Connection and Management Class
 * 
 * A secure database connection class for the Renal Tales application
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v2.0
 */
class Database {
    
    private static $instance = null;
    private $connection;
    private $host;
    private $database;
    private $username;
    private $password;
    private $charset;
    private $isConnected = false;
    private $cacheManager = null;
    private $cacheEnabled = true;
    
    /**
     * Private constructor for singleton pattern
     * 
     * @param array $config Database configuration
     */
    private function __construct(array $config = []) {
        // Load database configuration
        $this->loadConfiguration($config);
        $this->connect();
    }
    
    /**
     * Get singleton instance
     * 
     * @param array $config Database configuration (only used on first call)
     * @return Database
     */
    public static function getInstance(array $config = []): Database {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception('Cannot unserialize a singleton.');
    }
    
    /**
     * Load database configuration from config array
     * 
     * @param array $config Database configuration
     */
    private function loadConfiguration(array $config): void {
        if (!empty($config)) {
            // Use provided configuration
            $this->host = $config['host'] ?? 'localhost';
            $this->database = $config['name'] ?? $config['database'] ?? 'renaltales';
            $this->username = $config['user'] ?? $config['username'] ?? 'root';
            $this->password = $config['password'] ?? '';
            $this->charset = $config['charset'] ?? 'utf8mb4';
            return;
        }
        
        // Include bootstrap to ensure environment is loaded
        if (!isset($GLOBALS['config'])) {
            require_once dirname(__DIR__, 2) . '/bootstrap.php';
        }
        
        // Fallback to global config if available
        $globalConfig = $GLOBALS['config'] ?? [];
        $dbConfig = $globalConfig['database'] ?? [];
        
        $this->host = $dbConfig['host'] ?? 'localhost';
        $this->database = $dbConfig['name'] ?? 'renaltales';
        $this->username = $dbConfig['user'] ?? 'root';
        $this->password = $dbConfig['password'] ?? '';
        $this->charset = $dbConfig['charset'] ?? 'utf8mb4';
    }
    
    
    /**
     * Establish database connection
     * 
     * @throws Exception if connection fails
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset} COLLATE {$this->charset}_unicode_ci"
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            $this->isConnected = true;
            
        } catch(PDOException $e) {
            $this->isConnected = false;
            error_log('Database connection failed: ' . $e->getMessage());
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get PDO connection
     * 
     * @return PDO
     * @throws Exception if not connected
     */
    public function getConnection() {
        if (!$this->isConnected || $this->connection === null) {
            throw new Exception('Database not connected');
        }
        return $this->connection;
    }
    
    /**
     * Check if database is connected
     * 
     * @return bool
     */
    public function isConnected() {
        return $this->isConnected;
    }
    
    /**
     * Execute a prepared statement
     * 
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     * @throws Exception if execution fails
     */
    public function execute($sql, $params = []) {
        try {
            // Validate SQL query for potential injection attempts
            if (!$this->validateSQLQuery($sql)) {
                throw new Exception('SQL query contains potentially dangerous content');
            }
            
            $stmt = $this->connection->prepare($sql);
            
            // Sanitize parameters
            $sanitizedParams = $this->sanitizeParameters($params);
            
            $stmt->execute($sanitizedParams);
            return $stmt;
        } catch(PDOException $e) {
            error_log('Database query failed: ' . $e->getMessage() . ' SQL: ' . $sql);
            throw new Exception('Database query failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate SQL query for potential injection attempts
     * 
     * @param string $sql
     * @return bool
     */
    private function validateSQLQuery($sql) {
        // Remove comments and normalize whitespace
        $sql = preg_replace('/\s+/', ' ', $sql);
        $sql = preg_replace('/--.*$/', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Check for dangerous patterns
        $dangerousPatterns = [
            '/;\s*(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|EXECUTE)/i',
            '/UNION\s+(ALL\s+)?SELECT/i',
            '/(\bOR\b|\bAND\b)\s+\d+\s*=\s*\d+/i',
            '/(\bOR\b|\bAND\b)\s+[\'"].*[\'"]\s*=\s*[\'"].*[\'"]/',
            '/\bEXEC\s*\(/i',
            '/\bEXECUTE\s*\(/i',
            '/\bSP_\w+/i',
            '/\bXP_\w+/i',
            '/\bSHUTDOWN\b/i',
            '/\bDROP\s+(TABLE|DATABASE|SCHEMA)/i'
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $sql)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Sanitize parameters for SQL execution
     * 
     * @param array $params
     * @return array
     */
    private function sanitizeParameters($params) {
        $sanitized = [];
        
        foreach ($params as $key => $value) {
            if (is_string($value)) {
                // Remove null bytes and control characters
                $value = str_replace(["\0", "\x00", "\x1a"], '', $value);
                
                // Limit string length to prevent DoS
                if (strlen($value) > 10000) {
                    $value = substr($value, 0, 10000);
                }
            }
            
            $sanitized[$key] = $value;
        }
        
        return $sanitized;
    }
    
    /**
     * Execute a SELECT query and return all results (with caching)
     * 
     * @param string $sql
     * @param array $params
     * @param int $ttl Cache time to live in seconds
     * @return array
     */
    public function select($sql, $params = [], int $ttl = null) {
        // Check cache first for SELECT queries if caching is enabled
        if ($this->cacheEnabled && $this->isCacheableQuery($sql)) {
            $cached = $this->getCachedQuery($sql, $params);
            if ($cached !== null) {
                return $cached;
            }
        }
        
        $stmt = $this->execute($sql, $params);
        $result = $stmt->fetchAll();
        
        // Cache the result if it's a cacheable query
        if ($this->cacheEnabled && $this->isCacheableQuery($sql)) {
            $this->cacheQuery($sql, $params, $result, $ttl);
        }
        
        return $result;
    }
    
    /**
     * Execute a SELECT query and return single row
     * 
     * @param string $sql
     * @param array $params
     * @return array|false
     */
    public function selectOne($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Execute an INSERT query and return last insert ID
     * 
     * @param string $sql
     * @param array $params
     * @return string
     */
    public function insert($sql, $params = []) {
        $this->execute($sql, $params);
        return $this->connection->lastInsertId();
    }
    
    /**
     * Execute an UPDATE or DELETE query and return affected rows
     * 
     * @param string $sql
     * @param array $params
     * @return int
     */
    public function update($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Start a database transaction
     * 
     * @return bool
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit a database transaction
     * 
     * @return bool
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback a database transaction
     * 
     * @return bool
     */
    public function rollback() {
        return $this->connection->rollback();
    }
    
    /**
     * Check if we're in a transaction
     * 
     * @return bool
     */
    public function inTransaction() {
        return $this->connection->inTransaction();
    }
    
    /**
     * Close database connection
     */
    public function close() {
        $this->connection = null;
        $this->isConnected = false;
    }
    
    /**
     * Test database connection
     * 
     * @return array Connection status information
     */
    public function testConnection() {
        $status = [
            'connected' => false,
            'host' => $this->host,
            'database' => $this->database,
            'username' => $this->username,
            'charset' => $this->charset,
            'error' => null,
            'version' => null
        ];
        
        try {
            if ($this->isConnected && $this->connection !== null) {
                // Test with a simple query
                $stmt = $this->connection->query('SELECT VERSION() as version');
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $status['connected'] = true;
                $status['version'] = $result['version'] ?? 'Unknown';
            } else {
                $status['error'] = 'Database not connected';
            }
        } catch(PDOException $e) {
            error_log('Exception in Database.php: ' . $e->getMessage());
            $status['error'] = $e->getMessage();
        }
        
        return $status;
    }
    
    /**
     * Get cache manager instance
     * 
     * @return CacheManager
     */
    private function getCacheManager() {
        if ($this->cacheManager === null) {
            $this->cacheManager = new CacheManager();
        }
        return $this->cacheManager;
    }
    
    /**
     * Check if query is cacheable
     * 
     * @param string $sql
     * @return bool
     */
    private function isCacheableQuery(string $sql): bool {
        $sql = trim(strtoupper($sql));
        
        // Only cache SELECT queries that don't contain certain keywords
        if (!str_starts_with($sql, 'SELECT')) {
            return false;
        }
        
        // Don't cache queries with NOW(), RAND(), UUID(), etc.
        $nonCacheablePatterns = [
            '/\bNOW\s*\(/',
            '/\bRAND\s*\(/',
            '/\bUUID\s*\(/',
            '/\bCURRENT_TIMESTAMP/',
            '/\bCURRENT_TIME/',
            '/\bCURRENT_DATE/',
            '/\bLAST_INSERT_ID\s*\(/',
            '/\bFOUND_ROWS\s*\(/',
        ];
        
        foreach ($nonCacheablePatterns as $pattern) {
            if (preg_match($pattern, $sql)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Cache query results
     * 
     * @param string $sql
     * @param array $params
     * @param array $result
     * @param int $ttl
     * @return bool
     */
    private function cacheQuery(string $sql, array $params, array $result, int $ttl = null): bool {
        try {
            return $this->getCacheManager()->cacheQuery($sql, $params, $result, $ttl);
        } catch (Exception $e) {
            error_log('Cache query error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cached query results
     * 
     * @param string $sql
     * @param array $params
     * @return array|null
     */
    private function getCachedQuery(string $sql, array $params): ?array {
        try {
            return $this->getCacheManager()->getCachedQuery($sql, $params);
        } catch (Exception $e) {
            error_log('Get cached query error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Enable/disable query caching
     * 
     * @param bool $enabled
     */
    public function setCacheEnabled(bool $enabled): void {
        $this->cacheEnabled = $enabled;
    }
    
    /**
     * Clear query cache
     * 
     * @param string $pattern Optional pattern to match specific cache keys
     * @return int Number of cleared entries
     */
    public function clearCache(string $pattern = '*'): int {
        try {
            return $this->getCacheManager()->invalidateQueryCache($pattern);
        } catch (Exception $e) {
            error_log('Clear cache error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get cache performance statistics
     * 
     * @return array
     */
    public function getCacheStats(): array {
        try {
            return $this->getCacheManager()->getPerformanceStats();
        } catch (Exception $e) {
            error_log('Get cache stats error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Analyze query performance using EXPLAIN
     * 
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function explainQuery(string $sql, array $params = []): array {
        try {
            $explainSql = 'EXPLAIN ' . $sql;
            $stmt = $this->execute($explainSql, $params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('EXPLAIN query error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Destructor - Close connection
     */
    public function __destruct() {
        $this->close();
    }
}
