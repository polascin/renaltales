<?php

/**
 * RBACManager - Role-Based Access Control Manager
 * 
 * Manages user roles, permissions, and access control throughout the application
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

require_once __DIR__ . '/Database.php';

class RBACManager {
    
    private $db;
    private $userPermissions = [];
    private $rolePermissions = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Assign role to user
     * 
     * @param int $userId User ID
     * @param string $roleName Role name
     * @param int $assignedBy User ID who assigned the role
     * @param string $expiresAt Expiration date (optional)
     * @return bool Success status
     */
    public function assignRole($userId, $roleName, $assignedBy = null, $expiresAt = null) {
        try {
            // Get role ID
            $role = $this->getRoleByName($roleName);
            if (!$role) {
                return false;
            }
            
            // Check if user already has this role
            if ($this->hasRole($userId, $roleName)) {
                return true; // Already has role
            }
            
            // Insert user role
            $sql = "INSERT INTO user_roles (user_id, role_id, assigned_by, expires_at) 
                    VALUES (?, ?, ?, ?)";
            
            $result = $this->db->insert($sql, [
                $userId,
                $role['id'],
                $assignedBy,
                $expiresAt
            ]);
            
            // Clear permission cache for this user
            unset($this->userPermissions[$userId]);
            
            return $result !== false;
            
        } catch (Exception $e) {
            error_log('Role assignment error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove role from user
     * 
     * @param int $userId User ID
     * @param string $roleName Role name
     * @return bool Success status
     */
    public function removeRole($userId, $roleName) {
        try {
            // Get role ID
            $role = $this->getRoleByName($roleName);
            if (!$role) {
                return false;
            }
            
            // Delete user role
            $sql = "DELETE FROM user_roles WHERE user_id = ? AND role_id = ?";
            $result = $this->db->execute($sql, [$userId, $role['id']]);
            
            // Clear permission cache for this user
            unset($this->userPermissions[$userId]);
            
            return $result > 0;
            
        } catch (Exception $e) {
            error_log('Role removal error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user has specific role
     * 
     * @param int $userId User ID
     * @param string $roleName Role name
     * @return bool True if user has role
     */
    public function hasRole($userId, $roleName) {
        try {
            $sql = "SELECT COUNT(*) as count FROM user_roles ur
                    JOIN roles r ON ur.role_id = r.id
                    WHERE ur.user_id = ? AND r.name = ? AND ur.is_active = TRUE
                    AND (ur.expires_at IS NULL OR ur.expires_at > NOW())";
            
            $result = $this->db->selectOne($sql, [$userId, $roleName]);
            return ($result['count'] ?? 0) > 0;
            
        } catch (Exception $e) {
            error_log('Role check error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user has specific permission
     * 
     * @param int $userId User ID
     * @param string $permissionName Permission name
     * @return bool True if user has permission
     */
    public function hasPermission($userId, $permissionName) {
        try {
            // Get user permissions (with caching)
            $permissions = $this->getUserPermissions($userId);
            return in_array($permissionName, $permissions);
            
        } catch (Exception $e) {
            error_log('Permission check error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all permissions for a user
     * 
     * @param int $userId User ID
     * @return array Array of permission names
     */
    public function getUserPermissions($userId) {
        // Check cache first
        if (isset($this->userPermissions[$userId])) {
            return $this->userPermissions[$userId];
        }
        
        try {
            $sql = "SELECT DISTINCT p.name FROM permissions p
                    JOIN role_permissions rp ON p.id = rp.permission_id
                    JOIN roles r ON rp.role_id = r.id
                    JOIN user_roles ur ON r.id = ur.role_id
                    WHERE ur.user_id = ? AND ur.is_active = TRUE
                    AND (ur.expires_at IS NULL OR ur.expires_at > NOW())";
            
            $result = $this->db->select($sql, [$userId]);
            $permissions = array_column($result, 'name');
            
            // Cache the result
            $this->userPermissions[$userId] = $permissions;
            
            return $permissions;
            
        } catch (Exception $e) {
            error_log('Get user permissions error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all roles for a user
     * 
     * @param int $userId User ID
     * @return array Array of role data
     */
    public function getUserRoles($userId) {
        try {
            $sql = "SELECT r.*, ur.assigned_at, ur.expires_at, ur.is_active
                    FROM roles r
                    JOIN user_roles ur ON r.id = ur.role_id
                    WHERE ur.user_id = ? AND ur.is_active = TRUE
                    AND (ur.expires_at IS NULL OR ur.expires_at > NOW())
                    ORDER BY r.name";
            
            return $this->db->select($sql, [$userId]);
            
        } catch (Exception $e) {
            error_log('Get user roles error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get role by name
     * 
     * @param string $roleName Role name
     * @return array|false Role data or false
     */
    public function getRoleByName($roleName) {
        try {
            $sql = "SELECT * FROM roles WHERE name = ? LIMIT 1";
            return $this->db->selectOne($sql, [$roleName]);
            
        } catch (Exception $e) {
            error_log('Get role by name error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all roles
     * 
     * @return array Array of all roles
     */
    public function getAllRoles() {
        try {
            $sql = "SELECT * FROM roles ORDER BY name";
            return $this->db->select($sql);
            
        } catch (Exception $e) {
            error_log('Get all roles error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all permissions
     * 
     * @return array Array of all permissions
     */
    public function getAllPermissions() {
        try {
            $sql = "SELECT * FROM permissions ORDER BY category, name";
            return $this->db->select($sql);
            
        } catch (Exception $e) {
            error_log('Get all permissions error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get permissions for a role
     * 
     * @param string $roleName Role name
     * @return array Array of permission names
     */
    public function getRolePermissions($roleName) {
        // Check cache first
        if (isset($this->rolePermissions[$roleName])) {
            return $this->rolePermissions[$roleName];
        }
        
        try {
            $sql = "SELECT p.name FROM permissions p
                    JOIN role_permissions rp ON p.id = rp.permission_id
                    JOIN roles r ON rp.role_id = r.id
                    WHERE r.name = ?
                    ORDER BY p.name";
            
            $result = $this->db->select($sql, [$roleName]);
            $permissions = array_column($result, 'name');
            
            // Cache the result
            $this->rolePermissions[$roleName] = $permissions;
            
            return $permissions;
            
        } catch (Exception $e) {
            error_log('Get role permissions error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create new role
     * 
     * @param string $name Role name
     * @param string $displayName Display name
     * @param string $description Description
     * @param array $permissions Array of permission names
     * @return bool Success status
     */
    public function createRole($name, $displayName, $description = null, $permissions = []) {
        try {
            $this->db->beginTransaction();
            
            // Insert role
            $sql = "INSERT INTO roles (name, display_name, description) VALUES (?, ?, ?)";
            $roleId = $this->db->insert($sql, [$name, $displayName, $description]);
            
            if (!$roleId) {
                $this->db->rollback();
                return false;
            }
            
            // Assign permissions to role
            if (!empty($permissions)) {
                foreach ($permissions as $permissionName) {
                    $permission = $this->getPermissionByName($permissionName);
                    if ($permission) {
                        $permSql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)";
                        $this->db->insert($permSql, [$roleId, $permission['id']]);
                    }
                }
            }
            
            $this->db->commit();
            
            // Clear cache
            unset($this->rolePermissions[$name]);
            
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Create role error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update role permissions
     * 
     * @param string $roleName Role name
     * @param array $permissions Array of permission names
     * @return bool Success status
     */
    public function updateRolePermissions($roleName, $permissions) {
        try {
            $role = $this->getRoleByName($roleName);
            if (!$role) {
                return false;
            }
            
            $this->db->beginTransaction();
            
            // Remove existing permissions
            $sql = "DELETE FROM role_permissions WHERE role_id = ?";
            $this->db->execute($sql, [$role['id']]);
            
            // Add new permissions
            foreach ($permissions as $permissionName) {
                $permission = $this->getPermissionByName($permissionName);
                if ($permission) {
                    $permSql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)";
                    $this->db->insert($permSql, [$role['id'], $permission['id']]);
                }
            }
            
            $this->db->commit();
            
            // Clear cache
            unset($this->rolePermissions[$roleName]);
            
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Update role permissions error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get permission by name
     * 
     * @param string $permissionName Permission name
     * @return array|false Permission data or false
     */
    public function getPermissionByName($permissionName) {
        try {
            $sql = "SELECT * FROM permissions WHERE name = ? LIMIT 1";
            return $this->db->selectOne($sql, [$permissionName]);
            
        } catch (Exception $e) {
            error_log('Get permission by name error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user is admin
     * 
     * @param int $userId User ID
     * @return bool True if user is admin
     */
    public function isAdmin($userId) {
        return $this->hasRole($userId, 'admin');
    }
    
    /**
     * Check if user is moderator
     * 
     * @param int $userId User ID
     * @return bool True if user is moderator
     */
    public function isModerator($userId) {
        return $this->hasRole($userId, 'moderator');
    }
    
    /**
     * Assign default role to user
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function assignDefaultRole($userId) {
        try {
            // Get default role
            $sql = "SELECT * FROM roles WHERE is_default = TRUE LIMIT 1";
            $defaultRole = $this->db->selectOne($sql);
            
            if (!$defaultRole) {
                // Fallback to 'user' role
                $defaultRole = $this->getRoleByName('user');
            }
            
            if ($defaultRole) {
                return $this->assignRole($userId, $defaultRole['name']);
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log('Assign default role error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get users with specific role
     * 
     * @param string $roleName Role name
     * @return array Array of user data
     */
    public function getUsersWithRole($roleName) {
        try {
            $sql = "SELECT u.*, ur.assigned_at, ur.expires_at
                    FROM users_new u
                    JOIN user_roles ur ON u.id = ur.user_id
                    JOIN roles r ON ur.role_id = r.id
                    WHERE r.name = ? AND ur.is_active = TRUE
                    AND (ur.expires_at IS NULL OR ur.expires_at > NOW())
                    ORDER BY u.username";
            
            return $this->db->select($sql, [$roleName]);
            
        } catch (Exception $e) {
            error_log('Get users with role error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Clean expired roles
     * 
     * @return int Number of expired roles cleaned
     */
    public function cleanExpiredRoles() {
        try {
            $sql = "UPDATE user_roles SET is_active = FALSE 
                    WHERE expires_at IS NOT NULL AND expires_at < NOW() AND is_active = TRUE";
            
            return $this->db->execute($sql);
            
        } catch (Exception $e) {
            error_log('Clean expired roles error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Middleware for checking permissions
     * 
     * @param int $userId User ID
     * @param string $permission Permission name
     * @param callable $callback Callback to execute if permission granted
     * @param callable $failCallback Callback to execute if permission denied
     * @return mixed Result of callback execution
     */
    public function requirePermission($userId, $permission, $callback = null, $failCallback = null) {
        if ($this->hasPermission($userId, $permission)) {
            return $callback ? $callback() : true;
        } else {
            return $failCallback ? $failCallback() : false;
        }
    }
    
    /**
     * Middleware for checking roles
     * 
     * @param int $userId User ID
     * @param string $role Role name
     * @param callable $callback Callback to execute if role granted
     * @param callable $failCallback Callback to execute if role denied
     * @return mixed Result of callback execution
     */
    public function requireRole($userId, $role, $callback = null, $failCallback = null) {
        if ($this->hasRole($userId, $role)) {
            return $callback ? $callback() : true;
        } else {
            return $failCallback ? $failCallback() : false;
        }
    }
    
    /**
     * Clear all permission caches
     */
    public function clearCache() {
        $this->userPermissions = [];
        $this->rolePermissions = [];
    }
}
