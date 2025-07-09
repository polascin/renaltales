<?php

/**
 * ProfileManager - Profile Management
 * 
 * Manages user profiles including viewing and updating profile information
 * 
 * @version 2025.v1.0
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/UserProfile.php';

class ProfileManager {
    
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Get user profile by user ID
     * 
     * @param int $userId User ID
     * @return array|false User profile data or false
     */
    public function getProfile($userId) {
        return $this->userModel->findWithProfile($userId);
    }
    
    /**
     * Update user profile
     * 
     * @param int $userId User ID
     * @param array $profileData Profile data to update
     * @return bool Success status
     */
    public function updateProfile($userId, $profileData) {
        try {
            $profileModel = new UserProfile();
            return $profileModel->update($userId, $profileData);
        } catch (Exception $e) {
            error_log('Profile update error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate profile data
     * 
     * @param array $data Profile data
     * @return array Validation errors
     */
    public function validateProfileData($data) {
        $errors = [];
        
        // First name validation
        if (isset($data['first_name']) && strlen($data['first_name']) > 100) {
            $errors['first_name'] = 'First name must be less than 100 characters';
        }
        
        // Last name validation
        if (isset($data['last_name']) && strlen($data['last_name']) > 100) {
            $errors['last_name'] = 'Last name must be less than 100 characters';
        }
        
        // Display name validation
        if (isset($data['display_name']) && strlen($data['display_name']) > 150) {
            $errors['display_name'] = 'Display name must be less than 150 characters';
        }
        
        return $errors;
    }
    
    /**
     * Get user preferences
     * 
     * @param int $userId User ID
     * @return array User preferences
     */
    public function getPreferences($userId) {
        // This could include more preference settings
        return $this->userModel->getLanguagePreferences($userId);
    }
}

?>
