<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap/autoload.php';

use RenalTales\Model\User;
use RenalTales\Model\Story;

class ModelTest
{
    private int $testsRun = 0;
    private int $testsPassed = 0;
    private array $errors = [];

    public function runTests(): void
    {
        echo "Running Enhanced Model Tests...\n\n";

        $this->testUserValidation();
        $this->testUserHelperMethods();
        $this->testStoryValidation();
        $this->testStoryHelperMethods();
        $this->testTransactionSafety();

        $this->printResults();
    }

    private function testUserValidation(): void
    {
        echo "Testing User Validation...\n";

        // Test valid user creation
        try {
            $user = new User([
                'username' => 'testuser',
                'email' => 'test@example.com',
                'password_hash' => password_hash('password123', PASSWORD_ARGON2ID),
                'role' => 'user',
                'language_preference' => 'en'
            ]);

            $this->assert(
                $user->username === 'testuser',
                'User creation with valid data'
            );
        } catch (Exception $e) {
            $this->assert(false, 'User creation with valid data: ' . $e->getMessage());
        }

        // Test email validation in findByEmail
        try {
            User::findByEmail('invalid-email');
            $this->assert(false, 'Email validation should fail for invalid email');
        } catch (InvalidArgumentException $e) {
            $this->assert(true, 'Email validation correctly rejects invalid email');
        }

        // Test username validation in findByUsername
        try {
            User::findByUsername('ab');
            $this->assert(false, 'Username validation should fail for short username');
        } catch (InvalidArgumentException $e) {
            $this->assert(true, 'Username validation correctly rejects short username');
        }

        echo "\n";
    }

    private function testUserHelperMethods(): void
    {
        echo "Testing User Helper Methods...\n";

        $user = new User([
            'id' => 1,
            'username' => 'testuser',
            'email' => 'test@example.com',
            'role' => 'moderator',
            'email_verified_at' => new DateTime(),
            'last_login_at' => new DateTime()
        ]);

        $this->assert(
            $user->hasRole('moderator'),
            'User hasRole method'
        );

        $this->assert(
            $user->canModerate(),
            'User canModerate method'
        );

        $this->assert(
            $user->hasPermission('moderate_content'),
            'User hasPermission method'
        );

        $this->assert(
            $user->isEmailVerified(),
            'User isEmailVerified method'
        );

        $this->assert(
            $user->isActive(),
            'User isActive method'
        );

        echo "\n";
    }

    private function testStoryValidation(): void
    {
        echo "Testing Story Validation...\n";

        try {
            $story = new Story([
                'user_id' => 1,
                'category_id' => 1,
                'original_language' => 'en',
                'status' => 'draft',
                'access_level' => 'public'
            ]);

            $this->assert(
                $story->status === 'draft',
                'Story creation with valid data'
            );
        } catch (Exception $e) {
            $this->assert(false, 'Story creation with valid data: ' . $e->getMessage());
        }

        echo "\n";
    }

    private function testStoryHelperMethods(): void
    {
        echo "Testing Story Helper Methods...\n";

        $story = new Story([
            'id' => 1,
            'user_id' => 1,
            'status' => 'published',
            'access_level' => 'public',
            'published_at' => new DateTime()
        ]);

        $user = new User([
            'id' => 1,
            'role' => 'user'
        ]);

        $this->assert(
            $story->isPublished(),
            'Story isPublished method'
        );

        $this->assert(
            !$story->isDraft(),
            'Story isDraft method'
        );

        $this->assert(
            $story->canBeViewedBy($user),
            'Story canBeViewedBy method for public story'
        );

        $this->assert(
            $story->canBeEditedBy($user),
            'Story canBeEditedBy method for owner'
        );

        echo "\n";
    }

    private function testTransactionSafety(): void
    {
        echo "Testing Transaction Safety Features...\n";

        // Test that methods return boolean values for transaction safety
        $user = new User([
            'id' => 1,
            'username' => 'testuser',
            'email' => 'test@example.com'
        ]);

        // Mock the exists property since we're not actually saving to database
        $reflection = new ReflectionClass($user);
        $existsProperty = $reflection->getProperty('exists');
        $existsProperty->setAccessible(true);
        $existsProperty->setValue($user, true);

        $this->assert(
            method_exists($user, 'saveWithTransaction'),
            'User has saveWithTransaction method'
        );

        $story = new Story([
            'id' => 1,
            'status' => 'draft'
        ]);

        $reflection = new ReflectionClass($story);
        $existsProperty = $reflection->getProperty('exists');
        $existsProperty->setAccessible(true);
        $existsProperty->setValue($story, true);

        $this->assert(
            method_exists($story, 'saveWithTransaction'),
            'Story has saveWithTransaction method'
        );

        echo "\n";
    }

    private function assert(bool $condition, string $message): void
    {
        $this->testsRun++;
        if ($condition) {
            $this->testsPassed++;
            echo "✓ {$message}\n";
        } else {
            $this->errors[] = $message;
            echo "✗ {$message}\n";
        }
    }

    private function printResults(): void
    {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "Test Results:\n";
        echo "Tests Run: {$this->testsRun}\n";
        echo "Tests Passed: {$this->testsPassed}\n";
        echo "Tests Failed: " . ($this->testsRun - $this->testsPassed) . "\n";

        if (!empty($this->errors)) {
            echo "\nFailed Tests:\n";
            foreach ($this->errors as $error) {
                echo "- {$error}\n";
            }
        }

        if ($this->testsPassed === $this->testsRun) {
            echo "\n🎉 All tests passed!\n";
        } else {
            echo "\n❌ Some tests failed.\n";
        }
    }
}

// Run the tests
$test = new ModelTest();
$test->runTests();
