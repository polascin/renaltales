<?php

declare(strict_types=1);

namespace RenalTales\Core;

use PDO;

/**
 * Renal Tales Logging System
 *
 * This class provides methods to log various user activities and security events
 * to the database logging tables.
 */

class Logger
{
    private \PDO $db;

    public function __construct(\PDO $database)
    {
        $this->db = $database;
    }

    /**
     * Log user registration events
     */
    public function logRegistration(?int $userId, string $username, string $email, string $status, ?string $failureReason = null, ?array $additionalData = null): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO user_registration_logs 
            (user_id, username, email, registration_status, failure_reason, ip_address, user_agent, 
             country, city, registration_source, referrer, additional_data) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $userId,
            $username,
            $email,
            $status,
            $failureReason,
            $this->getClientIP(),
            $this->getUserAgent(),
            $this->getCountry(),
            $this->getCity(),
            $this->getRegistrationSource(),
            $this->getReferrer(),
            $additionalData ? json_encode($additionalData) : null
        ]);
    }

    /**
     * Log user login events
     */
    public function logLogin($userId, $username, $additionalData = null)
    {
        $stmt = $this->db->prepare("
            INSERT INTO login_logs 
            (user_id, username, ip_address, user_agent, country, city, login_source, additional_data) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $userId,
            $username,
            $this->getClientIP(),
            $this->getUserAgent(),
            $this->getCountry(),
            $this->getCity(),
            $this->getLoginSource(),
            $additionalData ? json_encode($additionalData) : null
        ]);
    }

    /**
     * Log user logout events
     */
    public function logLogout($userId, $username, $additionalData = null)
    {
        $stmt = $this->db->prepare("
            INSERT INTO logout_logs 
            (user_id, username, ip_address, user_agent, country, city, logout_source, additional_data) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $userId,
            $username,
            $this->getClientIP(),
            $this->getUserAgent(),
            $this->getCountry(),
            $this->getCity(),
            $this->getLogoutSource(),
            $additionalData ? json_encode($additionalData) : null
        ]);
    }

    /**
     * Log user activity events
     */
    public function logActivity(
        $userId,
        $username,
        $actionType,
        $actionDescription,
        $resourceType = null,
        $resourceId = null,
        $oldValues = null,
        $newValues = null,
        $severity = 'medium',
        $additionalData = null
    ) {
        $stmt = $this->db->prepare("
            INSERT INTO user_activity_logs 
            (user_id, username, action_type, action_description, resource_type, resource_id, 
             old_values, new_values, ip_address, user_agent, country, city, request_method, 
             request_url, session_id, severity, additional_data) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $userId,
            $username,
            $actionType,
            $actionDescription,
            $resourceType,
            $resourceId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $this->getClientIP(),
            $this->getUserAgent(),
            $this->getCountry(),
            $this->getCity(),
            $this->getRequestMethod(),
            $this->getRequestUrl(),
            $this->getSessionId(),
            $severity,
            $additionalData ? json_encode($additionalData) : null
        ]);
    }

    /**
     * Log failed login attempts
     */
    public function logFailedLogin(
        $userId,
        $usernameOrEmail,
        $failureReason,
        $attemptCount = 1,
        $isBlocked = false,
        $blockedUntil = null,
        $threatLevel = 'medium',
        $attemptedPasswordHash = null,
        $additionalData = null
    ) {
        $stmt = $this->db->prepare("
            INSERT INTO failed_login_attempts 
            (user_id, username_or_email, ip_address, user_agent, country, city, failure_reason, 
             attempted_password_hash, request_method, request_url, session_id, attempt_count, 
             is_blocked, blocked_until, threat_level, additional_data) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $userId,
            $usernameOrEmail,
            $this->getClientIP(),
            $this->getUserAgent(),
            $this->getCountry(),
            $this->getCity(),
            $failureReason,
            $attemptedPasswordHash,
            $this->getRequestMethod(),
            $this->getRequestUrl(),
            $this->getSessionId(),
            $attemptCount,
            $isBlocked ? 1 : 0,
            $blockedUntil,
            $threatLevel,
            $additionalData ? json_encode($additionalData) : null
        ]);
    }

    /**
     * Check if IP is currently blocked
     */
    public function isIPBlocked($ipAddress = null)
    {
        if (!$ipAddress) {
            $ipAddress = $this->getClientIP();
        }

        $stmt = $this->db->prepare("
            SELECT COUNT(*) as blocked_count 
            FROM failed_login_attempts 
            WHERE ip_address = ? 
            AND is_blocked = 1 
            AND (blocked_until IS NULL OR blocked_until > NOW())
        ");

        $stmt->execute([$ipAddress]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['blocked_count'] > 0;
    }

    /**
     * Get failed login attempt count for IP in last hour
     */
    public function getFailedAttemptCount($ipAddress = null, $hours = 1)
    {
        if (!$ipAddress) {
            $ipAddress = $this->getClientIP();
        }

        $stmt = $this->db->prepare("
            SELECT COUNT(*) as attempt_count 
            FROM failed_login_attempts 
            WHERE ip_address = ? 
            AND created_at > DATE_SUB(NOW(), INTERVAL ? HOUR)
        ");

        $stmt->execute([$ipAddress, $hours]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['attempt_count'];
    }

    /**
     * Block IP address for specified duration
     */
    public function blockIP($ipAddress, $duration = '1 HOUR', $threatLevel = 'high')
    {
        $stmt = $this->db->prepare("
            UPDATE failed_login_attempts 
            SET is_blocked = 1, 
                blocked_until = DATE_ADD(NOW(), INTERVAL $duration),
                threat_level = ?
            WHERE ip_address = ? 
            AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");

        return $stmt->execute([$threatLevel, $ipAddress]);
    }

    // Helper methods to get client information

    private function getClientIP()
    {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (
                        filter_var(
                            $ip,
                            FILTER_VALIDATE_IP,
                            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
                        ) !== false
                    ) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    private function getUserAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    private function getRequestMethod()
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    private function getRequestUrl()
    {
        return $_SERVER['REQUEST_URI'] ?? '';
    }

    private function getSessionId()
    {
        return session_id() ?: null;
    }

    private function getReferrer()
    {
        return $_SERVER['HTTP_REFERER'] ?? null;
    }

    private function getRegistrationSource()
    {
        // Detect source based on user agent or other factors
        $userAgent = $this->getUserAgent();
        if (strpos($userAgent, 'Mobile') !== false) {
            return 'mobile';
        } elseif (strpos($userAgent, 'API') !== false) {
            return 'api';
        }
        return 'web';
    }

    private function getLoginSource()
    {
        return $this->getRegistrationSource();
    }

    private function getLogoutSource()
    {
        return $this->getRegistrationSource();
    }

    private function getCountry()
    {
        // This would typically use a GeoIP service
        // For now, return null - implement with your preferred GeoIP service
        return null;
    }

    private function getCity()
    {
        // This would typically use a GeoIP service
        // For now, return null - implement with your preferred GeoIP service
        return null;
    }
}
