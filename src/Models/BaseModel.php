<?php

declare(strict_types=1);

namespace RenalTales\Models;

require_once __DIR__ . '/../Core/Database.php';

use RenalTales\Core\Database;

/**
 * BaseModel - Base class for all models
 * 
 * Provides common functionality for database operations and business logic
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

abstract class BaseModel {
    
    protected Database $db;
    protected string $table;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find a record by ID
     * 
     * @param int $id
     * @return array|false
     */
    public function find(int $id): array|false {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->db->selectOne($sql, [$id]);
    }
    
    /**
     * Find all records
     * 
     * @param array $conditions
     * @param int|null $limit
     * @param int $offset
     * @return array
     */
    public function findAll(array $conditions = [], ?int $limit = null, int $offset = 0): array {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
            if ($offset > 0) {
                $sql .= " OFFSET " . (int)$offset;
            }
        }
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Create a new record
     * 
     * @param array $data
     * @return string Last insert ID
     */
    public function create(array $data): string {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        return $this->db->insert($sql, array_values($data));
    }
    
    /**
     * Update a record
     * 
     * @param int $id
     * @param array $data
     * @return int Number of affected rows
     */
    public function update(int $id, array $data): int {
        $fields = [];
        $params = [];
        
        foreach ($data as $field => $value) {
            $fields[] = "{$field} = ?";
            $params[] = $value;
        }
        
        $params[] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->db->update($sql, $params);
    }
    
    /**
     * Delete a record
     * 
     * @param int $id
     * @return int Number of affected rows
     */
    public function delete(int $id): int {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->update($sql, [$id]);
    }
    
    /**
     * Count records
     * 
     * @param array $conditions
     * @return int
     */
    public function count(array $conditions = []): int {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $result = $this->db->selectOne($sql, $params);
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Get paginated results
     * 
     * @param array $conditions
     * @param int $page
     * @param int $perPage
     * @param string $orderBy
     * @param string $orderDirection
     * @return array
     */
    public function paginate(array $conditions = [], int $page = 1, int $perPage = 10, string $orderBy = 'id', string $orderDirection = 'DESC'): array {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $totalCount = $this->count($conditions);
        
        // Build SQL for data
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        // Add ordering
        $orderDirection = strtoupper($orderDirection) === 'ASC' ? 'ASC' : 'DESC';
        $sql .= " ORDER BY {$orderBy} {$orderDirection}";
        
        // Add pagination
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $data = $this->db->select($sql, $params);
        
        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalCount,
                'total_pages' => ceil($totalCount / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $totalCount),
                'has_next' => $page < ceil($totalCount / $perPage),
                'has_prev' => $page > 1
            ]
        ];
    }
    
    /**
     * Search with pagination
     * 
     * @param string $searchField
     * @param string $searchTerm
     * @param int $page
     * @param int $perPage
     * @param string $orderBy
     * @param string $orderDirection
     * @return array
     */
    public function search(string $searchField, string $searchTerm, int $page = 1, int $perPage = 10, string $orderBy = 'id', string $orderDirection = 'DESC'): array {
        $offset = ($page - 1) * $perPage;
        
        // Get total count for search
        $countSql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$searchField} LIKE ?";
        $searchParam = '%' . $searchTerm . '%';
        $result = $this->db->selectOne($countSql, [$searchParam]);
        $totalCount = $result ? (int)$result['count'] : 0;
        
        // Get search results
        $orderDirection = strtoupper($orderDirection) === 'ASC' ? 'ASC' : 'DESC';
        $sql = "SELECT * FROM {$this->table} WHERE {$searchField} LIKE ? ORDER BY {$orderBy} {$orderDirection} LIMIT ? OFFSET ?";
        $params = [$searchParam, $perPage, $offset];
        
        $data = $this->db->select($sql, $params);
        
        return [
            'data' => $data,
            'search' => [
                'field' => $searchField,
                'term' => $searchTerm
            ],
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalCount,
                'total_pages' => ceil($totalCount / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $totalCount),
                'has_next' => $page < ceil($totalCount / $perPage),
                'has_prev' => $page > 1
            ]
        ];
    }
    
    /**
     * Validate data before operations
     * 
     * @param array $data
     * @return array Validation errors
     */
    abstract protected function validate(array $data): array;
}
