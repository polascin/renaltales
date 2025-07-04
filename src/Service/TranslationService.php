<?php
declare(strict_types=1);

namespace RenalTales\Service;

use RenalTales\Repository\StoryContentRepository;
use RenalTales\Model\User;
use RenalTales\Model\StoryContent;

class TranslationService extends Service
{
    private StoryContentRepository $contentRepository;

    public function __construct()
    {
        parent::__construct();
        $this->contentRepository = new StoryContentRepository();
    }

    public function translateStory(int $storyId, array $data, User $translator): StoryContent
    {
        $this->validateTranslationData($data);
        $this->validateTranslatorAccess($translator);

        // Check if translation already exists
        $existingTranslation = $this->contentRepository->findByStoryAndLanguage(
            $storyId,
            $data['language']
        );

        if ($existingTranslation) {
            throw new \RuntimeException('Translation already exists for this language');
        }

        $translation = new StoryContent([
            'story_id' => $storyId,
            'language' => $data['language'],
            'title' => $this->sanitizeString($data['title']),
            'content' => $this->sanitizeHTML($data['content']),
            'translator_id' => $translator->id,
            'status' => 'draft'
        ]);

        if (!$translation->save()) {
            throw new \RuntimeException('Failed to create translation');
        }

        $translation->generateExcerpt();
        $translation->generateMetaDescription();

        $this->logInfo('Translation created', [
            'story_id' => $storyId,
            'language' => $data['language'],
            'translator_id' => $translator->id
        ]);

        return $translation;
    }

    public function updateTranslation(int $translationId, array $data, User $translator): StoryContent
    {
        $translation = $this->contentRepository->find($translationId);
        if (!$translation) {
            throw new \RuntimeException('Translation not found');
        }

        $this->validateTranslationAccess($translation, $translator);
        $this->validateTranslationData($data, false);

        // Create revision before updating
        $translation->createRevision(
            $translator->id,
            $data['revision_notes'] ?? 'Translation updated'
        );

        if (isset($data['title'])) {
            $translation->title = $this->sanitizeString($data['title']);
        }
        if (isset($data['content'])) {
            $translation->content = $this->sanitizeHTML($data['content']);
            $translation->generateExcerpt();
            $translation->generateMetaDescription();
        }

        if (!$translation->save()) {
            throw new \RuntimeException('Failed to update translation');
        }

        $this->logInfo('Translation updated', [
            'translation_id' => $translationId,
            'translator_id' => $translator->id
        ]);

        return $translation;
    }

    public function submitForReview(int $translationId, User $translator): void
    {
        $translation = $this->contentRepository->find($translationId);
        if (!$translation) {
            throw new \RuntimeException('Translation not found');
        }

        $this->validateTranslationAccess($translation, $translator);

        if ($translation->status !== 'draft') {
            throw new \RuntimeException('Only draft translations can be submitted for review');
        }

        $translation->submitForReview();

        $this->logInfo('Translation submitted for review', [
            'translation_id' => $translationId,
            'translator_id' => $translator->id
        ]);
    }

    public function approveTranslation(int $translationId, User $moderator): void
    {
        $translation = $this->contentRepository->find($translationId);
        if (!$translation) {
            throw new \RuntimeException('Translation not found');
        }

        if (!$moderator->hasRole('moderator') && !$moderator->hasRole('admin')) {
            throw new \RuntimeException('Insufficient permissions');
        }

        if ($translation->status !== 'pending_review') {
            throw new \RuntimeException('Only translations pending review can be approved');
        }

        $translation->publish();

        $this->logInfo('Translation approved', [
            'translation_id' => $translationId,
            'moderator_id' => $moderator->id
        ]);
    }

    public function rejectTranslation(int $translationId, User $moderator, string $reason): void
    {
        $translation = $this->contentRepository->find($translationId);
        if (!$translation) {
            throw new \RuntimeException('Translation not found');
        }

        if (!$moderator->hasRole('moderator') && !$moderator->hasRole('admin')) {
            throw new \RuntimeException('Insufficient permissions');
        }

        if ($translation->status !== 'pending_review') {
            throw new \RuntimeException('Only translations pending review can be rejected');
        }

        $translation->reject();

        $this->logInfo('Translation rejected', [
            'translation_id' => $translationId,
            'moderator_id' => $moderator->id,
            'reason' => $reason
        ]);
    }

    public function assignTranslator(int $translationId, int $translatorId, User $moderator): void
    {
        $translation = $this->contentRepository->find($translationId);
        if (!$translation) {
            throw new \RuntimeException('Translation not found');
        }

        if (!$moderator->hasRole('moderator') && !$moderator->hasRole('admin')) {
            throw new \RuntimeException('Insufficient permissions');
        }

        $translation->assignTranslator($translatorId);

        $this->logInfo('Translator assigned', [
            'translation_id' => $translationId,
            'translator_id' => $translatorId,
            'moderator_id' => $moderator->id
        ]);
    }

    public function getTranslationProgress(int $storyId): array
    {
        return $this->contentRepository->getLanguageCoverage($storyId);
    }

    public function findAvailableTranslations(int $storyId): array
    {
        $translations = $this->contentRepository->findByStory($storyId, 'published');
        return array_column($translations, 'language');
    }

    public function findMissingTranslations(int $storyId): array
    {
        $available = $this->findAvailableTranslations($storyId);
        return array_diff(
            $this->config->get('languages.supported'),
            $available
        );
    }

    private function validateTranslationData(array $data, bool $requireAll = true): void
    {
        $required = $requireAll ? ['language', 'title', 'content'] : [];
        if ($required) {
            $this->validateRequired($data, $required);
        }

        if (isset($data['language'])) {
            $this->validateEnum(
                $data['language'],
                'Language',
                $this->config->get('languages.supported')
            );
        }

        if (isset($data['title'])) {
            $this->validateLength($data['title'], 'Title', 3, 255);
        }

        if (isset($data['content']) && mb_strlen($data['content']) < 100) {
            throw new \InvalidArgumentException('Content must be at least 100 characters');
        }
    }

    private function validateTranslatorAccess(User $translator): void
    {
        if (!$translator->hasRole('translator') && !$translator->hasRole('admin')) {
            throw new \RuntimeException('User must be a translator');
        }
    }

    private function validateTranslationAccess(StoryContent $translation, User $user): void
    {
        if ($translation->translator_id !== $user->id 
            && !$user->hasRole('moderator')
            && !$user->hasRole('admin')
        ) {
            throw new \RuntimeException('Access denied');
        }
    }
}
