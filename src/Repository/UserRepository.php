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
}
