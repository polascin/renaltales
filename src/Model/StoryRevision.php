<?php
declare(strict_types=1);

namespace RenalTales\Model;

class StoryRevision extends Model
{
    protected static function getTable(): string
    {
        return 'story_revisions';
    }

    protected static function getFields(): array
    {
        return [
            'id',
            'story_content_id',
            'editor_id',
            'title',
            'content',
            'excerpt',
            'meta_description',
            'revision_notes',
            'created_at'
        ];
    }

    protected static function getValidationRules(): array
    {
        return [
            'story_content_id' => 'required|exists:story_contents,id',
            'editor_id' => 'required|exists:users,id',
            'title' => 'required|max:255',
            'content' => 'required',
            'excerpt' => 'max:1000',
            'meta_description' => 'max:255',
            'revision_notes' => 'max:1000'
        ];
    }

    public function getStoryContent(): ?StoryContent
    {
        return StoryContent::find($this->story_content_id);
    }

    public function getEditor(): ?User
    {
        return User::find($this->editor_id);
    }

    public function restore(): bool
    {
        $content = $this->getStoryContent();
        if (!$content) {
            return false;
        }

        $content->title = $this->title;
        $content->content = $this->content;
        $content->excerpt = $this->excerpt;
        $content->meta_description = $this->meta_description;
        
        return $content->save();
    }

    public function compareWith(?self $other = null): array
    {
        if ($other === null) {
            $content = $this->getStoryContent();
            if (!$content) {
                return [];
            }
            return [
                'title' => [
                    'old' => $content->title,
                    'new' => $this->title
                ],
                'content' => [
                    'old' => $content->content,
                    'new' => $this->content
                ],
                'excerpt' => [
                    'old' => $content->excerpt,
                    'new' => $this->excerpt
                ],
                'meta_description' => [
                    'old' => $content->meta_description,
                    'new' => $this->meta_description
                ]
            ];
        }

        return [
            'title' => [
                'old' => $other->title,
                'new' => $this->title
            ],
            'content' => [
                'old' => $other->content,
                'new' => $this->content
            ],
            'excerpt' => [
                'old' => $other->excerpt,
                'new' => $this->excerpt
            ],
            'meta_description' => [
                'old' => $other->meta_description,
                'new' => $this->meta_description
            ]
        ];
    }

    public static function getRecentRevisions(int $limit = 10): array
    {
        $db = self::getDatabase();
        $sql = "
            SELECT r.*, sc.title as story_title, u.username as editor_name
            FROM story_revisions r
            JOIN story_contents sc ON r.story_content_id = sc.id
            JOIN users u ON r.editor_id = u.id
            ORDER BY r.created_at DESC
            LIMIT :limit
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
