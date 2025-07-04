<?php
declare(strict_types=1);

namespace RenalTales\Service;

use RenalTales\Repository\UserRepository;
use RenalTales\Repository\StoryRepository;
use RenalTales\Repository\StoryContentRepository;
use RenalTales\Model\User;

class UserService extends Service
{
    private UserRepository $userRepository;
    private StoryRepository $storyRepository;
    private StoryContentRepository $contentRepository;

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = new UserRepository();
        $this->storyRepository = new StoryRepository();
        $this->contentRepository = new StoryContentRepository();
    }

    public function updateProfile(int $userId, array $data, User $actor): User
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new \RuntimeException('User not found');
        }

        // Check permissions
        if ($actor->id !== $userId && !$actor->hasRole('admin')) {
            throw new \RuntimeException('Permission denied');
        }

        $this->validateProfileData($data);

        // Start transaction
        $this->userRepository->beginTransaction();

        try {
            if (isset($data['email']) && $data['email'] !== $user->email) {
                if ($this->userRepository->findByEmail($data['email'])) {
                    throw new \RuntimeException('Email already in use');
                }
                $user->email = $data['email'];
                $user->email_verified_at = null;
                // TODO: Send verification email
            }

            if (isset($data['username']) && $data['username'] !== $user->username) {
                if ($this->userRepository->findByUsername($data['username'])) {
                    throw new \RuntimeException('Username already taken');
                }
                $user->username = $this->sanitizeString($data['username']);
            }

            if (isset($data['full_name'])) {
                $user->full_name = $this->sanitizeString($data['full_name']);
            }

            if (isset($data['language_preference'])) {
                $user->language_preference = $data['language_preference'];
            }

            if (!$user->save()) {
                throw new \RuntimeException('Failed to update profile');
            }

            $this->userRepository->commit();
            $this->logInfo('Profile updated', ['user_id' => $user->id]);

            return $user;
        } catch (\Exception $e) {
            $this->userRepository->rollBack();
            throw $e;
        }
    }

    public function updateRole(int $userId, string $newRole, User $admin): void
    {
        if (!$admin->hasRole('admin')) {
            throw new \RuntimeException('Only administrators can change roles');
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new \RuntimeException('User not found');
        }

        $this->validateEnum(
            $newRole,
            'Role',
            ['user', 'verified_user', 'translator', 'moderator', 'admin']
        );

        $user->role = $newRole;
        $user->save();

        $this->logInfo('User role updated', [
            'user_id' => $user->id,
            'old_role' => $user->role,
            'new_role' => $newRole,
            'admin_id' => $admin->id
        ]);
    }

    public function getUserStatistics(int $userId): array
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new \RuntimeException('User not found');
        }

        return [
            'stories' => [
                'total' => $this->storyRepository->count(['user_id' => $userId]),
                'published' => $this->storyRepository->count([
                    'user_id' => $userId,
                    'status' => 'published'
                ]),
                'pending' => $this->storyRepository->count([
                    'user_id' => $userId,
                    'status' => 'pending_review'
                ])
            ],
            'translations' => [
                'total' => $this->contentRepository->count(['translator_id' => $userId]),
                'published' => $this->contentRepository->count([
                    'translator_id' => $userId,
                    'status' => 'published'
                ]),
                'pending' => $this->contentRepository->count([
                    'translator_id' => $userId,
                    'status' => 'pending_review'
                ])
            ],
            'activity' => [
                'last_login' => $user->last_login_at,
                'member_since' => $user->created_at,
                'stories_last_30_days' => $this->storyRepository->count([
                    'user_id' => $userId,
                    'created_at >=' => date('Y-m-d', strtotime('-30 days'))
                ]),
                'translations_last_30_days' => $this->contentRepository->count([
                    'translator_id' => $userId,
                    'created_at >=' => date('Y-m-d', strtotime('-30 days'))
                ])
            ]
        ];
    }

    public function getTranslatorStats(int $userId): array
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new \RuntimeException('User not found');
        }

        if (!$user->hasRole('translator') && !$user->hasRole('admin')) {
            throw new \RuntimeException('User is not a translator');
        }

        return $this->contentRepository->getTranslatorStatistics($userId);
    }

    public function getModerationStats(int $userId): array
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new \RuntimeException('User not found');
        }

        if (!$user->hasRole('moderator') && !$user->hasRole('admin')) {
            throw new \RuntimeException('User is not a moderator');
        }

        // TODO: Implement moderation statistics tracking
        return [];
    }

    public function deactivateUser(int $userId, User $admin, string $reason): void
    {
        if (!$admin->hasRole('admin')) {
            throw new \RuntimeException('Only administrators can deactivate users');
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new \RuntimeException('User not found');
        }

        if ($user->id === $admin->id) {
            throw new \RuntimeException('Cannot deactivate your own account');
        }

        // Start transaction
        $this->userRepository->beginTransaction();

        try {
            // Unpublish all user's content
            $stories = $this->storyRepository->findByUser($userId);
            foreach ($stories as $story) {
                $story->unpublish();
            }

            // Remove from translation assignments
            $translations = $this->contentRepository->findByTranslator($userId);
            foreach ($translations as $translation) {
                $translation->removeTranslator();
            }

            // Deactivate user
            $user->deactivate();
            $user->save();

            $this->userRepository->commit();

            $this->logInfo('User deactivated', [
                'user_id' => $user->id,
                'admin_id' => $admin->id,
                'reason' => $reason
            ]);
        } catch (\Exception $e) {
            $this->userRepository->rollBack();
            throw $e;
        }
    }

    public function reactivateUser(int $userId, User $admin): void
    {
        if (!$admin->hasRole('admin')) {
            throw new \RuntimeException('Only administrators can reactivate users');
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new \RuntimeException('User not found');
        }

        $user->reactivate();
        $user->save();

        $this->logInfo('User reactivated', [
            'user_id' => $user->id,
            'admin_id' => $admin->id
        ]);
    }

    private function validateProfileData(array $data): void
    {
        if (isset($data['username'])) {
            $this->validateLength($data['username'], 'Username', 3, 50);
        }

        if (isset($data['email'])) {
            $this->validateEmail($data['email']);
        }

        if (isset($data['full_name'])) {
            $this->validateLength($data['full_name'], 'Full name', 2, 100);
        }

        if (isset($data['language_preference'])) {
            $this->validateEnum(
                $data['language_preference'],
                'Language',
                $this->config->get('languages.supported')
            );
        }
    }
}
