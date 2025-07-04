<?php
declare(strict_types=1);

namespace RenalTales\Repository;

class StoryStatisticsRepository extends Repository
{
    protected string $table = 'story_statistics';
    protected array $allowedOrderBy = [
        'view_count',
        'total_comments',
        'total_translations',
        'average_rating',
        'last_comment_at',
        'last_translation_at'
    ];

    public function incrementViews(int $storyId): void
    {
        $sql = "
            UPDATE story_statistics 
            SET view_count = view_count + 1,
                updated_at = CURRENT_TIMESTAMP
            WHERE story_id = :story_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['story_id' => $storyId]);
    }

    public function getPopularStories(string $metric = 'view_count', int $limit = 10): array
    {
        if (!in_array($metric, $this->allowedOrderBy)) {
            throw new \InvalidArgumentException("Invalid metric: {$metric}");
        }

        $sql = "
            SELECT 
                s.*,
                ss.view_count,
                ss.total_comments,
                ss.total_translations,
                ss.average_rating
            FROM story_statistics ss
            JOIN stories s ON ss.story_id = s.id
            WHERE s.status = 'published'
            ORDER BY ss.{$metric} DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getTrendingStories(int $days = 7, int $limit = 10): array
    {
        $sql = "
            SELECT 
                s.*,
                ss.view_count,
                ss.total_comments,
                ss.total_translations,
                ss.average_rating,
                (
                    ss.view_count * 1 +
                    ss.total_comments * 2 +
                    ss.total_translations * 3 +
                    (ss.average_rating * 10) * 2
                ) as trend_score
            FROM story_statistics ss
            JOIN stories s ON ss.story_id = s.id
            WHERE s.status = 'published'
            AND ss.updated_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
            ORDER BY trend_score DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getStoryStats(int $storyId): array
    {
        $sql = "
            SELECT 
                view_count,
                total_comments,
                total_translations,
                average_rating,
                last_comment_at,
                last_translation_at
            FROM story_statistics
            WHERE story_id = :story_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['story_id' => $storyId]);
        return $stmt->fetch() ?: [];
    }

    public function getMostEngagingStories(int $limit = 10): array
    {
        $sql = "
            SELECT 
                s.*,
                ss.total_comments,
                ss.average_rating,
                ss.view_count,
                ROUND(
                    (ss.total_comments / ss.view_count) * 100, 
                    2
                ) as engagement_rate
            FROM story_statistics ss
            JOIN stories s ON ss.story_id = s.id
            WHERE s.status = 'published'
            AND ss.view_count > 0
            ORDER BY engagement_rate DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function rebuildStatistics(): void
    {
        $this->db->beginTransaction();

        try {
            // Clear existing statistics
            $this->db->exec("TRUNCATE TABLE story_statistics");

            // Rebuild comment counts and last comment dates
            $sql = "
                INSERT INTO story_statistics (
                    story_id, 
                    total_comments,
                    last_comment_at
                )
                SELECT 
                    story_id,
                    COUNT(*) as total_comments,
                    MAX(created_at) as last_comment_at
                FROM comments
                WHERE status = 'approved'
                GROUP BY story_id
            ";
            $this->db->exec($sql);

            // Update translation counts
            $sql = "
                UPDATE story_statistics ss
                JOIN (
                    SELECT 
                        story_id,
                        COUNT(*) as translation_count,
                        MAX(created_at) as last_translation
                    FROM story_contents
                    WHERE status = 'published'
                    GROUP BY story_id
                ) t ON ss.story_id = t.story_id
                SET 
                    ss.total_translations = t.translation_count,
                    ss.last_translation_at = t.last_translation
            ";
            $this->db->exec($sql);

            // Update ratings
            $sql = "
                UPDATE story_statistics ss
                JOIN (
                    SELECT 
                        story_id,
                        AVG(rating) as avg_rating
                    FROM story_ratings
                    GROUP BY story_id
                ) r ON ss.story_id = r.story_id
                SET ss.average_rating = r.avg_rating
            ";
            $this->db->exec($sql);

            // Update view counts
            $sql = "
                UPDATE story_statistics ss
                JOIN (
                    SELECT 
                        story_id,
                        COUNT(*) as views
                    FROM story_views
                    GROUP BY story_id
                ) v ON ss.story_id = v.story_id
                SET ss.view_count = v.views
            ";
            $this->db->exec($sql);

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
