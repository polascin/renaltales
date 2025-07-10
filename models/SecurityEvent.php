<?php

/**
 * SecurityEvent Model - For normalized user security events
 *
 * Handles security event data operations
 *
 * @version 2025.v1.0
 */

require_once __DIR__ . '/BaseModel.php';

class SecurityEvent extends BaseModel {
    
    protected string $table = 'security_events';
    
    /**
     * Create security event
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
     * Find events by user ID
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function findByUserId($userId, $limit = 50) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
        return $this->db->select($sql, [$userId, $limit]);
    }
    
    /**
     * Find events by type
     * 
     * @param string $type
     * @param int $limit
     * @return array
     */
    public function findByType($type, $limit = 50) {
        $sql = "SELECT * FROM {$this->table} WHERE event_type = ? ORDER BY created_at DESC LIMIT ?";
        return $this->db->select($sql, [$type, $limit]);
    }
    
    /**
     * Validate event data
     * 
     * @param array $data
     * @return array Validation errors
     */
    protected function validate(array $data): array {
        $errors = [];
        
        // Event type validation
        if (empty($data['event_type'])) {
            $errors['event_type'] = 'Event type is required';
        }
        
        // IP address validation
        if (isset($data['ip_address']) && !empty($data['ip_address'])) {
            if (!filter_var($data['ip_address'], FILTER_VALIDATE_IP)) {
                $errors['ip_address'] = 'Invalid IP address';
            }
        }
        
        // Risk score validation
        if (isset($data['risk_score']) && !is_int($data['risk_score'])) {
            $errors['risk_score'] = 'Risk score must be an integer';
        }

        return $errors;
    }
}

?>
