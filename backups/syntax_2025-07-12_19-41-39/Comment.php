<?php

/**
 * Comment Model - Comment management for stories
 * 
 * Handles comment operations for story interaction
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

require_once 'BaseModel.php';

class Comment extends BaseModel {
    
    protected $table = 'story_comments';
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Validate comment data
     * 
     * @param array $data
     * @return array Validation errors
     */
    protected function validate(array $data): array {
        $errors = [];
        
        // Story ID validation
        if (empty($data['story_id'])) {
            $errors['story_id'] = 'Story ID is required';
        }
        
        // User ID validation
        if (empty($data['user_id'])) {
            $errors['user_id'] = 'User ID is required';
        }
        
        // Content validation
        if (empty($data['content'])) {
            $errors['content'] = 'Comment content is required';
        } elseif (strlen($data['content']) > 1000) {
            $errors['content'] = 'Comment must be less than 1000 characters';
        }
        
        // Parent ID validation (if provided)
        if (!empty($data['parent_id'])) {
            $parent = $this->find($data['parent_id']);
            if (!$parent) {
                $errors['parent_id'] = 'Parent comment not found';
            } elseif ($parent['story_id'] != $data['story_id']) {
                $errors['parent_id'] = 'Parent comment must be from the same story';
            }
        }
        
        // Status validation
        if (isset($data['status']) && !in_array($data['status'], ['pending', 'approved', 'rejected'])) {
            $errors['status'] = 'Invalid comment status';
        }
        
        return $errors;
    }
    
    /**
     * Create a new comment
     * 
     * @param array $data
     * @return string|false Comment ID on success, false on failure
     */
    public function createComment($data) {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return false;
        }
        
        $commentData = [
            'story_id' => $data['story_id'],
            'user_id' => $data['user_id'],
            'parent_id' => $data['parent_id'] ?? null,
            'content' => trim($data['content']),
            'status' => $data['status'] ?? 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($commentData);
    }
    
    /**
     * Update comment
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateComment($id, $data) {
        $comment = $this->find($id);
        if (!$comment) {
            return false;
        }
        
        // Merge with existing data for validation
        $data = array_merge($comment, $data);
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return false;
        }
        
        $updateData = [
            'content' => trim($data['content']),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($id, $updateData);
    }
    
    /**
     * Get comments for a story
     * 
     * @param int $storyId
     * @param string $status
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getStoryComments($storyId, $status = 'approved', $limit = 50, $offset = 0) {
        $sql = "SELECT c.*, u.username as author_name 
                FROM {$this->table} c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.story_id = ? AND c.status = ? 
                ORDER BY c.created_at ASC 
                LIMIT ? OFFSET ?";
        
        return $this->db->select($sql, [$storyId, $status, $limit, $offset]);
    }
    
    /**
     * Get threaded comments for a story
     * 
     * @param int $storyId
     * @param string $status
     * @return array
     */
    public function getThreadedComments($storyId, $status = 'approved') {
        $sql = "SELECT c.*, u.username as author_name 
                FROM {$this->table} c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.story_id = ? AND c.status = ? 
                ORDER BY c.parent_id ASC, c.created_at ASC";
        
        $comments = $this->db->select($sql, [$storyId, $status]);
        
        return $this->buildCommentTree($comments);
    }
    
    /**
     * Get replies to a comment
     * 
     * @param int $commentId
     * @param string $status
     * @return array
     */
    public function getReplies($commentId, $status = 'approved') {
        $sql = "SELECT c.*, u.username as author_name 
                FROM {$this->table} c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.parent_id = ? AND c.status = ? 
                ORDER BY c.created_at ASC";
        
        return $this->db->select($sql, [$commentId, $status]);
    }
    
    /**
     * Approve comment
     * 
     * @param int $id
     * @return bool
     */
    public function approveComment($id) {
        return $this->update($id, [
            'status' => 'approved',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Reject comment
     * 
     * @param int $id
     * @return bool
     */
    public function rejectComment($id) {
        return $this->update($id, [
            'status' => 'rejected',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get pending comments
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getPendingComments($limit = 20, $offset = 0) {
        $sql = "SELECT c.*, u.username as author_name, s.title as story_title 
                FROM {$this->table} c 
                JOIN users u ON c.user_id = u.id 
                JOIN stories s ON c.story_id = s.id 
                WHERE c.status = 'pending' 
                ORDER BY c.created_at ASC 
                LIMIT ? OFFSET ?";
        
        return $this->db->select($sql, [$limit, $offset]);
    }
    
    /**
     * Get comment count for story
     * 
     * @param int $storyId
     * @param string $status
     * @return int
     */
    public function getStoryCommentCount($storyId, $status = 'approved') {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE story_id = ? AND status = ?";
        $result = $this->db->selectOne($sql, [$storyId, $status]);
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Get comment statistics
     * 
     * @return array
     */
    public function getCommentStats() {
        $sql = "SELECT 
                    status,
                    COUNT(*) as count
                FROM {$this->table} 
                GROUP BY status";
        
        return $this->db->select($sql);
    }
    
    /**
     * Get user's comments
     * 
     * @param int $userId
     * @param string $status
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getUserComments($userId, $status = null, $limit = 20, $offset = 0) {
        $sql = "SELECT c.*, s.title as story_title 
                FROM {$this->table} c 
                JOIN stories s ON c.story_id = s.id 
                WHERE c.user_id = ?";
        
        $params = [$userId];
        
        if ($status) {
            $sql .= " AND c.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY c.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Delete comment and its replies
     * 
     * @param int $id
     * @return bool
     */
    public function deleteComment($id) {
        try {
            $this->db->beginTransaction();
            
            // Delete all replies first
            $this->deleteReplies($id);
            
            // Delete the comment
            $this->delete($id);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Comment deletion failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete all replies to a comment
     * 
     * @param int $commentId
     * @return bool
     */
    private function deleteReplies($commentId) {
        $replies = $this->getReplies($commentId, null); // Get all replies regardless of status
        
        foreach ($replies as $reply) {
            $this->deleteComment($reply['id']); // Recursive deletion
        }
        
        return true;
    }
    
    /**
     * Build comment tree structure
     * 
     * @param array $comments
     * @return array
     */
    private function buildCommentTree($comments) {
        $tree = [];
        $refs = [];
        
        foreach ($comments as $comment) {
            $comment['replies'] = [];
            $refs[$comment['id']] = $comment;
            
            if ($comment['parent_id'] === null) {
                $tree[] = &$refs[$comment['id']];
            } else {
                if (isset($refs[$comment['parent_id']])) {
                    $refs[$comment['parent_id']]['replies'][] = &$refs[$comment['id']];
                }
            }
        }
        
        return $tree;
    }
    
    /**
     * Check if user can comment on story
     * 
     * @param int $userId
     * @param int $storyId
     * @return bool
     */
    public function canUserComment($userId, $storyId) {
        // Check if story exists and is published
        $story = $this->db->selectOne("SELECT * FROM stories WHERE id = ? AND published = 1", [$storyId]);
        if (!$story) {
            return false;
        }
        
        // Check if user exists and is active
        $user = $this->db->selectOne("SELECT * FROM users WHERE id = ? AND status = 'active'", [$userId]);
        if (!$user) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get recent comments
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentComments($limit = 10) {
        $sql = "SELECT c.*, u.username as author_name, s.title as story_title 
                FROM {$this->table} c 
                JOIN users u ON c.user_id = u.id 
                JOIN stories s ON c.story_id = s.id 
                WHERE c.status = 'approved' 
                ORDER BY c.created_at DESC 
                LIMIT ?";
        
        return $this->db->select($sql, [$limit]);
    }
}
