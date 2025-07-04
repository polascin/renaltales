<?php
/**
 * Story Controller
 * Handles story CRUD operations with authorization checks and pagination
 */

require_once APP_PATH . '/Core/Controller.php';

class StoryController extends Controller {
    private $storyService;
    private $storyRepository;
    private $contentRepository;
    
    public function __construct() {
        parent::__construct();
        
        // Initialize services if using the modern architecture
        if (class_exists('RenalTales\Service\StoryService')) {
            require_once ROOT_PATH . '/src/Service/StoryService.php';
            require_once ROOT_PATH . '/src/Repository/StoryRepository.php';
            require_once ROOT_PATH . '/src/Repository/StoryContentRepository.php';
            require_once ROOT_PATH . '/src/Model/Story.php';
            require_once ROOT_PATH . '/src/Model/StoryContent.php';
            require_once ROOT_PATH . '/src/Model/User.php';
            
            $this->storyService = new \RenalTales\Service\StoryService();
            $this->storyRepository = new \RenalTales\Repository\StoryRepository();
            $this->contentRepository = new \RenalTales\Repository\StoryContentRepository();
        }
    }
    
    /**
     * Show paginated list of published stories
     */
    public function index() {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;
        $category = $_GET['category'] ?? null;
        $language = $_GET['language'] ?? null;
        $search = $_GET['search'] ?? null;
        
        try {
            // Build query conditions
            $conditions = ['s.status = ?'];
            $params = ['published'];
            
            // Add access level filtering based on user authentication
            if (!$this->currentUser) {
                $conditions[] = 's.access_level = ?';
                $params[] = 'public';
            } elseif (!$this->hasPermission('view_premium_content')) {
                $conditions[] = 's.access_level IN (?, ?, ?)';
                $params = array_merge($params, ['public', 'registered', 'verified']);
            }
            
            // Add category filter
            if ($category) {
                $conditions[] = 'sc.slug = ?';
                $params[] = $category;
            }
            
            // Add language filter
            if ($language) {
                $conditions[] = 'content.language = ?';
                $params[] = $language;
            }
            
            // Add search filter
            if ($search) {
                $conditions[] = '(content.title LIKE ? OR content.content LIKE ? OR content.excerpt LIKE ?)';
                $searchTerm = '%' . $search . '%';
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
            }
            
            // Build the query
            $query = "
                SELECT s.*, u.username, u.full_name, 
                       content.title, content.excerpt, content.language,
                       sc.name as category_name, sc.slug as category_slug,
                       COUNT(DISTINCT c.id) as comment_count,
                       s.created_at, s.published_at
                FROM stories s
                LEFT JOIN users u ON s.user_id = u.id
                LEFT JOIN story_categories sc ON s.category_id = sc.id
                LEFT JOIN story_contents content ON s.id = content.story_id 
                    AND content.status = 'published' 
                    AND content.language = s.original_language
                LEFT JOIN comments c ON s.id = c.story_id AND c.status = 'approved'
                WHERE " . implode(' AND ', $conditions) . "
                GROUP BY s.id
                ORDER BY s.published_at DESC, s.created_at DESC
            ";
            
            // Get paginated results
            $pagination = $this->paginate($query, $params, $page, $perPage);
            
            // Get categories for filter
            $categories = $this->db->fetchAll(
                "SELECT * FROM story_categories ORDER BY name"
            );
            
            // Get available languages
            $languages = $GLOBALS['SUPPORTED_STORY_LANGUAGES'];
            
            $this->view('stories/index', [
                'stories' => $pagination['items'],
                'pagination' => $pagination,
                'categories' => $categories,
                'languages' => $languages,
                'currentCategory' => $category,
                'currentLanguage' => $language,
                'currentSearch' => $search,
                'page_title' => 'Stories'
            ]);
            
        } catch (Exception $e) {
            error_log("Stories index error: " . $e->getMessage());
            $this->error('Failed to load stories');
        }
    }
    
