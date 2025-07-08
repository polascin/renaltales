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
        // Database configuration based on provided rules
        $this->host = 'localhost';
        $this->database = 'renaltales';
        $this->username = 'root';
        $this->password = ''; // Empty password for local development
        $this->charset = 'utf8mb4';
        
        $this->connect();
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
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Database query failed: ' . $e->getMessage() . ' SQL: ' . $sql);
            throw new Exception('Database query failed: ' . $e->getMessage());
        }
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
     * Destructor - Close connection
     */
    public function __destruct() {
        $this->close();
    }
}

?>
