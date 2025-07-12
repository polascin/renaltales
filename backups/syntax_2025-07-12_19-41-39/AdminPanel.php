<?php

/**
 * AdminPanel - User Administration
 * 
 * Provides admin functionalities to manage users, roles, and permissions
 * 
 * @version 2025.v1.0
 */

require_once __DIR__ . '/RBACManager.php';
require_once __DIR__ . '/../models/User.php';

class AdminPanel {
    
    private $rbacManager;
    
    public function __construct() {
        $this->rbacManager = new RBACManager();
    }
    
    /**
     * List all users with roles
     * 
     * @return array Array of users with roles
     */
    public function listUsers() {
        $userModel = new User();
        return $userModel->allWithRoles();
    }
    
    /**
     * Assign role to user
     * 
     * @param int $userId User ID
     * @param string $roleName Role name
     * @return bool Success status
     */
    public function assignUserRole($userId, $roleName) {
        return $this->rbacManager->assignRole($userId, $roleName);
    }
    
    /**
     * Remove role from user
     * 
     * @param int $userId User ID
     * @param string $roleName Role name
     * @return bool Success status
     */
    public function removeUserRole($userId, $roleName) {
        return $this->rbacManager->removeRole($userId, $roleName);
    }
    
    /**
     * List all roles
     * 
     * @return array Array of roles
     */
    public function listRoles() {
        return $this->rbacManager->getAllRoles();
    }
    
    /**
     * List all permissions
     * 
     * @return array Array of permissions
     */
    public function listPermissions() {
        return $this->rbacManager->getAllPermissions();
    }
}
