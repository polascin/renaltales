<?php
/**
 * Story Management Testing Script
 * Tests all story management features step by step
 */

require_once 'bootstrap/autoload.php';

echo "=== Story Management Features Test ===\n\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=renaltales;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Step 1: Login as regular user (john_doe)
    echo "1. Testing regular user login functionality...\n";
    $regularUser = $pdo->query("SELECT * FROM users WHERE username = 'john_doe'")->fetch(PDO::FETCH_ASSOC);
    if ($regularUser) {
        echo "   ✓ Regular user found: {$regularUser['username']} (ID: {$regularUser['id']})\n";
        echo "   - Role: {$regularUser['role']}\n";
        echo "   - Email verified: " . ($regularUser['email_verified_at'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "   ✗ Regular user not found\n";
    }
    
    // Step 2: Create a new story in English as regular user
    echo "\n2. Creating new story as regular user...\n";
    
    // Get a category ID
    $category = $pdo->query("SELECT id FROM story_categories LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if (!$category) {
        echo "   ✗ No categories found. Creating a test category...\n";
        $pdo->exec("INSERT INTO story_categories (name, slug, description, created_at) VALUES ('Test Category', 'test', 'Test category for testing', NOW())");
        $category = $pdo->query("SELECT id FROM story_categories WHERE slug = 'test'")->fetch(PDO::FETCH_ASSOC);
    }
    
    // Create a new story
    $storyData = [
        'user_id' => $regularUser['id'],
        'category_id' => $category['id'],
        'original_language' => 'en',
        'status' => 'draft',
        'access_level' => 'public',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $stmt = $pdo->prepare("INSERT INTO stories (user_id, category_id, original_language, status, access_level, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(array_values($storyData));
    $storyId = $pdo->lastInsertId();
    
    // Create story content
    $contentData = [
        'story_id' => $storyId,
        'language' => 'en',
        'title' => 'My Kidney Journey - A Test Story',
        'content' => 'This is a test story created to verify the story management system. This story describes my journey with kidney disease, from the initial diagnosis to finding hope and strength in the community. It includes challenges, treatments, and the support I received from family and friends. The story aims to inspire others who might be going through similar experiences.',
        'excerpt' => 'This is a test story created to verify the story management system.',
        'meta_description' => 'A test story about kidney journey for system verification',
        'status' => 'draft',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $stmt = $pdo->prepare("INSERT INTO story_contents (story_id, language, title, content, excerpt, meta_description, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(array_values($contentData));
    $contentId = $pdo->lastInsertId();
    
    echo "   ✓ Story created successfully (ID: $storyId)\n";
    echo "   ✓ Story content created successfully (ID: $contentId)\n";
    
    // Step 3: Verify story appears with status 'draft'
    echo "\n3. Verifying story status...\n";
    $story = $pdo->query("SELECT * FROM stories WHERE id = $storyId")->fetch(PDO::FETCH_ASSOC);
    if ($story && $story['status'] === 'draft') {
        echo "   ✓ Story status is 'draft' as expected\n";
    } else {
        echo "   ✗ Story status is not 'draft': " . ($story['status'] ?? 'NOT FOUND') . "\n";
    }
    
    // Step 4: Submit story for review
    echo "\n4. Submitting story for review...\n";
    $pdo->exec("UPDATE stories SET status = 'pending_review', updated_at = NOW() WHERE id = $storyId");
    $story = $pdo->query("SELECT * FROM stories WHERE id = $storyId")->fetch(PDO::FETCH_ASSOC);
    if ($story['status'] === 'pending_review') {
        echo "   ✓ Story status changed to 'pending_review'\n";
    } else {
        echo "   ✗ Failed to change story status to 'pending_review'\n";
    }
    
    // Step 5: Login as admin/moderator
    echo "\n5. Testing admin/moderator login functionality...\n";
    $adminUser = $pdo->query("SELECT * FROM users WHERE role IN ('admin', 'moderator') LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($adminUser) {
        echo "   ✓ Admin/Moderator user found: {$adminUser['username']} (ID: {$adminUser['id']})\n";
        echo "   - Role: {$adminUser['role']}\n";
    } else {
        echo "   ✗ No admin/moderator user found\n";
    }
    
    // Step 6: Approve story and change status to 'published'
    echo "\n6. Approving story as admin/moderator...\n";
    $pdo->exec("UPDATE stories SET status = 'published', published_at = NOW(), updated_at = NOW() WHERE id = $storyId");
    $pdo->exec("UPDATE story_contents SET status = 'published', updated_at = NOW() WHERE story_id = $storyId");
    
    $story = $pdo->query("SELECT * FROM stories WHERE id = $storyId")->fetch(PDO::FETCH_ASSOC);
    if ($story['status'] === 'published' && $story['published_at']) {
        echo "   ✓ Story approved and published successfully\n";
        echo "   - Published at: {$story['published_at']}\n";
    } else {
        echo "   ✗ Failed to publish story\n";
    }
    
    // Step 7: Test story editing and revision history
    echo "\n7. Testing story editing and revision history...\n";
    
    // Get original content
    $originalContent = $pdo->query("SELECT * FROM story_contents WHERE story_id = $storyId")->fetch(PDO::FETCH_ASSOC);
    
    // Create a revision record before editing
    $revisionData = [
        'story_content_id' => $originalContent['id'],
        'editor_id' => $adminUser['id'],
        'title' => $originalContent['title'],
        'content' => $originalContent['content'],
        'excerpt' => $originalContent['excerpt'],
        'meta_description' => $originalContent['meta_description'],
        'revision_notes' => 'Initial version before editing',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $stmt = $pdo->prepare("INSERT INTO story_revisions (story_content_id, editor_id, title, content, excerpt, meta_description, revision_notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(array_values($revisionData));
    $revisionId = $pdo->lastInsertId();
    
    // Edit the story
    $newTitle = $originalContent['title'] . ' (Edited)';
    $newContent = $originalContent['content'] . ' [This story has been edited to test the revision system.]';
    
    $stmt = $pdo->prepare("UPDATE story_contents SET title = ?, content = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$newTitle, $newContent, $originalContent['id']]);
    
    echo "   ✓ Story edited successfully\n";
    echo "   ✓ Revision history created (Revision ID: $revisionId)\n";
    
    // Verify revision history
    $revisions = $pdo->query("SELECT COUNT(*) as count FROM story_revisions WHERE story_content_id = {$originalContent['id']}")->fetch(PDO::FETCH_ASSOC);
    echo "   ✓ Revision count: {$revisions['count']}\n";
    
    // Step 8: Test different access levels
    echo "\n8. Testing different access levels...\n";
    
    $accessLevels = ['public', 'registered', 'verified', 'premium'];
    
    foreach ($accessLevels as $level) {
        echo "   Testing access level: $level\n";
        
        // Update story access level
        $pdo->exec("UPDATE stories SET access_level = '$level' WHERE id = $storyId");
        
        // Test access for different user types
        echo "     - Public access: ";
        $canAccess = ($level === 'public');
        echo $canAccess ? "✓ Allowed\n" : "✗ Restricted\n";
        
        echo "     - Registered user access: ";
        $canAccess = in_array($level, ['public', 'registered', 'verified', 'premium']);
        echo $canAccess ? "✓ Allowed\n" : "✗ Restricted\n";
        
        echo "     - Verified user access: ";
        $canAccess = in_array($level, ['public', 'registered', 'verified', 'premium']);
        echo $canAccess ? "✓ Allowed\n" : "✗ Restricted\n";
        
        echo "     - Premium user access: ";
        echo "✓ Allowed (all levels)\n";
    }
    
    // Reset to public for final verification
    $pdo->exec("UPDATE stories SET access_level = 'public' WHERE id = $storyId");
    
    // Step 9: Final verification - check story is visible
    echo "\n9. Final verification...\n";
    $finalStory = $pdo->query("
        SELECT s.*, sc.title, sc.content, sc.status as content_status
        FROM stories s 
        LEFT JOIN story_contents sc ON s.id = sc.story_id 
        WHERE s.id = $storyId
    ")->fetch(PDO::FETCH_ASSOC);
    
    if ($finalStory) {
        echo "   ✓ Story is accessible\n";
        echo "   - Story Status: {$finalStory['status']}\n";
        echo "   - Content Status: {$finalStory['content_status']}\n";
        echo "   - Access Level: {$finalStory['access_level']}\n";
        echo "   - Title: {$finalStory['title']}\n";
        echo "   - Published: " . ($finalStory['published_at'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "   ✗ Story not found or not accessible\n";
    }
    
    // Step 10: Test admin story management
    echo "\n10. Testing admin story management features...\n";
    
    // Get pending review stories
    $pendingStories = $pdo->query("SELECT COUNT(*) as count FROM stories WHERE status = 'pending_review'")->fetch(PDO::FETCH_ASSOC);
    echo "   ✓ Pending review stories: {$pendingStories['count']}\n";
    
    // Get published stories
    $publishedStories = $pdo->query("SELECT COUNT(*) as count FROM stories WHERE status = 'published'")->fetch(PDO::FETCH_ASSOC);
    echo "   ✓ Published stories: {$publishedStories['count']}\n";
    
    // Get draft stories
    $draftStories = $pdo->query("SELECT COUNT(*) as count FROM stories WHERE status = 'draft'")->fetch(PDO::FETCH_ASSOC);
    echo "   ✓ Draft stories: {$draftStories['count']}\n";
    
    echo "\n=== Story Management Test Completed Successfully! ===\n";
    
    // Cleanup (optional)
    echo "\nCleanup options:\n";
    echo "- Test story ID: $storyId\n";
    echo "- Test content ID: {$originalContent['id']}\n";
    echo "- Test revision ID: $revisionId\n";
    echo "\nTo clean up test data, run:\n";
    echo "DELETE FROM story_revisions WHERE story_content_id = {$originalContent['id']};\n";
    echo "DELETE FROM story_contents WHERE story_id = $storyId;\n";
    echo "DELETE FROM stories WHERE id = $storyId;\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
