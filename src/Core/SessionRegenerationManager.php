<?php

declare(strict_types=1);

namespace RenalTales\Core;

use RenalTales\Core\SessionManager;
use RenalTales\Core\SecurityManager;
use RenalTales\Core\Database;
use RenalTales\Models\SecurityEvent;
use Exception;

/**
 * Enhanced Session Regeneration Manager
 * 
 * Provides advanced session security with intelligent regeneration,
 * security monitoring, and threat detection.
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

class SessionRegenerationManager {
    
    private SessionManager $sessionManager;
    private SecurityManager $securityManager;
    private Database $db;
    
    // Regeneration settings
    private int $normalRegenerationInterval = 300; // 5 minutes
    private int $adminRegenerationInterval = 180; // 3 minutes
    private int $suspiciousActivityInterval = 60; // 1 minute
    private int $privilegeChangeInterval = 30; // 30 seconds
    
    // Security thresholds
    private int $maxRegenerationsPerHour = 50;
    private int $ipChangeThreshold = 3;
    private int $userAgentChangeThreshold = 1;
    
    // Risk scoring
    private array $riskFactors = [
        'ip_change' => 3,
        'user_agent_change' => 4,
        'privilege_escalation' => 5,
        'suspicious_activity' => 3,
        'off_hours_access' => 2,
        'new_location' => 2,
        'rapid_requests' => 2
    ];
    
    public function __construct() {
        $this->sessionManager = new SessionManager();
        $this->securityManager = new SecurityManager();
        $this->db = Database::getInstance();
        
        $this->loadConfiguration();
    }
    
    /**
     * Load regeneration configuration
     */
    private function loadConfiguration(): void {
        $config = include __DIR__ . '/../config/security.php';
        
        if (isset($config['session_regeneration'])) {
            $regenConfig = $config['session_regeneration'];
            
            $this->normalRegenerationInterval = $regenConfig['normal_interval'] ?? $this->normalRegenerationInterval;
            $this->adminRegenerationInterval = $regenConfig['admin_interval'] ?? $this->adminRegenerationInterval;
            $this->suspiciousActivityInterval = $regenConfig['suspicious_interval'] ?? $this->suspiciousActivityInterval;
            $this->privilegeChangeInterval = $regenConfig['privilege_change_interval'] ?? $this->privilegeChangeInterval;
        }
    }
    
    /**
     * Perform intelligent session regeneration based on context
     * 
     * @param array $context Security context
     * @return bool Regeneration performed
     */
    public function intelligentRegeneration(array $context = []): bool {
        try {
            // Calculate risk score
            $riskScore = $this->calculateRiskScore($context);
            
            // Determine regeneration interval based on risk
            $interval = $this->getRegenerationInterval($riskScore, $context);
            
            // Check if regeneration is needed
            if ($this->shouldRegenerate($interval, $context)) {
                return $this->performSecureRegeneration($context);
            }
            
            return false;
            
        } catch(Exception $e) 
            error_log('Intelligent regeneration error: ' . $e->getMessage());
            // Force regeneration on error for security
            return $this->performSecureRegeneration($context);
        
    }
    
    /**
     * Calculate security risk score
     * 
     * @param array $context Security context
     * @return int Risk score (0-20)
     */
    private function calculateRiskScore(array $context): int {
        $score = 0;
        
        // Check for IP address changes
        if ($this->hasIPChanged($context)) {
            $score += $this->riskFactors['ip_change'];
        }
        
        // Check for user agent changes
        if ($this->hasUserAgentChanged($context)) {
            $score += $this->riskFactors['user_agent_change'];
        }
        
        // Check for privilege escalation
        if ($this->hasPrivilegeEscalation($context)) {
            $score += $this->riskFactors['privilege_escalation'];
        }
        
        // Check for suspicious activity patterns
        if ($this->hasSuspiciousActivity($context)) {
            $score += $this->riskFactors['suspicious_activity'];
        }
        
        // Check for off-hours access
        if ($this->isOffHoursAccess()) {
            $score += $this->riskFactors['off_hours_access'];
        }
        
        // Check for new geographical location
        if ($this->isNewLocation($context)) {
            $score += $this->riskFactors['new_location'];
        }
        
        // Check for rapid requests
        if ($this->hasRapidRequests($context)) {
            $score += $this->riskFactors['rapid_requests'];
        }
        
        return min($score, 20); // Cap at 20
    }
    
    /**
     * Get regeneration interval based on risk and context
     * 
     * @param int $riskScore Risk score
     * @param array $context Security context
     * @return int Regeneration interval in seconds
     */
    private function getRegenerationInterval(int $riskScore, array $context): int {
        // High risk - immediate regeneration
        if ($riskScore >= 10) {
            return 0;
        }
        
        // Medium-high risk - short interval
        if ($riskScore >= 6) {
            return $this->suspiciousActivityInterval;
        }
        
        // Admin user - shorter interval
        if ($this->isAdminUser($context)) {
            return $this->adminRegenerationInterval;
        }
        
        // Privilege change - immediate regeneration
        if ($this->hasPrivilegeChange($context)) {
            return $this->privilegeChangeInterval;
        }
        
        // Normal interval
        return $this->normalRegenerationInterval;
    }
    
    /**
     * Check if session regeneration should occur
     * 
     * @param int $interval Required interval
     * @param array $context Security context
     * @return bool Should regenerate
     */
    private function shouldRegenerate(int $interval, array $context): bool {
        // Force regeneration if interval is 0
        if ($interval === 0) {
            return true;
        }
        
        // Check last regeneration time
        $lastRegeneration = $_SESSION['_security']['last_regeneration'] ?? 0;
        $timeSinceRegeneration = time() - $lastRegeneration;
        
        // Check regeneration limits
        if ($this->hasExceededRegenerationLimit()) {
            $this->logSecurityEvent(
                $this->getCurrentUserId(),
                'excessive_regeneration',
                $this->getClientIP(),
                'Regeneration limit exceeded'
            );
            return false;
        }
        
        return $timeSinceRegeneration >= $interval;
    }
    
    /**
     * Perform secure session regeneration
     * 
     * @param array $context Security context
     * @return bool Success status
     */
    private function performSecureRegeneration(array $context = []): bool {
        try {
            $oldSessionId = session_id();
            $userId = $this->getCurrentUserId();
            
            // Pre-regeneration security checks
            if (!$this->validatePreRegeneration($context)) {
                return false;
            }
            
            // Store critical session data
            $criticalData = $this->preserveCriticalSessionData();
            
            // Regenerate session ID
            if (!session_regenerate_id(true)) {
                throw new Exception('Session regeneration failed');
            }
            
            $newSessionId = session_id();
            
            // Restore critical data
            $this->restoreCriticalSessionData($criticalData);
            
            // Update security metadata
            $this->updateSessionSecurityMetadata($oldSessionId, $newSessionId, $context);
            
            // Log regeneration event
            $this->logSessionRegeneration($userId, $oldSessionId, $newSessionId, $context);
            
            // Update regeneration tracking
            $this->updateRegenerationTracking();
            
            return true;
            
        } catch(Exception $e) 
            error_log('Session regeneration failed: ' . $e->getMessage());
            
            // Log failed regeneration
            $this->logSecurityEvent(
                $this->getCurrentUserId(),
                'regeneration_failure',
                $this->getClientIP(),
                'Session regeneration failed: ' . $e->getMessage()
            );
            
            return false;
        
    }
    
    /**
     * Validate pre-regeneration conditions
     * 
     * @param array $context Security context
     * @return bool Valid for regeneration
     */
    private function validatePreRegeneration(array $context): bool {
        // Check if session is active
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }
        
        // Check for session hijacking indicators
        if ($this->detectSessionHijacking($context)) {
            $this->logSecurityEvent(
                $this->getCurrentUserId(),
                'hijacking_detected',
                $this->getClientIP(),
                'Session hijacking detected during regeneration'
            );
            return false;
        }
        
        // Check for concurrent regeneration attempts
        if ($this->hasConcurrentRegeneration()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Preserve critical session data during regeneration
     * 
     * @return array Critical session data
     */
    private function preserveCriticalSessionData(): array {
        return [
            'user_id' => $_SESSION['user_id'] ?? null,
            'admin_user_id' => $_SESSION['admin_user_id'] ?? null,
            'user_role' => $_SESSION['user_role'] ?? null,
            'login_time' => $_SESSION['login_time'] ?? null,
            'csrf_token' => $_SESSION['_csrf_token'] ?? null,
            'language' => $_SESSION['user_language'] ?? null,
            'permissions' => $_SESSION['user_permissions'] ?? null
        ];
    }
    
    /**
     * Restore critical session data after regeneration
     * 
     * @param array $criticalData Critical session data
     */
    private function restoreCriticalSessionData(array $criticalData): void {
        foreach ($criticalData as $key => $value) {
            if ($value !== null) {
                if ($key === 'csrf_token') {
                    $_SESSION['_csrf_token'] = $value;
                } else {
                    $_SESSION[$key] = $value;
                }
            }
        }
    }
    
    /**
     * Update session security metadata
     * 
     * @param string $oldSessionId Old session ID
     * @param string $newSessionId New session ID
     * @param array $context Security context
     */
    private function updateSessionSecurityMetadata(string $oldSessionId, string $newSessionId, array $context): void {
        $now = time();
        
        $_SESSION['_security'] = [
            'last_regeneration' => $now,
            'regeneration_count' => ($_SESSION['_security']['regeneration_count'] ?? 0) + 1,
            'ip_address' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'old_session_id' => $oldSessionId,
            'risk_score' => $this->calculateRiskScore($context),
            'regeneration_reason' => $context['reason'] ?? 'periodic'
        ];
    }
    
    /**
     * Log session regeneration event
     * 
     * @param int|null $userId User ID
     * @param string $oldSessionId Old session ID
     * @param string $newSessionId New session ID
     * @param array $context Security context
     */
    private function logSessionRegeneration(?int $userId, string $oldSessionId, string $newSessionId, array $context): void {
        $reason = $context['reason'] ?? 'periodic';
        $riskScore = $this->calculateRiskScore($context);
        
        $description = "Session regenerated - Reason: {$reason}, Risk: {$riskScore}/20, Old: " . substr($oldSessionId, 0, 8) . "..., New: " . substr($newSessionId, 0, 8) . "...";
        
        $this->logSecurityEvent($userId, 'session_regenerated', $this->getClientIP(), $description);
    }
    
    /**
     * Update regeneration tracking
     */
    private function updateRegenerationTracking(): void {
        $hour = date('Y-m-d H');
        $key = "regeneration_count_{$hour}";
        
        $_SESSION['_regeneration_tracking'][$key] = ($_SESSION['_regeneration_tracking'][$key] ?? 0) + 1;
        
        // Clean old tracking data
        $this->cleanOldRegenerationTracking();
    }
    
    /**
     * Clean old regeneration tracking data
     */
    private function cleanOldRegenerationTracking(): void {
        if (!isset($_SESSION['_regeneration_tracking'])) {
            return;
        }
        
        $currentHour = date('Y-m-d H');
        $cutoff = date('Y-m-d H', strtotime('-2 hours'));
        
        foreach ($_SESSION['_regeneration_tracking'] as $key => $count) {
            $hour = str_replace('regeneration_count_', '', $key);
            if ($hour < $cutoff) {
                unset($_SESSION['_regeneration_tracking'][$key]);
            }
        }
    }
    
    /**
     * Check if regeneration limit has been exceeded
     * 
     * @return bool Limit exceeded
     */
    private function hasExceededRegenerationLimit(): bool {
        $currentHour = date('Y-m-d H');
        $key = "regeneration_count_{$currentHour}";
        
        $count = $_SESSION['_regeneration_tracking'][$key] ?? 0;
        
        return $count >= $this->maxRegenerationsPerHour;
    }
    
    /**
     * Check if IP address has changed
     * 
     * @param array $context Security context
     * @return bool IP changed
     */
    private function hasIPChanged(array $context): bool {
        $currentIP = $this->getClientIP();
        $storedIP = $_SESSION['_security']['ip_address'] ?? '';
        
        return !empty($storedIP) && $storedIP !== $currentIP;
    }
    
    /**
     * Check if user agent has changed
     * 
     * @param array $context Security context
     * @return bool User agent changed
     */
    private function hasUserAgentChanged(array $context): bool {
        $currentUA = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $storedUA = $_SESSION['_security']['user_agent'] ?? '';
        
        return !empty($storedUA) && $storedUA !== $currentUA;
    }
    
    /**
     * Check for privilege escalation
     * 
     * @param array $context Security context
     * @return bool Privilege escalation detected
     */
    private function hasPrivilegeEscalation(array $context): bool {
        return isset($context['privilege_escalation']) && $context['privilege_escalation'] === true;
    }
    
    /**
     * Check for privilege changes
     * 
     * @param array $context Security context
     * @return bool Privilege changed
     */
    private function hasPrivilegeChange(array $context): bool {
        return isset($context['privilege_change']) && $context['privilege_change'] === true;
    }
    
    /**
     * Check for suspicious activity
     * 
     * @param array $context Security context
     * @return bool Suspicious activity detected
     */
    private function hasSuspiciousActivity(array $context): bool {
        // Check recent failed login attempts
        $recentFailures = $this->getRecentFailedAttempts($this->getClientIP());
        if ($recentFailures >= 3) {
            return true;
        }
        
        // Check for suspicious patterns
        if (isset($context['suspicious_activity']) && $context['suspicious_activity'] === true) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if access is during off-hours
     * 
     * @return bool Off-hours access
     */
    private function isOffHoursAccess(): bool {
        $config = include __DIR__ . '/../config/security.php';
        $businessHours = $config['security_monitoring']['business_hours'] ?? null;
        
        if (!$businessHours) {
            return false;
        }
        
        $timezone = new DateTimeZone($businessHours['timezone'] ?? 'UTC');
        $now = new DateTime('now', $timezone);
        $hour = (int)$now->format('H');
        
        $startHour = (int)date('H', strtotime($businessHours['start']));
        $endHour = (int)date('H', strtotime($businessHours['end']));
        
        return $hour < $startHour || $hour >= $endHour;
    }
    
    /**
     * Check if access is from new location
     * 
     * @param array $context Security context
     * @return bool New location detected
     */
    private function isNewLocation(array $context): bool {
        // This would integrate with IP geolocation service
        // For now, return false as it requires external service
        return false;
    }
    
    /**
     * Check for rapid requests
     * 
     * @param array $context Security context
     * @return bool Rapid requests detected
     */
    private function hasRapidRequests(array $context): bool {
        $now = time();
        $lastRequest = $_SESSION['_security']['last_request'] ?? 0;
        $requestInterval = $now - $lastRequest;
        
        $_SESSION['_security']['last_request'] = $now;
        
        // Consider rapid if less than 1 second between requests
        return $requestInterval < 1;
    }
    
    /**
     * Check if current user is admin
     * 
     * @param array $context Security context
     * @return bool Is admin user
     */
    private function isAdminUser(array $context): bool {
        return isset($_SESSION['admin_user_id']) || 
               (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
    }
    
    /**
     * Detect session hijacking
     * 
     * @param array $context Security context
     * @return bool Hijacking detected
     */
    private function detectSessionHijacking(array $context): bool {
        // Check for rapid IP changes
        if ($this->hasIPChanged($context)) {
            $changeCount = $_SESSION['_security']['ip_change_count'] ?? 0;
            $_SESSION['_security']['ip_change_count'] = $changeCount + 1;
            
            if ($changeCount >= $this->ipChangeThreshold) {
                return true;
            }
        }
        
        // Check for user agent changes
        if ($this->hasUserAgentChanged($context)) {
            $changeCount = $_SESSION['_security']['ua_change_count'] ?? 0;
            $_SESSION['_security']['ua_change_count'] = $changeCount + 1;
            
            if ($changeCount >= $this->userAgentChangeThreshold) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check for concurrent regeneration attempts
     * 
     * @return bool Concurrent regeneration detected
     */
    private function hasConcurrentRegeneration(): bool {
        $now = time();
        $lastAttempt = $_SESSION['_security']['regeneration_attempt'] ?? 0;
        $_SESSION['_security']['regeneration_attempt'] = $now;
        
        // Consider concurrent if within 1 second
        return ($now - $lastAttempt) < 1;
    }
    
    /**
     * Get recent failed attempts for IP
     * 
     * @param string $ipAddress IP address
     * @return int Number of recent failed attempts
     */
    private function getRecentFailedAttempts(string $ipAddress): int {
        try {
            $sql = "SELECT COUNT(*) as count FROM security_events 
                    WHERE ip_address = ? AND event_type = 'login_failure' 
                    AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            
            $result = $this->db->selectOne($sql, [$ipAddress]);
            return $result['count'] ?? 0;
            
        } catch(Exception $e) 
            error_log('Failed to get recent failed attempts: ' . $e->getMessage());
            return 0;
        
    }
    
    /**
     * Get current user ID
     * 
     * @return int|null User ID
     */
    private function getCurrentUserId(): ?int {
        return $_SESSION['user_id'] ?? $_SESSION['admin_user_id'] ?? null;
    }
    
    /**
     * Get client IP address
     * 
     * @return string Client IP
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
}
