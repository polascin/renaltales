<?php

/**
 * AdminController
 * Handles administrative functions for the RenalTales platform
 */

class AdminController extends Controller {
    
    public function __construct() {
        parent::__construct();
        
        // Require admin or moderator access for all admin functions
        $this->requireAuth();
        if (!$this->hasPermission('moderate_stories') && !$this->isAdmin()) {
            $this->forbidden('Access denied. Administrative privileges required.');
        }
    }
    
    /**
     * Admin Dashboard - Main overview page
     */
    public function dashboard() {
        try {
            $stats = $this->getDashboardStatistics();
            $recentActivity = $this->getRecentActivity();
            $pendingContent = $this->getPendingContent();
            $systemHealth = $this->getSystemHealth();
            
            $this->view('admin/dashboard', [
                'title' => 'Admin Dashboard',
                'stats' => $stats,
                'recent_activity' => $recentActivity,
                'pending_content' => $pendingContent,
                'system_health' => $systemHealth,
                'current_page' => 'admin_dashboard'
            ]);
            
        } catch (Exception $e) {
            error_log("Admin dashboard error: " . $e->getMessage());
            $this->error('Failed to load admin dashboard');
        }
    }
    
    /**
     * Content Moderation - Manage pending stories and comments
     */
    public function moderation() {
        try {
            $tab = $_GET['tab'] ?? 'stories';
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 20;
            
            if ($tab === 'stories') {
                $content = $this->getPendingStories($page, $perPage);
            } else {
                $content = $this->getPendingComments($page, $perPage);
            }
            
            $this->view('admin/moderation', [
                'title' => 'Content Moderation',
                'tab' => $tab,
                'content' => $content,
                'current_page' => 'admin_moderation'
            ]);
            
        } catch (Exception $e) {
            error_log("Moderation error: " . $e->getMessage());
            $this->error('Failed to load moderation panel');
        }
    }
    
    /**
     * User Management - Manage platform users
     */
    public function users() {
        try {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $search = trim($_GET['search'] ?? '');
            $role = $_GET['role'] ?? '';
            $status = $_GET['status'] ?? '';
            $perPage = 25;
            
            $users = $this->getUsers($page, $perPage, $search, $role, $status);
            $userStats = $this->getUserStatistics();
            
            $this->view('admin/users', [
                'title' => 'User Management',
                'users' => $users,
                'user_stats' => $userStats,
                'search' => $search,
                'role_filter' => $role,
                'status_filter' => $status,
                'current_page' => 'admin_users'
            ]);
            
        } catch (Exception $e) {
            error_log("User management error: " . $e->getMessage());
            $this->error('Failed to load user management');
        }
    }
    
    /**
     * Statistics and Analytics
     */
    public function statistics() {
        try {
            $timeframe = $_GET['timeframe'] ?? '30';
            $stats = $this->getDetailedStatistics($timeframe);
            
            $this->view('admin/statistics', [
                'title' => 'Statistics & Analytics',
                'stats' => $stats,
                'timeframe' => $timeframe,
                'current_page' => 'admin_statistics'
            ]);
            
        } catch (Exception $e) {
            error_log("Statistics error: " . $e->getMessage());
            $this->error('Failed to load statistics');
        }
    }
    
