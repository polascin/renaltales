<?php

declare(strict_types=1);

namespace RenalTales\Contracts;

/**
 * Base Repository Interface
 *
 * Defines the contract for all repository implementations
 * providing common CRUD operations and data access patterns.
 *
 * @package RenalTales\Contracts
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */
interface RepositoryInterface
{
    /**
     * Find an entity by its identifier
     *
     * @param mixed $id The identifier
     * @return mixed|null The entity or null if not found
     */
    public function find($id): mixed;

    /**
     * Find all entities
     *
     * @return array<mixed> Array of all entities
     */
    public function findAll(): array;

    /**
     * Find entities by specific criteria
     *
     * @param array<string, mixed> $criteria Search criteria
     * @param array<string, string>|null $orderBy Order criteria
     * @param int|null $limit Limit results
     * @param int|null $offset Offset results
     * @return array<mixed> Array of entities
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    /**
     * Find a single entity by criteria
     *
     * @param array<string, mixed> $criteria Search criteria
     * @return mixed|null The entity or null if not found
     */
    public function findOneBy(array $criteria): mixed;

    /**
     * Create a new entity
     *
     * @param array<string, mixed> $data Entity data
     * @return mixed The created entity
     */
    public function create(array $data): mixed;

    /**
     * Update an existing entity
     *
     * @param mixed $id The identifier
     * @param array<string, mixed> $data Updated data
     * @return mixed The updated entity
     */
    public function update($id, array $data): mixed;

    /**
     * Delete an entity
     *
     * @param mixed $id The identifier
     * @return bool True if deleted, false otherwise
     */
    public function delete($id): bool;

    /**
     * Count entities
     *
     * @param array<string, mixed> $criteria Search criteria
     * @return int Number of entities
     */
    public function count(array $criteria = []): int;

    /**
     * Check if entity exists
     *
     * @param mixed $id The identifier
     * @return bool True if exists, false otherwise
     */
    public function exists($id): bool;
}
