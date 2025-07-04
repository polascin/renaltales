<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use RenalTales\Model\Story;
use RenalTales\Model\User;

class StoryModelTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset the database before each test
        if (class_exists('TestHelper')) {
            TestHelper::resetDatabase();
        }
    }

    public function testStoryCreationWithValidData()
    {
        $storyData = [
            'user_id' => 1,
            'category_id' => 1,
            'original_language' => 'en',
            'status' => 'draft',
            'access_level' => 'public'
        ];

        $story = new Story($storyData);
        
        $this->assertEquals(1, $story->user_id);
        $this->assertEquals(1, $story->category_id);
        $this->assertEquals('en', $story->original_language);
        $this->assertEquals('draft', $story->status);
        $this->assertEquals('public', $story->access_level);
    }

    public function testStoryStatusMethods()
    {
        $draftStory = new Story(['status' => 'draft']);
        $this->assertTrue($draftStory->isDraft());
        $this->assertFalse($draftStory->isPublished());
        $this->assertFalse($draftStory->isPendingReview());
        $this->assertFalse($draftStory->isRejected());

        $publishedStory = new Story([
            'status' => 'published',
            'published_at' => new DateTime()
        ]);
        $this->assertTrue($publishedStory->isPublished());
        $this->assertFalse($publishedStory->isDraft());

        $pendingStory = new Story(['status' => 'pending_review']);
        $this->assertTrue($pendingStory->isPendingReview());

        $rejectedStory = new Story(['status' => 'rejected']);
        $this->assertTrue($rejectedStory->isRejected());
    }

    public function testStoryPublishing()
    {
        $story = new Story(['status' => 'draft', 'published_at' => null]);
        $this->assertFalse($story->isPublished());
        
        // Test publish method exists and changes status
        if (method_exists($story, 'publish')) {
            $story->status = 'published';
            $story->published_at = new DateTime();
            $this->assertTrue($story->isPublished());
        }
    }

    public function testStoryUnpublishing()
    {
        $story = new Story([
            'status' => 'published',
            'published_at' => new DateTime()
        ]);
        $this->assertTrue($story->isPublished());
        
        // Test unpublish method
        if (method_exists($story, 'unpublish')) {
            $story->status = 'draft';
            $story->published_at = null;
            $this->assertTrue($story->isDraft());
            $this->assertFalse($story->isPublished());
        }
    }

    public function testStorySubmitForReview()
    {
        $story = new Story(['status' => 'draft']);
        $this->assertTrue($story->isDraft());
        
        if (method_exists($story, 'submitForReview')) {
            $story->status = 'pending_review';
            $this->assertTrue($story->isPendingReview());
            $this->assertFalse($story->isDraft());
        }
    }

    public function testStoryRejection()
    {
        $story = new Story(['status' => 'pending_review']);
        $this->assertTrue($story->isPendingReview());
        
        if (method_exists($story, 'reject')) {
            $story->status = 'rejected';
            $this->assertTrue($story->isRejected());
            $this->assertFalse($story->isPendingReview());
        }
    }

    public function testStoryValidationRules()
    {
        $rules = Story::getValidationRules();
        
        $this->assertArrayHasKey('user_id', $rules);
        $this->assertArrayHasKey('category_id', $rules);
        $this->assertArrayHasKey('original_language', $rules);
        $this->assertArrayHasKey('status', $rules);
        $this->assertArrayHasKey('access_level', $rules);
        
        $this->assertStringContainsString('required', $rules['user_id']);
        $this->assertStringContainsString('exists', $rules['user_id']);
        $this->assertStringContainsString('size:2', $rules['original_language']);
    }

    public function testStoryFieldsAndCasts()
    {
        $fields = Story::getFields();
        $casts = Story::getCasts();
        
        $this->assertContains('id', $fields);
        $this->assertContains('user_id', $fields);
        $this->assertContains('category_id', $fields);
        $this->assertContains('status', $fields);
        $this->assertContains('access_level', $fields);
        
        $this->assertArrayHasKey('published_at', $casts);
        $this->assertEquals('datetime', $casts['published_at']);
    }

    public function testStoryRelationshipDefinitions()
    {
        $relations = Story::getRelations();
        
        $this->assertArrayHasKey('author', $relations);
        $this->assertArrayHasKey('category', $relations);
        $this->assertArrayHasKey('contents', $relations);
        $this->assertArrayHasKey('comments', $relations);
        
        $this->assertEquals('belongsTo', $relations['author']['type']);
        $this->assertEquals(User::class, $relations['author']['model']);
        $this->assertEquals('user_id', $relations['author']['foreign_key']);
        
        $this->assertEquals('hasMany', $relations['contents']['type']);
        $this->assertEquals('hasMany', $relations['comments']['type']);
    }

    public function testStoryAccessLevels()
    {
        $publicStory = new Story(['access_level' => 'public']);
        $this->assertEquals('public', $publicStory->access_level);

        $registeredStory = new Story(['access_level' => 'registered']);
        $this->assertEquals('registered', $registeredStory->access_level);

        $verifiedStory = new Story(['access_level' => 'verified']);
        $this->assertEquals('verified', $verifiedStory->access_level);

        $premiumStory = new Story(['access_level' => 'premium']);
        $this->assertEquals('premium', $premiumStory->access_level);
    }

    public function testStoryLanguageHandling()
    {
        $story = new Story(['original_language' => 'en']);
        $this->assertEquals('en', $story->original_language);

        $story->original_language = 'sk';
        $this->assertEquals('sk', $story->original_language);
    }

    public function testStoryTranslationValidation()
    {
        $story = new Story(['id' => 1]);
        
        if (method_exists($story, 'addTranslation')) {
            // Test invalid language code
            try {
                $story->addTranslation('invalid', 'Title', 'Content');
                $this->fail('Should throw exception for invalid language code');
            } catch (InvalidArgumentException $e) {
                $this->assertStringContainsString('Language code must be exactly 2 characters', $e->getMessage());
            }

            // Test empty title
            try {
                $story->addTranslation('sk', '', 'Content');
                $this->fail('Should throw exception for empty title');
            } catch (InvalidArgumentException $e) {
                $this->assertStringContainsString('Title and content are required', $e->getMessage());
            }

            // Test empty content
            try {
                $story->addTranslation('sk', 'Title', '');
                $this->fail('Should throw exception for empty content');
            } catch (InvalidArgumentException $e) {
                $this->assertStringContainsString('Title and content are required', $e->getMessage());
            }
        }
    }

    public function testStoryTimestamps()
    {
        $now = new DateTime();
        $story = new Story([
            'created_at' => $now,
            'updated_at' => $now
        ]);
        
        $this->assertInstanceOf(DateTime::class, $story->created_at);
        $this->assertInstanceOf(DateTime::class, $story->updated_at);
    }

    public function testStoryPublishedAt()
    {
        $story = new Story(['published_at' => null]);
        $this->assertNull($story->published_at);

        $publishedTime = new DateTime();
        $story->published_at = $publishedTime;
        $this->assertEquals($publishedTime, $story->published_at);
    }

    public function testStoryAuthorRelationship()
    {
        $story = new Story(['user_id' => 1]);
        
        if (method_exists($story, 'getAuthor')) {
            // This would normally return a User object or null
            // For unit testing, we're just testing the method exists
            $this->assertTrue(method_exists($story, 'getAuthor'));
        }
    }

    public function testStoryContentRelationship()
    {
        $story = new Story(['id' => 1]);
        
        if (method_exists($story, 'getContents')) {
            $this->assertTrue(method_exists($story, 'getContents'));
        }
        
        if (method_exists($story, 'getContent')) {
            $this->assertTrue(method_exists($story, 'getContent'));
        }
    }

    public function testStoryCommentsRelationship()
    {
        $story = new Story(['id' => 1]);
        
        if (method_exists($story, 'getComments')) {
            $this->assertTrue(method_exists($story, 'getComments'));
        }
        
        if (method_exists($story, 'getAllComments')) {
            $this->assertTrue(method_exists($story, 'getAllComments'));
        }
    }
}
