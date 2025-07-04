<?php
declare(strict_types=1);

namespace RenalTales\Model;

use DateTime;

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
        return User::find($this->user_id);
    }

    public function getCategory(): ?StoryCategory
    {
        return StoryCategory::find($this->category_id);
    }

    public function getContents(): array
    {
        return StoryContent::where(['story_id' => $this->id]);
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

    public function publish(): void
    {
        $this->status = 'published';
        $this->published_at = new DateTime();
        $this->save();
    }

    public function unpublish(): void
    {
        $this->status = 'draft';
        $this->published_at = null;
        $this->save();
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

    public function submitForReview(): void
    {
        $this->status = 'pending_review';
        $this->save();
    }

    public function reject(): void
    {
        $this->status = 'rejected';
        $this->save();
    }

    public function getComments(): array
    {
        return Comment::where(['story_id' => $this->id, 'status' => 'approved']);
    }

    public function getAllComments(): array
    {
        return Comment::where(['story_id' => $this->id]);
    }

    public function addTranslation(string $language, string $title, string $content): StoryContent
    {
        $translation = new StoryContent([
            'story_id' => $this->id,
            'language' => $language,
            'title' => $title,
            'content' => $content,
            'status' => 'draft'
        ]);
        $translation->save();
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
}
