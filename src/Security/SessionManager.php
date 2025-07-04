<?php
declare(strict_types=1);

namespace RenalTales\Security;

use RenalTales\Core\Config;
use RenalTales\Database\Database;
use PDO;

class SessionManager
{
    private Config $config;
    private PDO $db;
    private int $sessionLifetime;
    private int $rememberMeLifetime;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->db = Database::getInstance()->getConnection();
        $this->sessionLifetime = $config->get('security.session_lifetime', 3600); // 1 hour
        $this->rememberMeLifetime = $config->get('security.remember_me_lifetime', 2592000); // 30 days
    }

    /**
     * Create a new session
     */
    public function createSession(int $userId, bool $rememberMe = false): string
    {
        $sessionToken = $this->generateSessionToken();
        $expiresAt = new \DateTime();
        $expiresAt->add(new \DateInterval(
            $rememberMe ? "PT{$this->rememberMeLifetime}S" : "PT{$this->sessionLifetime}S"
        ));

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ipAddress = $this->getClientIP();

        $stmt = $this->db->prepare("
            INSERT INTO user_sessions (
                session_token, user_id, ip_address, user_agent, 
                expires_at, remember_me, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            hash('sha256', $sessionToken),
            $userId,
            $ipAddress,
            $userAgent,
            $expiresAt->format('Y-m-d H:i:s'),
            $rememberMe ? 1 : 0
        ]);

        return $sessionToken;
    }

    /**
     * Validate session token
     */
    public function validateSession(string $sessionToken): ?int
    {
        $hashedToken = hash('sha256', $sessionToken);
        
        $stmt = $this->db->prepare("
            SELECT user_id, expires_at, ip_address, user_agent 
            FROM user_sessions 
            WHERE session_token = ? AND expires_at > NOW()
        ");
        
        $stmt->execute([$hashedToken]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            return null;
        }

        // Validate session security
        if (!$this->validateSessionSecurity($sessionToken, $_SERVER['HTTP_USER_AGENT'] ?? '', $this->getClientIP())) {
            $this->destroySession($sessionToken);
            return null;
        }

        // Update last activity
        $this->updateSessionActivity($hashedToken);

        return (int)$session['user_id'];
    }

    /**
     * Validate session security (IP and User Agent)
     */
    public function validateSessionSecurity(string $sessionToken, string $userAgent, string $ipAddress): bool
    {
        $hashedToken = hash('sha256', $sessionToken);
        
        $stmt = $this->db->prepare("
            SELECT ip_address, user_agent 
            FROM user_sessions 
            WHERE session_token = ?
        ");
        
        $stmt->execute([$hashedToken]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            return false;
        }

        // Check if IP address changed (optional - can be disabled for mobile users)
        if ($this->config->get('security.validate_ip', true) && $session['ip_address'] !== $ipAddress) {
            return false;
        }

        // Check if User Agent changed significantly
        if (!$this->userAgentMatches($session['user_agent'], $userAgent)) {
            return false;
        }

        return true;
    }

    /**
     * Destroy a session
     */
    public function destroySession(string $sessionToken): bool
    {
        $hashedToken = hash('sha256', $sessionToken);
        
        $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE session_token = ?");
        return $stmt->execute([$hashedToken]);
    }

    /**
     * Destroy all sessions for a user
     */
    public function destroyAllUserSessions(int $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    /**
     * Get user's active sessions
     */
    public function getUserSessions(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT ip_address, user_agent, created_at, last_activity, remember_me
            FROM user_sessions 
            WHERE user_id = ? AND expires_at > NOW()
            ORDER BY last_activity DESC
        ");
        
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Clean up expired sessions
     */
    public function cleanupExpiredSessions(): int
    {
        $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE expires_at <= NOW()");
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Extend session if remember me is enabled
     */
    public function extendSession(string $sessionToken): bool
    {
        $hashedToken = hash('sha256', $sessionToken);
        
        $stmt = $this->db->prepare("
            SELECT remember_me FROM user_sessions 
            WHERE session_token = ? AND expires_at > NOW()
        ");
        
        $stmt->execute([$hashedToken]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session || !$session['remember_me']) {
            return false;
        }

        $newExpiresAt = new \DateTime();
        $newExpiresAt->add(new \DateInterval("PT{$this->rememberMeLifetime}S"));

        $stmt = $this->db->prepare("
            UPDATE user_sessions 
            SET expires_at = ? 
            WHERE session_token = ?
        ");

        return $stmt->execute([
            $newExpiresAt->format('Y-m-d H:i:s'),
            $hashedToken
        ]);
    }

    /**
     * Generate secure session token
     */
    private function generateSessionToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Update session last activity
     */
    private function updateSessionActivity(string $hashedToken): void
    {
        $stmt = $this->db->prepare("
            UPDATE user_sessions 
            SET last_activity = NOW() 
            WHERE session_token = ?
        ");
        
        $stmt->execute([$hashedToken]);
    }

    /**
     * Get client IP address
     */
    private function getClientIP(): string
    {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                   'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Check if user agents match (allowing for minor differences)
     */
    private function userAgentMatches(string $stored, string $current): bool
    {
        // If user agents are identical, return true
        if ($stored === $current) {
            return true;
        }

        // Extract major browser and version info for comparison
        $storedInfo = $this->extractBrowserInfo($stored);
        $currentInfo = $this->extractBrowserInfo($current);

        // Check if major browser and version match
        return $storedInfo['browser'] === $currentInfo['browser'] && 
               $storedInfo['version'] === $currentInfo['version'];
    }

    /**
     * Extract browser information from user agent
     */
    private function extractBrowserInfo(string $userAgent): array
    {
        $browser = 'unknown';
        $version = 'unknown';

        if (preg_match('/Chrome\/(\d+)/', $userAgent, $matches)) {
            $browser = 'Chrome';
            $version = $matches[1];
        } elseif (preg_match('/Firefox\/(\d+)/', $userAgent, $matches)) {
            $browser = 'Firefox';
            $version = $matches[1];
        } elseif (preg_match('/Safari\/(\d+)/', $userAgent, $matches)) {
            $browser = 'Safari';
            $version = $matches[1];
        } elseif (preg_match('/Edge\/(\d+)/', $userAgent, $matches)) {
            $browser = 'Edge';
            $version = $matches[1];
        }

        return ['browser' => $browser, 'version' => $version];
    }

    /**
     * Get session statistics
     */
    public function getSessionStatistics(): array
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_sessions,
                COUNT(DISTINCT user_id) as unique_users,
                SUM(CASE WHEN remember_me = 1 THEN 1 ELSE 0 END) as remember_me_sessions,
                AVG(TIMESTAMPDIFF(SECOND, created_at, COALESCE(last_activity, NOW()))) as avg_duration
            FROM user_sessions 
            WHERE expires_at > NOW()
        ");

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Revoke session by token hash
     */
    public function revokeSessionByHash(string $hashedToken): bool
    {
        $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE session_token = ?");
        return $stmt->execute([$hashedToken]);
    }

    /**
     * Check if session exists
     */
    public function sessionExists(string $sessionToken): bool
    {
        $hashedToken = hash('sha256', $sessionToken);
        
        $stmt = $this->db->prepare("
            SELECT 1 FROM user_sessions 
            WHERE session_token = ? AND expires_at > NOW()
        ");
        
        $stmt->execute([$hashedToken]);
        return $stmt->fetchColumn() !== false;
    }
}
