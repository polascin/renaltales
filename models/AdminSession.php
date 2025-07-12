<?php

/**
 * AdminSession Model
 * 
 * Manages admin session records in the database
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

require_once __DIR__ . '/../core/Database.php';

class AdminSession {
    
    private Database $db;
    private string $table = 'admin_sessions';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create new admin session record
     * 
     * @param array $data Session data
     * @return int|false Insert ID or false on failure
     */
    public function create(array $data) {
        try {
            $fields = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
            
            $insertId = $this->db->insert($sql, $data);
            return $insertId ? (int)$insertId : false;
            
        } catch (Exception $e) {
            error_log('AdminSession create error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find session by user ID and token
     * 
     * @param int $userId User ID
     * @param string $hashedToken Hashed session token
     * @return array|false Session data or false if not found
     */
    public function findByUserAndToken(int $userId, string $hashedToken) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE user_id = ? AND session_token = ? 
                    AND expires_at > NOW() 
                    LIMIT 1";
            
            return $this->db->selectOne($sql, [$userId, $hashedToken]);
            
        } catch (Exception $e) {
            error_log('AdminSession findByUserAndToken error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update admin session record
     * 
     * @param int $id Session ID
     * @param array $data Update data
     * @return bool Success status
     */
    public function update(int $id, array $data): bool {
        try {
            $setClause = [];
            foreach (array_keys($data) as $field) {
                $setClause[] = "{$field} = :{$field}";
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE id = :id";
            $data['id'] = $id;
            
            $result = $this->db->update($sql, $data);
            return $result > 0;
            
        } catch (Exception $e) {
            error_log('AdminSession update error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update session by token
     * 
     * @param string $hashedToken Hashed session token
     * @param array $data Update data
     * @return bool Success status
     */
    public function updateByToken(string $hashedToken, array $data): bool {
        try {
            $setClause = [];
            foreach (array_keys($data) as $field) {
                $setClause[] = "{$field} = :{$field}";
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE session_token = :session_token";
            $data['session_token'] = $hashedToken;
            
            $result = $this->db->update($sql, $data);
            return $result > 0;
            
        } catch (Exception $e) {
            error_log('AdminSession updateByToken error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get active sessions for user
     * 
     * @param int $userId User ID
     * @return array Active sessions
     */
    public function getActiveSessions(int $userId): array {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE user_id = ? AND expires_at > NOW() 
                    ORDER BY last_activity DESC";
            
            return $this->db->select($sql, [$userId]);
            
        } catch (Exception $e) {
            error_log('AdminSession getActiveSessions error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get oldest session for user
     * 
     * @param int $userId User ID
     * @return array|false Oldest session or false if none found
     */
    public function getOldestSession(int $userId) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE user_id = ? AND expires_at > NOW() 
                    ORDER BY created_at ASC 
                    LIMIT 1";
            
            return $this->db->selectOne($sql, [$userId]);
            
        } catch (Exception $e) {
            error_log('AdminSession getOldestSession error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete session record
     * 
     * @param int $id Session ID
     * @return bool Success status
     */
    public function delete(int $id): bool {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $result = $this->db->update($sql, [$id]);
            return $result > 0;
            
        } catch (Exception $e) {
            error_log('AdminSession delete error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete session by user and token
     * 
     * @param int $userId User ID
     * @param string $hashedToken Hashed session token
     * @return bool Success status
     */
    public function deleteByUserAndToken(int $userId, string $hashedToken): bool {
        try {
            $sql = "DELETE FROM {$this->table} WHERE user_id = ? AND session_token = ?";
            $result = $this->db->update($sql, [$userId, $hashedToken]);
            return $result > 0;
            
        } catch (Exception $e) {
            error_log('AdminSession deleteByUserAndToken error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete expired sessions
     * 
     * @param return int Number of deleted sessions
     */
    public function deleteExpiredSessions(): int {
        try {
            $sql = "DELETE FROM {$this->table} WHERE expires_at <= NOW()";
            return $this->db->update($sql);
            
        } catch (Exception $e) {
            error_log('AdminSession deleteExpiredSessions error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Delete all sessions for user
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function deleteUserSessions(int $userId): bool {
        try {
            $sql = "DELETE FROM {$this->table} WHERE user_id = ?";
            $result = $this->db->update($sql, [$userId]);
            return $result > 0;
            
        } catch (Exception $e) {
            error_log('AdminSession deleteUserSessions error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get session statistics
     * 
     * @return array Session statistics
     */
    public function getSessionStats(): array {
        try {
            $stats = [];
            
            // Total active sessions
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE expires_at > NOW()";
            $result = $this->db->selectOne($sql);
            $stats['active_sessions'] = $result['count'] ?? 0;
            
            // Sessions by user
            $sql = "SELECT user_id, COUNT(*) as session_count 
                    FROM {$this->table} 
                    WHERE expires_at > NOW() 
                    GROUP BY user_id";
            $stats['sessions_by_user'] = $this->db->select($sql);
            
            // Sessions created today
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE DATE(created_at) = CURDATE()";
            $result = $this->db->selectOne($sql);
            $stats['sessions_today'] = $result['count'] ?? 0;
            
            return $stats;
            
        } catch (Exception $e) {
            error_log('AdminSession getSessionStats error: ' . $e->getMessage());
            return [];
        }
    }
}
