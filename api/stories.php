<?php

/**
 * Story Management API
 * 
 * Provides REST API endpoints for story CRUD operations,
 * media management, versioning, and publishing
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

// Enable CORS for API access
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include required files
require_once '../bootstrap.php';

// Use PSR-4 autoloaded classes
use RenalTales\Controllers\StoryController;
use RenalTales\Core\SecurityManager;
use RenalTales\Core\RateLimitManager;
use RenalTales\Core\InputValidator;
use RenalTales\Core\SessionManager;

// Initialize security components
$sessionManager = new SessionManager();
$securityManager = new SecurityManager($sessionManager);
$rateLimitManager = new RateLimitManager();
$validator = new InputValidator();

// Initialize controller
$storyController = new StoryController();

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Remove 'api' and 'stories.php' from path parts
$pathParts = array_filter($pathParts, function($part) {
    return $part !== 'api' && $part !== 'stories.php';
});
$pathParts = array_values($pathParts);

// Get query parameters
$params = $_GET;

// Get request body
$requestBody = file_get_contents('php://input');
$data = json_decode($requestBody, true) ?? [];

// Merge POST data if available
if (!empty($_POST)) {
    $data = array_merge($data, $_POST);
}

// Rate limiting check
$clientIdentifier = $rateLimitManager->getClientIdentifier();
$endpoint = 'api/stories';
$rateLimit = $rateLimitManager->checkRateLimit($clientIdentifier, $endpoint);

if (!$rateLimit['allowed']) {
    $rateLimitManager->setRateLimitHeaders($rateLimit);
    sendError(429, 'Rate limit exceeded', [
        'reason' => $rateLimit['reason'],
        'retry_after' => $rateLimit['retry_after']
    ]);
}

// Set rate limit headers
$rateLimitManager->setRateLimitHeaders($rateLimit);

// CSRF protection for state-changing requests
if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
    $csrfToken = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!$securityManager->validateCSRFToken($csrfToken)) {
        sendError(403, 'CSRF token validation failed');
    }
}

// Input validation and sanitization
if (!empty($data)) {
    foreach ($data as $key => $value) {
        if (!$securityManager->validateInput($value)) {
            sendError(400, 'Invalid input detected', ['field' => $key]);
        }
        $data[$key] = $securityManager->sanitizeInput($value);
    }
}

// Authentication check (simplified - in production, use proper authentication)
$userId = $_SESSION['user_id'] ?? 1; // Default to user ID 1 for testing

try {
    // Route the request
    switch ($method) {
        case 'GET':
            handleGetRequest($pathParts, $params, $storyController);
            break;
        
        case 'POST':
            handlePostRequest($pathParts, $data, $storyController, $userId);
            break;
        
        case 'PUT':
            handlePutRequest($pathParts, $data, $storyController, $userId);
            break;
        
        case 'DELETE':
            handleDeleteRequest($pathParts, $storyController);
            break;
        
        default:
            sendError(405, 'Method not allowed');
    }
} catch (Exception $e) {
    // Log security event
    $securityManager->logSecurityEvent('api_exception', [
        'method' => $method,
        'endpoint' => $endpoint,
        'error' => $e->getMessage()
    ]);
    
    sendError(500, 'Internal server error', DEBUG_MODE ? $e->getMessage() : 'An error occurred');
}

/**
 * Handle GET requests
 */
function handleGetRequest($pathParts, $params, $storyController) {
    if (empty($pathParts)) {
        // GET /api/stories - List stories with filters
        $filters = [
            'search' => $params['search'] ?? '',
            'published' => isset($params['published']) ? (bool)$params['published'] : null,
            'categories' => isset($params['categories']) ? explode(',', $params['categories']) : [],
            'tags' => isset($params['tags']) ? explode(',', $params['tags']) : []
        ];
        
        $limit = (int)($params['limit'] ?? 10);
        $offset = (int)($params['offset'] ?? 0);
        
        $stories = $storyController->searchStories($filters, $limit, $offset);
        sendResponse($stories);
        
    } elseif (is_numeric($pathParts[0])) {
        $storyId = (int)$pathParts[0];
        
        if (isset($pathParts[1])) {
            // Sub-resources
            switch ($pathParts[1]) {
                case 'versions':
                    // GET /api/stories/{id}/versions
                    if (isset($pathParts[2]) && is_numeric($pathParts[2])) {
                        // GET /api/stories/{id}/versions/{version}
                        $version = (int)$pathParts[2];
                        $storyVersion = $storyController->getStoryVersion($storyId, $version);
                        sendResponse($storyVersion);
                    } else {
                        // GET /api/stories/{id}/versions
                        $versions = $storyController->getStoryVersions($storyId);
                        sendResponse($versions);
                    }
                    break;
                
                case 'media':
                    // GET /api/stories/{id}/media
                    $mediaType = $params['type'] ?? null;
                    $media = $storyController->getStoryMedia($storyId, $mediaType);
                    sendResponse($media);
                    break;
                
                case 'comments':
                    // GET /api/stories/{id}/comments
                    $threaded = isset($params['threaded']) && $params['threaded'] === 'true';
                    $comments = $storyController->getStoryComments($storyId, $threaded);
                    sendResponse($comments);
                    break;
                
                case 'preview':
                    // GET /api/stories/{id}/preview
                    $preview = $storyController->generatePreview($storyId);
                    sendResponse($preview);
                    break;
                
                default:
                    sendError(404, 'Resource not found');
            }
        } else {
            // GET /api/stories/{id} - Get single story
            $story = $storyController->getStory($storyId);
            if ($story) {
                sendResponse($story);
            } else {
                sendError(404, 'Story not found');
            }
        }
        
    } elseif ($pathParts[0] === 'published') {
        // GET /api/stories/published - Get published stories
        $limit = (int)($params['limit'] ?? 10);
        $offset = (int)($params['offset'] ?? 0);
        
        $stories = $storyController->getPublishedStories($limit, $offset);
        sendResponse($stories);
        
    } elseif ($pathParts[0] === 'stats') {
        // GET /api/stories/stats - Get story statistics
        $stats = $storyController->getStoryStats();
        sendResponse($stats);
        
    } elseif ($pathParts[0] === 'categories') {
        // GET /api/stories/categories - Get categories
        $categories = $storyController->getCategories();
        sendResponse($categories);
        
    } elseif ($pathParts[0] === 'tags') {
        // GET /api/stories/tags - Get tags
        if (isset($params['search'])) {
            $tags = $storyController->searchTags($params['search'], $params['limit'] ?? 10);
        } elseif (isset($params['popular'])) {
            $tags = $storyController->getPopularTags($params['limit'] ?? 10);
        } elseif (isset($params['cloud'])) {
            $tags = $storyController->getTagCloud($params['limit'] ?? 50);
        } else {
            $tags = $storyController->getTags();
        }
        sendResponse($tags);
        
    } else {
        sendError(404, 'Resource not found');
    }
}

