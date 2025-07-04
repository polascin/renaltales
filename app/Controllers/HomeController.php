<?php
/**
 * Home Controller
 * Handles the main homepage and public content
 */

require_once APP_PATH . '/Core/Controller.php';

class HomeController extends Controller {

    public function index() {
        // Temporary test version - using static data until database is fully set up
        try {
            // Try to get data from database
            $featuredStories = [];
            $recentStories = [];
            $categories = [];
            $stats = [
                'total_stories' => 0,
                'total_users' => 0,
                'total_comments' => 0,
                'supported_languages' => count($GLOBALS['SUPPORTED_STORY_LANGUAGES'] ?? [])
            ];

            // If database connection works, try to get real data
            if ($this->db) {
                try {
                    // Get story categories with counts
                    foreach ($GLOBALS['STORY_CATEGORIES'] as $slug => $name) {
                        $categories[] = [
                            'slug' => $slug,
                            'name' => $name,
                            'count' => 0 // Default to 0 for now
                        ];
                    }

                    // Try to get basic stats if tables exist
                    $stats['total_stories'] = 0; // Will be updated when tables exist
                    $stats['total_users'] = 0;
                    $stats['total_comments'] = 0;
                } catch (Exception $e) {
                    // Database tables don't exist yet, use defaults
                    error_log("Database tables not ready: " . $e->getMessage());
                }
            }

        } catch (Exception $e) {
            // Database connection failed, use static data
            $featuredStories = [];
            $recentStories = [];
            $categories = [];
            foreach ($GLOBALS['STORY_CATEGORIES'] as $slug => $name) {
                $categories[] = [
                    'slug' => $slug,
                    'name' => $name,
                    'count' => 0
                ];
            }
            $stats = [
                'total_stories' => 0,
                'total_users' => 0,
                'total_comments' => 0,
                'supported_languages' => count($GLOBALS['SUPPORTED_STORY_LANGUAGES'] ?? [])
            ];
        }

        $this->view('home/index', [
            'featuredStories' => $featuredStories,
            'recentStories' => $recentStories,
            'categories' => $categories,
            'stats' => $stats
        ]);
    }
}
