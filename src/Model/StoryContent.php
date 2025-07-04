<?php
declare(strict_types=1);

namespace RenalTales\Model;

class StoryContent extends Model
{
    protected static function getTable(): string
    {
        return 'story_contents';
    }

    protected static function getFields(): array
    {
        return [
            'id',
            'story_id',
            'language',
            'title',
            'content',
            'excerpt',
            'meta_description',
            'translator_id',
            'status',
            'created_at',
            'updated_at'
        ];
    }

    protected static function getValidationRules(): array
    {
        return [
            'story_id' => 'required|exists:stories,id',
            'language' => 'required|size:2',
            'title' => 'required|max:255',
            'content' => 'required',
            'excerpt' => 'max:1000',
            'meta_description' => 'max:255',
            'translator_id' => 'exists:users,id',
            'status' => 'required|in:draft,pending_review,published,rejected'
        ];
    }

    public function getStory(): ?Story
    {
        return Story::find($this->story_id);
    }

    public function getTranslator(): ?User
    {
        return $this->translator_id ? User::find($this->translator_id) : null;
    }

    public function createRevision(int $editorId, string $notes = ''): StoryRevision
    {
        $revision = new StoryRevision([
            'story_content_id' => $this->id,
            'editor_id' => $editorId,
            'title' => $this->title,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'meta_description' => $this->meta_description,
            'revision_notes' => $notes
        ]);
        $revision->save();
        return $revision;
    }

    public function getRevisions(): array
    {
        return StoryRevision::where(['story_content_id' => $this->id]);
    }

    public function publish(): void
    {
        $this->status = 'published';
        $this->save();

        // If this is the original language content, publish the story too
        $story = $this->getStory();
        if ($story && $story->original_language === $this->language) {
            $story->publish();
        }
    }

    public function unpublish(): void
    {
        $this->status = 'draft';
        $this->save();

        // If this is the original language content, unpublish the story too
        $story = $this->getStory();
        if ($story && $story->original_language === $this->language) {
            $story->unpublish();
        }
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

    public function assignTranslator(int $translatorId): void
    {
        $this->translator_id = $translatorId;
        $this->save();
    }

    public function removeTranslator(): void
    {
        $this->translator_id = null;
        $this->save();
    }

    public function generateExcerpt(int $length = 200): void
    {
        if (empty($this->excerpt)) {
            $this->excerpt = substr(strip_tags($this->content), 0, $length) . '...';
            $this->save();
        }
    }

    public function generateMetaDescription(): void
    {
        if (empty($this->meta_description)) {
            $this->meta_description = substr(strip_tags($this->content), 0, 155) . '...';
            $this->save();
        }
    }
}
