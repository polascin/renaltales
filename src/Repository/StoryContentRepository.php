<?php
declare(strict_types=1);

namespace RenalTales\Repository;

use RenalTales\Model\StoryContent;
use PDO;

class StoryContentRepository extends Repository
{
    protected string $table = 'story_contents';
    protected string $modelClass = StoryContent::class;
    protected array $allowedOrderBy = ['created_at', 'updated_at'];

    public function findByStoryAndLanguage(int $storyId, string $language): ?StoryContent
    {
        return $this->findOneBy([
            'story_id' => $storyId,
            'language' => $language
        ]);
    }

    public function findByStory(int $storyId, string $status = null): array
    {
        $criteria = ['story_id' => $storyId];
        if ($status !== null) {
            $criteria['status'] = $status;
        }
        return $this->findBy($criteria);
    }

    public function findByTranslator(int $translatorId, string $status = null): array
    {
        $criteria = ['translator_id' => $translatorId];
        if ($status !== null) {
            $criteria['status'] = $status;
        }
        return $this->findBy($criteria, ['updated_at' => 'DESC']);
    }

    public function findPendingReview(): array
    {
        return $this->findBy(
            ['status' => 'pending_review'],
            ['updated_at' => 'DESC']
        );
    }

    public function getTranslationStatistics(): array
    {
        $sql = "
            SELECT 
                language,
                COUNT(*) as total_translations,
                COUNT(CASE WHEN status = 'published' THEN 1 END) as published_translations,
                COUNT(CASE WHEN status = 'pending_review' THEN 1 END) as pending_review,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as created_last_30_days
            FROM story_contents
            GROUP BY language
            ORDER BY total_translations DESC
        ";

        return $this->executeQuery($sql);
    }

    public function getTranslatorStatistics(int $translatorId): array
    {
        $sql = "
            SELECT 
                language,
                COUNT(*) as total_translations,
                COUNT(CASE WHEN status = 'published' THEN 1 END) as published_translations,
                COUNT(CASE WHEN status = 'pending_review' THEN 1 END) as pending_review
            FROM story_contents
            WHERE translator_id = :translator_id
            GROUP BY language
            ORDER BY total_translations DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':translator_id', $translatorId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getRecentTranslations(int $limit = 10): array
    {
        $sql = "
            SELECT sc.*, s.title as story_title, u.username as translator_name
            FROM story_contents sc
            JOIN stories s ON sc.story_id = s.id
            LEFT JOIN users u ON sc.translator_id = u.id
            WHERE sc.status = 'published'
            ORDER BY sc.updated_at DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(
            fn($row) => new StoryContent($row),
            $stmt->fetchAll()
        );
    }

    public function searchTranslations(string $query, string $language = null): array
    {
        $sql = "
            SELECT sc.*
            FROM story_contents sc
            WHERE (
                sc.title LIKE :query
                OR sc.content LIKE :query
                OR sc.excerpt LIKE :query
            )
            AND sc.status = 'published'
        ";

        if ($language !== null) {
            $sql .= " AND sc.language = :language";
        }

        $sql .= " ORDER BY sc.updated_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':query', "%{$query}%");
        if ($language !== null) {
            $stmt->bindValue(':language', $language);
        }
        $stmt->execute();

        return array_map(
            fn($row) => new StoryContent($row),
            $stmt->fetchAll()
        );
    }

    public function findIncompleteTranslations(): array
    {
        $sql = "
            SELECT sc.*
            FROM story_contents sc
            WHERE sc.status IN ('draft', 'pending_review')
            AND sc.translator_id IS NOT NULL
            ORDER BY sc.updated_at DESC
        ";

        return array_map(
            fn($row) => new StoryContent($row),
            $this->executeQuery($sql)
        );
    }

    public function getLanguageCoverage(): array
    {
        $sql = "
            SELECT 
                s.id as story_id,
                s.original_language,
                GROUP_CONCAT(DISTINCT sc.language) as translated_languages
            FROM stories s
            LEFT JOIN story_contents sc ON s.id = sc.story_id AND sc.status = 'published'
            WHERE s.status = 'published'
            GROUP BY s.id
        ";

        return $this->executeQuery($sql);
    }
}
