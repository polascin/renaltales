<?php
/**
 * Home Controller
 * Handles the main homepage and public content
 */

require_once APP_PATH . '/Core/Controller.php';

class HomeController extends Controller {

    public function index() {
        // Get featured stories (public access)
        $featuredStories = $this->db->fetchAll(
            "SELECT s.*, u.username, u.first_name, u.last_name, 
                    COUNT(c.id) as comment_count
             FROM stories s
             LEFT JOIN users u ON s.user_id = u.id
             LEFT JOIN comments c ON s.id = c.story_id AND c.status = 'approved'
             WHERE s.status = 'published' 
               AND s.access_level = 'public'
               AND s.featured = 1
             GROUP BY s.id
             ORDER BY s.created_at DESC
             LIMIT 6"
        );

        // Get recent public stories
        $recentStories = $this->db->fetchAll(
            "SELECT s.*, u.username, u.first_name, u.last_name,
                    COUNT(c.id) as comment_count
             FROM stories s
             LEFT JOIN users u ON s.user_id = u.id
             LEFT JOIN comments c ON s.id = c.story_id AND c.status = 'approved'
             WHERE s.status = 'published' 
               AND s.access_level = 'public'
             GROUP BY s.id
             ORDER BY s.created_at DESC
             LIMIT 12"
        );

        // Get story categories with counts
        $categories = [];
        foreach ($GLOBALS['STORY_CATEGORIES'] as $slug => $name) {
            $count = $this->db->count(
                'stories',
                'category = ? AND status = ? AND access_level = ?',
                [$slug, 'published', 'public']
            );
            $categories[] = [
                'slug' => $slug,
                'name' => $name,
                'count' => $count
            ];
        }

        // Get site statistics
        $stats = [
            'total_stories' => $this->db->count(
                'stories', 
                'status = ? AND access_level = ?', 
                ['published', 'public']
            ),
            'total_users' => $this->db->count('users', 'active = ?', [1]),
            'total_comments' => $this->db->count(
                'comments', 
                'status = ?', 
                ['approved']
            ),
            'supported_languages' => count($this->language->getSupportedLanguages())
        ];

        $this->view('home/index', [
            'featuredStories' => $featuredStories,
            'recentStories' => $recentStories,
            'categories' => $categories,
            'stats' => $stats
        ]);
    }
}
