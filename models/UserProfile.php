<?php

/**
 * UserProfile Model - For normalized user profiles
 * 
 * Handles user profile data operations
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

require_once __DIR__ . '/BaseModel.php';

class UserProfile extends BaseModel {
    
    protected $table = 'user_profiles';
    
    /**
     * Find profile by user ID
     * 
     * @param int $userId
     * @return array|false
     */
    public function findByUserId($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? LIMIT 1";
        return $this->db->selectOne($sql, [$userId]);
    }
    
    /**
     * Update profile by user ID
     * 
     * @param int $userId
     * @param array $data
     * @return int Number of affected rows
     */
    public function updateByUserId($userId, $data) {
        $fields = [];
        $params = [];
        
        foreach ($data as $field => $value) {
            if ($field !== 'user_id') { // Don't allow updating user_id
                $fields[] = "{$field} = ?";
                $params[] = $value;
            }
        }
        
        $params[] = $userId;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE user_id = ?";
        
        return $this->db->update($sql, $params);
    }
    
    /**
     * Create or update profile
     * 
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function createOrUpdate($userId, $data) {
        $existing = $this->findByUserId($userId);
        
        if ($existing) {
            return $this->updateByUserId($userId, $data) > 0;
        } else {
            $data['user_id'] = $userId;
            return $this->create($data) !== false;
        }
    }
    
    /**
     * Get profiles by language
     * 
     * @param string $language
     * @param int $limit
     * @return array
     */
    public function findByLanguage($language, $limit = 50) {
        $sql = "SELECT * FROM {$this->table} WHERE language = ? LIMIT ?";
        return $this->db->select($sql, [$language, $limit]);
    }
    
    /**
     * Get profiles by country
     * 
     * @param string $country
     * @param int $limit
     * @return array
     */
    public function findByCountry($country, $limit = 50) {
        $sql = "SELECT * FROM {$this->table} WHERE country = ? LIMIT ?";
        return $this->db->select($sql, [$country, $limit]);
    }
    
    /**
     * Search profiles by display name
     * 
     * @param string $searchTerm
     * @param int $limit
     * @return array
     */
    public function searchByDisplayName($searchTerm, $limit = 50) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE display_name LIKE ? 
                   OR first_name LIKE ? 
                   OR last_name LIKE ?
                LIMIT ?";
        $searchPattern = "%{$searchTerm}%";
        return $this->db->select($sql, [$searchPattern, $searchPattern, $searchPattern, $limit]);
    }
    
    /**
     * Validate profile data
     * 
     * @param array $data
     * @return array Validation errors
     */
    protected function validate($data) {
        $errors = [];
        
        // Display name validation
        if (isset($data['display_name']) && strlen($data['display_name']) > 150) {
            $errors['display_name'] = 'Display name must be less than 150 characters';
        }
        
        // First name validation
        if (isset($data['first_name']) && strlen($data['first_name']) > 100) {
            $errors['first_name'] = 'First name must be less than 100 characters';
        }
        
        // Last name validation
        if (isset($data['last_name']) && strlen($data['last_name']) > 100) {
            $errors['last_name'] = 'Last name must be less than 100 characters';
        }
        
        // Avatar URL validation
        if (isset($data['avatar_url']) && !empty($data['avatar_url'])) {
            if (!filter_var($data['avatar_url'], FILTER_VALIDATE_URL)) {
                $errors['avatar_url'] = 'Invalid avatar URL format';
            } elseif (strlen($data['avatar_url']) > 500) {
                $errors['avatar_url'] = 'Avatar URL must be less than 500 characters';
            }
        }
        
        // Phone validation
        if (isset($data['phone']) && !empty($data['phone'])) {
            if (!preg_match('/^[+\d\s\-\(\)]+$/', $data['phone'])) {
                $errors['phone'] = 'Invalid phone number format';
            } elseif (strlen($data['phone']) > 20) {
                $errors['phone'] = 'Phone number must be less than 20 characters';
            }
        }
        
        // Date of birth validation
        if (isset($data['date_of_birth']) && !empty($data['date_of_birth'])) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_of_birth'])) {
                $errors['date_of_birth'] = 'Invalid date format (YYYY-MM-DD required)';
            } elseif (strtotime($data['date_of_birth']) > time()) {
                $errors['date_of_birth'] = 'Date of birth cannot be in the future';
            }
        }
        
        // Gender validation
        if (isset($data['gender']) && !empty($data['gender'])) {
            $validGenders = ['male', 'female', 'other', 'prefer_not_to_say'];
            if (!in_array($data['gender'], $validGenders)) {
                $errors['gender'] = 'Invalid gender value';
            }
        }
        
        // Country validation (ISO 3166-1 alpha-2)
        if (isset($data['country']) && !empty($data['country'])) {
            if (!preg_match('/^[A-Z]{2}$/', $data['country'])) {
                $errors['country'] = 'Country must be a valid ISO 3166-1 alpha-2 code';
            }
        }
        
        // Language validation
        if (isset($data['language']) && !empty($data['language'])) {
            if (strlen($data['language']) > 10) {
                $errors['language'] = 'Language code must be less than 10 characters';
            }
        }
        
        // Timezone validation
        if (isset($data['timezone']) && !empty($data['timezone'])) {
            if (!in_array($data['timezone'], timezone_identifiers_list())) {
                $errors['timezone'] = 'Invalid timezone';
            }
        }
        
        return $errors;
    }
}