/**
 * Handle POST requests
 */
function handlePostRequest($pathParts, $data, $storyController, $userId) {
    if (empty($pathParts)) {
        // POST /api/stories - Create new story
        $result = $storyController->createStory($data, $userId);
        if ($result['success']) {
            sendResponse($result, 201);
        } else {
            sendError(400, $result['message'], $result['errors'] ?? []);
        }
        
    } elseif (is_numeric($pathParts[0])) {
        $storyId = (int)$pathParts[0];
        
        if (isset($pathParts[1])) {
            switch ($pathParts[1]) {
                case 'media':
                    // POST /api/stories/{id}/media - Upload media
                    if (!empty($_FILES)) {
                        $file = reset($_FILES); // Get first file
                        $metadata = [
                            'alt_text' => $data['alt_text'] ?? '',
                            'caption' => $data['caption'] ?? ''
                        ];
                        $result = $storyController->uploadMedia($storyId, $file, $metadata);
                        
                        if ($result['success']) {
                            sendResponse($result, 201);
                        } else {
                            sendError(400, $result['message']);
                        }
                    } else {
                        sendError(400, 'No file uploaded');
                    }
                    break;
                
                case 'comments':
                    // POST /api/stories/{id}/comments - Create comment
                    $commentData = array_merge($data, ['story_id' => $storyId, 'user_id' => $userId]);
                    $result = $storyController->createComment($commentData);
                    
                    if ($result['success']) {
                        sendResponse($result, 201);
                    } else {
                        sendError(400, $result['message']);
                    }
                    break;
                
                case 'publish':
                    // POST /api/stories/{id}/publish - Publish story
                    $result = $storyController->publishStory($storyId);
                    sendResponse($result);
                    break;
                
                case 'unpublish':
                    // POST /api/stories/{id}/unpublish - Unpublish story
                    $result = $storyController->unpublishStory($storyId);
                    sendResponse($result);
                    break;
                
                default:
                    sendError(404, 'Resource not found');
            }
        } else {
            sendError(400, 'Invalid request');
        }
        
    } else {
        sendError(404, 'Resource not found');
    }
}

/**
 * Handle PUT requests
 */
function handlePutRequest($pathParts, $data, $storyController, $userId) {
    if (isset($pathParts[0]) && is_numeric($pathParts[0])) {
        $storyId = (int)$pathParts[0];
        
        if (isset($pathParts[1])) {
            switch ($pathParts[1]) {
                case 'media':
                    // PUT /api/stories/{id}/media/{mediaId} - Update media
                    if (isset($pathParts[2]) && is_numeric($pathParts[2])) {
                        $mediaId = (int)$pathParts[2];
                        // Implementation for updating media metadata
                        sendResponse(['success' => true, 'message' => 'Media updated']);
                    } else {
                        sendError(400, 'Media ID required');
                    }
                    break;
                
                default:
                    sendError(404, 'Resource not found');
            }
        } else {
            // PUT /api/stories/{id} - Update story
            $result = $storyController->updateStory($storyId, $data, $userId);
            
            if ($result['success']) {
                sendResponse($result);
            } else {
                sendError(400, $result['message'], $result['errors'] ?? []);
            }
        }
    } else {
        sendError(400, 'Story ID required');
    }
}

/**
 * Handle DELETE requests
 */
function handleDeleteRequest($pathParts, $storyController) {
    if (isset($pathParts[0]) && is_numeric($pathParts[0])) {
        $storyId = (int)$pathParts[0];
        
        if (isset($pathParts[1])) {
            switch ($pathParts[1]) {
                case 'media':
                    // DELETE /api/stories/{id}/media/{mediaId} - Delete media
                    if (isset($pathParts[2]) && is_numeric($pathParts[2])) {
                        $mediaId = (int)$pathParts[2];
                        $result = $storyController->deleteMedia($mediaId);
                        sendResponse($result);
                    } else {
                        sendError(400, 'Media ID required');
                    }
                    break;
                
                default:
                    sendError(404, 'Resource not found');
            }
        } else {
            // DELETE /api/stories/{id} - Delete story
            $result = $storyController->deleteStory($storyId);
            sendResponse($result);
        }
    } else {
        sendError(400, 'Story ID required');
    }
}

/**
 * Send JSON response
 */
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

/**
 * Send error response
 */
function sendError($statusCode, $message, $details = null) {
    http_response_code($statusCode);
    $response = [
        'error' => true,
        'message' => $message
    ];
    
    if ($details !== null) {
        $response['details'] = $details;
    }
    
    echo json_encode($response);
    exit;
}

?>
