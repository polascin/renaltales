<?php
declare(strict_types=1);

namespace RenalTales\Model;

use DateTime;
use Exception;

class Story extends Model
{
    protected static function getTable(): string
    {
        return 'stories';
    }

    protected static function getFields(): array
    {
        return [
            'id',
            'user_id',
            'category_id',
            'original_language',
            'status',
            'access_level',
            'created_at',
            'updated_at',
            'published_at'
        ];
    }
    
    protected static function getFillable(): array
    {
        return [
            'user_id',
            'category_id',
            'original_language',
            'status',
            'access_level',
            'published_at'
        ];
    }
    
    protected static function getCasts(): array
    {
        return [
            'published_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime'
        ];
    }
    
    protected static function getRelations(): array
    {
        return [
            'author' => [
                'type' => 'belongsTo',
                'model' => User::class,
                'foreign_key' => 'user_id'
            ],
            'category' => [
                'type' => 'belongsTo',
                'model' => StoryCategory::class,
                'foreign_key' => 'category_id'
            ],
            'contents' => [
                'type' => 'hasMany',
                'model' => StoryContent::class,
                'foreign_key' => 'story_id'
            ],
            'comments' => [
                'type' => 'hasMany',
                'model' => Comment::class,
                'foreign_key' => 'story_id'
            ]
        ];
    }

    protected static function getValidationRules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:story_categories,id',
            'original_language' => 'required|size:2',
            'status' => 'required|in:draft,pending_review,published,rejected',
            'access_level' => 'required|in:public,registered,verified,premium'
        ];
    }

    public function getAuthor(): ?User
    {
        return $this->getRelation('author') ?? User::find($this->user_id);
    }

    public function getCategory(): ?StoryCategory
    {
        return $this->getRelation('category') ?? StoryCategory::find($this->category_id);
    }

    public function getContents(): array
    {
        return $this->getRelation('contents') ?? StoryContent::where(['story_id' => $this->id]);
    }

    public function getContent(?string $language = null): ?StoryContent
    {
        if ($language === null) {
            $language = $this->original_language;
        }

        $contents = StoryContent::where([
            'story_id' => $this->id,
            'language' => $language,
            'status' => 'published'
        ]);

        return !empty($contents) ? $contents[0] : null;
    }

    public function publish(): bool
    {
        $this->status = 'published';
        $this->published_at = new DateTime();
        return $this->saveWithTransaction();
    }

    public function unpublish(): bool
    {
        $this->status = 'draft';
        $this->published_at = null;
        return $this->saveWithTransaction();
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at !== null;
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPendingReview(): bool
    {
        return $this->status === 'pending_review';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function submitForReview(): bool
    {
        $this->status = 'pending_review';
        return $this->saveWithTransaction();
    }

    public function reject(): bool
    {
        $this->status = 'rejected';
        return $this->saveWithTransaction();
    }

    public function getComments(): array
    {
        $comments = $this->getRelation('comments');
        if ($comments !== null) {
            return array_filter($comments, fn($comment) => $comment->status === 'approved');
        }
        return Comment::where(['story_id' => $this->id, 'status' => 'approved']);
    }

    public function getAllComments(): array
    {
        return $this->getRelation('comments') ?? Comment::where(['story_id' => $this->id]);
    }

    public function addTranslation(string $language, string $title, string $content, ?int $translatorId = null): StoryContent
    {
        if (strlen($language) !== 2) {
            throw new \InvalidArgumentException('Language code must be exactly 2 characters');
        }
        
        if (empty($title) || empty($content)) {
            throw new \InvalidArgumentException('Title and content are required');
        }
        
        if ($this->hasTranslation($language)) {
            throw new \InvalidArgumentException("Translation for language '{$language}' already exists");
        }
        
        $translation = new StoryContent([
            'story_id' => $this->id,
            'language' => $language,
            'title' => $title,
            'content' => $content,
            'translator_id' => $translatorId,
            'status' => 'draft'
        ]);
        
        if (!$translation->saveWithTransaction()) {
            throw new Exception('Failed to save translation');
        }
        
        return $translation;
    }

    public function hasTranslation(string $language): bool
    {
        $contents = StoryContent::where([
            'story_id' => $this->id,
            'language' => $language
        ]);
        return !empty($contents);
    }

    public function getAvailableLanguages(): array
    {
        $contents = StoryContent::where([
            'story_id' => $this->id,
            'status' => 'published'
        ]);
        
        return array_map(
            fn($content) => $content->language,
            $contents
        );
    }
    
    // Enhanced static methods
    public static function createStory(array $data): self
    {
        $story = new static();
        
        // Set default values
        $data['status'] = $data['status'] ?? 'draft';
        $data['access_level'] = $data['access_level'] ?? 'public';
        
        $story->fill($data);
        
        if (!$story->saveWithTransaction()) {
            throw new Exception('Failed to create story');
        }
        
        return $story;
    }
    
    public static function getPublishedStories(array $with = [], int $limit = null): array
    {
        $conditions = ['status' => 'published'];
        
        if ($limit !== null) {
            return static::findBy($conditions, $with, $limit);
        }
        
        return static::where($conditions, $with);
    }
    
    public static function getDraftStories(int $userId, array $with = []): array
    {
        return static::where([
            'user_id' => $userId,
            'status' => 'draft'
        ], $with);
    }
    
    public static function getPendingReviewStories(array $with = []): array
    {
        return static::where(['status' => 'pending_review'], $with);
    }
    
    public static function getStoriesByCategory(int $categoryId, array $with = []): array
    {
        return static::where([
            'category_id' => $categoryId,
            'status' => 'published'
        ], $with);
    }
    
    public function canBeEditedBy(User $user): bool
    {
        // Authors can edit their own stories, moderators and admins can edit any story
        return $this->user_id === $user->id || $user->canModerate();
    }
    
    public function canBeViewedBy(?User $user = null): bool
    {
        // Published stories visibility depends on access level
        if (!$this->isPublished()) {
            // Only author and moderators can view unpublished stories
            return $user && ($this->user_id === $user->id || $user->canModerate());
        }
        
        switch ($this->access_level) {
            case 'public':
                return true;
            case 'registered':
                return $user !== null;
            case 'verified':
                return $user && $user->isEmailVerified();
            case 'premium':
                return $user && $user->hasRole('premium');
            default:
                return false;
        }
    }
    
    public function updateContent(string $language, string $title, string $content): bool
    {
        $storyContent = $this->getContent($language);
        
        if (!$storyContent) {
            throw new \InvalidArgumentException("No content found for language: {$language}");
        }
        
        $storyContent->title = $title;
        $storyContent->content = $content;
        
        return $storyContent->saveWithTransaction();
    }
    
    public function getWordCount(string $language = null): int
    {
        $content = $this->getContent($language);
        
        if (!$content) {
            return 0;
        }
        
        return str_word_count(strip_tags($content->content));
    }
    
    public function getReadingTime(string $language = null): int
    {
        $wordCount = $this->getWordCount($language);
        
        // Assuming average reading speed of 200 words per minute
        return (int) ceil($wordCount / 200);
    }
    
    public function toArray(bool $includeRelations = false): array
    {
        $data = $this->attributes;
        
        if ($includeRelations) {
            foreach ($this->relations as $name => $relation) {
                if (is_array($relation)) {
                    $data[$name] = array_map(
                        fn($item) => method_exists($item, 'toArray') ? $item->toArray() : $item,
                        $relation
                    );
                } elseif ($relation && method_exists($relation, 'toArray')) {
                    $data[$name] = $relation->toArray();
                }
            }
        }
        
        return $data;
    }
}
