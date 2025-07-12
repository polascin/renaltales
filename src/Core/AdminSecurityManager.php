<?php

declare(strict_types=1);

namespace RenalTales\Core;

use RenalTales\Core\AuthenticationManager;
use RenalTales\Core\SessionManager;
use RenalTales\Core\SecurityManager;
use RenalTales\Core\RBACManager;
use RenalTales\Core\Database;
use RenalTales\Models\SecurityEvent;
use RenalTales\Models\AdminSession;
use Exception;

/**
 * Enhanced Admin Security Manager
 * 
 * Provides advanced security features for admin interface including:
 * - Enhanced session regeneration
 * - Admin-specific authentication
 * - Security monitoring and alerting
 * - Advanced audit logging
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

class AdminSecurityManager {
    
    private AuthenticationManager $authManager;
    private SessionManager $sessionManager;
    private SecurityManager $securityManager;
    private RBACManager $rbacManager;
    private Database $db;
    
    // Admin security settings
    private int $adminSessionTimeout = 3600; // 1 hour
    private int $adminSessionRegenerationInterval = 180; // 3 minutes
    private int $maxConcurrentAdminSessions = 2;
    private int $adminPasswordComplexity = 4; // Highest level
    private bool $requireAdminTwoFactor = true;
    private bool $adminIPWhitelisting = false;
    private array $allowedAdminIPs = [];
    
    public function __construct() {
        $this->authManager = new AuthenticationManager();
        $this->sessionManager = new SessionManager();
        $this->securityManager = new SecurityManager();
        $this->rbacManager = new RBACManager();
        $this->db = Database::getInstance();
        
        $this->initializeAdminSecurity();
    }
    
    /**
     * Initialize admin-specific security measures
     */
    private function initializeAdminSecurity(): void {
        // Load admin security configuration
        $this->loadAdminSecurityConfig();
        
        // Set admin session parameters
        $this->configureAdminSession();
        
        // Initialize monitoring
        $this->initializeSecurityMonitoring();
    }
    
    /**
     * Load admin security configuration
     */
    private function loadAdminSecurityConfig(): void {
        $config = include __DIR__ . '/../config/security.php';
        
        if (isset($config['admin_security'])) {
            $adminConfig = $config['admin_security'];
            
            $this->adminSessionTimeout = $adminConfig['session_timeout'] ?? $this->adminSessionTimeout;
            $this->adminSessionRegenerationInterval = $adminConfig['regeneration_interval'] ?? $this->adminSessionRegenerationInterval;
            $this->maxConcurrentAdminSessions = $adminConfig['max_concurrent_sessions'] ?? $this->maxConcurrentAdminSessions;
            $this->requireAdminTwoFactor = $adminConfig['require_2fa'] ?? $this->requireAdminTwoFactor;
            $this->adminIPWhitelisting = $adminConfig['ip_whitelisting'] ?? $this->adminIPWhitelisting;
            $this->allowedAdminIPs = $adminConfig['allowed_ips'] ?? $this->allowedAdminIPs;
        }
    }
    
    /**
     * Configure admin session parameters
     */
    private function configureAdminSession(): void {
        // Set stricter cookie parameters for admin sessions
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0, // Browser session only
                'path' => '/admin/',
                'domain' => $_SERVER['HTTP_HOST'] ?? '',
                'secure' => $this->isHttps(),
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }
    }
    
    /**
     * Authenticate admin user with enhanced security
     * 
     * @param string $identifier Email or username
     * @param string $password Password
     * @param string $twoFactorCode Optional 2FA code
     * @return array Authentication result
     */
    public function authenticateAdmin(string $identifier, string $password, string $twoFactorCode = ''): array {
        $ipAddress = $this->getClientIP();
        
        try {
            // Check IP whitelist if enabled
            if ($this->adminIPWhitelisting && !$this->isAllowedAdminIP($ipAddress)) {
                $this->logSecurityEvent(null, 'admin_access_denied', $ipAddress, 'Admin access from non-whitelisted IP');
                return [
                    'success' => false,
                    'message' => 'Admin access not allowed from this IP address.'
                ];
            }
            
            // Perform standard authentication
            $authResult = $this->authManager->authenticate($identifier, $password, $ipAddress);
            
            if (!$authResult['success']) {
                return $authResult;
            }
            
            $user = $authResult['user'];
            
            // Verify admin privileges
            if (!$this->rbacManager->isAdmin($user['id'])) {
                $this->logSecurityEvent($user['id'], 'unauthorized_admin_access', $ipAddress, 'Non-admin user attempted admin login');
                return [
                    'success' => false,
                    'message' => 'Admin privileges required.'
                ];
            }
            
            // Check for 2FA requirement
            if ($this->requireAdminTwoFactor) {
                if (empty($twoFactorCode)) {
                    return [
                        'success' => false,
                        'message' => 'Two-factor authentication required.',
                        'requires_2fa' => true,
                        'user_id' => $user['id']
                    ];
                }
                
                // Verify 2FA code
                $twoFactorResult = $this->authManager->complete2FAAuthentication($user['id'], $twoFactorCode, $ipAddress);
                if (!$twoFactorResult['success']) {
                    return $twoFactorResult;
                }
            }
            
            // Check concurrent sessions
            if (!$this->checkConcurrentSessions($user['id'])) {
                $this->logSecurityEvent($user['id'], 'max_sessions_exceeded', $ipAddress, 'Maximum concurrent admin sessions exceeded');
                return [
                    'success' => false,
                    'message' => 'Maximum number of concurrent admin sessions exceeded.'
                ];
            }
            
            // Create admin session
            $sessionResult = $this->createAdminSession($user['id'], $ipAddress);
            
            if ($sessionResult['success']) {
                $this->logSecurityEvent($user['id'], 'admin_login_success', $ipAddress, 'Admin login successful');
                
                return [
                    'success' => true,
                    'user' => $user,
                    'session_id' => $sessionResult['session_id']
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to create admin session.'
            ];
            
        } catch(Exception $e) 
            error_log('Admin authentication error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Admin authentication system error.'
            ];
        
    }
    
    /**
     * Create admin session with enhanced security tracking
     * 
     * @param int $userId User ID
     * @param string $ipAddress IP address
     * @return array Session creation result
     */
    private function createAdminSession(int $userId, string $ipAddress): array {
        try {
            // Generate secure session token
            $sessionToken = bin2hex(random_bytes(32));
            $sessionId = session_id();
            
            // Store admin session in database
            $adminSession = new AdminSession();
            $sessionData = [
                'user_id' => $userId,
                'session_id' => $sessionId,
                'session_token' => hash('sha256', $sessionToken),
                'ip_address' => $ipAddress,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => date('Y-m-d H:i:s'),
                'last_activity' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', time() + $this->adminSessionTimeout)
            ];
            
            $recordId = $adminSession->create($sessionData);
            
            if ($recordId) {
                // Set session variables
                $_SESSION['admin_user_id'] = $userId;
                $_SESSION['admin_session_token'] = $sessionToken;
                $_SESSION['admin_session_start'] = time();
                $_SESSION['admin_last_regeneration'] = time();
                
                // Force immediate session regeneration for admin
                $this->regenerateAdminSession();
                
                return [
                    'success' => true,
                    'session_id' => $sessionId
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to create session record.'
            ];
            
        } catch(Exception $e) 
            error_log('Admin session creation error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Session creation failed.'
            ];
        
    }
    
    /**
     * Enhanced session regeneration for admin sessions
     */
    public function regenerateAdminSession(): void {
        try {
            if (session_status() === PHP_SESSION_ACTIVE) {
                // Store current session data
                $adminUserId = $_SESSION['admin_user_id'] ?? null;
                $sessionToken = $_SESSION['admin_session_token'] ?? null;
                
                // Regenerate session ID
                session_regenerate_id(true);
                
                // Update session tracking
                if ($adminUserId && $sessionToken) {
                    $this->updateAdminSessionRecord($adminUserId, $sessionToken, session_id());
                }
                
                // Update regeneration timestamp
                $_SESSION['admin_last_regeneration'] = time();
                
                $this->logSecurityEvent($adminUserId, 'admin_session_regenerated', $this->getClientIP(), 'Admin session ID regenerated');
            }
        } catch(Exception $e) 
            error_log('Admin session regeneration failed: ' . $e->getMessage());
        
    }
    
    /**
     * Update admin session record with new session ID
     * 
     * @param int $userId User ID
     * @param string $sessionToken Session token
     * @param string $newSessionId New session ID
     */
    private function updateAdminSessionRecord(int $userId, string $sessionToken, string $newSessionId): void {
        try {
            $adminSession = new AdminSession();
            $hashedToken = hash('sha256', $sessionToken);
            
            $adminSession->updateByToken($hashedToken, [
                'session_id' => $newSessionId,
                'last_activity' => date('Y-m-d H:i:s')
            ]);
        } catch(Exception $e) 
            error_log('Failed to update admin session record: ' . $e->getMessage());
        
    }
    
    /**
     * Validate admin session and perform security checks
     * 
     * @return bool True if session is valid
     */
    public function validateAdminSession(): bool {
        try {
            // Check if admin session exists
            if (!isset($_SESSION['admin_user_id']) || !isset($_SESSION['admin_session_token'])) {
                return false;
            }
            
            $userId = $_SESSION['admin_user_id'];
            $sessionToken = $_SESSION['admin_session_token'];
            
            // Validate session in database
            $adminSession = new AdminSession();
            $sessionRecord = $adminSession->findByUserAndToken($userId, hash('sha256', $sessionToken));
            
            if (!$sessionRecord) {
                $this->logSecurityEvent($userId, 'invalid_admin_session', $this->getClientIP(), 'Invalid admin session token');
                return false;
            }
            
            // Check session expiration
            if (strtotime($sessionRecord['expires_at']) < time()) {
                $this->logSecurityEvent($userId, 'expired_admin_session', $this->getClientIP(), 'Admin session expired');
                $this->destroyAdminSession();
                return false;
            }
            
            // Check IP address consistency
            $currentIP = $this->getClientIP();
            if ($sessionRecord['ip_address'] !== $currentIP) {
                $this->logSecurityEvent($userId, 'admin_ip_mismatch', $currentIP, 'Admin session IP address mismatch');
                $this->destroyAdminSession();
                return false;
            }
            
            // Check for session regeneration
            $lastRegeneration = $_SESSION['admin_last_regeneration'] ?? 0;
            if (time() - $lastRegeneration > $this->adminSessionRegenerationInterval) {
                $this->regenerateAdminSession();
            }
            
            // Update last activity
            $adminSession->update($sessionRecord['id'], [
                'last_activity' => date('Y-m-d H:i:s')
            ]);
            
            return true;
            
        } catch(Exception $e) 
            error_log('Admin session validation error: ' . $e->getMessage());
            return false;
        
    }
    
    /**
     * Check concurrent admin sessions
     * 
     * @param int $userId User ID
     * @return bool True if within limits
     */
    private function checkConcurrentSessions(int $userId): bool {
        try {
            $adminSession = new AdminSession();
            $activeSessions = $adminSession->getActiveSessions($userId);
            
            if (count($activeSessions) >= $this->maxConcurrentAdminSessions) {
                // Optionally terminate oldest session
                $this->terminateOldestSession($userId);
                return count($activeSessions) < $this->maxConcurrentAdminSessions;
            }
            
            return true;
            
        } catch(Exception $e) 
            error_log('Concurrent session check error: ' . $e->getMessage());
            return false;
        
    }
    
    /**
     * Terminate oldest admin session
     * 
     * @param int $userId User ID
     */
    private function terminateOldestSession(int $userId): void {
        try {
            $adminSession = new AdminSession();
            $oldestSession = $adminSession->getOldestSession($userId);
            
            if ($oldestSession) {
                $adminSession->delete($oldestSession['id']);
                $this->logSecurityEvent($userId, 'admin_session_terminated', $this->getClientIP(), 'Oldest admin session terminated due to limit');
            }
        } catch(Exception $e) 
            error_log('Failed to terminate oldest session: ' . $e->getMessage());
        
    }
    
    /**
     * Destroy admin session completely
     */
    public function destroyAdminSession(): void {
        try {
            $userId = $_SESSION['admin_user_id'] ?? null;
            $sessionToken = $_SESSION['admin_session_token'] ?? null;
            
            if ($userId && $sessionToken) {
                // Remove from database
                $adminSession = new AdminSession();
                $adminSession->deleteByUserAndToken($userId, hash('sha256', $sessionToken));
                
                $this->logSecurityEvent($userId, 'admin_session_destroyed', $this->getClientIP(), 'Admin session destroyed');
            }
            
            // Clear session variables
            unset($_SESSION['admin_user_id']);
            unset($_SESSION['admin_session_token']);
            unset($_SESSION['admin_session_start']);
            unset($_SESSION['admin_last_regeneration']);
            
            // Destroy session if it's admin-only
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
            
        } catch(Exception $e) 
            error_log('Admin session destruction error: ' . $e->getMessage());
        
    }
    
    /**
     * Initialize security monitoring
     */
    private function initializeSecurityMonitoring(): void {
        // Register security event handlers
        $this->registerSecurityEventHandlers();
        
        // Start monitoring processes
        $this->startSecurityMonitoring();
    }
    
    /**
     * Register security event handlers
     */
    private function registerSecurityEventHandlers(): void {
        // This would typically integrate with a event system
        // For now, we'll use direct logging
    }
    
    /**
     * Start security monitoring
     */
    private function startSecurityMonitoring(): void {
        // Clean up expired sessions
        $this->cleanupExpiredSessions();
        
        // Check for suspicious activity
        $this->checkSuspiciousActivity();
    }
    
    /**
     * Clean up expired admin sessions
     */
    private function cleanupExpiredSessions(): void {
        try {
            $adminSession = new AdminSession();
            $cleanedCount = $adminSession->deleteExpiredSessions();
            
            if ($cleanedCount > 0) {
                $this->logSecurityEvent(null, 'expired_sessions_cleaned', $this->getClientIP(), "Cleaned up {$cleanedCount} expired admin sessions");
            }
        } catch(Exception $e) 
            error_log('Failed to cleanup expired sessions: ' . $e->getMessage());
        
    }
    
    /**
     * Check for suspicious admin activity
     */
    private function checkSuspiciousActivity(): void {
        try {
            // Check for multiple failed admin login attempts
            $this->checkFailedLoginAttempts();
            
            // Check for unusual access patterns
            $this->checkUnusualAccessPatterns();
            
            // Check for privilege escalation attempts
            $this->checkPrivilegeEscalation();
            
        } catch(Exception $e) 
            error_log('Suspicious activity check error: ' . $e->getMessage());
        
    }
    
    /**
     * Check failed admin login attempts
     */
    private function checkFailedLoginAttempts(): void {
        $sql = "SELECT ip_address, COUNT(*) as attempt_count 
                FROM security_events 
                WHERE event_type = 'admin_login_failure' 
                AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                GROUP BY ip_address 
                HAVING attempt_count >= 5";
        
        $suspiciousIPs = $this->db->select($sql);
        
        foreach ($suspiciousIPs as $ipData) {
            $this->triggerSecurityAlert('suspicious_admin_activity', [
                'type' => 'multiple_failed_admin_logins',
                'ip_address' => $ipData['ip_address'],
                'attempt_count' => $ipData['attempt_count']
            ]);
        }
    }
    
    /**
     * Check unusual access patterns
     */
    private function checkUnusualAccessPatterns(): void {
        // Check for admin access from new geographical locations
        // Check for admin access outside normal hours
        // Check for rapid successive admin actions
        
        $sql = "SELECT user_id, ip_address, COUNT(*) as session_count
                FROM admin_sessions 
                WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY user_id, ip_address
                HAVING session_count > 10";
        
        $unusualPatterns = $this->db->select($sql);
        
        foreach ($unusualPatterns as $pattern) {
            $this->triggerSecurityAlert('unusual_admin_pattern', [
                'user_id' => $pattern['user_id'],
                'ip_address' => $pattern['ip_address'],
                'session_count' => $pattern['session_count']
            ]);
        }
    }
    
    /**
     * Check for privilege escalation attempts
     */
    private function checkPrivilegeEscalation(): void {
        $sql = "SELECT user_id, COUNT(*) as escalation_attempts
                FROM security_events 
                WHERE event_type = 'unauthorized_admin_access'
                AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                GROUP BY user_id
                HAVING escalation_attempts >= 3";
        
        $escalationAttempts = $this->db->select($sql);
        
        foreach ($escalationAttempts as $attempt) {
            $this->triggerSecurityAlert('privilege_escalation_attempt', [
                'user_id' => $attempt['user_id'],
                'attempt_count' => $attempt['escalation_attempts']
            ]);
        }
    }
    
    /**
     * Trigger security alert
     * 
     * @param string $alertType Type of alert
     * @param array $alertData Alert data
     */
    private function triggerSecurityAlert(string $alertType, array $alertData): void {
        try {
            // Log the alert
            $this->logSecurityEvent(
                $alertData['user_id'] ?? null,
                'security_alert',
                $alertData['ip_address'] ?? $this->getClientIP(),
                "Security alert: {$alertType} - " . json_encode($alertData)
            );
            
            // Send real-time notification (email, SMS, webhook, etc.)
            $this->sendSecurityAlert($alertType, $alertData);
            
        } catch(Exception $e) 
            error_log('Failed to trigger security alert: ' . $e->getMessage());
        
    }
    
    /**
     * Send security alert notification
     * 
     * @param string $alertType Alert type
     * @param array $alertData Alert data
     */
    private function sendSecurityAlert(string $alertType, array $alertData): void {
        // This would integrate with email system, SMS, Slack, etc.
        // For now, we'll log it prominently
        
        $alertMessage = "SECURITY ALERT: {$alertType} - " . json_encode($alertData);
        error_log("[SECURITY ALERT] " . $alertMessage);
        
        // Write to dedicated security alert log
        $alertLogFile = __DIR__ . '/../storage/logs/security_alerts.log';
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'alert_type' => $alertType,
            'data' => $alertData,
            'server' => $_SERVER['HTTP_HOST'] ?? 'unknown'
        ];
        
        file_put_contents($alertLogFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log security event
     * 
     * @param int|null $userId User ID
     * @param string $eventType Event type
     * @param string $ipAddress IP address
     * @param string $description Description
     */
    private function logSecurityEvent(?int $userId, string $eventType, string $ipAddress, string $description): void {
        try {
            $securityEvent = new SecurityEvent();
            $securityEvent->create([
                'user_id' => $userId,
                'event_type' => $eventType,
                'ip_address' => $ipAddress,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'failure_reason' => $description,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch(Exception $e) 
            error_log('Failed to log security event: ' . $e->getMessage());
        
    }
    
    /**
     * Check if IP is in admin whitelist
     * 
     * @param string $ipAddress IP address to check
     * @return bool True if allowed
     */
    private function isAllowedAdminIP(string $ipAddress): bool {
        if (empty($this->allowedAdminIPs)) {
            return true; // No whitelist configured
        }
        
        return in_array($ipAddress, $this->allowedAdminIPs, true);
    }
    
    /**
     * Get client IP address
     * 
     * @return string Client IP address
     */
    private function getClientIP(): string {
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
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Check if connection is HTTPS
     * 
     * @return bool True if HTTPS
     */
    private function isHttps(): bool {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }
}
