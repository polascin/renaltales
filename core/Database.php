<?php

/**
 * Database - Database Connection and Management Class
 * 
 * A secure database connection class for the Renal Tales application
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
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
    
    /**
     * Constructor - Private to implement singleton pattern
     */
    private function __construct() {
        // Load database configuration
        $this->loadConfiguration();
        $this->connect();
    }
    
    /**
     * Load database configuration from config file
     */
    private function loadConfiguration() {
        // Include bootstrap to ensure environment is loaded
        if (!isset($GLOBALS['config'])) {
            require_once dirname(__DIR__) . '/bootstrap.php';
        }
        
        // Load DatabaseConfig if available
        if (file_exists(__DIR__ . '/DatabaseConfig.php')) {
            require_once __DIR__ . '/DatabaseConfig.php';
            
            try {
                $dbConfig = DatabaseConfig::getInstance();
                $connection = $dbConfig->getConnection();
                
                $this->host = $connection['host'];
                $this->database = $connection['database'];
                $this->username = $connection['username'];
                $this->password = $connection['password'];
                $this->charset = $connection['charset'];
                return;
            } catch (Exception $e) {
                error_log('DatabaseConfig failed: ' . $e->getMessage());
                // Fall through to legacy configuration
            }
        }
        
        // Legacy configuration loading
        $configFile = dirname(__DIR__) . '/config/database.php';
        if (file_exists($configFile)) {
            $dbConfig = require $configFile;
            $connection = $dbConfig['connections'][$dbConfig['default']] ?? $dbConfig['connections']['mysql'];
            
            $this->host = $connection['host'];
            $this->database = $connection['database'];
            $this->username = $connection['username'];
            $this->password = $connection['password'];
            $this->charset = $connection['charset'];
        } else {
            // Fallback to global config if available
            $config = $GLOBALS['config'] ?? [];
            $dbConfig = $config['database'] ?? [];
            
            $this->host = $dbConfig['host'] ?? 'localhost';
            $this->database = $dbConfig['name'] ?? 'renaltales';
            $this->username = $dbConfig['user'] ?? 'root';
            $this->password = $dbConfig['password'] ?? '';
            $this->charset = $dbConfig['charset'] ?? 'utf8mb4';
        }
    }
    
    /**
     * Get database instance (singleton pattern)
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization of the instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
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
            
        } catch (PDOException $e) {
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
        } catch (PDOException $e) {
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
     * Execute a SELECT query and return all results
     * 
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function select($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
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
        } catch (PDOException $e) {
            $status['error'] = $e->getMessage();
        }
        
        return $status;
    }
    
    /**
     * Destructor - Close connection
     */
    public function __destruct() {
        $this->close();
    }
}
