<?php

require_once 'tests/BaseTestCase.php';
require_once 'controllers/StoryController.php';

/**
 * API Tests for Story Management
 * 
 * Tests all story API endpoints including CRUD operations,
 * authentication, validation, and error handling
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */
class StoryAPITest extends BaseTestCase
{
    private $storyController;
    private $testUser;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->storyController = new StoryController();
        $this->testUser = $this->createTestUser();
        
        // Create some test stories
        $this->createTestStory($this->testUser['id'], [
            'title' => 'Published Story',
            'status' => 'published'
        ]);
        
        $this->createTestStory($this->testUser['id'], [
            'title' => 'Draft Story',
            'status' => 'draft'
        ]);
    }
    
    /**
     * Test getting all stories
     */
    public function testGetAllStories(): void
    {
        $this->mockHttpRequest('GET', '/api/stories');
        
        $stories = $this->storyController->searchStories();
        
        $this->assertIsArray($stories);
        $this->assertGreaterThan(0, count($stories));
        
        // Check story structure
        foreach ($stories as $story) {
            $this->assertArrayHasKey('id', $story);
            $this->assertArrayHasKey('title', $story);
            $this->assertArrayHasKey('content', $story);
            $this->assertArrayHasKey('author_id', $story);
            $this->assertArrayHasKey('status', $story);
        }
    }
    
    /**
     * Test getting a single story
     */
    public function testGetSingleStory(): void
    {
        $testStory = $this->createTestStory($this->testUser['id']);
        
        $this->mockHttpRequest('GET', '/api/stories/' . $testStory['id']);
        
        $story = $this->storyController->getStory($testStory['id']);
        
        $this->assertIsArray($story);
        $this->assertEquals($testStory['id'], $story['id']);
        $this->assertEquals($testStory['title'], $story['title']);
        $this->assertEquals($testStory['content'], $story['content']);
    }
    
    /**
     * Test getting non-existent story
     */
    public function testGetNonExistentStory(): void
    {
        $this->mockHttpRequest('GET', '/api/stories/99999');
        
        $story = $this->storyController->getStory(99999);
        
        $this->assertNull($story);
    }
    
    /**
     * Test creating a new story
     */
    public function testCreateStory(): void
    {
        $storyData = [
            'title' => 'New Test Story',
            'content' => 'This is the content of the new test story.',
            'categories' => ['Health', 'Personal'],
            'tags' => ['kidney', 'health', 'story'],
            'status' => 'draft'
        ];
        
        $this->mockHttpRequest('POST', '/api/stories', $storyData);
        
        $result = $this->storyController->createStory($storyData, $this->testUser['id']);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('story_id', $result);
        $this->assertArrayHasKey('data', $result);
        
        // Verify story was created in database
        $this->assertDatabaseHas('stories', [
            'title' => $storyData['title'],
            'content' => $storyData['content'],
            'author_id' => $this->testUser['id']
        ]);
    }
    
    /**
     * Test creating story with invalid data
     */
    public function testCreateStoryWithInvalidData(): void
    {
        $invalidData = [
            'title' => '', // Empty title
            'content' => 'Content without title',
            'status' => 'invalid_status'
        ];
        
        $this->mockHttpRequest('POST', '/api/stories', $invalidData);
        
        $result = $this->storyController->createStory($invalidData, $this->testUser['id']);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }
    
    /**
     * Test updating a story
     */
    public function testUpdateStory(): void
    {
        $testStory = $this->createTestStory($this->testUser['id']);
        
        $updateData = [
            'title' => 'Updated Story Title',
            'content' => 'Updated story content.',
            'status' => 'published'
        ];
        
        $this->mockHttpRequest('PUT', '/api/stories/' . $testStory['id'], $updateData);
        
        $result = $this->storyController->updateStory($testStory['id'], $updateData, $this->testUser['id']);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        
        // Verify story was updated in database
        $this->assertDatabaseHas('stories', [
            'id' => $testStory['id'],
            'title' => $updateData['title'],
            'content' => $updateData['content'],
            'status' => $updateData['status']
        ]);
    }
    
    /**
     * Test updating non-existent story
     */
    public function testUpdateNonExistentStory(): void
    {
        $updateData = [
            'title' => 'Updated Title',
            'content' => 'Updated content.'
        ];
        
        $this->mockHttpRequest('PUT', '/api/stories/99999', $updateData);
        
        $result = $this->storyController->updateStory(99999, $updateData, $this->testUser['id']);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }
    
    /**
     * Test deleting a story
     */
    public function testDeleteStory(): void
    {
        $testStory = $this->createTestStory($this->testUser['id']);
        
        $this->mockHttpRequest('DELETE', '/api/stories/' . $testStory['id']);
        
        $result = $this->storyController->deleteStory($testStory['id']);
        
        $this->assertTrue($result['success']);
        
        // Verify story was deleted from database
        $this->assertDatabaseMissing('stories', [
            'id' => $testStory['id']
        ]);
    }
    
    /**
     * Test deleting non-existent story
     */
    public function testDeleteNonExistentStory(): void
    {
        $this->mockHttpRequest('DELETE', '/api/stories/99999');
        
        $result = $this->storyController->deleteStory(99999);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }
    
    /**
     * Test searching stories
     */
    public function testSearchStories(): void
    {
        // Create stories with specific content
        $this->createTestStory($this->testUser['id'], [
            'title' => 'Kidney Health Story',
            'content' => 'This story is about kidney health and dialysis.',
            'status' => 'published'
        ]);
        
        $this->createTestStory($this->testUser['id'], [
            'title' => 'Heart Health Story',
            'content' => 'This story is about heart health.',
            'status' => 'published'
        ]);
        
        $filters = [
            'search' => 'kidney',
            'published' => true
        ];
        
        $this->mockHttpRequest('GET', '/api/stories', $filters);
        
        $stories = $this->storyController->searchStories($filters);
        
        $this->assertIsArray($stories);
        $this->assertGreaterThan(0, count($stories));
        
        // Check that all returned stories contain the search term
        foreach ($stories as $story) {
            $hasSearchTerm = stripos($story['title'], 'kidney') !== false || 
                           stripos($story['content'], 'kidney') !== false;
            $this->assertTrue($hasSearchTerm);
        }
    }
    
    /**
     * Test publishing a story
     */
    public function testPublishStory(): void
    {
        $testStory = $this->createTestStory($this->testUser['id'], [
            'status' => 'draft'
        ]);
        
        $this->mockHttpRequest('POST', '/api/stories/' . $testStory['id'] . '/publish');
        
        $result = $this->storyController->publishStory($testStory['id']);
        
        $this->assertTrue($result['success']);
        
        // Verify story status was updated
        $this->assertDatabaseHas('stories', [
            'id' => $testStory['id'],
            'status' => 'published'
        ]);
    }
    
    /**
     * Test unpublishing a story
     */
    public function testUnpublishStory(): void
    {
        $testStory = $this->createTestStory($this->testUser['id'], [
            'status' => 'published'
        ]);
        
        $this->mockHttpRequest('POST', '/api/stories/' . $testStory['id'] . '/unpublish');
        
        $result = $this->storyController->unpublishStory($testStory['id']);
        
        $this->assertTrue($result['success']);
        
        // Verify story status was updated
        $this->assertDatabaseHas('stories', [
            'id' => $testStory['id'],
            'status' => 'draft'
        ]);
    }
    
    /**
     * Test getting story statistics
     */
    public function testGetStoryStatistics(): void
    {
        $this->mockHttpRequest('GET', '/api/stories/stats');
        
        $stats = $this->storyController->getStoryStats();
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_stories', $stats);
        $this->assertArrayHasKey('published_stories', $stats);
        $this->assertArrayHasKey('total_categories', $stats);
        $this->assertArrayHasKey('total_tags', $stats);
        $this->assertArrayHasKey('total_media', $stats);
        $this->assertArrayHasKey('total_comments', $stats);
    }
    
    /**
     * Test getting categories
     */
    public function testGetCategories(): void
    {
        $this->mockHttpRequest('GET', '/api/stories/categories');
        
        $categories = $this->storyController->getCategories();
        
        $this->assertIsArray($categories);
        
        // Check category structure if any exist
        if (count($categories) > 0) {
            $this->assertArrayHasKey('id', $categories[0]);
            $this->assertArrayHasKey('name', $categories[0]);
        }
    }
    
    /**
     * Test getting tags
     */
    public function testGetTags(): void
    {
        $this->mockHttpRequest('GET', '/api/stories/tags');
        
        $tags = $this->storyController->getTags();
        
        $this->assertIsArray($tags);
        
        // Check tag structure if any exist
        if (count($tags) > 0) {
            $this->assertArrayHasKey('id', $tags[0]);
            $this->assertArrayHasKey('name', $tags[0]);
        }
    }
    
    /**
     * Test file upload for story
     */
    public function testFileUpload(): void
    {
        $testStory = $this->createTestStory($this->testUser['id']);
        
        // Mock file upload
        $mockFile = [
            'name' => 'test-image.jpg',
            'type' => 'image/jpeg',
            'size' => 1024,
            'tmp_name' => $this->createTempFile('fake image data', 'test-image.jpg'),
            'error' => UPLOAD_ERR_OK
        ];
        
        $metadata = [
            'alt_text' => 'Test image',
            'caption' => 'This is a test image'
        ];
        
        $this->mockHttpRequest('POST', '/api/stories/' . $testStory['id'] . '/media');
        
        $result = $this->storyController->uploadMedia($testStory['id'], $mockFile, $metadata);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('media_id', $result);
        $this->assertArrayHasKey('data', $result);
        
        // Clean up
        $this->cleanupTempFiles([$mockFile['tmp_name']]);
    }
    
    /**
     * Test invalid file upload
     */
    public function testInvalidFileUpload(): void
    {
        $testStory = $this->createTestStory($this->testUser['id']);
        
        // Mock invalid file upload (PHP file)
        $mockFile = [
            'name' => 'malicious.php',
            'type' => 'application/x-httpd-php',
            'size' => 1024,
            'tmp_name' => $this->createTempFile('<?php echo "hack"; ?>', 'malicious.php'),
            'error' => UPLOAD_ERR_OK
        ];
        
        $this->mockHttpRequest('POST', '/api/stories/' . $testStory['id'] . '/media');
        
        $result = $this->storyController->uploadMedia($testStory['id'], $mockFile);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
        
        // Clean up
        $this->cleanupTempFiles([$mockFile['tmp_name']]);
    }
    
    /**
     * Test API rate limiting
     */
    public function testRateLimiting(): void
    {
        // This test would require implementing rate limiting in the controller
        // For now, we'll simulate the concept
        
        $this->markTestSkipped('Rate limiting not implemented in current controller');
    }
    
    /**
     * Test pagination
     */
    public function testPagination(): void
    {
        // Create multiple stories
        for ($i = 0; $i < 15; $i++) {
            $this->createTestStory($this->testUser['id'], [
                'title' => "Story $i",
                'status' => 'published'
            ]);
        }
        
        // Test first page
        $this->mockHttpRequest('GET', '/api/stories', ['limit' => 10, 'offset' => 0]);
        $firstPage = $this->storyController->searchStories(['published' => true], 10, 0);
        
        $this->assertIsArray($firstPage);
        $this->assertCount(10, $firstPage);
        
        // Test second page
        $this->mockHttpRequest('GET', '/api/stories', ['limit' => 10, 'offset' => 10]);
        $secondPage = $this->storyController->searchStories(['published' => true], 10, 10);
        
        $this->assertIsArray($secondPage);
        $this->assertGreaterThan(0, count($secondPage));
        
        // Ensure pages contain different stories
        $firstPageIds = array_column($firstPage, 'id');
        $secondPageIds = array_column($secondPage, 'id');
        $this->assertEmpty(array_intersect($firstPageIds, $secondPageIds));
    }
    
    /**
     * Test authentication requirements
     */
    public function testAuthenticationRequirements(): void
    {
        // Test creating story without authentication
        $storyData = [
            'title' => 'Unauthorized Story',
            'content' => 'This should not be created.'
        ];
        
        $this->mockHttpRequest('POST', '/api/stories', $storyData);
        
        // This would require implementing authentication check in controller
        // For now, we'll check that user ID is required
        $result = $this->storyController->createStory($storyData, null);
        
        $this->assertFalse($result['success']);
    }
}
