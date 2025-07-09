<?php

/**
 * Tag Model - Tag management for stories
 * 
 * Handles tag operations for story organization and search
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

require_once 'BaseModel.php';

class Tag extends BaseModel {
    
    protected $table = 'tags';
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Validate tag data
     * 
     * @param array $data
     * @return array Validation errors
     */
    protected function validate($data) {
        $errors = [];
        
        // Name validation
        if (empty($data['name'])) {
            $errors['name'] = 'Tag name is required';
        } elseif (strlen($data['name']) > 100) {
            $errors['name'] = 'Tag name must be less than 100 characters';
        }
        
        // Check for duplicate tag names
        if (!empty($data['name'])) {
            $existing = $this->findByName($data['name']);
            if ($existing && (empty($data['id']) || $existing['id'] != $data['id'])) {
                $errors['name'] = 'Tag name already exists';
            }
        }
        
        return $errors;
    }
    
    /**
     * Create a new tag
     * 
     * @param array $data
     * @return string|false Tag ID on success, false on failure
     */
    public function createTag($data) {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return false;
        }
        
        $tagData = [
            'name' => trim($data['name'])
        ];
        
        return $this->create($tagData);
    }
    
    /**
     * Update tag
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateTag($id, $data) {
        $data['id'] = $id; // Add ID for duplicate checking
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return false;
        }
        
        $tagData = [
            'name' => trim($data['name'])
        ];
        
        return $this->update($id, $tagData);
    }
    
    /**
     * Find tag by name
     * 
     * @param string $name
     * @return array|false
     */
    public function findByName($name) {
        $sql = "SELECT * FROM {$this->table} WHERE name = ? LIMIT 1";
        return $this->db->selectOne($sql, [$name]);
    }
    
    /**
     * Get all tags with story counts
     * 
     * @return array
     */
    public function getAllWithCounts() {
        $sql = "SELECT t.*, COUNT(st.story_id) as story_count 
                FROM tags t 
                LEFT JOIN story_tags st ON t.id = st.tag_id 
                GROUP BY t.id 
                ORDER BY t.name ASC";
        return $this->db->select($sql);
    }
    
    /**
     * Get tags by story
     * 
     * @param int $storyId
     * @return array
     */
    public function getByStory($storyId) {
        $sql = "SELECT t.* FROM tags t 
                JOIN story_tags st ON t.id = st.tag_id 
                WHERE st.story_id = ? 
                ORDER BY t.name ASC";
        return $this->db->select($sql, [$storyId]);
    }
    
    /**
     * Get or create tag by name
     * 
     * @param string $name
     * @return int Tag ID
     */
    public function getOrCreate($name) {
        $tag = $this->findByName($name);
        if ($tag) {
            return $tag['id'];
        }
        
        return $this->createTag(['name' => $name]);
    }
    
    /**
     * Get or create multiple tags from array
     * 
     * @param array $tagNames
     * @return array Array of tag IDs
     */
    public function getOrCreateMultiple($tagNames) {
        $tagIds = [];
        
        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if (!empty($tagName)) {
                $tagIds[] = $this->getOrCreate($tagName);
            }
        }
        
        return $tagIds;
    }
    
    /**
     * Delete tag (only if no stories are using it)
     * 
     * @param int $id
     * @return bool
     */
    public function deleteTag($id) {
        // Check if tag is being used by any stories
        $sql = "SELECT COUNT(*) as count FROM story_tags WHERE tag_id = ?";
        $result = $this->db->selectOne($sql, [$id]);
        
        if ($result && $result['count'] > 0) {
            return false; // Cannot delete tag with associated stories
        }
        
        return $this->delete($id);
    }
    
    /**
     * Get popular tags (by story count)
     * 
     * @param int $limit
     * @return array
     */
    public function getPopular($limit = 10) {
        $sql = "SELECT t.*, COUNT(st.story_id) as story_count 
                FROM tags t 
                LEFT JOIN story_tags st ON t.id = st.tag_id 
                GROUP BY t.id 
                HAVING story_count > 0 
                ORDER BY story_count DESC 
                LIMIT ?";
        return $this->db->select($sql, [$limit]);
    }
    
    /**
     * Search tags by name
     * 
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function searchTags($query, $limit = 10) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE name LIKE ? 
                ORDER BY name ASC 
                LIMIT ?";
        return $this->db->select($sql, ['%' . $query . '%', $limit]);
    }
    
    /**
     * Get tag cloud data
     * 
     * @param int $limit
     * @return array
     */
    public function getTagCloud($limit = 50) {
        $sql = "SELECT t.*, COUNT(st.story_id) as story_count 
                FROM tags t 
                LEFT JOIN story_tags st ON t.id = st.tag_id 
                GROUP BY t.id 
                HAVING story_count > 0 
                ORDER BY story_count DESC, t.name ASC 
                LIMIT ?";
        return $this->db->select($sql, [$limit]);
    }
}

?>
