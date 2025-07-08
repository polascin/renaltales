<?php

/**
 * BaseModel - Base class for all models
 * 
 * Provides common functionality for database operations and business logic
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

abstract class BaseModel {
    
    protected $db;
    protected $table;
    
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
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->db->selectOne($sql, [$id]);
    }
    
    /**
     * Find all records
     * 
     * @param array $conditions
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findAll($conditions = [], $limit = null, $offset = 0) {
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
    public function create($data) {
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
    public function update($id, $data) {
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
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->update($sql, [$id]);
    }
    
    /**
     * Count records
     * 
     * @param array $conditions
     * @return int
     */
    public function count($conditions = []) {
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
     * Validate data before operations
     * 
     * @param array $data
     * @return array Validation errors
     */
    abstract protected function validate($data);
}

?>
