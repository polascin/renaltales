<?php
declare(strict_types=1);

namespace RenalTales\Security;

use RenalTales\Core\Config;
use RenalTales\Database\Database;
use PDO;

class LoginThrottling
{
    private Config $config;
    private PDO $db;
    private int $maxAttempts;
    private int $lockoutTime;
    private bool $progressiveLockout;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->db = Database::getInstance()->getConnection();
        $this->maxAttempts = $config->get('security.max_login_attempts', 5);
        $this->lockoutTime = $config->get('security.lockout_time', 900); // 15 minutes
        $this->progressiveLockout = $config->get('security.progressive_lockout', true);
    }

    /**
     * Check if IP address is throttled
     */
    public function isThrottled(string $ipAddress): bool
    {
        $this->cleanupExpiredAttempts();
        
        $stmt = $this->db->prepare("
            SELECT attempt_count, locked_until 
            FROM login_attempts 
            WHERE ip_address = ? AND locked_until > NOW()
        ");
        
        $stmt->execute([$ipAddress]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result !== false;
    }

    /**
     * Check if user is throttled
     */
    public function isUserThrottled(string $email): bool
    {
        $this->cleanupExpiredAttempts();
        
        $stmt = $this->db->prepare("
            SELECT attempt_count, locked_until 
            FROM login_attempts 
            WHERE email = ? AND locked_until > NOW()
        ");
        
        $stmt->execute([strtolower($email)]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result !== false;
    }

    /**
     * Record a failed login attempt
     */
    public function recordFailedAttempt(string $email, string $ipAddress): void
    {
        $email = strtolower($email);
        
        // Record IP-based attempt
        $this->recordAttempt($ipAddress, null);
        
        // Record email-based attempt
        $this->recordAttempt(null, $email);
    }

    /**
     * Clear failed attempts for IP and email
     */
    public function clearFailedAttempts(string $email, string $ipAddress): void
    {
        $email = strtolower($email);
        
        $stmt = $this->db->prepare("
            DELETE FROM login_attempts 
            WHERE ip_address = ? OR email = ?
        ");
        
        $stmt->execute([$ipAddress, $email]);
    }

    /**
     * Get remaining lockout time for IP
     */
    public function getRemainingLockoutTime(string $ipAddress): int
    {
        $stmt = $this->db->prepare("
            SELECT TIMESTAMPDIFF(SECOND, NOW(), locked_until) as remaining
            FROM login_attempts 
            WHERE ip_address = ? AND locked_until > NOW()
        ");
        
        $stmt->execute([$ipAddress]);
        $result = $stmt->fetchColumn();
        
        return max(0, (int)$result);
    }

    /**
     * Get attempt count for IP
     */
    public function getAttemptCount(string $ipAddress): int
    {
        $stmt = $this->db->prepare("
            SELECT attempt_count 
            FROM login_attempts 
            WHERE ip_address = ?
        ");
        
        $stmt->execute([$ipAddress]);
        $result = $stmt->fetchColumn();
        
        return (int)$result;
    }

    /**
     * Check rate limit for any action
     */
    public function checkRateLimit(string $action, string $identifier, int $maxAttempts = 5, int $timeWindow = 3600): bool
    {
        $key = "{$action}:{$identifier}";
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM rate_limits 
            WHERE action_key = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        
        $stmt->execute([$key, $timeWindow]);
        $count = (int)$stmt->fetchColumn();
        
        if ($count >= $maxAttempts) {
            return false;
        }
        
        // Record this attempt
        $stmt = $this->db->prepare("
            INSERT INTO rate_limits (action_key, created_at) VALUES (?, NOW())
        ");
        
        $stmt->execute([$key]);
        
        return true;
    }

    /**
     * Clean up old rate limit records
     */
    public function cleanupRateLimits(): int
    {
        $stmt = $this->db->prepare("
            DELETE FROM rate_limits 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)
        ");
        
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Ban IP address
     */
    public function banIP(string $ipAddress, int $duration = 86400): bool
    {
        $banUntil = new \DateTime();
        $banUntil->add(new \DateInterval("PT{$duration}S"));
        
        $stmt = $this->db->prepare("
            INSERT INTO ip_bans (ip_address, banned_until, created_at) 
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE banned_until = VALUES(banned_until)
        ");
        
        return $stmt->execute([$ipAddress, $banUntil->format('Y-m-d H:i:s')]);
    }

    /**
     * Check if IP is banned
     */
    public function isIPBanned(string $ipAddress): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1 FROM ip_bans 
            WHERE ip_address = ? AND banned_until > NOW()
        ");
        
        $stmt->execute([$ipAddress]);
        return $stmt->fetchColumn() !== false;
    }

    /**
     * Unban IP address
     */
    public function unbanIP(string $ipAddress): bool
    {
        $stmt = $this->db->prepare("DELETE FROM ip_bans WHERE ip_address = ?");
        return $stmt->execute([$ipAddress]);
    }

    /**
     * Get login attempt statistics
     */
    public function getAttemptStatistics(): array
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_attempts,
                COUNT(DISTINCT ip_address) as unique_ips,
                COUNT(DISTINCT email) as unique_emails,
                AVG(attempt_count) as avg_attempts,
                MAX(attempt_count) as max_attempts
            FROM login_attempts 
            WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get top attacking IPs
     */
    public function getTopAttackingIPs(int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                ip_address, 
                SUM(attempt_count) as total_attempts,
                MAX(locked_until) as last_lockout
            FROM login_attempts 
            WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            GROUP BY ip_address
            ORDER BY total_attempts DESC
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Record attempt (internal method)
     */
    private function recordAttempt(?string $ipAddress, ?string $email): void
    {
        // Get current attempt record
        $whereClause = $ipAddress ? "ip_address = ?" : "email = ?";
        $identifier = $ipAddress ?: $email;
        
        $stmt = $this->db->prepare("
            SELECT attempt_count, created_at 
            FROM login_attempts 
            WHERE {$whereClause}
        ");
        
        $stmt->execute([$identifier]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($current) {
            $attemptCount = $current['attempt_count'] + 1;
            $lockoutDuration = $this->calculateLockoutDuration($attemptCount);
            
            $lockedUntil = new \DateTime();
            if ($attemptCount >= $this->maxAttempts) {
                $lockedUntil->add(new \DateInterval("PT{$lockoutDuration}S"));
            }
            
            $stmt = $this->db->prepare("
                UPDATE login_attempts 
                SET attempt_count = ?, locked_until = ?, updated_at = NOW()
                WHERE {$whereClause}
            ");
            
            $stmt->execute([
                $attemptCount,
                $attemptCount >= $this->maxAttempts ? $lockedUntil->format('Y-m-d H:i:s') : null,
                $identifier
            ]);
        } else {
            // Create new attempt record
            $stmt = $this->db->prepare("
                INSERT INTO login_attempts (ip_address, email, attempt_count, created_at, updated_at) 
                VALUES (?, ?, 1, NOW(), NOW())
            ");
            
            $stmt->execute([$ipAddress, $email]);
        }
    }

    /**
     * Calculate lockout duration based on attempt count
     */
    private function calculateLockoutDuration(int $attemptCount): int
    {
        if (!$this->progressiveLockout) {
            return $this->lockoutTime;
        }
        
        // Progressive lockout: 15 min, 30 min, 1 hour, 2 hours, etc.
        $multiplier = min($attemptCount - $this->maxAttempts + 1, 8); // Cap at 8x
        return $this->lockoutTime * $multiplier;
    }

    /**
     * Clean up expired attempts
     */
    private function cleanupExpiredAttempts(): void
    {
        $stmt = $this->db->prepare("
            DELETE FROM login_attempts 
            WHERE locked_until IS NOT NULL AND locked_until <= NOW()
        ");
        
        $stmt->execute();
        
        // Clean up old attempts that are not locked
        $stmt = $this->db->prepare("
            DELETE FROM login_attempts 
            WHERE locked_until IS NULL AND created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        
        $stmt->execute();
    }

    /**
     * Reset all attempts for debugging/admin purposes
     */
    public function resetAllAttempts(): int
    {
        $stmt = $this->db->prepare("DELETE FROM login_attempts");
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Get lockout information for IP or email
     */
    public function getLockoutInfo(string $identifier): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                ip_address,
                email,
                attempt_count,
                locked_until,
                TIMESTAMPDIFF(SECOND, NOW(), locked_until) as remaining_seconds
            FROM login_attempts 
            WHERE (ip_address = ? OR email = ?) AND locked_until > NOW()
        ");
        
        $stmt->execute([$identifier, $identifier]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Add IP to whitelist (not implemented in this basic version)
     */
    public function addToWhitelist(string $ipAddress): bool
    {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO ip_whitelist (ip_address, created_at) 
            VALUES (?, NOW())
        ");
        
        return $stmt->execute([$ipAddress]);
    }

    /**
     * Check if IP is whitelisted
     */
    public function isWhitelisted(string $ipAddress): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1 FROM ip_whitelist WHERE ip_address = ?
        ");
        
        $stmt->execute([$ipAddress]);
        return $stmt->fetchColumn() !== false;
    }

    /**
     * Remove IP from whitelist
     */
    public function removeFromWhitelist(string $ipAddress): bool
    {
        $stmt = $this->db->prepare("DELETE FROM ip_whitelist WHERE ip_address = ?");
        return $stmt->execute([$ipAddress]);
    }
}
