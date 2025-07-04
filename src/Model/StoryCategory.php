<?php
declare(strict_types=1);

namespace RenalTales\Model;

class StoryCategory extends Model
{
    protected static function getTable(): string
    {
        return 'story_categories';
    }

    protected static function getFields(): array
    {
        return [
            'id',
            'name',
            'slug',
            'description',
            'created_at',
            'updated_at'
        ];
    }

    protected static function getValidationRules(): array
    {
        return [
            'name' => 'required|max:50',
            'slug' => 'required|max:50|unique:story_categories',
            'description' => 'max:1000'
        ];
    }

    public function getStories(string $status = 'published'): array
    {
        return Story::where([
            'category_id' => $this->id,
            'status' => $status
        ]);
    }

    public function getAllStories(): array
    {
        return Story::where(['category_id' => $this->id]);
    }

    public static function findBySlug(string $slug): ?self
    {
        return self::findBy('slug', $slug);
    }

    public function setSlug(string $name): void
    {
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Ensure uniqueness
        $baseSlug = $slug;
        $counter = 1;
        while (self::findBySlug($slug) !== null) {
            $slug = $baseSlug . '-' . $counter++;
        }
        
        $this->slug = $slug;
    }

    public static function getStoryCounts(): array
    {
        $db = self::getDatabase();
        $sql = "
            SELECT c.id, c.name, COUNT(s.id) as story_count
            FROM story_categories c
            LEFT JOIN stories s ON c.id = s.category_id AND s.status = 'published'
            GROUP BY c.id, c.name
            ORDER BY c.name
        ";
        
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }
}