    /**
     * System Settings Management
     */
    public function settings() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->updateSettings();
                return;
            }
            
            $settings = $this->getSystemSettings();
            
            $this->view('admin/settings', [
                'title' => 'System Settings',
                'settings' => $settings,
                'current_page' => 'admin_settings'
            ]);
            
        } catch (Exception $e) {
            error_log("Settings error: " . $e->getMessage());
            $this->error('Failed to load system settings');
        }
    }
    
    /**
     * Approve Story
     */
    public function approveStory() {
        try {
            $this->validateCsrf();
            $storyId = (int)($_POST['story_id'] ?? 0);
            
            if (!$storyId) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid story ID']);
                return;
            }
            
            $story = $this->db->fetch("SELECT * FROM stories WHERE id = ?", [$storyId]);
            if (!$story) {
                $this->jsonResponse(['success' => false, 'message' => 'Story not found']);
                return;
            }
            
            // Update story status
            $this->db->execute("
                UPDATE stories 
                SET status = 'published', published_at = NOW(), approved_by = ?, updated_at = NOW()
                WHERE id = ?
            ", [$this->currentUser['id'], $storyId]);
            
            // Update content status
            $this->db->execute("
                UPDATE story_contents 
                SET status = 'published', updated_at = NOW()
                WHERE story_id = ?
            ", [$storyId]);
            
            // Log activity
            $this->logActivity('story_approved', "Approved story ID: {$storyId}", $this->currentUser['id']);
            
            // Create notification for author
            $this->createNotification($story['user_id'], 'story_approved', 'Story Approved', 
                'Your story has been approved and published!', ['story_id' => $storyId]);
            
            $this->jsonResponse(['success' => true, 'message' => 'Story approved successfully']);
            
        } catch (Exception $e) {
            error_log("Story approval error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Failed to approve story']);
        }
    }
    
    /**
     * Reject Story
     */
    public function rejectStory() {
        try {
            $this->validateCsrf();
            $storyId = (int)($_POST['story_id'] ?? 0);
            $reason = trim($_POST['reason'] ?? '');
            
            if (!$storyId) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid story ID']);
                return;
            }
            
            $story = $this->db->fetch("SELECT * FROM stories WHERE id = ?", [$storyId]);
            if (!$story) {
                $this->jsonResponse(['success' => false, 'message' => 'Story not found']);
                return;
            }
            
            // Update story status
            $this->db->execute("
                UPDATE stories 
                SET status = 'rejected', rejection_reason = ?, reviewed_by = ?, updated_at = NOW()
                WHERE id = ?
            ", [$reason, $this->currentUser['id'], $storyId]);
            
            // Log activity
            $this->logActivity('story_rejected', "Rejected story ID: {$storyId} - Reason: {$reason}", $this->currentUser['id']);
            
            // Create notification for author
            $this->createNotification($story['user_id'], 'story_rejected', 'Story Rejected', 
                "Your story was rejected. Reason: {$reason}", ['story_id' => $storyId]);
            
            $this->jsonResponse(['success' => true, 'message' => 'Story rejected']);
            
        } catch (Exception $e) {
            error_log("Story rejection error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Failed to reject story']);
        }
    }
    
    /**
     * Update User Role
     */
    public function updateUserRole() {
        try {
            $this->requireAdmin(); // Only admins can change roles
            $this->validateCsrf();
            
            $userId = (int)($_POST['user_id'] ?? 0);
            $newRole = trim($_POST['role'] ?? '');
            
            if (!$userId || !$newRole) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid parameters']);
                return;
            }
            
            $validRoles = array_keys($GLOBALS['CONFIG']['user_roles']);
            if (!in_array($newRole, $validRoles)) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid role']);
                return;
            }
            
            $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
            if (!$user) {
                $this->jsonResponse(['success' => false, 'message' => 'User not found']);
                return;
            }
            
            // Prevent demoting the last admin
            if ($user['role'] === 'admin' && $newRole !== 'admin') {
                $adminCount = $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")['count'];
                if ($adminCount <= 1) {
                    $this->jsonResponse(['success' => false, 'message' => 'Cannot demote the last admin']);
                    return;
                }
            }
            
            $this->db->execute("UPDATE users SET role = ?, updated_at = NOW() WHERE id = ?", [$newRole, $userId]);
            
            $this->logActivity('user_role_changed', "Changed user {$user['username']} role from {$user['role']} to {$newRole}", $this->currentUser['id']);
            
            $this->jsonResponse(['success' => true, 'message' => 'User role updated successfully']);
            
        } catch (Exception $e) {
            error_log("User role update error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update user role']);
        }
    }
    
    /**
     * Get Dashboard Statistics
     */
    private function getDashboardStatistics() {
        $stats = [];
        
        // User statistics
        $stats['users'] = $this->db->fetch("
            SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_this_week,
                COUNT(CASE WHEN email_verified_at IS NOT NULL THEN 1 END) as verified,
                COUNT(CASE WHEN role = 'admin' THEN 1 END) as admins,
                COUNT(CASE WHEN role = 'moderator' THEN 1 END) as moderators
            FROM users
        ");
        
        // Story statistics
        $stats['stories'] = $this->db->fetch("
            SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'published' THEN 1 END) as published,
                COUNT(CASE WHEN status = 'pending_review' THEN 1 END) as pending,
                COUNT(CASE WHEN status = 'draft' THEN 1 END) as drafts,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_this_week
            FROM stories
        ");
        
        // Comment statistics
        $stats['comments'] = $this->db->fetch("
            SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as last_24h
            FROM comments
        ");
        
        // System health
        $stats['system'] = [
            'database_size' => $this->getDatabaseSize(),
            'active_sessions' => $this->getActiveSessionCount(),
            'error_count_24h' => $this->getErrorCount24h()
        ];
        
        return $stats;
    }
    
    /**
     * Get Recent Activity
     */
    private function getRecentActivity($limit = 10) {
        return $this->db->fetchAll("
            SELECT al.*, u.username 
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.id
            ORDER BY al.created_at DESC
            LIMIT ?
        ", [$limit]);
    }
    
    /**
     * Get Pending Content for Quick Review
     */
    private function getPendingContent() {
        $pending = [];
        
        // Pending stories
        $pending['stories'] = $this->db->fetchAll("
            SELECT s.id, sc.title, u.username, s.created_at
            FROM stories s
            LEFT JOIN story_contents sc ON s.id = sc.story_id AND sc.language = s.original_language
            LEFT JOIN users u ON s.user_id = u.id
            WHERE s.status = 'pending_review'
            ORDER BY s.created_at ASC
            LIMIT 5
        ");
        
        // Pending comments
        $pending['comments'] = $this->db->fetchAll("
            SELECT c.id, c.content, u.username, c.created_at, s.id as story_id, sc.title as story_title
            FROM comments c
            LEFT JOIN users u ON c.user_id = u.id
            LEFT JOIN stories s ON c.story_id = s.id
            LEFT JOIN story_contents sc ON s.id = sc.story_id AND sc.language = s.original_language
            WHERE c.status = 'pending'
            ORDER BY c.created_at ASC
            LIMIT 5
        ");
        
        return $pending;
    }
    
    /**
     * Get System Health Status
     */
    private function getSystemHealth() {
        $health = [];
        
        // Check database connection
        try {
            $this->db->fetch("SELECT 1");
            $health['database'] = 'healthy';
        } catch (Exception $e) {
            $health['database'] = 'error';
        }
        
        // Check storage space (if possible)
        $storageDir = ROOT_PATH . '/storage';
        if (is_dir($storageDir)) {
            $freeBytes = disk_free_space($storageDir);
            $totalBytes = disk_total_space($storageDir);
            if ($freeBytes && $totalBytes) {
                $usagePercent = (($totalBytes - $freeBytes) / $totalBytes) * 100;
                $health['storage'] = $usagePercent > 90 ? 'warning' : 'healthy';
                $health['storage_usage'] = round($usagePercent, 1);
            }
        }
        
        // Check log file sizes
        $logDir = ROOT_PATH . '/storage/logs';
        if (is_dir($logDir)) {
            $logSize = 0;
            foreach (glob($logDir . '/*.log') as $logFile) {
                $logSize += filesize($logFile);
            }
            $health['logs_size'] = round($logSize / (1024 * 1024), 2); // MB
        }
        
        return $health;
    }
    
    /**
     * Get detailed statistics for analytics
     */
    private function getDetailedStatistics($days) {
        $stats = [];
        
        // Story engagement over time
        $stats['story_engagement'] = $this->db->fetchAll("
            SELECT 
                DATE(s.published_at) as date,
                COUNT(*) as stories_published,
                COALESCE(AVG(ss.views), 0) as avg_views,
                COALESCE(AVG(ss.likes), 0) as avg_likes
            FROM stories s
            LEFT JOIN story_statistics ss ON s.id = ss.story_id
            WHERE s.published_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND s.status = 'published'
            GROUP BY DATE(s.published_at)
            ORDER BY date DESC
        ", [$days]);
        
        // User registration trends
        $stats['user_trends'] = $this->db->fetchAll("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as new_users,
                COUNT(CASE WHEN email_verified_at IS NOT NULL THEN 1 END) as verified_users
            FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ", [$days]);
        
        // Category popularity
        $stats['category_popularity'] = $this->db->fetchAll("
            SELECT 
                cat.name,
                COUNT(s.id) as story_count,
                AVG(COALESCE(ss.views, 0)) as avg_views
            FROM story_categories cat
            LEFT JOIN stories s ON cat.id = s.category_id AND s.status = 'published'
            LEFT JOIN story_statistics ss ON s.id = ss.story_id
            GROUP BY cat.id, cat.name
            ORDER BY story_count DESC
        ");
        
        // Language distribution
        $stats['language_distribution'] = $this->db->fetchAll("
            SELECT 
                s.original_language,
                COUNT(*) as story_count
            FROM stories s
            WHERE s.status = 'published'
            GROUP BY s.original_language
            ORDER BY story_count DESC
        ");
        
        return $stats;
    }
    
    /**
     * Utility methods
     */
    private function getDatabaseSize() {
        try {
            $result = $this->db->fetch("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
            ");
            return $result['size_mb'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getActiveSessionCount() {
        try {
            $result = $this->db->fetch("
                SELECT COUNT(*) as count 
                FROM user_sessions 
                WHERE last_activity > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function getErrorCount24h() {
        try {
            $result = $this->db->fetch("
                SELECT COUNT(*) as count 
                FROM security_logs 
                WHERE event = 'error' 
                AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function createNotification($userId, $type, $title, $message, $data = []) {
        try {
            $this->db->insert('notifications', [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => json_encode($data),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Notification creation failed: " . $e->getMessage());
        }
    }
    
    private function isAdmin() {
        return $this->currentUser && $this->currentUser['role'] === 'admin';
    }
    
    private function requireAdmin() {
        if (!$this->isAdmin()) {
            $this->forbidden('Administrator access required');
        }
    }
}
