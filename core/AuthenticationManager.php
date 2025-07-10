<?php

/**
 * AuthenticationManager - Comprehensive Authentication System
 * 
 * Handles user authentication, password hashing, session management, and security features
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/SessionManager.php';
require_once __DIR__ . '/TwoFactorAuthManager.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/SecurityEvent.php';

class AuthenticationManager {
    
    private $db;
    private $sessionManager;
    private $twoFactorManager;
    private $maxLoginAttempts = 5;
    private $lockoutDuration = 1800; // 30 minutes in seconds
    private $sessionTimeout = 3600; // 1 hour
    
    public function __construct($sessionManager = null) {
        $this->db = Database::getInstance();
        $this->sessionManager = $sessionManager ?: new SessionManager();
        $this->twoFactorManager = new TwoFactorAuthManager();
    }
    
    /**
     * Authenticate user with email/username and password
     * 
     * @param string $identifier Email or username
     * @param string $password Plain text password
     * @param string $ipAddress Client IP address
     * @return array|false Authentication result or false on failure
     */
    public function authenticate($identifier, $password, $ipAddress = null) {
        $ipAddress = $ipAddress ?: $this->getClientIP();
        
        try {
            // Check for brute force attacks
            if ($this->isIPBlocked($ipAddress)) {
                $this->logSecurityEvent(null, 'login_blocked', $ipAddress, 'IP address blocked due to multiple failed attempts');
                return [
                    'success' => false,
                    'message' => 'Too many failed login attempts. Please try again later.',
                    'blocked' => true
                ];
            }
            
            // Find user by email or username
            $user = $this->findUser($identifier);
            
            if (!$user) {
                $this->handleFailedLogin(null, $ipAddress, 'Invalid credentials');
                return [
                    'success' => false,
                    'message' => 'Invalid email/username or password.'
                ];
            }
            
            // Check if account is locked
            if ($this->isAccountLocked($user['id'])) {
                $this->logSecurityEvent($user['id'], 'login_blocked', $ipAddress, 'Account locked due to multiple failed attempts');
                return [
                    'success' => false,
                    'message' => 'Account is temporarily locked. Please try again later.',
                    'locked' => true
                ];
            }
            
            // Check if account is active (check email_verified_at instead of status)
            if (!$user['email_verified_at']) {
                $this->logSecurityEvent($user['id'], 'login_blocked', $ipAddress, 'Unverified account login attempt');
                return [
                    'success' => false,
                    'message' => 'Account is not verified. Please check your email for verification link.'
                ];
            }
            
            // Verify password
            if (!$this->verifyPassword($password, $user['password_hash'])) {
                $this->handleFailedLogin($user['id'], $ipAddress, 'Invalid password');
                return [
                    'success' => false,
                    'message' => 'Invalid email/username or password.'
                ];
            }
            
            // Check for two-factor authentication
            if ($this->isTwoFactorEnabled($user['id'])) {
                return [
                    'success' => false,
                    'message' => 'Two-factor authentication required.',
                    'requires_2fa' => true,
                    'user_id' => $user['id']
                ];
            }
            
            // Authentication successful
            $this->handleSuccessfulLogin($user, $ipAddress);
            
            return [
                'success' => true,
                'user' => $this->sanitizeUserData($user),
                'session_token' => $this->generateSessionToken($user['id'])
            ];
            
        } catch (Exception $e) {
            error_log('Authentication error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Authentication system error. Please try again.'
            ];
        }
    }
    
    /**
     * Register new user with secure password hashing
     * 
     * @param array $userData User registration data
     * @return array Registration result
     */
    public function register($userData) {
        try {
            // Validate required fields
            $requiredFields = ['username', 'email', 'password'];
            foreach ($requiredFields as $field) {
                if (empty($userData[$field])) {
                    return [
                        'success' => false,
                        'message' => "Field '{$field}' is required."
                    ];
                }
            }
            
            // Validate password strength
            $passwordValidation = $this->validatePasswordStrength($userData['password']);
            if (!$passwordValidation['valid']) {
                return [
                    'success' => false,
                    'message' => $passwordValidation['message']
                ];
            }
            
            // Check if user already exists
            if ($this->userExists($userData['email'], $userData['username'])) {
                return [
                    'success' => false,
                    'message' => 'User with this email or username already exists.'
                ];
            }
            
            // Hash password securely
            $hashedPassword = $this->hashPassword($userData['password']);
            
            // Prepare user data
            $userRecord = [
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password_hash' => $hashedPassword,
                'status' => 'active',
                'email_verified' => false,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Create user with profile
            $userModel = new User();
            $userId = $userModel->createWithProfile($userRecord, $userData['profile'] ?? []);
            
            if ($userId) {
                // Log registration event
                $this->logSecurityEvent($userId, 'user_registered', $this->getClientIP(), 'User registered successfully');
                
                return [
                    'success' => true,
                    'message' => 'User registered successfully.',
                    'user_id' => $userId
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ];
            
        } catch (Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Registration system error. Please try again.'
            ];
        }
    }
    
    /**
     * Change user password with security checks
     * 
     * @param int $userId User ID
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return array Result of password change
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Get user data
            $userModel = new User();
            $user = $userModel->find($userId);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found.'
                ];
            }
            
            // Verify current password
            if (!$this->verifyPassword($currentPassword, $user['password_hash'])) {
                $this->logSecurityEvent($userId, 'password_change_failed', $this->getClientIP(), 'Invalid current password');
                return [
                    'success' => false,
                    'message' => 'Current password is incorrect.'
                ];
            }
            
            // Validate new password strength
            $passwordValidation = $this->validatePasswordStrength($newPassword);
            if (!$passwordValidation['valid']) {
                return [
                    'success' => false,
                    'message' => $passwordValidation['message']
                ];
            }
            
            // Check if new password is different from current
            if ($this->verifyPassword($newPassword, $user['password_hash'])) {
                return [
                    'success' => false,
                    'message' => 'New password must be different from current password.'
                ];
            }
            
            // Hash new password
            $hashedPassword = $this->hashPassword($newPassword);
            
            // Update password
            $updated = $userModel->update($userId, [
                'password_hash' => $hashedPassword,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($updated) {
                // Log password change event
                $this->logSecurityEvent($userId, 'password_changed', $this->getClientIP(), 'Password changed successfully');
                
                // Invalidate all sessions for this user
                $this->invalidateAllUserSessions($userId);
                
                return [
                    'success' => true,
                    'message' => 'Password changed successfully.'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to change password.'
            ];
            
        } catch (Exception $e) {
            error_log('Password change error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Password change system error. Please try again.'
            ];
        }
    }
    
    /**
     * Hash password using secure algorithm
     * 
     * @param string $password Plain text password
     * @return string Hashed password
     */
    private function hashPassword($password) {
        // Use Argon2ID if available, otherwise fall back to bcrypt
        if (defined('PASSWORD_ARGON2ID')) {
            return password_hash($password, PASSWORD_ARGON2ID, [
                'memory_cost' => 65536, // 64 MB
                'time_cost' => 4,       // 4 iterations
                'threads' => 3,         // 3 threads
            ]);
        } else {
            return password_hash($password, PASSWORD_BCRYPT, [
                'cost' => 12
            ]);
        }
    }
    
    /**
     * Verify password against hash
     * 
     * @param string $password Plain text password
     * @param string $hash Stored password hash
     * @return bool True if password matches
     */
    private function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Validate password strength
     * 
     * @param string $password Password to validate
     * @return array Validation result
     */
    private function validatePasswordStrength($password) {
        $errors = [];
        
        // Minimum length
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        // Maximum length
        if (strlen($password) > 128) {
            $errors[] = 'Password must be less than 128 characters long';
        }
        
        // Must contain uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        // Must contain lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        // Must contain digit
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one digit';
        }
        
        // Must contain special character
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        // Check for common passwords
        if ($this->isCommonPassword($password)) {
            $errors[] = 'Password is too common. Please choose a more unique password';
        }
        
        return [
            'valid' => empty($errors),
            'message' => implode('. ', $errors)
        ];
    }
    
    /**
     * Check if password is commonly used
     * 
     * @param string $password Password to check
     * @return bool True if password is common
     */
    private function isCommonPassword($password) {
        $commonPasswords = [
            'password', '123456', '123456789', 'qwerty', 'abc123',
            'password123', 'admin', 'letmein', 'welcome', 'monkey',
            'dragon', 'master', 'shadow', 'football', 'baseball'
        ];
        
        return in_array(strtolower($password), $commonPasswords);
    }
    
    /**
     * Find user by email or username
     * 
     * @param string $identifier Email or username
     * @return array|false User data or false
     */
    private function findUser($identifier) {
        $userModel = new User();
        
        // Try to find by email first
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return $userModel->findByEmail($identifier);
        }
        
        // Try to find by username
        return $userModel->findByUsername($identifier);
    }
    
    /**
     * Check if user exists by email or username
     * 
     * @param string $email Email address
     * @param string $username Username
     * @return bool True if user exists
     */
    private function userExists($email, $username) {
        $userModel = new User();
        
        return $userModel->findByEmail($email) || $userModel->findByUsername($username);
    }
    
    /**
     * Handle successful login
     * 
     * @param array $user User data
     * @param string $ipAddress Client IP address
     */
    private function handleSuccessfulLogin($user, $ipAddress) {
        $userModel = new User();
        
        // Update last login
        $userModel->updateLastLogin($user['id'], $ipAddress);
        
        // Log successful login
        $this->logSecurityEvent($user['id'], 'login_success', $ipAddress, 'Successful login');
        
        // Clear failed attempts for this IP
        $this->clearFailedAttempts($ipAddress);
        
        // Set session data
        $this->sessionManager->setSession('user_id', $user['id']);
        $this->sessionManager->setSession('user_email', $user['email']);
        $this->sessionManager->setSession('user_username', $user['username']);
        $this->sessionManager->setSession('login_time', time());
    }
    
    /**
     * Handle failed login attempt
     * 
     * @param int|null $userId User ID if known
     * @param string $ipAddress Client IP address
     * @param string $reason Failure reason
     */
    private function handleFailedLogin($userId, $ipAddress, $reason) {
        // Log failed login
        $this->logSecurityEvent($userId, 'login_failure', $ipAddress, $reason);
        
        // Increment failed attempts for IP
        $this->incrementFailedAttempts($ipAddress);
        
        // If user is known, increment user's failed login count
        if ($userId) {
            $userModel = new User();
            $userModel->incrementFailedLogin($userId);
        }
    }
    
    /**
     * Check if IP address is blocked
     * 
     * @param string $ipAddress IP address to check
     * @return bool True if blocked
     */
    private function isIPBlocked($ipAddress) {
        $sql = "SELECT COUNT(*) as count FROM security_events 
                WHERE ip_address = ? AND event_type = 'login_failure' 
                AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        
        $result = $this->db->selectOne($sql, [$ipAddress]);
        
        return ($result['count'] ?? 0) >= $this->maxLoginAttempts;
    }
    
    /**
     * Check if user account is locked
     * 
     * @param int $userId User ID
     * @return bool True if locked
     */
    private function isAccountLocked($userId) {
        $userModel = new User();
        return $userModel->isLocked($userId);
    }
    
    /**
     * Increment failed login attempts for IP
     * 
     * @param string $ipAddress IP address
     */
    private function incrementFailedAttempts($ipAddress) {
        // This is handled by the security events logging
        // Additional rate limiting logic can be added here
    }
    
    /**
     * Clear failed login attempts for IP
     * 
     * @param string $ipAddress IP address
     */
    private function clearFailedAttempts($ipAddress) {
        // Remove recent failed attempts for this IP
        $sql = "DELETE FROM security_events 
                WHERE ip_address = ? AND event_type = 'login_failure' 
                AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        
        $this->db->execute($sql, [$ipAddress]);
    }
    
    /**
     * Log security event
     * 
     * @param int|null $userId User ID
     * @param string $eventType Event type
     * @param string $ipAddress IP address
     * @param string $description Event description
     */
    private function logSecurityEvent($userId, $eventType, $ipAddress, $description) {
        $securityEvent = new SecurityEvent();
        $securityEvent->create([
            'user_id' => $userId,
            'event_type' => $eventType,
            'ip_address' => $ipAddress,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'failure_reason' => $description,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Generate session token
     * 
     * @param int $userId User ID
     * @return string Session token
     */
    private function generateSessionToken($userId) {
        $tokenData = [
            'user_id' => $userId,
            'issued_at' => time(),
            'expires_at' => time() + $this->sessionTimeout,
            'session_id' => session_id()
        ];
        
        return base64_encode(json_encode($tokenData));
    }
    
    /**
     * Validate session token
     * 
     * @param string $token Session token
     * @return array|false Token data or false
     */
    public function validateSessionToken($token) {
        try {
            $tokenData = json_decode(base64_decode($token), true);
            
            if (!$tokenData || !isset($tokenData['expires_at'])) {
                return false;
            }
            
            if (time() > $tokenData['expires_at']) {
                return false;
            }
            
            return $tokenData;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Invalidate all sessions for a user
     * 
     * @param int $userId User ID
     */
    private function invalidateAllUserSessions($userId) {
        $sql = "DELETE FROM user_sessions_new WHERE user_id = ?";
        $this->db->execute($sql, [$userId]);
    }
    
    /**
     * Check if two-factor authentication is enabled
     * 
     * @param int $userId User ID
     * @return bool True if enabled
     */
    private function isTwoFactorEnabled($userId) {
        return $this->twoFactorManager->is2FAEnabled($userId);
    }
    
    /**
     * Get client IP address
     * 
     * @return string Client IP address
     */
    private function getClientIP() {
        $ipHeaders = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Sanitize user data for output
     * 
     * @param array $user User data
     * @return array Sanitized user data
     */
    private function sanitizeUserData($user) {
        unset($user['password_hash']);
        return $user;
    }
    
    /**
     * Logout user
     * 
     * @param int $userId User ID
     */
    public function logout($userId = null) {
        if ($userId) {
            $this->logSecurityEvent($userId, 'logout', $this->getClientIP(), 'User logged out');
        }
        
        // Clear session data
        $this->sessionManager->clearSession();
        
        // Destroy session
        $this->sessionManager->destroySession();
    }
    
    /**
     * Get current authenticated user
     * 
     * @return array|false User data or false
     */
    public function getCurrentUser() {
        $userId = $this->sessionManager->getSession('user_id');
        
        if (!$userId) {
            return false;
        }
        
        $userModel = new User();
        $user = $userModel->findWithProfile($userId);
        
        return $user ? $this->sanitizeUserData($user) : false;
    }
    
    /**
     * Check if user is authenticated
     * 
     * @return bool True if authenticated
     */
    public function isAuthenticated() {
        return $this->sessionManager->getSession('user_id') !== null;
    }
    
    /**
     * Complete 2FA authentication
     * 
     * @param int $userId User ID from initial authentication
     * @param string $code 2FA code
     * @param string $ipAddress Client IP address
     * @return array Authentication result
     */
    public function complete2FAAuthentication($userId, $code, $ipAddress = null) {
        $ipAddress = $ipAddress ?: $this->getClientIP();
        
        try {
            // Verify 2FA code
            if (!$this->twoFactorManager->verify2FACode($userId, $code)) {
                $this->logSecurityEvent($userId, 'login_failure', $ipAddress, '2FA code verification failed');
                return [
                    'success' => false,
                    'message' => 'Invalid 2FA code.'
                ];
            }
            
            // Get user data
            $userModel = new User();
            $user = $userModel->find($userId);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found.'
                ];
            }
            
            // Complete authentication
            $this->handleSuccessfulLogin($user, $ipAddress);
            
            return [
                'success' => true,
                'user' => $this->sanitizeUserData($user),
                'session_token' => $this->generateSessionToken($user['id'])
            ];
            
        } catch (Exception $e) {
            error_log('2FA authentication error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => '2FA authentication system error. Please try again.'
            ];
        }
    }
    
    /**
     * Get 2FA manager instance
     * 
     * @return TwoFactorAuthManager
     */
    public function getTwoFactorManager() {
        return $this->twoFactorManager;
    }
    
    /**
     * Store remember me token for user
     * 
     * @param int $userId User ID
     * @param string $token Remember me token
     * @param int $expires Expiration timestamp
     * @return bool Success status
     */
    public function storeRememberToken(int $userId, string $token, int $expires): bool {
        try {
            $hashedToken = hash('sha256', $token);
            
            // First, clean up expired tokens
            $this->cleanupExpiredRememberTokens();
            
            // Store the new token
            $sql = "
                INSERT INTO remember_tokens (user_id, token_hash, expires_at, created_at) 
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                    token_hash = VALUES(token_hash),
                    expires_at = VALUES(expires_at),
                    created_at = NOW()
            ";
            
            $stmt = $this->db->execute($sql, [
                $userId,
                $hashedToken,
                date('Y-m-d H:i:s', $expires)
            ]);
            
            return $stmt !== false;
            
        } catch (Exception $e) {
            error_log('Error storing remember token: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify remember me token
     * 
     * @param string $token Remember me token
     * @return array|false User data or false if invalid
     */
    public function verifyRememberToken(string $token) {
        try {
            $hashedToken = hash('sha256', $token);
            
            $sql = "
                SELECT u.*, rt.expires_at 
                FROM remember_tokens rt 
                JOIN users u ON rt.user_id = u.id 
                WHERE rt.token_hash = ? AND rt.expires_at > NOW() AND u.is_active = 1
            ";
            
            $result = $this->db->selectOne($sql, [$hashedToken]);
            
            if ($result) {
                // Update token expiration
                $newExpires = time() + (30 * 24 * 60 * 60); // 30 days
                $this->storeRememberToken($result['id'], $token, $newExpires);
                
                return $result;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log('Error verifying remember token: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clean up expired remember tokens
     */
    private function cleanupExpiredRememberTokens(): void {
        try {
            $sql = "DELETE FROM remember_tokens WHERE expires_at < NOW()";
            $this->db->execute($sql);
        } catch (Exception $e) {
            error_log('Error cleaning up expired remember tokens: ' . $e->getMessage());
        }
    }
}

?>
