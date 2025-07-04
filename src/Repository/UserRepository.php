<?php
declare(strict_types=1);

namespace RenalTales\Repository;

use RenalTales\Model\User;
use PDO;

class UserRepository extends Repository
{
    protected string $table = 'users';
    protected string $modelClass = User::class;
    protected array $allowedOrderBy = ['username', 'created_at', 'last_login_at'];

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findByUsername(string $username): ?User
    {
        return $this->findOneBy(['username' => $username]);
    }

    public function findByRole(string $role, array $orderBy = [], int $limit = null): array
    {
        return $this->findBy(['role' => $role], $orderBy, $limit);
    }

    public function findTranslators(): array
    {
        return $this->findBy(['role' => ['translator', 'admin']]);
    }

    public function findVerifiedUsers(): array
    {
        return $this->findBy([
            'role' => ['verified_user', 'translator', 'moderator', 'admin'],
            'email_verified_at IS NOT' => null
        ]);
    }

    public function updateLastLogin(int $userId): bool
    {
        return $this->update($userId, [
            'last_login_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getActiveUsers(int $minutes = 15): array
    {
        $sql = "
            SELECT u.*, COUNT(s.id) as story_count
            FROM users u
            LEFT JOIN stories s ON u.id = s.user_id
            WHERE u.last_login_at >= DATE_SUB(NOW(), INTERVAL :minutes MINUTE)
            GROUP BY u.id
            ORDER BY u.last_login_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':minutes', $minutes, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(
            fn($row) => new User($row),
            $stmt->fetchAll()
        );
    }

    public function getUserStatistics(): array
    {
        $sql = "
            SELECT
                COUNT(*) as total_users,
                COUNT(CASE WHEN email_verified_at IS NOT NULL THEN 1 END) as verified_users,
                COUNT(CASE WHEN role = 'translator' THEN 1 END) as translators,
                COUNT(CASE WHEN role = 'moderator' THEN 1 END) as moderators,
                COUNT(CASE WHEN last_login_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as active_last_30_days
            FROM users
        ";

        return $this->executeSingleResult($sql);
    }

    public function getMostActiveUsers(int $limit = 10): array
    {
        $sql = "
            SELECT 
                u.*,
                COUNT(DISTINCT s.id) as story_count,
                COUNT(DISTINCT c.id) as comment_count,
                COUNT(DISTINCT sc.id) as translation_count
            FROM users u
            LEFT JOIN stories s ON u.id = s.user_id
            LEFT JOIN comments c ON u.id = c.user_id
            LEFT JOIN story_contents sc ON u.id = sc.translator_id
            WHERE u.email_verified_at IS NOT NULL
            GROUP BY u.id
            ORDER BY (COUNT(DISTINCT s.id) + COUNT(DISTINCT c.id) + COUNT(DISTINCT sc.id)) DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(
            fn($row) => new User($row),
            $stmt->fetchAll()
        );
    }

    public function search(string $query): array
    {
        $sql = "
            SELECT *
            FROM users
            WHERE username LIKE :query
               OR email LIKE :query
               OR full_name LIKE :query
            ORDER BY username
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':query', "%{$query}%");
        $stmt->execute();

        return array_map(
            fn($row) => new User($row),
            $stmt->fetchAll()
        );
    }

    // Security-related methods
    
    public function findVerificationToken(string $token): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM email_verification_tokens 
            WHERE token = ? AND expires_at > NOW()
        ");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    public function createVerificationToken(int $userId, string $token): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO email_verification_tokens (user_id, token, expires_at) 
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))
        ");
        return $stmt->execute([$userId, $token]);
    }
    
    public function deleteVerificationToken(string $token): bool
    {
        $stmt = $this->db->prepare("DELETE FROM email_verification_tokens WHERE token = ?");
        return $stmt->execute([$token]);
    }
    
    public function findPasswordResetToken(string $token): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM password_reset_tokens 
            WHERE token = ? AND expires_at > NOW()
        ");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    public function createPasswordResetToken(int $userId, string $token): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO password_reset_tokens (user_id, token, expires_at) 
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))
        ");
        return $stmt->execute([$userId, $token]);
    }
    
    public function deletePasswordResetToken(string $token): bool
    {
        $stmt = $this->db->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
        return $stmt->execute([$token]);
    }
    
    public function store2FABackupCodes(int $userId, string $encryptedCodes): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO user_2fa_backup_codes (user_id, encrypted_codes) 
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE encrypted_codes = VALUES(encrypted_codes)
        ");
        return $stmt->execute([$userId, $encryptedCodes]);
    }
    
    public function get2FABackupCodes(int $userId): ?string
    {
        $stmt = $this->db->prepare("
            SELECT encrypted_codes FROM user_2fa_backup_codes WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetchColumn();
        return $result !== false ? $result : null;
    }
    
    public function delete2FABackupCodes(int $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM user_2fa_backup_codes WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
    
    public function createApiToken(int $userId, string $tokenHash, string $name, ?array $scopes = null, ?\DateTime $expiresAt = null): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO api_tokens (user_id, token_hash, name, scopes, expires_at) 
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $userId,
            $tokenHash,
            $name,
            $scopes ? json_encode($scopes) : null,
            $expiresAt ? $expiresAt->format('Y-m-d H:i:s') : null
        ]);
    }
    
    public function findApiToken(string $tokenHash): ?array
    {
        $stmt = $this->db->prepare("
            SELECT at.*, u.* 
            FROM api_tokens at
            JOIN users u ON at.user_id = u.id
            WHERE at.token_hash = ? 
              AND (at.expires_at IS NULL OR at.expires_at > NOW())
        ");
        $stmt->execute([$tokenHash]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    public function updateApiTokenLastUsed(string $tokenHash): bool
    {
        $stmt = $this->db->prepare("
            UPDATE api_tokens SET last_used_at = NOW() WHERE token_hash = ?
        ");
        return $stmt->execute([$tokenHash]);
    }
    
    public function revokeApiToken(string $tokenHash): bool
    {
        $stmt = $this->db->prepare("DELETE FROM api_tokens WHERE token_hash = ?");
        return $stmt->execute([$tokenHash]);
    }
    
    public function getUserApiTokens(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT name, scopes, last_used_at, expires_at, created_at
            FROM api_tokens 
            WHERE user_id = ? 
              AND (expires_at IS NULL OR expires_at > NOW())
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function logSecurityEvent(string $eventType, ?int $userId, string $ipAddress, ?string $userAgent, array $details = []): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO security_events (event_type, user_id, ip_address, user_agent, details)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $eventType,
            $userId,
            $ipAddress,
            $userAgent,
            json_encode($details)
        ]);
    }
    
    public function getSecurityEvents(int $userId, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM security_events 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cleanupExpiredTokens(): int
    {
        $deletedCount = 0;
        
        // Clean up expired verification tokens
        $stmt = $this->db->prepare("DELETE FROM email_verification_tokens WHERE expires_at <= NOW()");
        $stmt->execute();
        $deletedCount += $stmt->rowCount();
        
        // Clean up expired password reset tokens
        $stmt = $this->db->prepare("DELETE FROM password_reset_tokens WHERE expires_at <= NOW()");
        $stmt->execute();
        $deletedCount += $stmt->rowCount();
        
        // Clean up expired API tokens
        $stmt = $this->db->prepare("DELETE FROM api_tokens WHERE expires_at <= NOW()");
        $stmt->execute();
        $deletedCount += $stmt->rowCount();
        
        return $deletedCount;
    }
}
