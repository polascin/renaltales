<?php
declare(strict_types=1);

namespace RenalTales\Repository;

use RenalTales\Model\Story;
use PDO;

class StoryRepository extends Repository
{
    protected string $table = 'stories';
    protected string $modelClass = Story::class;
    protected array $allowedOrderBy = ['created_at', 'updated_at', 'published_at'];

    public function findPublished(array $orderBy = [], int $limit = null): array
    {
        return $this->findBy(
            ['status' => 'published'],
            $orderBy ?: ['published_at' => 'DESC'],
            $limit
        );
    }

    public function findByCategory(int $categoryId, string $status = 'published'): array
    {
        return $this->findBy(
            [
                'category_id' => $categoryId,
                'status' => $status
            ],
            ['published_at' => 'DESC']
        );
    }

    public function findByUser(int $userId, ?string $status = null): array
    {
        $criteria = ['user_id' => $userId];
        if ($status !== null) {
            $criteria['status'] = $status;
        }
        return $this->findBy($criteria, ['created_at' => 'DESC']);
    }

    public function findPendingReview(): array
    {
        return $this->findBy(
            ['status' => 'pending_review'],
            ['updated_at' => 'DESC']
        );
    }

    public function findByLanguage(string $language, string $status = 'published'): array
    {
        $sql = "
            SELECT DISTINCT s.*
            FROM stories s
            JOIN story_contents sc ON s.id = sc.story_id
            WHERE sc.language = :language
            AND sc.status = :status
            ORDER BY s.published_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'language' => $language,
            'status' => $status
        ]);

        return array_map(
            fn($row) => new Story($row),
            $stmt->fetchAll()
        );
    }

    public function findNeedingTranslation(string $language): array
    {
        $sql = "
            SELECT s.*
            FROM stories s
            WHERE s.status = 'published'
            AND NOT EXISTS (
                SELECT 1
                FROM story_contents sc
                WHERE sc.story_id = s.id
                AND sc.language = :language
            )
            ORDER BY s.published_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['language' => $language]);

        return array_map(
            fn($row) => new Story($row),
            $stmt->fetchAll()
        );
    }

    public function getStoryStatistics(): array
    {
        $sql = "
            SELECT
                COUNT(*) as total_stories,
                COUNT(CASE WHEN status = 'published' THEN 1 END) as published_stories,
                COUNT(CASE WHEN status = 'pending_review' THEN 1 END) as pending_review,
                COUNT(CASE WHEN access_level = 'public' THEN 1 END) as public_stories,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as created_last_30_days
            FROM stories
        ";

        return $this->executeSingleResult($sql);
    }

    public function getPopularStories(int $limit = 10): array
    {
        $sql = "
            SELECT s.*, 
                   COUNT(DISTINCT c.id) as comment_count,
                   COUNT(DISTINCT sc.id) as translation_count
            FROM stories s
            LEFT JOIN comments c ON s.id = c.story_id AND c.status = 'approved'
            LEFT JOIN story_contents sc ON s.id = sc.story_id AND sc.status = 'published'
            WHERE s.status = 'published'
            GROUP BY s.id
            ORDER BY (COUNT(DISTINCT c.id) + COUNT(DISTINCT sc.id)) DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(
            fn($row) => new Story($row),
            $stmt->fetchAll()
        );
    }

    public function search(string $query, string $language = null, array $options = []): array
    {
        $sql = "
            SELECT DISTINCT 
                s.*,
                MATCH(sc.title, sc.content, sc.excerpt) AGAINST (:query IN BOOLEAN MODE) as relevance,
                sc.title as matched_title,
                SUBSTRING(sc.content, 
                    GREATEST(1, LOCATE(:simple_query, sc.content) - 100),
                    200
                ) as content_excerpt
            FROM stories s
            JOIN story_contents sc ON s.id = sc.story_id
            WHERE s.status = 'published'
            AND sc.status = 'published'
            AND MATCH(sc.title, sc.content, sc.excerpt) AGAINST (:query IN BOOLEAN MODE)
        ";

        // Add category filter if specified
        if (!empty($options['category_id'])) {
            $sql .= " AND s.category_id = :category_id";
        }

        // Add access level filter if specified
        if (!empty($options['access_level'])) {
            $sql .= " AND s.access_level = :access_level";
        }

        // Add date range filter if specified
        if (!empty($options['date_from'])) {
            $sql .= " AND s.created_at >= :date_from";
        }
        if (!empty($options['date_to'])) {
            $sql .= " AND s.created_at <= :date_to";
        }

        if ($language !== null) {
            $sql .= " AND sc.language = :language";
        }

        $sql .= " ORDER BY s.published_at DESC";

        $stmt = $this->db->prepare($sql);
        // Prepare search terms
        $searchTerms = '+' . implode(' +', array_filter(explode(' ', $query)));
        $stmt->bindValue(':query', $searchTerms);
        $stmt->bindValue(':simple_query', $query);

        // Bind additional parameters if present
        if (!empty($options['category_id'])) {
            $stmt->bindValue(':category_id', $options['category_id'], \PDO::PARAM_INT);
        }
        if (!empty($options['access_level'])) {
            $stmt->bindValue(':access_level', $options['access_level']);
        }
        if (!empty($options['date_from'])) {
            $stmt->bindValue(':date_from', $options['date_from']);
        }
        if (!empty($options['date_to'])) {
            $stmt->bindValue(':date_to', $options['date_to']);
        }
        if ($language !== null) {
            $stmt->bindValue(':language', $language);
        }
        $stmt->execute();

        return array_map(
            fn($row) => new Story($row),
            $stmt->fetchAll()
        );
    }

    public function getStoriesByTranslator(int $translatorId): array
    {
        $sql = "
            SELECT DISTINCT s.*
            FROM stories s
            JOIN story_contents sc ON s.id = sc.story_id
            WHERE sc.translator_id = :translator_id
            ORDER BY sc.updated_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':translator_id', $translatorId, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(
            fn($row) => new Story($row),
            $stmt->fetchAll()
        );
    }

    public function getTranslationProgress(): array
    {
        $sql = "
            SELECT 
                s.id,
                s.original_language,
                COUNT(DISTINCT sc.language) as translation_count,
                GROUP_CONCAT(DISTINCT sc.language) as available_languages
            FROM stories s
            LEFT JOIN story_contents sc ON s.id = sc.story_id AND sc.status = 'published'
            WHERE s.status = 'published'
            GROUP BY s.id
        ";

        return $this->executeQuery($sql);
    }
}
