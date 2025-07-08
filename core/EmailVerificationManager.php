<?php

/**
 * EmailVerificationManager - Email Verification Functionality
 * 
 * Manages email verification tokens and functionality for the Renal Tales application
 * 
 * @version 2025.v1.0test
 */

require_once 'Database.php';

class EmailVerificationManager {
    
    private $db;
    private $tokenLength = 64;
    private $tokenExpiry = 86400; // 24 hours in seconds
    
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
     * Create an email verification token for a user
     * 
     * @param string $email
     * @return array|false Returns array with token info on success, false on failure
     */
    public function createEmailVerificationToken($email, $verificationType = 'registration', $oldEmail = null) {
        try {
            // Check if user exists
            $user = $this->db->selectOne(
                "SELECT id, email, status FROM users WHERE email = ?",
                [$email]
            );
            
            if (!$user) {
                // Don't reveal if email exists or not for security
                return false;
            }
            
            // Check for existing unexpired tokens (rate limiting)
            $existingToken = $this->db->selectOne(
                "SELECT id FROM email_verifications 
                 WHERE user_id = ? AND expires_at > NOW() AND is_verified = FALSE
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
                "INSERT INTO email_verifications 
                 (user_id, email, token, token_hash, expires_at, ip_address, user_agent, verification_type, old_email) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $user['id'],
                    $user['email'],
                    $token,
                    $tokenHash,
                    $expiresAt,
                    $this->getClientIP(),
                    $this->getUserAgent(),
                    $verificationType,
                    $oldEmail
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
            
        } catch (Exception $e) {
            error_log('Email verification token creation failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate and retrieve email verification token information
     * 
     * @param string $token
     * @return array|false Returns token info on success, false on failure
     */
    public function validateEmailVerificationToken($token) {
        try {
            if (empty($token)) {
                return false;
            }
            
            $tokenHash = $this->hashToken($token);
            
            // Find valid token
            $tokenInfo = $this->db->selectOne(
                "SELECT ev.*, u.email as user_email, u.status as user_status 
                 FROM email_verifications ev
                 JOIN users u ON ev.user_id = u.id
                 WHERE ev.token = ? AND ev.token_hash = ? 
                 AND ev.expires_at > NOW() AND ev.is_verified = FALSE",
                [$token, $tokenHash]
            );
            
            return $tokenInfo ?: false;
            
        } catch (Exception $e) {
            error_log('Email verification token validation failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify email using token
     * 
     * @param string $token
     * @return bool
     */
    public function verifyEmail($token) {
        try {
            // Validate token first
            $tokenInfo = $this->validateEmailVerificationToken($token);
            if (!$tokenInfo) {
                return false;
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            // Update user's email verified status
            $emailUpdated = $this->db->update(
                "UPDATE users SET email_verified = TRUE, email_verified_at = NOW(), updated_at = NOW() WHERE id = ?",
                [$tokenInfo['user_id']]
            );
            
            if ($emailUpdated > 0) {
                // Mark token as verified
                $tokenMarked = $this->db->update(
                    "UPDATE email_verifications 
                     SET is_verified = TRUE, verified_at = NOW() 
                     WHERE id = ?",
                    [$tokenInfo['id']]
                );
                
                if ($tokenMarked > 0) {
                    $this->db->commit();
                    return true;
                }
            }
            
            $this->db->rollback();
            return false;
            
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            error_log('Email verification failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clean up expired tokens
     * 
     * @return int Number of tokens cleaned up
     */
    public function cleanupExpiredTokens() {
        try {
            $deleted = $this->db->update(
                "DELETE FROM email_verifications 
                 WHERE expires_at < NOW() 
                 OR (is_verified = TRUE AND verified_at < DATE_SUB(NOW(), INTERVAL 7 DAY))"
            );
            
            return $deleted;
            
        } catch (Exception $e) {
            error_log('Email verification token cleanup failed: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get email verification statistics
     * 
     * @return array
     */
    public function getEmailVerificationStats() {
        try {
            $stats = [];
            
            // Total pending verifications
            $pendingVerifications = $this->db->selectOne(
                "SELECT COUNT(*) as count FROM email_verifications 
                 WHERE expires_at > NOW() AND is_verified = FALSE"
            );
            $stats['pending_verifications'] = $pendingVerifications['count'] ?? 0;
            
            // Tokens verified in last 24 hours
            $recentVerifications = $this->db->selectOne(
                "SELECT COUNT(*) as count FROM email_verifications 
                 WHERE verified_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)"
            );
            $stats['recent_verifications'] = $recentVerifications['count'] ?? 0;
            
            // Expired tokens
            $expiredTokens = $this->db->selectOne(
                "SELECT COUNT(*) as count FROM email_verifications 
                 WHERE expires_at < NOW() AND is_verified = FALSE"
            );
            $stats['expired_tokens'] = $expiredTokens['count'] ?? 0;
            
            return $stats;
            
        } catch (Exception $e) {
            error_log('Email verification stats retrieval failed: ' . $e->getMessage());
            return [];
        }
    }
}

?>

