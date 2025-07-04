<?php
declare(strict_types=1);

namespace RenalTales\Model;

use DateTime;
use Exception;

class User extends Model
{
    protected static function getTable(): string
    {
        return 'users';
    }

    protected static function getFields(): array
    {
        return [
            'id',
            'username',
            'email',
            'password_hash',
            'full_name',
            'role',
            'two_factor_secret',
            'two_factor_enabled',
            'language_preference',
            'email_verified_at',
            'remember_token',
            'last_login_at',
            'created_at',
            'updated_at'
        ];
    }
    
    protected static function getFillable(): array
    {
        return [
            'username',
            'email',
            'password_hash',
            'full_name',
            'role',
            'two_factor_secret',
            'two_factor_enabled',
            'language_preference',
            'email_verified_at',
            'remember_token',
            'last_login_at'
        ];
    }
    
    protected static function getHidden(): array
    {
        return [
            'password_hash',
            'remember_token',
            'two_factor_secret'
        ];
    }
    
    protected static function getCasts(): array
    {
        return [
            'two_factor_enabled' => 'boolean',
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime'
        ];
    }
    
    protected static function getRelations(): array
    {
        return [
            'stories' => [
                'type' => 'hasMany',
                'model' => Story::class,
                'foreign_key' => 'user_id'
            ],
            'translations' => [
                'type' => 'hasMany',
                'model' => StoryContent::class,
                'foreign_key' => 'translator_id'
            ],
            'comments' => [
                'type' => 'hasMany',
                'model' => Comment::class,
                'foreign_key' => 'user_id'
            ]
        ];
    }

    protected static function getValidationRules(): array
    {
        return [
            'username' => 'required|min:3|max:50',
            'email' => 'required|email|max:255',
            'password_hash' => 'required',
            'full_name' => 'max:100',
            'role' => 'required|in:user,verified_user,translator,moderator,admin',
            'language_preference' => 'required|size:2'
        ];
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = password_hash($password, PASSWORD_ARGON2ID);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }

    public function verifyEmail(): bool
    {
        $this->email_verified_at = new DateTime();
        return $this->saveWithTransaction();
    }

    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function enable2FA(string $secret): bool
    {
        $this->two_factor_secret = $secret;
        $this->two_factor_enabled = true;
        return $this->saveWithTransaction();
    }

    public function disable2FA(): bool
    {
        $this->two_factor_secret = null;
        $this->two_factor_enabled = false;
        return $this->saveWithTransaction();
    }

    public function updateLastLogin(): bool
    {
        $this->last_login_at = new DateTime();
        return $this->saveWithTransaction();
    }

    public function getStories(array $with = []): array
    {
        return $this->getRelation('stories') ?? Story::where(['user_id' => $this->id], $with);
    }

    public function getTranslations(array $with = []): array
    {
        return $this->getRelation('translations') ?? StoryContent::where(['translator_id' => $this->id], $with);
    }
    
    public function getComments(array $with = []): array
    {
        return $this->getRelation('comments') ?? Comment::where(['user_id' => $this->id], $with);
    }
    
    // Enhanced static methods with better validation
    public static function findByEmail(string $email, array $with = []): ?self
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
        
        return static::findBy('email', $email, $with);
    }
    
    public static function findByUsername(string $username, array $with = []): ?self
    {
        if (strlen($username) < 3) {
            throw new \InvalidArgumentException('Username must be at least 3 characters');
        }
        
        return static::findBy('username', $username, $with);
    }
    
    public static function createUser(array $data): self
    {
        $user = new static();
        
        // Set default values
        $data['role'] = $data['role'] ?? 'user';
        $data['language_preference'] = $data['language_preference'] ?? 'en';
        $data['two_factor_enabled'] = false;
        
        // Hash password if provided
        if (isset($data['password'])) {
            $user->setPassword($data['password']);
            unset($data['password']);
        }
        
        $user->fill($data);
        
        if (!$user->saveWithTransaction()) {
            throw new Exception('Failed to create user');
        }
        
        return $user;
    }
    
    public function updateProfile(array $data): bool
    {
        // Only allow certain fields to be updated
        $allowedFields = ['username', 'email', 'full_name', 'language_preference'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        $this->fill($updateData);
        return $this->saveWithTransaction();
    }
    
    public function changePassword(string $currentPassword, string $newPassword): bool
    {
        if (!$this->verifyPassword($currentPassword)) {
            throw new \InvalidArgumentException('Current password is incorrect');
        }
        
        if (strlen($newPassword) < 8) {
            throw new \InvalidArgumentException('New password must be at least 8 characters');
        }
        
        $this->setPassword($newPassword);
        return $this->saveWithTransaction();
    }
    
    public function hasPermission(string $permission): bool
    {
        $rolePermissions = [
            'user' => ['read_stories', 'comment'],
            'verified_user' => ['read_stories', 'comment', 'create_stories'],
            'translator' => ['read_stories', 'comment', 'create_stories', 'translate_stories'],
            'moderator' => ['read_stories', 'comment', 'create_stories', 'translate_stories', 'moderate_content'],
            'admin' => ['*']
        ];
        
        $userPermissions = $rolePermissions[$this->role] ?? [];
        
        return in_array('*', $userPermissions) || in_array($permission, $userPermissions);
    }
    
    public function canModerate(): bool
    {
        return $this->hasRole('moderator') || $this->hasRole('admin');
    }
    
    public function canTranslate(): bool
    {
        return $this->hasPermission('translate_stories');
    }
    
    public function isActive(): bool
    {
        // Consider user active if they logged in within the last 30 days
        if (!$this->last_login_at) {
            return false;
        }
        
        $thirtyDaysAgo = new DateTime('-30 days');
        return $this->last_login_at >= $thirtyDaysAgo;
    }
    
    public function toArray(bool $includeHidden = false): array
    {
        $data = $this->attributes;
        
        if (!$includeHidden) {
            $hidden = static::getHidden();
            $data = array_diff_key($data, array_flip($hidden));
        }
        
        return $data;
    }
}
