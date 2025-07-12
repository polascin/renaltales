<?php

declare(strict_types=1);

/**
 * Story Model - Story management and operations
 * 
 * Handles all story-related database operations including CRUD,
 * versioning, media management, and search functionality
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

require_once 'BaseModel.php';

class Story extends BaseModel {
    
    protected string $table = 'stories';
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Validate story data
     * 
     * @param array $data
     * @return array Validation errors
     */
    protected function validate(array $data): array {
        $errors = [];
        
        // Title validation
        if (empty($data['title'])) {
            $errors['title'] = 'Title is required';
        } elseif (strlen($data['title']) > 255) {
            $errors['title'] = 'Title must be less than 255 characters';
        }
        
        // Content validation
        if (empty($data['content'])) {
            $errors['content'] = 'Content is required';
        }
        
        // Published validation
        if (isset($data['published']) && !is_bool($data['published'])) {
            $errors['published'] = 'Published must be a boolean value';
        }
        
        return $errors;
    }
    
    /**
     * Create a new story with version tracking
     * 
     * @param array $data
     * @param int $userId
     * @return string|false Story ID on success, false on failure
     */
    public function createStory($data, $userId) {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return false;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Create story
            $storyData = [
                'title' => $data['title'],
                'content' => $data['content'],
                'published' => $data['published'] ?? false,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $storyId = $this->create($storyData);
            
            // Create initial version
            $versionData = [
                'story_id' => $storyId,
                'version_number' => 1,
                'title' => $data['title'],
                'content' => $data['content'],
                'metadata' => json_encode($data['metadata'] ?? []),
                'created_by' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
                'notes' => 'Initial version'
            ];
            
            $this->createVersion($versionData);
            
            $this->db->commit();
            return $storyId;
            
        } catch(Exception $e) 
            $this->db->rollback();
            error_log('Story creation failed: ' . $e->getMessage());
            return false;
        
    }
    
    /**
     * Update a story with version tracking
     * 
     * @param int $id
     * @param array $data
     * @param int $userId
     * @return bool
     */
    public function updateStory($id, $data, $userId) {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return false;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Get current version number
            $currentVersion = $this->getLatestVersion($id);
            $newVersionNumber = $currentVersion ? $currentVersion['version_number'] + 1 : 1;
            
            // Update story
            $storyData = [
                'title' => $data['title'],
                'content' => $data['content'],
                'published' => $data['published'] ?? false,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->update($id, $storyData);
            
            // Create new version
            $versionData = [
                'story_id' => $id,
                'version_number' => $newVersionNumber,
                'title' => $data['title'],
                'content' => $data['content'],
                'metadata' => json_encode($data['metadata'] ?? []),
                'created_by' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
                'notes' => $data['notes'] ?? 'Story updated'
            ];
            
            $this->createVersion($versionData);
            
            $this->db->commit();
            return true;
            
        } catch(Exception $e) 
            $this->db->rollback();
            error_log('Story update failed: ' . $e->getMessage());
            return false;
        
    }
    
    /**
     * Get story with categories and tags
     * 
     * @param int $id
     * @return array|false
     */
    public function getStoryWithMetadata($id) {
        $story = $this->find($id);
        if (!$story) {
            return false;
        }
        
        // Get categories
        $story['categories'] = $this->getStoryCategories($id);
        
        // Get tags
        $story['tags'] = $this->getStoryTags($id);
        
        // Get media
        $story['media'] = $this->getStoryMedia($id);
        
        return $story;
    }
    
    /**
     * Search stories with filters
     * 
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function searchStories($filters = [], $limit = 10, $offset = 0) {
        $sql = "SELECT DISTINCT s.* FROM stories s";
        $joins = [];
        $conditions = [];
        $params = [];
        
        // Add joins for category and tag filters
        if (!empty($filters['categories'])) {
            $joins[] = "JOIN story_categories sc ON s.id = sc.story_id";
            $joins[] = "JOIN categories c ON sc.category_id = c.id";
            $conditions[] = "c.name IN (" . str_repeat('?,', count($filters['categories']) - 1) . "?)";
            $params = array_merge($params, $filters['categories']);
        }
        
        if (!empty($filters['tags'])) {
            $joins[] = "JOIN story_tags st ON s.id = st.story_id";
            $joins[] = "JOIN tags t ON st.tag_id = t.id";
            $conditions[] = "t.name IN (" . str_repeat('?,', count($filters['tags']) - 1) . "?)";
            $params = array_merge($params, $filters['tags']);
        }
        
        // Add joins to SQL
        if (!empty($joins)) {
            $sql .= " " . implode(" ", array_unique($joins));
        }
        
        // Add text search
        if (!empty($filters['search'])) {
            $conditions[] = "(s.title LIKE ? OR s.content LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Add published filter
        if (isset($filters['published'])) {
            $conditions[] = "s.published = ?";
            $params[] = $filters['published'];
        }
        
        // Add conditions to SQL
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        // Add ordering
        $sql .= " ORDER BY s.updated_at DESC";
        
        // Add limit and offset
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->select($sql, $params);
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
     * Get story categories
     * 
     * @param int $storyId
     * @return array
     */
    public function getStoryCategories($storyId) {
        $sql = "SELECT c.* FROM categories c 
                JOIN story_categories sc ON c.id = sc.category_id 
                WHERE sc.story_id = ?";
        return $this->db->select($sql, [$storyId]);
    }
    
    /**
     * Get story tags
     * 
     * @param int $storyId
     * @return array
     */
    public function getStoryTags($storyId) {
        $sql = "SELECT t.* FROM tags t 
                JOIN story_tags st ON t.id = st.tag_id 
                WHERE st.story_id = ?";
        return $this->db->select($sql, [$storyId]);
    }
    
    /**
     * Get story media
     * 
     * @param int $storyId
     * @return array
     */
    public function getStoryMedia($storyId) {
        $sql = "SELECT * FROM story_media WHERE story_id = ? ORDER BY created_at ASC";
        return $this->db->select($sql, [$storyId]);
    }
    
    /**
     * Add categories to story
     * 
     * @param int $storyId
     * @param array $categoryIds
     * @return bool
     */
    public function addStoryCategories($storyId, $categoryIds) {
        try {
            $this->db->beginTransaction();
            
            // Remove existing categories
            $this->db->update("DELETE FROM story_categories WHERE story_id = ?", [$storyId]);
            
            // Add new categories
            foreach ($categoryIds as $categoryId) {
                $this->db->insert("INSERT INTO story_categories (story_id, category_id) VALUES (?, ?)", 
                    [$storyId, $categoryId]);
            }
            
            $this->db->commit();
            return true;
            
        } catch(Exception $e) 
            $this->db->rollback();
            error_log('Adding story categories failed: ' . $e->getMessage());
            return false;
        
    }
    
    /**
     * Add tags to story
     * 
     * @param int $storyId
     * @param array $tagIds
     * @return bool
     */
    public function addStoryTags($storyId, $tagIds) {
        try {
            $this->db->beginTransaction();
            
            // Remove existing tags
            $this->db->update("DELETE FROM story_tags WHERE story_id = ?", [$storyId]);
            
            // Add new tags
            foreach ($tagIds as $tagId) {
                $this->db->insert("INSERT INTO story_tags (story_id, tag_id) VALUES (?, ?)", 
                    [$storyId, $tagId]);
            }
            
            $this->db->commit();
            return true;
            
        } catch(Exception $e) 
            $this->db->rollback();
            error_log('Adding story tags failed: ' . $e->getMessage());
            return false;
        
    }
    
    /**
     * Create story version
     * 
     * @param array $data
     * @return string|false
     */
    private function createVersion($data) {
        $sql = "INSERT INTO story_versions (story_id, version_number, title, content, metadata, created_by, created_at, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        return $this->db->insert($sql, [
            $data['story_id'],
            $data['version_number'],
            $data['title'],
            $data['content'],
            $data['metadata'],
            $data['created_by'],
            $data['created_at'],
            $data['notes']
        ]);
    }
    
    /**
     * Get latest version of story
     * 
     * @param int $storyId
     * @return array|false
     */
    public function getLatestVersion($storyId) {
        $sql = "SELECT * FROM story_versions WHERE story_id = ? ORDER BY version_number DESC LIMIT 1";
        return $this->db->selectOne($sql, [$storyId]);
    }
    
    /**
     * Get all versions of story
     * 
     * @param int $storyId
     * @return array
     */
    public function getStoryVersions($storyId) {
        $sql = "SELECT sv.*, u.username as created_by_name 
                FROM story_versions sv 
                LEFT JOIN users u ON sv.created_by = u.id 
                WHERE sv.story_id = ? 
                ORDER BY sv.version_number DESC";
        return $this->db->select($sql, [$storyId]);
    }
    
    /**
     * Get specific version of story
     * 
     * @param int $storyId
     * @param int $versionNumber
     * @return array|false
     */
    public function getStoryVersion($storyId, $versionNumber) {
        $sql = "SELECT sv.*, u.username as created_by_name 
                FROM story_versions sv 
                LEFT JOIN users u ON sv.created_by = u.id 
                WHERE sv.story_id = ? AND sv.version_number = ?";
        return $this->db->selectOne($sql, [$storyId, $versionNumber]);
    }
    
    /**
     * Publish story
     * 
     * @param int $id
     * @return bool
     */
    public function publishStory($id) {
        return $this->update($id, ['published' => true, 'updated_at' => date('Y-m-d H:i:s')]);
    }
    
    /**
     * Unpublish story
     * 
     * @param int $id
     * @return bool
     */
    public function unpublishStory($id) {
        return $this->update($id, ['published' => false, 'updated_at' => date('Y-m-d H:i:s')]);
    }
}
