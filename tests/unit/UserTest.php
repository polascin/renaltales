<?php
use PHPUnit\Framework\TestCase;
use RenalTales\Model\User;
use RenalTales\Model\Story;

class UserTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset the database before each test
        TestHelper::resetDatabase();
    }

    public function testUserCreation()
    {
        $userData = TestHelper::createTestUser();
        $user = User::find($userData['id']);

        $this->assertEquals($userData['username'], $user->username);
        $this->assertEquals($userData['email'], $user->email);
        $this->assertTrue($user->isEmailVerified());
    }

    public function testPasswordVerification()
    {
        $userData = TestHelper::createTestUser();
        $user = User::find($userData['id']);

        $this->assertTrue($user->verifyPassword('password123'));
        $this->assertFalse($user->verifyPassword('wrongpassword'));
    }

    public function testRoleChecking()
    {
        $userData = TestHelper::createTestUser(['role' => 'admin']);
        $user = User::find($userData['id']);

        $this->assertTrue($user->hasRole('admin'));
    }

    public function testUserRelationships()
    {
        $userData = TestHelper::createTestUser();
        $user = User::find($userData['id'], ['stories']);

        $this->assertCount(0, $user->getStories());
    }
}

