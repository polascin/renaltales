<?php

/**
 * Database Helper Functions
 * 
 * These functions provide easy access to database functionality
 * throughout the application.
 */

use RenalTales\Core\Database;

if (!function_exists('database')) {
    /**
     * Get database instance
     * 
     * @return Database
     */
    function database(): Database
    {
        return Database::getInstance();
    }
}

if (!function_exists('pdo')) {
    /**
     * Get PDO connection
     * 
     * @return PDO
     */
    function pdo(): PDO
    {
        return Database::getInstance()->getConnection();
    }
}

if (!function_exists('db_query')) {
    /**
     * Execute a database query
     * 
     * @param string $query
     * @param array $params
     * @return PDOStatement
     */
    function db_query(string $query, array $params = []): PDOStatement
    {
        return Database::getInstance()->execute($query, $params);
    }
}

if (!function_exists('db_fetch_all')) {
    /**
     * Fetch all rows from a query
     * 
     * @param string $query
     * @param array $params
     * @return array
     */
    function db_fetch_all(string $query, array $params = []): array
    {
        return Database::getInstance()->fetchAll($query, $params);
    }
}

if (!function_exists('db_fetch_one')) {
    /**
     * Fetch one row from a query
     * 
     * @param string $query
     * @param array $params
     * @return array|null
     */
    function db_fetch_one(string $query, array $params = []): ?array
    {
        return Database::getInstance()->fetchOne($query, $params);
    }
}

if (!function_exists('db_fetch_column')) {
    /**
     * Fetch a single column value
     * 
     * @param string $query
     * @param array $params
     * @return mixed
     */
    function db_fetch_column(string $query, array $params = []): mixed
    {
        return Database::getInstance()->fetchColumn($query, $params);
    }
}

if (!function_exists('db_transaction')) {
    /**
     * Execute a transaction
     * 
     * @param callable $callback
     * @return mixed
     */
    function db_transaction(callable $callback): mixed
    {
        return Database::getInstance()->transaction($callback);
    }
}

if (!function_exists('db_insert_id')) {
    /**
     * Get the last inserted ID
     * 
     * @return string|false
     */
    function db_insert_id(): string|false
    {
        return Database::getInstance()->getLastInsertId();
    }
}

if (!function_exists('db_health_check')) {
    /**
     * Check database health
     * 
     * @return bool
     */
    function db_health_check(): bool
    {
        return Database::getInstance()->isHealthy();
    }
}

if (!function_exists('db_info')) {
    /**
     * Get database information
     * 
     * @return array
     */
    function db_info(): array
    {
        return Database::getInstance()->getInfo();
    }
}
