<?php
declare(strict_types=1);

namespace RenalTales\Repository;

use PDO;

class TagRepository extends Repository
{
    protected string $table = 'tags';
    protected array $allowedOrderBy = ['name', 'created_at'];

    public function findBySlug(string $slug): ?array
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    public function findByName(string $name): ?array
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function createTag(string $name, string $description = null): array
    {
        $slug = $this->generateSlug($name);
        
        return $this->create([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function attachToStory(int $tagId, int $storyId): void
    {
        $sql = "
            INSERT IGNORE INTO story_tags (story_id, tag_id)
            VALUES (:story_id, :tag_id)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'story_id' => $storyId,
            'tag_id' => $tagId
        ]);
    }

    public function detachFromStory(int $tagId, int $storyId): void
    {
        $sql = "
            DELETE FROM story_tags
            WHERE story_id = :story_id AND tag_id = :tag_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'story_id' => $storyId,
            'tag_id' => $tagId
        ]);
    }

    public function getStoryTags(int $storyId): array
    {
        $sql = "
            SELECT t.*
            FROM tags t
            JOIN story_tags st ON t.id = st.tag_id
            WHERE st.story_id = :story_id
            ORDER BY t.name
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['story_id' => $storyId]);
        return $stmt->fetchAll();
    }

    public function getPopularTags(int $limit = 10): array
    {
        $sql = "
            SELECT 
                t.*,
                COUNT(st.story_id) as usage_count
            FROM tags t
            LEFT JOIN story_tags st ON t.id = st.tag_id
            GROUP BY t.id
            ORDER BY usage_count DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findRelatedTags(int $tagId): array
    {
        $sql = "
            SELECT 
                t2.*,
                COUNT(*) as relevance
            FROM tags t1
            JOIN story_tags st1 ON t1.id = st1.tag_id
            JOIN story_tags st2 ON st1.story_id = st2.story_id
            JOIN tags t2 ON st2.tag_id = t2.id
            WHERE t1.id = :tag_id
            AND t2.id != :tag_id
            GROUP BY t2.id
            ORDER BY relevance DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tag_id', $tagId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTagCloud(): array
    {
        $sql = "
            SELECT 
                t.*,
                COUNT(st.story_id) as story_count,
                MIN(s.created_at) as first_used,
                MAX(s.created_at) as last_used
            FROM tags t
            LEFT JOIN story_tags st ON t.id = st.tag_id
            LEFT JOIN stories s ON st.story_id = s.id
            GROUP BY t.id
            ORDER BY t.name
        ";

        return $this->executeQuery($sql);
    }

    public function findOrCreate(string $name, string $description = null): array
    {
        $existing = $this->findByName($name);
        if ($existing) {
            return $existing;
        }

        return $this->createTag($name, $description);
    }

    protected function generateSlug(string $name): string
    {
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Ensure uniqueness
        $baseSlug = $slug;
        $counter = 1;
        while ($this->findBySlug($slug)) {
            $slug = $baseSlug . '-' . $counter++;
        }
        
        return $slug;
    }

    public function mergeTags(int $sourceTagId, int $targetTagId): void
    {
        $this->db->beginTransaction();

        try {
            // Move all story associations to the target tag
            $sql = "
                INSERT IGNORE INTO story_tags (story_id, tag_id)
                SELECT story_id, :target_tag_id
                FROM story_tags
                WHERE tag_id = :source_tag_id
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'source_tag_id' => $sourceTagId,
                'target_tag_id' => $targetTagId
            ]);

            // Delete the source tag associations
            $sql = "DELETE FROM story_tags WHERE tag_id = :tag_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['tag_id' => $sourceTagId]);

            // Delete the source tag
            $sql = "DELETE FROM tags WHERE id = :tag_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['tag_id' => $sourceTagId]);

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function suggestTags(int $storyId, int $limit = 5): array
    {
        $sql = "
            WITH story_words AS (
                SELECT DISTINCT
                    LOWER(
                        REGEXP_REPLACE(
                            REGEXP_REPLACE(word, '[^a-zA-Z0-9]+', ''),
                            '^[0-9]+$', ''
                        )
                    ) as word
                FROM (
                    SELECT SUBSTRING_INDEX(
                        SUBSTRING_INDEX(
                            CONCAT(sc.title, ' ', sc.content),
                            ' ',
                            numbers.n
                        ),
                        ' ',
                        -1
                    ) as word
                    FROM story_contents sc
                    CROSS JOIN (
                        SELECT a.N + b.N * 10 + 1 n
                        FROM (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a
                        CROSS JOIN (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) b
                        ORDER BY n
                    ) numbers
                    WHERE sc.story_id = :story_id
                    AND CHAR_LENGTH(SUBSTRING_INDEX(CONCAT(sc.title, ' ', sc.content), ' ', numbers.n)) < CHAR_LENGTH(CONCAT(sc.title, ' ', sc.content))
                ) words
                WHERE LENGTH(word) > 3
            )
            SELECT 
                t.*,
                COUNT(*) as relevance
            FROM tags t
            JOIN story_tags st ON t.id = st.tag_id
            JOIN stories s ON st.story_id = s.id
            JOIN story_contents sc ON s.id = sc.story_id
            WHERE EXISTS (
                SELECT 1 FROM story_words sw
                WHERE LOWER(t.name) LIKE CONCAT('%', sw.word, '%')
            )
            AND s.id != :story_id
            GROUP BY t.id
            ORDER BY relevance DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':story_id', $storyId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
