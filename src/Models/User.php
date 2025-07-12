<?php

declare(strict_types=1);

namespace RenalTales\Models;

use RenalTales\Models\BaseModel;

/**
 * User Model - Updated for normalized schema
 * 
 * Handles user data operations with the new normalized database structure
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

class User extends BaseModel {
    
    protected string $table = 'users';
    
    /**
     * Find user by email
     * 
     * @param string $email
     * @return array|false
     */
    public function findByEmail(string $email): array|false {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1";
        return $this->db->selectOne($sql, [$email]);
    }
    
    /**
     * Find user by username
     * 
     * @param string $username
     * @return array|false
     */
    public function findByUsername(string $username): array|false {
        $sql = "SELECT * FROM {$this->table} WHERE username = ? LIMIT 1";
        return $this->db->selectOne($sql, [$username]);
    }
    
    /**
     * Find user with profile data
     * 
     * @param int $userId
     * @return array|false
     */
    public function findWithProfile($userId) {
        $sql = "SELECT 
                    u.*, 
                    p.first_name, p.last_name, p.display_name, p.avatar_url, p.bio,
                    p.timezone, p.language, p.date_format, p.time_format,
                    p.phone, p.date_of_birth, p.gender, p.country, p.city,
                    p.privacy_settings, p.notification_settings
                FROM {$this->table} u
                LEFT JOIN user_profiles p ON u.id = p.user_id
                WHERE u.id = ? LIMIT 1";
        return $this->db->selectOne($sql, [$userId]);
    }
    
    /**
     * Create user with profile
     * 
     * @param array $userData
     * @param array $profileData
     * @return string|false User ID if successful
     */
    public function createWithProfile($userData, $profileData = []) {
        try {
            $this->db->beginTransaction();
            
            // Create user
            $userId = $this->create($userData);
            
            if ($userId) {
                // Create user profile
                $profileData['user_id'] = $userId;
                $profileModel = new \RenalTales\Models\UserProfile();
                $profileModel->create($profileData);
                
                $this->db->commit();
                return $userId;
            }
            
            $this->db->rollback();
            return false;
            
        } catch(Exception $e) 
    error_log('Exception in User.php: ' . $e->getMessage());
            $this->db->rollback();
            throw $e;
        
    }
    
    /**
     * Get all users with their roles
     * 
     * @return array
     */
    public function allWithRoles() {
        $sql = "SELECT u.*, GROUP_CONCAT(r.name) as roles
                FROM users_new u
                LEFT JOIN user_roles ur ON u.id = ur.user_id AND ur.is_active = TRUE
                LEFT JOIN roles r ON ur.role_id = r.id
                GROUP BY u.id
                ORDER BY u.username";
        
        return $this->db->select($sql);
    }
    
    /**
     * Update user last login
     * 
     * @param int $userId
     * @param string $ipAddress
     * @return bool
     */
    public function updateLastLogin($userId, $ipAddress = null) {
        try {
            $this->db->beginTransaction();
            
            // Update user
            $this->update($userId, [
                'last_login_at' => date('Y-m-d H:i:s'),
                'failed_login_count' => 0,
                'locked_until' => null
            ]);
            
            // Log security event
            $securityEvent = new \RenalTales\Models\SecurityEvent();
            $securityEvent->create([
                'user_id' => $userId,
                'event_type' => 'login_success',
                'ip_address' => $ipAddress ?: '127.0.0.1',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->db->commit();
            return true;
            
        } catch(Exception $e) 
    error_log('Exception in User.php: ' . $e->getMessage());
            $this->db->rollback();
            return false;
        
    }
    
    /**
     * Increment failed login count
     * 
     * @param int $userId
     * @return bool
     */
    public function incrementFailedLogin($userId) {
        $sql = "UPDATE {$this->table} 
                SET failed_login_count = failed_login_count + 1,
                    locked_until = CASE 
                        WHEN failed_login_count >= 4 THEN DATE_ADD(NOW(), INTERVAL 30 MINUTE)
                        ELSE locked_until
                    END
                WHERE id = ?";
        
        return $this->db->update($sql, [$userId]) > 0;
    }
    
    /**
     * Check if user is locked
     * 
     * @param int $userId
     * @return bool
     */
    public function isLocked($userId) {
        $sql = "SELECT locked_until FROM {$this->table} WHERE id = ?";
        $result = $this->db->selectOne($sql, [$userId]);
        
        if ($result && $result['locked_until']) {
            return strtotime($result['locked_until']) > time();
        }
        
        return false;
    }
    
    /**
     * Get user's language preferences
     * 
     * @param int $userId
     * @return array
     */
    public function getLanguagePreferences($userId) {
        $sql = "SELECT language_code, is_primary, proficiency_level 
                FROM language_preferences 
                WHERE user_id = ?
                ORDER BY is_primary DESC, language_code";
        
        return $this->db->select($sql, [$userId]);
    }
    
    /**
     * Validate user data
     * 
     * @param array $data
     * @return array Validation errors
     */
    protected function validate(array $data): array {
        $errors = [];
        
        // Username validation
        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        } elseif (strlen($data['username']) < 3) {
            $errors['username'] = 'Username must be at least 3 characters';
        } elseif (strlen($data['username']) > 50) {
            $errors['username'] = 'Username must be less than 50 characters';
        }
        
        // Email validation
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } elseif (strlen($data['email']) > 255) {
            $errors['email'] = 'Email must be less than 255 characters';
        }
        
        // Password validation (for creation)
        if (isset($data['password'])) {
            if (empty($data['password'])) {
                $errors['password'] = 'Password is required';
            } elseif (strlen($data['password']) < 8) {
                $errors['password'] = 'Password must be at least 8 characters';
            }
        }
        
        // Check uniqueness
        if (!empty($data['email'])) {
            $existing = $this->findByEmail($data['email']);
            if ($existing && (!isset($data['id']) || $existing['id'] !== $data['id'])) {
                $errors['email'] = 'Email already exists';
            }
        }
        
        if (!empty($data['username'])) {
            $existing = $this->findByUsername($data['username']);
            if ($existing && (!isset($data['id']) || $existing['id'] !== $data['id'])) {
                $errors['username'] = 'Username already exists';
            }
        }
        
        return $errors;
    }
}