    /**
     * Show story creation form
     */
    public function create() {
        $this->requireAuth();
        
        // Get categories
        $categories = $this->db->fetchAll(
            "SELECT * FROM story_categories ORDER BY name"
        );
        
        $this->view('stories/create', [
            'categories' => $categories,
            'languages' => $GLOBALS['SUPPORTED_STORY_LANGUAGES'],
            'access_levels' => $GLOBALS['ACCESS_LEVELS'],
            'csrf_token' => $this->generateCsrf(),
            'errors' => $this->flash('errors'),
            'old_input' => $this->flash('old_input'),
            'page_title' => 'Create New Story'
        ]);
    }
    
    /**
     * Store a new story
     */
    public function store() {
        $this->requireAuth();
        
        try {
            $this->validateCsrf();
            
            $input = [
                'title' => $this->sanitize($_POST['title'] ?? ''),
                'content' => $_POST['content'] ?? '', // Don't sanitize HTML content here
                'category_id' => (int)($_POST['category_id'] ?? 0),
                'language' => $this->sanitize($_POST['language'] ?? $this->currentUser['language_preference']),
                'access_level' => $this->sanitize($_POST['access_level'] ?? 'public'),
                'tags' => array_filter(array_map('trim', explode(',', $_POST['tags'] ?? ''))),
                'save_as_draft' => isset($_POST['save_as_draft'])
            ];
            
            $errors = $this->validateStoryInput($input);
            
            if (!empty($errors)) {
                $this->flash('errors', $errors);
                $this->flash('old_input', $input);
                $this->redirect('/story/create');
            }
            
            // Start database transaction
            $this->db->beginTransaction();
            
            try {
                // Create story
                $storyId = $this->db->insert('stories', [
                    'user_id' => $this->currentUser['id'],
                    'category_id' => $input['category_id'],
                    'original_language' => $input['language'],
                    'status' => $input['save_as_draft'] ? 'draft' : 'draft',
                    'access_level' => $input['access_level'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                if (!$storyId) {
                    throw new Exception('Failed to create story');
                }
                
                // Create story content
                $contentId = $this->db->insert('story_contents', [
                    'story_id' => $storyId,
                    'language' => $input['language'],
                    'title' => $input['title'],
                    'content' => $input['content'],
                    'excerpt' => $this->generateExcerpt($input['content']),
                    'meta_description' => $this->generateMetaDescription($input['content']),
                    'status' => 'draft',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                if (!$contentId) {
                    throw new Exception('Failed to create story content');
                }
                
                // Add tags if provided
                if (!empty($input['tags'])) {
                    $this->addTags($storyId, $input['tags']);
                }
                
                $this->db->commit();
                
                // Log activity
                $this->logActivity('story_created', "Created story: {$input['title']}", $this->currentUser['id']);
                
                $this->flash('success', 'Story created successfully!');
                $this->redirect("/story/{$storyId}");
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Story creation error: " . $e->getMessage());
            $this->flash('errors', ['general' => 'Failed to create story. Please try again.']);
            $this->flash('old_input', $input ?? []);
            $this->redirect('/story/create');
        }
    }
    
    /**
     * Show a specific story
     */
    public function show($id) {
        try {
            $id = (int)$id;
            
            // Get story with related data
            $story = $this->db->fetch("
                SELECT s.*, u.username, u.full_name, u.id as author_id,
                       sc.name as category_name, sc.slug as category_slug
                FROM stories s
                LEFT JOIN users u ON s.user_id = u.id
                LEFT JOIN story_categories sc ON s.category_id = sc.id
                WHERE s.id = ?
            ", [$id]);
            
            if (!$story) {
                $this->notFound();
            }
            
            // Check if user can view this story
            if (!$this->canViewStory($story)) {
                $this->forbidden('You do not have permission to view this story.');
            }
            
            // Get story content in preferred language or original language
            $preferredLang = $this->currentUser['language_preference'] ?? $story['original_language'];
            $content = $this->db->fetch("
                SELECT * FROM story_contents 
                WHERE story_id = ? AND language = ? AND status = 'published'
            ", [$id, $preferredLang]);
            
            // Fallback to original language if preferred not available
            if (!$content) {
                $content = $this->db->fetch("
                    SELECT * FROM story_contents 
                    WHERE story_id = ? AND language = ? AND status = 'published'
                ", [$id, $story['original_language']]);
            }
            
            if (!$content) {
                $this->error('Story content not available');
            }
            
            // Get available translations
            $translations = $this->db->fetchAll("
                SELECT language, title FROM story_contents 
                WHERE story_id = ? AND status = 'published'
                ORDER BY language
            ", [$id]);
            
            // Get approved comments
            $comments = $this->db->fetchAll("
                SELECT c.*, u.username, u.full_name
                FROM comments c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.story_id = ? AND c.status = 'approved'
                ORDER BY c.created_at ASC
            ", [$id]);
            
            // Get tags
            $tags = $this->db->fetchAll("
                SELECT t.name FROM tags t
                JOIN story_tags st ON t.id = st.tag_id
                WHERE st.story_id = ?
            ", [$id]);
            
            // Track view if not the author
            if (!$this->currentUser || $this->currentUser['id'] !== $story['user_id']) {
                $this->trackStoryView($id);
            }
            
            $this->view('stories/show', [
                'story' => $story,
                'content' => $content,
                'translations' => $translations,
                'comments' => $comments,
                'tags' => array_column($tags, 'name'),
                'can_edit' => $this->canEditStory($story),
                'can_delete' => $this->canDeleteStory($story),
                'csrf_token' => $this->generateCsrf(),
                'page_title' => $content['title']
            ]);
            
        } catch (Exception $e) {
            error_log("Story show error: " . $e->getMessage());
            $this->error('Failed to load story');
        }
    }
    
    /**
     * Show story edit form
     */
    public function edit($id) {
        $this->requireAuth();
        
        try {
            $id = (int)$id;
            
            // Get story
            $story = $this->db->fetch("
                SELECT s.*, sc.name as category_name
                FROM stories s
                LEFT JOIN story_categories sc ON s.category_id = sc.id
                WHERE s.id = ?
            ", [$id]);
            
            if (!$story) {
                $this->notFound();
            }
            
            // Check authorization
            if (!$this->canEditStory($story)) {
                $this->forbidden('You do not have permission to edit this story.');
            }
            
            // Get story content
            $content = $this->db->fetch("
                SELECT * FROM story_contents 
                WHERE story_id = ? AND language = ?
            ", [$id, $story['original_language']]);
            
            if (!$content) {
                $this->error('Story content not found');
            }
            
            // Get categories
            $categories = $this->db->fetchAll(
                "SELECT * FROM story_categories ORDER BY name"
            );
            
            // Get current tags
            $tags = $this->db->fetchAll("
                SELECT t.name FROM tags t
                JOIN story_tags st ON t.id = st.tag_id
                WHERE st.story_id = ?
            ", [$id]);
            
            $this->view('stories/edit', [
                'story' => $story,
                'content' => $content,
                'categories' => $categories,
                'tags' => implode(', ', array_column($tags, 'name')),
                'access_levels' => $GLOBALS['ACCESS_LEVELS'],
                'csrf_token' => $this->generateCsrf(),
                'errors' => $this->flash('errors'),
                'old_input' => $this->flash('old_input'),
                'page_title' => 'Edit Story: ' . $content['title']
            ]);
            
        } catch (Exception $e) {
            error_log("Story edit error: " . $e->getMessage());
            $this->error('Failed to load story for editing');
        }
    }
    
    /**
     * Update an existing story
     */
    public function update($id) {
        $this->requireAuth();
        
        try {
            $this->validateCsrf();
            $id = (int)$id;
            
            // Get story
            $story = $this->db->fetch("SELECT * FROM stories WHERE id = ?", [$id]);
            
            if (!$story) {
                $this->notFound();
            }
            
            // Check authorization
            if (!$this->canEditStory($story)) {
                $this->forbidden('You do not have permission to edit this story.');
            }
            
            $input = [
                'title' => $this->sanitize($_POST['title'] ?? ''),
                'content' => $_POST['content'] ?? '',
                'category_id' => (int)($_POST['category_id'] ?? 0),
                'access_level' => $this->sanitize($_POST['access_level'] ?? 'public'),
                'tags' => array_filter(array_map('trim', explode(',', $_POST['tags'] ?? ''))),
                'revision_notes' => $this->sanitize($_POST['revision_notes'] ?? ''),
                'save_as_draft' => isset($_POST['save_as_draft']),
                'submit_for_review' => isset($_POST['submit_for_review'])
            ];
            
            $errors = $this->validateStoryInput($input, true);
            
            if (!empty($errors)) {
                $this->flash('errors', $errors);
                $this->flash('old_input', $input);
                $this->redirect("/story/{$id}/edit");
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            try {
                // Update story metadata
                $this->db->execute("
                    UPDATE stories 
                    SET category_id = ?, access_level = ?, updated_at = ?
                    WHERE id = ?
                ", [$input['category_id'], $input['access_level'], date('Y-m-d H:i:s'), $id]);
                
                // Get current content
                $content = $this->db->fetch("
                    SELECT * FROM story_contents 
                    WHERE story_id = ? AND language = ?
                ", [$id, $story['original_language']]);
                
                if ($content) {
                    // Create revision if content changed
                    if ($content['title'] !== $input['title'] || $content['content'] !== $input['content']) {
                        $this->createContentRevision($content['id'], $input['revision_notes']);
                    }
                    
                    // Update content
                    $this->db->execute("
                        UPDATE story_contents 
                        SET title = ?, content = ?, excerpt = ?, meta_description = ?, updated_at = ?
                        WHERE id = ?
                    ", [
                        $input['title'],
                        $input['content'],
                        $this->generateExcerpt($input['content']),
                        $this->generateMetaDescription($input['content']),
                        date('Y-m-d H:i:s'),
                        $content['id']
                    ]);
                }
                
                // Update tags
                $this->updateTags($id, $input['tags']);
                
                // Update story status if requested
                if ($input['submit_for_review'] && $story['status'] === 'draft') {
                    $this->db->execute("
                        UPDATE stories SET status = 'pending_review' WHERE id = ?
                    ", [$id]);
                }
                
                $this->db->commit();
                
                // Log activity
                $this->logActivity('story_updated', "Updated story: {$input['title']}", $this->currentUser['id']);
                
                $this->flash('success', 'Story updated successfully!');
                $this->redirect("/story/{$id}");
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Story update error: " . $e->getMessage());
            $this->flash('errors', ['general' => 'Failed to update story. Please try again.']);
            $this->redirect("/story/{$id}/edit");
        }
    }
    
    /**
     * Delete a story
     */
    public function delete($id) {
        $this->requireAuth();
        
        try {
            $this->validateCsrf();
            $id = (int)$id;
            
            // Get story
            $story = $this->db->fetch("SELECT * FROM stories WHERE id = ?", [$id]);
            
            if (!$story) {
                $this->notFound();
            }
            
            // Check authorization
            if (!$this->canDeleteStory($story)) {
                $this->forbidden('You do not have permission to delete this story.');
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            try {
                // Delete related records
                $this->db->execute("DELETE FROM story_tags WHERE story_id = ?", [$id]);
                $this->db->execute("DELETE FROM comments WHERE story_id = ?", [$id]);
                $this->db->execute("DELETE FROM story_revisions WHERE story_content_id IN (SELECT id FROM story_contents WHERE story_id = ?)", [$id]);
                $this->db->execute("DELETE FROM story_contents WHERE story_id = ?", [$id]);
                $this->db->execute("DELETE FROM stories WHERE id = ?", [$id]);
                
                $this->db->commit();
                
                // Log activity
                $this->logActivity('story_deleted', "Deleted story ID: {$id}", $this->currentUser['id']);
                
                $this->flash('success', 'Story deleted successfully!');
                
                // Redirect based on user role
                if ($this->hasPermission('moderate_stories')) {
                    $this->redirect('/admin/stories');
                } else {
                    $this->redirect('/profile');
                }
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Story deletion error: " . $e->getMessage());
            $this->flash('errors', ['general' => 'Failed to delete story. Please try again.']);
            $this->back();
        }
    }
    
    /**
     * Show stories by category
     */
    public function category($slug) {
        try {
            // Get category
            $category = $this->db->fetch("
                SELECT * FROM story_categories WHERE slug = ?
            ", [$slug]);
            
            if (!$category) {
                $this->notFound();
            }
            
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 12;
            
            // Build query for category stories
            $conditions = ['s.status = ?', 's.category_id = ?'];
            $params = ['published', $category['id']];
            
            // Add access level filtering
            if (!$this->currentUser) {
                $conditions[] = 's.access_level = ?';
                $params[] = 'public';
            } elseif (!$this->hasPermission('view_premium_content')) {
                $conditions[] = 's.access_level IN (?, ?, ?)';
                $params = array_merge($params, ['public', 'registered', 'verified']);
            }
            
            $query = "
                SELECT s.*, u.username, u.full_name, 
                       content.title, content.excerpt, content.language,
                       COUNT(DISTINCT c.id) as comment_count
                FROM stories s
                LEFT JOIN users u ON s.user_id = u.id
                LEFT JOIN story_contents content ON s.id = content.story_id 
                    AND content.status = 'published' 
                    AND content.language = s.original_language
                LEFT JOIN comments c ON s.id = c.story_id AND c.status = 'approved'
                WHERE " . implode(' AND ', $conditions) . "
                GROUP BY s.id
                ORDER BY s.published_at DESC, s.created_at DESC
            ";
            
            $pagination = $this->paginate($query, $params, $page, $perPage);
            
            $this->view('stories/category', [
                'category' => $category,
                'stories' => $pagination['items'],
                'pagination' => $pagination,
                'page_title' => $category['name'] . ' Stories'
            ]);
            
        } catch (Exception $e) {
            error_log("Category stories error: " . $e->getMessage());
            $this->error('Failed to load category stories');
        }
    }
    
    /**
     * Validate story input
     */
    private function validateStoryInput($input, $isUpdate = false) {
        $errors = [];
        
        if (empty($input['title'])) {
            $errors['title'] = 'Title is required.';
        } elseif (strlen($input['title']) < 3) {
            $errors['title'] = 'Title must be at least 3 characters long.';
        } elseif (strlen($input['title']) > 255) {
            $errors['title'] = 'Title must not exceed 255 characters.';
        }
        
        if (empty($input['content'])) {
            $errors['content'] = 'Content is required.';
        } elseif (strlen(strip_tags($input['content'])) < 100) {
            $errors['content'] = 'Content must be at least 100 characters long.';
        }
        
        if (empty($input['category_id'])) {
            $errors['category_id'] = 'Category is required.';
        } else {
            $category = $this->db->fetch("SELECT id FROM story_categories WHERE id = ?", [$input['category_id']]);
            if (!$category) {
                $errors['category_id'] = 'Invalid category selected.';
            }
        }
        
        if (!in_array($input['access_level'], array_keys($GLOBALS['ACCESS_LEVELS']))) {
            $errors['access_level'] = 'Invalid access level selected.';
        }
        
        if (!$isUpdate && !empty($input['language'])) {
            if (!array_key_exists($input['language'], $GLOBALS['SUPPORTED_STORY_LANGUAGES'])) {
                $errors['language'] = 'Invalid language selected.';
            }
        }
        
        return $errors;
    }
    
    /**
     * Check if user can view a story
     */
    private function canViewStory($story) {
        // Authors and moderators can always view
        if ($this->currentUser && ($this->currentUser['id'] === $story['user_id'] || $this->hasPermission('moderate_stories'))) {
            return true;
        }
        
        // Published stories have access level restrictions
        if ($story['status'] === 'published') {
            switch ($story['access_level']) {
                case 'public':
                    return true;
                case 'registered':
                    return $this->currentUser !== null;
                case 'verified':
                    return $this->currentUser && $this->currentUser['email_verified_at'];
                case 'premium':
                    return $this->currentUser && $this->hasPermission('view_premium_content');
            }
        }
        
        return false;
    }
    
    /**
     * Check if user can edit a story
     */
    private function canEditStory($story) {
        if (!$this->currentUser) {
            return false;
        }
        
        // Authors can edit their own stories, moderators can edit any
        return $this->currentUser['id'] === $story['user_id'] || $this->hasPermission('moderate_stories');
    }
    
    /**
     * Check if user can delete a story
     */
    private function canDeleteStory($story) {
        if (!$this->currentUser) {
            return false;
        }
        
        // Authors can delete their own drafts, moderators can delete any
        if ($this->hasPermission('moderate_stories')) {
            return true;
        }
        
        return $this->currentUser['id'] === $story['user_id'] && $story['status'] === 'draft';
    }
    
    /**
     * Generate excerpt from content
     */
    private function generateExcerpt($content, $length = 200) {
        $text = strip_tags($content);
        return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
    }
    
    /**
     * Generate meta description from content
     */
    private function generateMetaDescription($content, $length = 155) {
        $text = strip_tags($content);
        return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
    }
    
    /**
     * Add tags to a story
     */
    private function addTags($storyId, $tags) {
        foreach ($tags as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) continue;
            
            // Find or create tag
            $tag = $this->db->fetch("SELECT id FROM tags WHERE name = ?", [$tagName]);
            if (!$tag) {
                $tagId = $this->db->insert('tags', [
                    'name' => $tagName,
                    'slug' => $this->slugify($tagName),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                $tagId = $tag['id'];
            }
            
            // Link tag to story
            $this->db->execute("
                INSERT IGNORE INTO story_tags (story_id, tag_id) VALUES (?, ?)
            ", [$storyId, $tagId]);
        }
    }
    
    /**
     * Update story tags
     */
    private function updateTags($storyId, $newTags) {
        // Remove existing tags
        $this->db->execute("DELETE FROM story_tags WHERE story_id = ?", [$storyId]);
        
        // Add new tags
        if (!empty($newTags)) {
            $this->addTags($storyId, $newTags);
        }
    }
    
    /**
     * Create content revision
     */
    private function createContentRevision($contentId, $notes = '') {
        $content = $this->db->fetch("SELECT * FROM story_contents WHERE id = ?", [$contentId]);
        if ($content) {
            $this->db->insert('story_revisions', [
                'story_content_id' => $contentId,
                'editor_id' => $this->currentUser['id'],
                'title' => $content['title'],
                'content' => $content['content'],
                'excerpt' => $content['excerpt'],
                'meta_description' => $content['meta_description'],
                'revision_notes' => $notes,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    /**
     * Track story view
     */
    private function trackStoryView($storyId) {
        // Simple view tracking - could be enhanced with more sophisticated logic
        $this->db->execute("
            INSERT INTO story_statistics (story_id, views, last_viewed_at)
            VALUES (?, 1, ?)
            ON DUPLICATE KEY UPDATE 
                views = views + 1, 
                last_viewed_at = VALUES(last_viewed_at)
        ", [$storyId, date('Y-m-d H:i:s')]);
    }
    
    /**
     * Create URL-friendly slug
     */
    private function slugify($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return strtolower($text);
    }
}
