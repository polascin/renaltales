<?php
declare(strict_types=1);

namespace RenalTales\Service;

use RenalTales\Repository\StoryRepository;
use RenalTales\Repository\StoryContentRepository;
use RenalTales\Repository\StoryStatisticsRepository;
use RenalTales\Repository\TagRepository;
use RenalTales\Model\Story;
use RenalTales\Model\User;

class StoryService extends Service
{
    private StoryRepository $storyRepository;
    private StoryContentRepository $contentRepository;
    private StoryStatisticsRepository $statsRepository;
    private TagRepository $tagRepository;

    public function __construct()
    {
        parent::__construct();
        $this->storyRepository = new StoryRepository();
        $this->contentRepository = new StoryContentRepository();
        $this->statsRepository = new StoryStatisticsRepository();
        $this->tagRepository = new TagRepository();
    }

    public function createStory(array $data, User $author): Story
    {
        $this->validateStoryData($data);

        // Start transaction
        $this->storyRepository->beginTransaction();

        try {
            // Create story
            $story = new Story([
                'user_id' => $author->id,
                'category_id' => $data['category_id'],
                'original_language' => $data['language'] ?? $author->language_preference,
                'status' => 'draft',
                'access_level' => $data['access_level'] ?? 'public'
            ]);
            
            if (!$story->save()) {
                throw new \RuntimeException('Failed to create story');
            }

            // Create initial content
            $content = $story->addTranslation(
                $story->original_language,
                $this->sanitizeString($data['title']),
                $this->sanitizeHTML($data['content'])
            );

            // Generate excerpt and meta description
            $content->generateExcerpt();
            $content->generateMetaDescription();

            // Add tags if provided
            if (!empty($data['tags'])) {
                $this->addTags($story->id, $data['tags']);
            }

            $this->storyRepository->commit();
            $this->logInfo('Story created', ['story_id' => $story->id]);
            
            return $story;
        } catch (\Exception $e) {
            $this->storyRepository->rollBack();
            $this->logError('Failed to create story', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateStory(int $storyId, array $data, User $editor): Story
    {
        $story = $this->storyRepository->find($storyId);
        if (!$story) {
            throw new \RuntimeException('Story not found');
        }

        $this->validateStoryAccess($story, $editor);
        $this->validateStoryData($data);

        $this->storyRepository->beginTransaction();

        try {
            // Update story metadata
            if (isset($data['category_id'])) {
                $story->category_id = $data['category_id'];
            }
            if (isset($data['access_level'])) {
                $story->access_level = $data['access_level'];
            }
            $story->save();

            // Update content
            $content = $story->getContent($story->original_language);
            if (!$content) {
                throw new \RuntimeException('Story content not found');
            }

            // Create revision before updating
            $content->createRevision($editor->id, $data['revision_notes'] ?? '');

            if (isset($data['title'])) {
                $content->title = $this->sanitizeString($data['title']);
            }
            if (isset($data['content'])) {
                $content->content = $this->sanitizeHTML($data['content']);
                $content->generateExcerpt();
                $content->generateMetaDescription();
            }
            $content->save();

            // Update tags
            if (isset($data['tags'])) {
                $this->updateTags($story->id, $data['tags']);
            }

            $this->storyRepository->commit();
            $this->logInfo('Story updated', ['story_id' => $story->id]);
            
            return $story;
        } catch (\Exception $e) {
            $this->storyRepository->rollBack();
            $this->logError('Failed to update story', [
                'story_id' => $storyId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function submitForReview(int $storyId, User $user): void
    {
        $story = $this->storyRepository->find($storyId);
        if (!$story) {
            throw new \RuntimeException('Story not found');
        }

        $this->validateStoryAccess($story, $user);

        if (!$story->isDraft()) {
            throw new \RuntimeException('Only draft stories can be submitted for review');
        }

        $story->submitForReview();
        $this->logInfo('Story submitted for review', ['story_id' => $story->id]);
    }

    public function publish(int $storyId, User $moderator): void
    {
        $story = $this->storyRepository->find($storyId);
        if (!$story) {
            throw new \RuntimeException('Story not found');
        }

        if (!$moderator->hasRole('moderator') && !$moderator->hasRole('admin')) {
            throw new \RuntimeException('Insufficient permissions');
        }

        if (!$story->isPendingReview()) {
            throw new \RuntimeException('Only stories pending review can be published');
        }

        $story->publish();
        $this->logInfo('Story published', [
            'story_id' => $story->id,
            'moderator_id' => $moderator->id
        ]);
    }

    public function reject(int $storyId, User $moderator, string $reason): void
    {
        $story = $this->storyRepository->find($storyId);
        if (!$story) {
            throw new \RuntimeException('Story not found');
        }

        if (!$moderator->hasRole('moderator') && !$moderator->hasRole('admin')) {
            throw new \RuntimeException('Insufficient permissions');
        }

        if (!$story->isPendingReview()) {
            throw new \RuntimeException('Only stories pending review can be rejected');
        }

        $story->reject();
        $this->logInfo('Story rejected', [
            'story_id' => $story->id,
            'moderator_id' => $moderator->id,
            'reason' => $reason
        ]);
    }

    public function addTranslation(int $storyId, array $data, User $translator): void
    {
        $story = $this->storyRepository->find($storyId);
        if (!$story) {
            throw new \RuntimeException('Story not found');
        }

        if (!$translator->hasRole('translator') && !$translator->hasRole('admin')) {
            throw new \RuntimeException('Insufficient permissions');
        }

        $this->validateRequired($data, ['language', 'title', 'content']);
        $this->validateEnum($data['language'], 'Language', $this->config->get('languages.supported'));

        if ($story->hasTranslation($data['language'])) {
            throw new \RuntimeException('Translation already exists');
        }

        $translation = $story->addTranslation(
            $data['language'],
            $this->sanitizeString($data['title']),
            $this->sanitizeHTML($data['content'])
        );
        
        $translation->assignTranslator($translator->id);
        $translation->generateExcerpt();
        $translation->generateMetaDescription();

        $this->logInfo('Translation added', [
            'story_id' => $story->id,
            'language' => $data['language'],
            'translator_id' => $translator->id
        ]);
    }

    public function search(string $query, array $options = []): array
    {
        return $this->storyRepository->search($query, $options['language'] ?? null, $options);
    }

    public function trackView(int $storyId, ?User $user, string $ipAddress): void
    {
        $this->statsRepository->incrementViews($storyId);
        
        // Record detailed view statistics
        $viewData = [
            'story_id' => $storyId,
            'ip_address' => $ipAddress,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        
        if ($user) {
            $viewData['user_id'] = $user->id;
        }

        $this->statsRepository->recordView($viewData);
    }

    private function validateStoryData(array $data): void
    {
        $this->validateRequired($data, ['title', 'content', 'category_id']);
        
        if (isset($data['access_level'])) {
            $this->validateEnum(
                $data['access_level'],
                'Access level',
                ['public', 'registered', 'verified', 'premium']
            );
        }

        $this->validateLength($data['title'], 'Title', 3, 255);
        
        if (mb_strlen($data['content']) < 100) {
            throw new \InvalidArgumentException('Content must be at least 100 characters');
        }
    }

    private function validateStoryAccess(Story $story, User $user): void
    {
        if ($story->user_id !== $user->id && !$user->hasRole('moderator') && !$user->hasRole('admin')) {
            throw new \RuntimeException('Access denied');
        }
    }

    private function addTags(int $storyId, array $tags): void
    {
        foreach ($tags as $tagName) {
            $tag = $this->tagRepository->findOrCreate($tagName);
            $this->tagRepository->attachToStory($tag['id'], $storyId);
        }
    }

    private function updateTags(int $storyId, array $newTags): void
    {
        $currentTags = $this->tagRepository->getStoryTags($storyId);
        $currentTagNames = array_column($currentTags, 'name');
        
        // Add new tags
        $tagsToAdd = array_diff($newTags, $currentTagNames);
        foreach ($tagsToAdd as $tagName) {
            $tag = $this->tagRepository->findOrCreate($tagName);
            $this->tagRepository->attachToStory($tag['id'], $storyId);
        }
        
        // Remove old tags
        $tagsToRemove = array_diff($currentTagNames, $newTags);
        foreach ($tagsToRemove as $tagName) {
            $tag = $this->tagRepository->findByName($tagName);
            if ($tag) {
                $this->tagRepository->detachFromStory($tag['id'], $storyId);
            }
        }
    }
}
