<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use RenalTales\Model\User;
use RenalTales\Model\Story;

class UserModelTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset the database before each test
        if (class_exists('TestHelper')) {
            TestHelper::resetDatabase();
        }
    }

    public function testUserCreationWithValidData()
    {
        $userData = [
            'username' => 'testuser123',
            'email' => 'test@example.com',
            'password_hash' => password_hash('password123', PASSWORD_ARGON2ID),
            'full_name' => 'Test User',
            'role' => 'user',
            'language_preference' => 'en'
        ];

        $user = new User($userData);
        
        $this->assertEquals('testuser123', $user->username);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('user', $user->role);
        $this->assertEquals('en', $user->language_preference);
    }

    public function testPasswordHashing()
    {
        $user = new User();
        $user->setPassword('testpassword123');
        
        $this->assertNotEquals('testpassword123', $user->password_hash);
        $this->assertTrue($user->verifyPassword('testpassword123'));
        $this->assertFalse($user->verifyPassword('wrongpassword'));
    }

    public function testEmailVerification()
    {
        $user = new User(['email_verified_at' => null]);
        $this->assertFalse($user->isEmailVerified());

        $user->email_verified_at = new DateTime();
        $this->assertTrue($user->isEmailVerified());
    }

    public function testRoleChecking()
    {
        $user = new User(['role' => 'admin']);
        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('user'));

        $moderatorUser = new User(['role' => 'moderator']);
        $this->assertTrue($moderatorUser->hasRole('moderator'));
        $this->assertFalse($moderatorUser->hasRole('admin'));
    }

    public function testTwoFactorAuthentication()
    {
        $user = new User(['two_factor_enabled' => false]);
        $this->assertFalse($user->two_factor_enabled);

        // Test enabling 2FA
        $secret = 'test_secret_key';
        $user->two_factor_secret = $secret;
        $user->two_factor_enabled = true;
        
        $this->assertTrue($user->two_factor_enabled);
        $this->assertEquals($secret, $user->two_factor_secret);

        // Test disabling 2FA
        $user->two_factor_secret = null;
        $user->two_factor_enabled = false;
        
        $this->assertFalse($user->two_factor_enabled);
        $this->assertNull($user->two_factor_secret);
    }

    public function testLastLoginUpdate()
    {
        $user = new User(['last_login_at' => null]);
        $this->assertNull($user->last_login_at);

        $user->last_login_at = new DateTime();
        $this->assertInstanceOf(DateTime::class, $user->last_login_at);
    }

    public function testFindByEmailValidation()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');
        
        User::findByEmail('invalid-email');
    }

    public function testFindByUsernameValidation()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Username must be at least 3 characters');
        
        User::findByUsername('ab');
    }

    public function testUserValidationRules()
    {
        $rules = User::getValidationRules();
        
        $this->assertArrayHasKey('username', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('role', $rules);
        $this->assertStringContainsString('required', $rules['username']);
        $this->assertStringContainsString('email', $rules['email']);
    }

    public function testUserFieldsAndCasts()
    {
        $fields = User::getFields();
        $casts = User::getCasts();
        
        $this->assertContains('username', $fields);
        $this->assertContains('email', $fields);
        $this->assertContains('password_hash', $fields);
        $this->assertArrayHasKey('two_factor_enabled', $casts);
        $this->assertEquals('boolean', $casts['two_factor_enabled']);
    }

    public function testHiddenFields()
    {
        $hidden = User::getHidden();
        
        $this->assertContains('password_hash', $hidden);
        $this->assertContains('remember_token', $hidden);
        $this->assertContains('two_factor_secret', $hidden);
    }

    public function testUserRelationshipDefinitions()
    {
        $relations = User::getRelations();
        
        $this->assertArrayHasKey('stories', $relations);
        $this->assertArrayHasKey('translations', $relations);
        $this->assertArrayHasKey('comments', $relations);
        
        $this->assertEquals('hasMany', $relations['stories']['type']);
        $this->assertEquals(Story::class, $relations['stories']['model']);
    }

    public function testUserToArray()
    {
        $userData = [
            'id' => 1,
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password_hash' => 'hashed_password',
            'role' => 'user'
        ];

        $user = new User($userData);
        $array = $user->toArray();

        // Should include public fields
        $this->assertArrayHasKey('username', $array);
        $this->assertArrayHasKey('email', $array);
        
        // Should exclude hidden fields
        $this->assertArrayNotHasKey('password_hash', $array);
    }

    public function testUserLanguagePreference()
    {
        $user = new User(['language_preference' => 'sk']);
        $this->assertEquals('sk', $user->language_preference);

        $user->language_preference = 'de';
        $this->assertEquals('de', $user->language_preference);
    }

    public function testUserCreateWithDefaults()
    {
        $data = [
            'username' => 'newuser',
            'email' => 'new@example.com',
            'password' => 'password123'
        ];

        // This would test the createUser static method if it exists
        if (method_exists(User::class, 'createUser')) {
            $user = User::createUser($data);
            $this->assertEquals('user', $user->role);
            $this->assertEquals('en', $user->language_preference);
            $this->assertFalse($user->two_factor_enabled);
        }
    }
}
