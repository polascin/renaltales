<?php

declare(strict_types=1);

namespace RenalTales\Core\Contracts;

use PDO;
use PDOStatement;

/**
 * Database Interface
 *
 * This interface defines the contract for database operations
 */
interface DatabaseInterface
{
    /**
     * Get the PDO connection instance
     */
    public function getConnection(): PDO;

    /**
     * Execute a prepared statement with parameters
     */
    public function execute(string $query, array $params = []): PDOStatement;

    /**
     * Fetch all rows from a query
     */
    public function fetchAll(string $query, array $params = []): array;

    /**
     * Fetch one row from a query
     */
    public function fetchOne(string $query, array $params = []): ?array;

    /**
     * Fetch a single column value
     */
    public function fetchColumn(string $query, array $params = []): mixed;

    /**
     * Get the last inserted ID
     */
    public function getLastInsertId(): string|false;

    /**
     * Begin a transaction
     */
    public function beginTransaction(): bool;

    /**
     * Commit a transaction
     */
    public function commit(): bool;

    /**
     * Rollback a transaction
     */
    public function rollback(): bool;

    /**
     * Check if currently in a transaction
     */
    public function inTransaction(): bool;

    /**
     * Execute a transaction with automatic rollback on failure
     */
    public function transaction(callable $callback): mixed;

    /**
     * Quote a string for use in a query
     */
    public function quote(string $value): string;

    /**
     * Check if the database connection is healthy
     */
    public function isHealthy(): bool;

    /**
     * Get database information
     */
    public function getInfo(): array;

    /**
     * Close the database connection
     */
    public function close(): void;
}
