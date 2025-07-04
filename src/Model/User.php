<?php
declare(strict_types=1);

namespace RenalTales\Model;

use DateTime;

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

    public function verifyEmail(): void
    {
        $this->email_verified_at = new DateTime();
        $this->save();
    }

    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function enable2FA(string $secret): void
    {
        $this->two_factor_secret = $secret;
        $this->two_factor_enabled = true;
        $this->save();
    }

    public function disable2FA(): void
    {
        $this->two_factor_secret = null;
        $this->two_factor_enabled = false;
        $this->save();
    }

    public function updateLastLogin(): void
    {
        $this->last_login_at = new DateTime();
        $this->save();
    }

    public function getStories(): array
    {
        return Story::where(['user_id' => $this->id]);
    }

    public function getTranslations(): array
    {
        return StoryContent::where(['translator_id' => $this->id]);
    }
}
