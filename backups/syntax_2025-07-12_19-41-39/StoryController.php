<?php

declare(strict_types=1);

/**
 * Story Controller - Handles story management operations
 * 
 * Provides endpoints for story CRUD operations, media management,
 * versioning, and publishing workflow
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

require_once 'models/Story.php';
require_once 'models/Category.php';
require_once 'models/Tag.php';
require_once 'models/Media.php';
require_once 'models/Comment.php';

class StoryController {
    
    private mixed $storyModel;
    private mixed $categoryModel;
    private mixed $tagModel;
    private mixed $mediaModel;
    private mixed $commentModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->storyModel = new Story();
        $this->categoryModel = new Category();
        $this->tagModel = new Tag();
        $this->mediaModel = new Media();
        $this->commentModel = new Comment();
    }
    
    /**
     * Create a new story
     * 
     * @param array $data
     * @param int $userId
     * @return array Response
     */
    public function createStory($data, $userId) {
        try {
            // Create story
            $storyId = $this->storyModel->createStory($data, $userId);
            
            if (!$storyId) {
                return [
                    'success' => false,
                    'message' => 'Failed to create story',
                    'errors' => []
                ];
            }
            
            // Handle categories
            if (!empty($data['categories'])) {
                $categoryIds = [];
                foreach ($data['categories'] as $categoryName) {
                    $categoryIds[] = $this->categoryModel->getOrCreate($categoryName);
                }
                $this->storyModel->addStoryCategories($storyId, $categoryIds);
            }
            
            // Handle tags
            if (!empty($data['tags'])) {
                $tagIds = $this->tagModel->getOrCreateMultiple($data['tags']);
                $this->storyModel->addStoryTags($storyId, $tagIds);
            }
            
            return [
                'success' => true,
                'message' => 'Story created successfully',
                'story_id' => $storyId,
                'data' => $this->getStory($storyId)
            ];
            
        } catch (Exception $e) {
            error_log('Story creation error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while creating the story',
                'errors' => []
            ];
        }
    }
    
    /**
     * Get story by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function getStory($id) {
        return $this->storyModel->getStoryWithMetadata($id);
    }
    
    /**
     * Update story
     * 
     * @param int $id
     * @param array $data
     * @param int $userId
     * @return array Response
     */
    public function updateStory($id, $data, $userId) {
        try {
            $success = $this->storyModel->updateStory($id, $data, $userId);
            
            if (!$success) {
                return [
                    'success' => false,
                    'message' => 'Failed to update story',
                    'errors' => []
                ];
            }
            
            // Handle categories
            if (isset($data['categories'])) {
                $categoryIds = [];
                foreach ($data['categories'] as $categoryName) {
                    $categoryIds[] = $this->categoryModel->getOrCreate($categoryName);
                }
                $this->storyModel->addStoryCategories($id, $categoryIds);
            }
            
            // Handle tags
            if (isset($data['tags'])) {
                $tagIds = $this->tagModel->getOrCreateMultiple($data['tags']);
                $this->storyModel->addStoryTags($id, $tagIds);
            }
            
            return [
                'success' => true,
                'message' => 'Story updated successfully',
                'data' => $this->getStory($id)
            ];
            
        } catch (Exception $e) {
            error_log('Story update error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while updating the story',
                'errors' => []
            ];
        }
    }
    
    /**
     * Delete story
     * 
     * @param int $id
     * @return array Response
     */
    public function deleteStory($id) {
        try {
            // Get story media to delete files
            $media = $this->mediaModel->getByStory($id);
            
            // Delete media files
            foreach ($media as $mediaItem) {
                $this->mediaModel->deleteMedia($mediaItem['id']);
            }
            
            // Delete story
            $success = $this->storyModel->delete($id);
            
            return [
                'success' => $success,
                'message' => $success ? 'Story deleted successfully' : 'Failed to delete story'
            ];
            
        } catch (Exception $e) {
            error_log('Story deletion error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while deleting the story'
            ];
        }
    }
    
    /**
     * Search stories
     * 
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function searchStories($filters = [], $limit = 10, $offset = 0) {
        $stories = $this->storyModel->searchStories($filters, $limit, $offset);
        
        // Add metadata to each story
        foreach ($stories as &$story) {
            $story['categories'] = $this->storyModel->getStoryCategories($story['id']);
            $story['tags'] = $this->storyModel->getStoryTags($story['id']);
            $story['featured_image'] = $this->mediaModel->getFeaturedImage($story['id']);
            $story['comment_count'] = $this->commentModel->getStoryCommentCount($story['id']);
        }
        
        return $stories;
    }
    
    /**
     * Get published stories
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getPublishedStories($limit = 10, $offset = 0) {
        return $this->searchStories(['published' => true], $limit, $offset);
    }
    
    /**
     * Publish story
     * 
     * @param int $id
     * @return array Response
     */
    public function publishStory($id) {
        $success = $this->storyModel->publishStory($id);
        
        return [
            'success' => $success,
            'message' => $success ? 'Story published successfully' : 'Failed to publish story'
        ];
    }
    
    /**
     * Unpublish story
     * 
     * @param int $id
     * @return array Response
     */
    public function unpublishStory($id) {
        $success = $this->storyModel->unpublishStory($id);
        
        return [
            'success' => $success,
            'message' => $success ? 'Story unpublished successfully' : 'Failed to unpublish story'
        ];
    }
    
    /**
     * Upload media for story
     * 
     * @param int $storyId
     * @param array $file
     * @param array $metadata
     * @return array Response
     */
    public function uploadMedia($storyId, $file, $metadata = []) {
        $mediaId = $this->mediaModel->uploadMedia($storyId, $file, $metadata);
        
        if (!$mediaId) {
            return [
                'success' => false,
                'message' => 'Failed to upload media'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Media uploaded successfully',
            'media_id' => $mediaId,
            'data' => $this->mediaModel->find($mediaId)
        ];
    }
    
    /**
     * Get story media
     * 
     * @param int $storyId
     * @param string $mediaType
     * @return array
     */
    public function getStoryMedia($storyId, $mediaType = null) {
        return $this->mediaModel->getByStory($storyId, $mediaType);
    }
    
    /**
     * Delete media
     * 
     * @param int $mediaId
     * @return array Response
     */
    public function deleteMedia($mediaId) {
        $success = $this->mediaModel->deleteMedia($mediaId);
        
        return [
            'success' => $success,
            'message' => $success ? 'Media deleted successfully' : 'Failed to delete media'
        ];
    }
    
    /**
     * Get story versions
     * 
     * @param int $storyId
     * @return array
     */
    public function getStoryVersions($storyId) {
        return $this->storyModel->getStoryVersions($storyId);
    }
    
    /**
     * Get specific story version
     * 
     * @param int $storyId
     * @param int $versionNumber
     * @return array|null
     */
    public function getStoryVersion($storyId, $versionNumber) {
        return $this->storyModel->getStoryVersion($storyId, $versionNumber);
    }
    
    /**
     * Get story comments
     * 
     * @param int $storyId
     * @param bool $threaded
     * @return array
     */
    public function getStoryComments($storyId, $threaded = false) {
        if ($threaded) {
            return $this->commentModel->getThreadedComments($storyId);
        }
        return $this->commentModel->getStoryComments($storyId);
    }
    
    /**
     * Create comment
     * 
     * @param array $data
     * @return array Response
     */
    public function createComment($data) {
        if (!$this->commentModel->canUserComment($data['user_id'], $data['story_id'])) {
            return [
                'success' => false,
                'message' => 'You cannot comment on this story'
            ];
        }
        
        $commentId = $this->commentModel->createComment($data);
        
        if (!$commentId) {
            return [
                'success' => false,
                'message' => 'Failed to create comment'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Comment created successfully',
            'comment_id' => $commentId,
            'data' => $this->commentModel->find($commentId)
        ];
    }
    
    /**
     * Get categories
     * 
     * @return array
     */
    public function getCategories() {
        return $this->categoryModel->getAllWithCounts();
    }
    
    /**
     * Get tags
     * 
     * @return array
     */
    public function getTags() {
        return $this->tagModel->getAllWithCounts();
    }
    
    /**
     * Get popular categories
     * 
     * @param int $limit
     * @return array
     */
    public function getPopularCategories($limit = 10) {
        return $this->categoryModel->getPopular($limit);
    }
    
    /**
     * Get popular tags
     * 
     * @param int $limit
     * @return array
     */
    public function getPopularTags($limit = 10) {
        return $this->tagModel->getPopular($limit);
    }
    
    /**
     * Get tag cloud
     * 
     * @param int $limit
     * @return array
     */
    public function getTagCloud($limit = 50) {
        return $this->tagModel->getTagCloud($limit);
    }
    
    /**
     * Search tags
     * 
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function searchTags($query, $limit = 10) {
        return $this->tagModel->searchTags($query, $limit);
    }
    
    /**
     * Get story statistics
     * 
     * @return array
     */
    public function getStoryStats() {
        return [
            'total_stories' => $this->storyModel->count(),
            'published_stories' => $this->storyModel->count(['published' => true]),
            'total_categories' => $this->categoryModel->count(),
            'total_tags' => $this->tagModel->count(),
            'total_media' => $this->mediaModel->count(),
            'total_comments' => $this->commentModel->count(),
            'media_stats' => $this->mediaModel->getStats(),
            'comment_stats' => $this->commentModel->getCommentStats()
        ];
    }
    
    /**
     * Generate story preview
     * 
     * @param int $storyId
     * @return array
     */
    public function generatePreview($storyId) {
        $story = $this->getStory($storyId);
        
        if (!$story) {
            return [
                'success' => false,
                'message' => 'Story not found'
            ];
        }
        
        // Generate preview data
        $preview = [
            'title' => $story['title'],
            'content' => $story['content'],
            'categories' => $story['categories'],
            'tags' => $story['tags'],
            'media' => $story['media'],
            'created_at' => $story['created_at'],
            'updated_at' => $story['updated_at'],
            'published' => $story['published']
        ];
        
        return [
            'success' => true,
            'preview' => $preview
        ];
    }
}
