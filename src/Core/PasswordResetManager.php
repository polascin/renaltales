<?php

/**
 * PasswordResetManager - Password Reset Functionality
 * 
 * Manages password reset tokens and functionality for the Renal Tales application
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

require_once 'Database.php';

class PasswordResetManager {
    
    private $db;
    private $tokenLength = 64;
    private $tokenExpiry = 3600; // 1 hour in seconds
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Generate a secure random token
     * 
     * @param int $length
     * @return string
     */
    private function generateSecureToken($length = null) {
        $length = $length ?? $this->tokenLength;
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Hash token for secure storage
     * 
     * @param string $token
     * @return string
     */
    private function hashToken($token) {
        return hash('sha256', $token);
    }
    
    /**
     * Get client IP address
     * 
     * @return string
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
     * Get user agent
     * 
     * @return string
     */
    private function getUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }
    
    /**
     * Create a password reset token for a user
     * 
     * @param string $email
     * @return array|false Returns array with token info on success, false on failure
     */
    public function createPasswordResetToken($email) {
        try {
            // Check if user exists and is active
            $user = $this->db->selectOne(
                "SELECT id, email, status FROM users WHERE email = ? AND status = 'active'",
                [$email]
            );
            
            if (!$user) {
                // Don't reveal if email exists or not for security
                return false;
            }
            
            // Check for existing unexpired tokens (rate limiting)
            $existingToken = $this->db->selectOne(
                "SELECT id FROM password_resets 
                 WHERE user_id = ? AND expires_at > NOW() AND is_used = FALSE
                 ORDER BY created_at DESC LIMIT 1",
                [$user['id']]
            );
            
            if ($existingToken) {
                // Token already exists and is still valid
                // For security, don't create a new one immediately
                return false;
            }
            
            // Generate secure token
            $token = $this->generateSecureToken();
            $tokenHash = $this->hashToken($token);
            $expiresAt = date('Y-m-d H:i:s', time() + $this->tokenExpiry);
            
            // Insert token into database
            $tokenId = $this->db->insert(
                "INSERT INTO password_resets 
                 (user_id, email, token, token_hash, expires_at, ip_address, user_agent) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $user['id'],
                    $user['email'],
                    $token,
                    $tokenHash,
                    $expiresAt,
                    $this->getClientIP(),
                    $this->getUserAgent()
                ]
            );
            
            if ($tokenId) {
                return [
                    'token_id' => $tokenId,
                    'token' => $token,
                    'email' => $user['email'],
                    'expires_at' => $expiresAt,
                    'user_id' => $user['id']
                ];
            }
            
            return false;
            
        } catch(Exception $e) 
            error_log('Password reset token creation failed: ' . $e->getMessage());
            return false;
        
    }
    
    /**
     * Validate and retrieve password reset token information
     * 
     * @param string $token
     * @return array|false Returns token info on success, false on failure
     */
    public function validatePasswordResetToken($token) {
        try {
            if (empty($token)) {
                return false;
            }
            
            $tokenHash = $this->hashToken($token);
            
            // Find valid token
            $tokenInfo = $this->db->selectOne(
                "SELECT pr.*, u.email as user_email, u.status as user_status 
                 FROM password_resets pr
                 JOIN users u ON pr.user_id = u.id
                 WHERE pr.token = ? AND pr.token_hash = ? 
                 AND pr.expires_at > NOW() AND pr.is_used = FALSE
                 AND u.status = 'active'",
                [$token, $tokenHash]
            );
            
            return $tokenInfo ?: false;
            
        } catch(Exception $e) 
            error_log('Password reset token validation failed: ' . $e->getMessage());
            return false;
        
    }
    
    /**
     * Use password reset token to reset password
     * 
     * @param string $token
     * @param string $newPassword
     * @return bool
     */
    public function resetPassword($token, $newPassword) {
        try {
            // Validate token first
            $tokenInfo = $this->validatePasswordResetToken($token);
            if (!$tokenInfo) {
                return false;
            }
            
            // Validate password strength
            if (!$this->validatePasswordStrength($newPassword)) {
                return false;
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            // Hash the new password
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update user password
            $passwordUpdated = $this->db->update(
                "UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?",
                [$passwordHash, $tokenInfo['user_id']]
            );
            
            if ($passwordUpdated > 0) {
                // Mark token as used
                $tokenMarked = $this->db->update(
                    "UPDATE password_resets 
                     SET is_used = TRUE, used_at = NOW() 
                     WHERE id = ?",
                    [$tokenInfo['id']]
                );
                
                if ($tokenMarked > 0) {
                    // Invalidate all other tokens for this user
                    $this->db->update(
                        "UPDATE password_resets 
                         SET is_used = TRUE, used_at = NOW() 
                         WHERE user_id = ? AND id != ? AND is_used = FALSE",
                        [$tokenInfo['user_id'], $tokenInfo['id']]
                    );
                    
                    $this->db->commit();
                    return true;
                }
            }
            
            $this->db->rollback();
            return false;
            
        } catch(Exception $e) 
    error_log('Exception in PasswordResetManager.php: ' . $e->getMessage());
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            
            error_log('Password reset failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate password strength
     * 
     * @param string $password
     * @return bool
     */
    private function validatePasswordStrength($password) {
        // Basic password requirements
        if (strlen($password) < 8) {
            return false;
        }
        
        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        // Check for at least one digit
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        // Check for at least one special character
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Clean up expired tokens
     * 
     * @return int Number of tokens cleaned up
     */
    public function cleanupExpiredTokens() {
        try {
            $deleted = $this->db->update(
                "DELETE FROM password_resets 
                 WHERE expires_at < NOW() 
                 OR (is_used = TRUE AND used_at < DATE_SUB(NOW(), INTERVAL 7 DAY))"
            );
            
            return $deleted;
            
        } catch(Exception $e) 
            error_log('Password reset token cleanup failed: ' . $e->getMessage());
            return 0;
        
    }
    
    /**
     * Get password reset statistics
     * 
     * @return array
     */
    public function getPasswordResetStats() {
        try {
            $stats = [];
            
            // Total active tokens
            $activeTokens = $this->db->selectOne(
                "SELECT COUNT(*) as count FROM password_resets 
                 WHERE expires_at > NOW() AND is_used = FALSE"
            );
            $stats['active_tokens'] = $activeTokens['count'] ?? 0;
            
            // Tokens used in last 24 hours
            $recentResets = $this->db->selectOne(
                "SELECT COUNT(*) as count FROM password_resets 
                 WHERE used_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)"
            );
            $stats['recent_resets'] = $recentResets['count'] ?? 0;
            
            // Expired tokens
            $expiredTokens = $this->db->selectOne(
                "SELECT COUNT(*) as count FROM password_resets 
                 WHERE expires_at < NOW() AND is_used = FALSE"
            );
            $stats['expired_tokens'] = $expiredTokens['count'] ?? 0;
            
            return $stats;
            
        } catch(Exception $e) 
            error_log('Password reset stats retrieval failed: ' . $e->getMessage());
            return [];
        
    }
}
