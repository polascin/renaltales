<?php

/**
 * Category Model - Category management for stories
 * 
 * Handles category operations for story organization
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

require_once 'BaseModel.php';

class Category extends BaseModel {
    
    protected $table = 'categories';
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Validate category data
     * 
     * @param array $data
     * @return array Validation errors
     */
    protected function validate($data) {
        $errors = [];
        
        // Name validation
        if (empty($data['name'])) {
            $errors['name'] = 'Category name is required';
        } elseif (strlen($data['name']) > 100) {
            $errors['name'] = 'Category name must be less than 100 characters';
        }
        
        // Check for duplicate category names
        if (!empty($data['name'])) {
            $existing = $this->findByName($data['name']);
            if ($existing && (empty($data['id']) || $existing['id'] != $data['id'])) {
                $errors['name'] = 'Category name already exists';
            }
        }
        
        return $errors;
    }
    
    /**
     * Create a new category
     * 
     * @param array $data
     * @return string|false Category ID on success, false on failure
     */
    public function createCategory($data) {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return false;
        }
        
        $categoryData = [
            'name' => trim($data['name'])
        ];
        
        return $this->create($categoryData);
    }
    
    /**
     * Update category
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateCategory($id, $data) {
        $data['id'] = $id; // Add ID for duplicate checking
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return false;
        }
        
        $categoryData = [
            'name' => trim($data['name'])
        ];
        
        return $this->update($id, $categoryData);
    }
    
    /**
     * Find category by name
     * 
     * @param string $name
     * @return array|false
     */
    public function findByName($name) {
        $sql = "SELECT * FROM {$this->table} WHERE name = ? LIMIT 1";
        return $this->db->selectOne($sql, [$name]);
    }
    
    /**
     * Get all categories with story counts
     * 
     * @return array
     */
    public function getAllWithCounts() {
        $sql = "SELECT c.*, COUNT(sc.story_id) as story_count 
                FROM categories c 
                LEFT JOIN story_categories sc ON c.id = sc.category_id 
                GROUP BY c.id 
                ORDER BY c.name ASC";
        return $this->db->select($sql);
    }
    
    /**
     * Get categories by story
     * 
     * @param int $storyId
     * @return array
     */
    public function getByStory($storyId) {
        $sql = "SELECT c.* FROM categories c 
                JOIN story_categories sc ON c.id = sc.category_id 
                WHERE sc.story_id = ? 
                ORDER BY c.name ASC";
        return $this->db->select($sql, [$storyId]);
    }
    
    /**
     * Get or create category by name
     * 
     * @param string $name
     * @return int Category ID
     */
    public function getOrCreate($name) {
        $category = $this->findByName($name);
        if ($category) {
            return $category['id'];
        }
        
        return $this->createCategory(['name' => $name]);
    }
    
    /**
     * Delete category (only if no stories are using it)
     * 
     * @param int $id
     * @return bool
     */
    public function deleteCategory($id) {
        // Check if category is being used by any stories
        $sql = "SELECT COUNT(*) as count FROM story_categories WHERE category_id = ?";
        $result = $this->db->selectOne($sql, [$id]);
        
        if ($result && $result['count'] > 0) {
            return false; // Cannot delete category with associated stories
        }
        
        return $this->delete($id);
    }
    
    /**
     * Get popular categories (by story count)
     * 
     * @param int $limit
     * @return array
     */
    public function getPopular($limit = 10) {
        $sql = "SELECT c.*, COUNT(sc.story_id) as story_count 
                FROM categories c 
                LEFT JOIN story_categories sc ON c.id = sc.category_id 
                GROUP BY c.id 
                HAVING story_count > 0 
                ORDER BY story_count DESC 
                LIMIT ?";
        return $this->db->select($sql, [$limit]);
    }
}

?>
