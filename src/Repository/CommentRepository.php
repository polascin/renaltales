<?php
declare(strict_types=1);

namespace RenalTales\Repository;

use RenalTales\Model\Comment;
use PDO;

class CommentRepository extends Repository
{
    protected string $table = 'comments';
    protected string $modelClass = Comment::class;
    protected array $allowedOrderBy = ['created_at', 'updated_at'];

    public function findByStory(int $storyId, string $status = 'approved'): array
    {
        return $this->findBy(
            ['story_id' => $storyId, 'status' => $status],
            ['created_at' => 'ASC']
        );
    }

    public function findByUser(int $userId, string $status = null): array
    {
        $criteria = ['user_id' => $userId];
        if ($status !== null) {
            $criteria['status'] = $status;
        }
        return $this->findBy($criteria, ['created_at' => 'DESC']);
    }

    public function findPendingModeration(): array
    {
        return $this->findBy(
            ['status' => 'pending'],
            ['created_at' => 'ASC']
        );
    }

    public function getReplies(int $commentId, string $status = 'approved'): array
    {
        return $this->findBy(
            ['parent_id' => $commentId, 'status' => $status],
            ['created_at' => 'ASC']
        );
    }

    public function getThreadedComments(int $storyId): array
    {
        $sql = "
            WITH RECURSIVE CommentTree AS (
                -- Base case: top-level comments
                SELECT 
                    c.*,
                    0 as depth,
                    CAST(LPAD(c.created_at, 20, '0') AS CHAR) as path
                FROM comments c
                WHERE c.story_id = :story_id
                AND c.parent_id IS NULL
                AND c.status = 'approved'
                
                UNION ALL
                
                -- Recursive case: replies
                SELECT 
                    c.*,
                    ct.depth + 1,
                    CONCAT(ct.path, ',', LPAD(c.created_at, 20, '0')) as path
                FROM comments c
                JOIN CommentTree ct ON c.parent_id = ct.id
                WHERE c.status = 'approved'
            )
            SELECT 
                ct.*,
                u.username as author_name
            FROM CommentTree ct
            JOIN users u ON ct.user_id = u.id
            ORDER BY ct.path
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':story_id', $storyId, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(
            fn($row) => new Comment($row),
            $stmt->fetchAll()
        );
    }

    public function getCommentStatistics(): array
    {
        $sql = "
            SELECT
                COUNT(*) as total_comments,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_comments,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_comments,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_comments,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as last_24h_comments,
                COUNT(CASE WHEN parent_id IS NOT NULL THEN 1 END) as reply_count
            FROM comments
        ";

        return $this->executeSingleResult($sql);
    }

    public function getRecentComments(int $limit = 10): array
    {
        $sql = "
            SELECT c.*, u.username as author_name, s.title as story_title
            FROM comments c
            JOIN users u ON c.user_id = u.id
            JOIN stories s ON c.story_id = s.id
            WHERE c.status = 'approved'
            ORDER BY c.created_at DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(
            fn($row) => new Comment($row),
            $stmt->fetchAll()
        );
    }

    public function getMostActiveDiscussions(int $limit = 5): array
    {
        $sql = "
            SELECT 
                s.id as story_id,
                s.title as story_title,
                COUNT(c.id) as comment_count,
                MAX(c.created_at) as last_comment_at
            FROM stories s
            JOIN comments c ON s.id = c.story_id
            WHERE c.status = 'approved'
            AND c.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY s.id
            ORDER BY comment_count DESC, last_comment_at DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getUserCommentStatistics(int $userId): array
    {
        $sql = "
            SELECT
                COUNT(*) as total_comments,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_comments,
                COUNT(CASE WHEN parent_id IS NOT NULL THEN 1 END) as replies_made,
                COUNT(DISTINCT story_id) as stories_commented
            FROM comments
            WHERE user_id = :user_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }
}
