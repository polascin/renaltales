<?php

/**
 * Log Model
 * Handles operations related to system logs within the application.
 *
 * @version 2025.v1.0
 */

require_once __DIR__ . '/BaseModel.php';

class Log extends BaseModel {
    protected $table = 'system_logs';

    /**
     * Create a log entry
     *
     * @param array $data
     * @return bool|string Last insert ID or false on failure
     */
    public function create($data) {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";

        return $this->db->insert($sql, array_values($data));
    }

    /**
     * Find logs by level
     *
     * @param string $level
     * @param int $limit
     * @return array
     */
    public function findByLevel($level, $limit = 50) {
        $sql = "SELECT * FROM {$this->table} WHERE level = ? ORDER BY created_at DESC LIMIT ?";
        return $this->db->select($sql, [$level, $limit]);
    }

    /**
     * Find logs by channel
     *
     * @param string $channel
     * @param int $limit
     * @return array
     */
    public function findByChannel($channel, $limit = 50) {
        $sql = "SELECT * FROM {$this->table} WHERE channel = ? ORDER BY created_at DESC LIMIT ?";
        return $this->db->select($sql, [$channel, $limit]);
    }

    /**
     * Validate log data
     *
     * @param array $data
     * @return array Validation errors
     */
    protected function validate($data) {
        $errors = [];

        // Level validation
        if (empty($data['level'])) {
            $errors['level'] = 'Log level is required';
        }

        // Message validation
        if (empty($data['message'])) {
            $errors['message'] = 'Log message is required';
        }

        return $errors;
    }
}

?>
