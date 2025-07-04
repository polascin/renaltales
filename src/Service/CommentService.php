<?php
declare(strict_types=1);

namespace RenalTales\Service;

use RenalTales\Repository\CommentRepository;
use RenalTales\Repository\StoryRepository;
use RenalTales\Model\User;
use RenalTales\Model\Comment;

class CommentService extends Service
{
    private CommentRepository $commentRepository;
    private StoryRepository $storyRepository;
    private const MAX_COMMENT_LENGTH = 1000;

    public function __construct()
    {
        parent::__construct();
        $this->commentRepository = new CommentRepository();
        $this->storyRepository = new StoryRepository();
    }

    public function addComment(array $data, User $user): Comment
    {
        $this->validateCommentData($data);

        $story = $this->storyRepository->find($data['story_id']);
        if (!$story) {
            throw new \RuntimeException('Story not found');
        }

        if (!$story->isPublished()) {
            throw new \RuntimeException('Cannot comment on unpublished stories');
        }

        // Check if user has access to the story
        if ($story->access_level !== 'public' && !$user->isVerified()) {
            throw new \RuntimeException('You do not have access to this story');
        }

        $comment = new Comment([
            'story_id' => $story->id,
            'user_id' => $user->id,
            'parent_id' => $data['parent_id'] ?? null,
            'content' => $this->sanitizeHTML($data['content']),
            'status' => $user->hasRole('moderator') ? 'approved' : 'pending'
        ]);

        if (!$comment->save()) {
            throw new \RuntimeException('Failed to save comment');
        }

        $this->logInfo('Comment added', [
            'comment_id' => $comment->id,
            'story_id' => $story->id,
            'user_id' => $user->id
        ]);

        return $comment;
    }

    public function updateComment(int $commentId, array $data, User $user): Comment
    {
        $comment = $this->commentRepository->find($commentId);
        if (!$comment) {
            throw new \RuntimeException('Comment not found');
        }

        if ($comment->user_id !== $user->id && !$user->hasRole('moderator')) {
            throw new \RuntimeException('Permission denied');
        }

        $this->validateCommentData($data, false);

        if (isset($data['content'])) {
            $comment->content = $this->sanitizeHTML($data['content']);
            
            // Reset approval status if content was changed by non-moderator
            if (!$user->hasRole('moderator')) {
                $comment->status = 'pending';
            }
        }

        if (!$comment->save()) {
            throw new \RuntimeException('Failed to update comment');
        }

        $this->logInfo('Comment updated', [
            'comment_id' => $comment->id,
            'user_id' => $user->id
        ]);

        return $comment;
    }

    public function deleteComment(int $commentId, User $user): void
    {
        $comment = $this->commentRepository->find($commentId);
        if (!$comment) {
            throw new \RuntimeException('Comment not found');
        }

        if ($comment->user_id !== $user->id && !$user->hasRole('moderator')) {
            throw new \RuntimeException('Permission denied');
        }

        // Start transaction to handle nested comments
        $this->commentRepository->beginTransaction();

        try {
            // Delete all replies first
            $replies = $this->commentRepository->getReplies($commentId);
            foreach ($replies as $reply) {
                $reply->delete();
            }

            // Delete the comment
            $comment->delete();

            $this->commentRepository->commit();

            $this->logInfo('Comment deleted', [
                'comment_id' => $commentId,
                'user_id' => $user->id
            ]);
        } catch (\Exception $e) {
            $this->commentRepository->rollBack();
            throw $e;
        }
    }

    public function approveComment(int $commentId, User $moderator): void
    {
        if (!$moderator->hasRole('moderator')) {
            throw new \RuntimeException('Only moderators can approve comments');
        }

        $comment = $this->commentRepository->find($commentId);
        if (!$comment) {
            throw new \RuntimeException('Comment not found');
        }

        if ($comment->status !== 'pending') {
            throw new \RuntimeException('Comment is not pending approval');
        }

        $comment->approve();

        $this->logInfo('Comment approved', [
            'comment_id' => $commentId,
            'moderator_id' => $moderator->id
        ]);
    }

    public function rejectComment(int $commentId, User $moderator, string $reason): void
    {
        if (!$moderator->hasRole('moderator')) {
            throw new \RuntimeException('Only moderators can reject comments');
        }

        $comment = $this->commentRepository->find($commentId);
        if (!$comment) {
            throw new \RuntimeException('Comment not found');
        }

        if ($comment->status !== 'pending') {
            throw new \RuntimeException('Comment is not pending approval');
        }

        $comment->reject();

        $this->logInfo('Comment rejected', [
            'comment_id' => $commentId,
            'moderator_id' => $moderator->id,
            'reason' => $reason
        ]);
    }

    public function getCommentThread(int $commentId): array
    {
        $comment = $this->commentRepository->find($commentId);
        if (!$comment) {
            throw new \RuntimeException('Comment not found');
        }

        return $this->commentRepository->getThreadedComments($comment->story_id);
    }

    public function getPendingComments(): array
    {
        return $this->commentRepository->findPendingModeration();
    }

    public function getRecentComments(int $limit = 10): array
    {
        return $this->commentRepository->getRecentComments($limit);
    }

    public function getMostActiveDiscussions(int $limit = 5): array
    {
        return $this->commentRepository->getMostActiveDiscussions($limit);
    }

    public function getUserCommentStatistics(int $userId): array
    {
        return $this->commentRepository->getUserCommentStatistics($userId);
    }

    private function validateCommentData(array $data, bool $requireAll = true): void
    {
        if ($requireAll) {
            $this->validateRequired($data, ['story_id', 'content']);
        }

        if (isset($data['content'])) {
            if (mb_strlen($data['content']) < 2) {
                throw new \InvalidArgumentException('Comment is too short');
            }
            if (mb_strlen($data['content']) > self::MAX_COMMENT_LENGTH) {
                throw new \InvalidArgumentException(
                    sprintf('Comment cannot be longer than %d characters', self::MAX_COMMENT_LENGTH)
                );
            }
        }

        if (isset($data['parent_id'])) {
            $parent = $this->commentRepository->find($data['parent_id']);
            if (!$parent) {
                throw new \InvalidArgumentException('Parent comment not found');
            }
            if ($parent->parent_id !== null) {
                throw new \InvalidArgumentException('Cannot nest comments more than one level deep');
            }
        }
    }

    private function checkSpam(string $content, User $user): bool
    {
        // Check if user has posted too many comments recently
        $recentComments = $this->commentRepository->count([
            'user_id' => $user->id,
            'created_at >=' => date('Y-m-d H:i:s', strtotime('-1 hour'))
        ]);

        if ($recentComments > 10) {
            throw new \RuntimeException('You are posting comments too quickly');
        }

        // Check for duplicate content
        $duplicates = $this->commentRepository->count([
            'user_id' => $user->id,
            'content' => $content,
            'created_at >=' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ]);

        if ($duplicates > 0) {
            throw new \RuntimeException('Duplicate comment detected');
        }

        // Add more spam detection logic as needed
        return false;
    }
}
