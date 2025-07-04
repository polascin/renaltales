<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap/autoload.php';

use RenalTales\Model\User;
use RenalTales\Model\Story;

// Example 1: Creating a new user with validation and transaction safety
try {
    $userData = [
        'username' => 'johndoe',
        'email' => 'john@example.com',
        'password' => 'securepassword123',
        'full_name' => 'John Doe',
        'language_preference' => 'en'
    ];
    
    $user = User::createUser($userData);
    echo "User created successfully with ID: " . $user->id . "\n";
    
} catch (Exception $e) {
    echo "Error creating user: " . $e->getMessage() . "\n";
}

// Example 2: Finding users with validation
try {
    // Find by email with validation
    $user = User::findByEmail('john@example.com');
    if ($user) {
        echo "Found user: " . $user->username . "\n";
    }
    
    // Find by username with validation
    $user = User::findByUsername('johndoe');
    if ($user) {
        echo "User role: " . $user->role . "\n";
    }
    
} catch (InvalidArgumentException $e) {
    echo "Validation error: " . $e->getMessage() . "\n";
}

// Example 3: Creating a story with relationships
try {
    if ($user) {
        $storyData = [
            'user_id' => $user->id,
            'category_id' => 1, // Assuming category exists
            'original_language' => 'en',
            'access_level' => 'public'
        ];
        
        $story = Story::createStory($storyData);
        
        // Add content to the story
        $story->addTranslation('en', 'My First Story', 'This is the content of my first story...');
        
        echo "Story created successfully with ID: " . $story->id . "\n";
    }
} catch (Exception $e) {
    echo "Error creating story: " . $e->getMessage() . "\n";
}

// Example 4: Using eager loading to get user with their stories
try {
    $userWithStories = User::find($user->id ?? 1, ['stories', 'translations']);
    
    if ($userWithStories) {
        $stories = $userWithStories->getStories();
        echo "User has " . count($stories) . " stories\n";
        
        foreach ($stories as $story) {
            echo "- Story ID: " . $story->id . " Status: " . $story->status . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error loading user with stories: " . $e->getMessage() . "\n";
}

// Example 5: Transaction-safe operations
try {
    if ($user) {
        // Update user profile with transaction safety
        $updateSuccess = $user->updateProfile([
            'full_name' => 'John Smith',
            'language_preference' => 'sk'
        ]);
        
        if ($updateSuccess) {
            echo "User profile updated successfully\n";
        }
        
        // Change password with validation
        $passwordChanged = $user->changePassword('securepassword123', 'newsecurepassword456');
        
        if ($passwordChanged) {
            echo "Password changed successfully\n";
        }
    }
} catch (Exception $e) {
    echo "Error updating user: " . $e->getMessage() . "\n";
}

// Example 6: Working with story permissions and operations
try {
    if ($story && $user) {
        // Check permissions
        if ($story->canBeEditedBy($user)) {
            echo "User can edit this story\n";
        }
        
        if ($story->canBeViewedBy($user)) {
            echo "User can view this story\n";
        }
        
        // Publish story with transaction safety
        if ($story->publish()) {
            echo "Story published successfully\n";
        }
        
        // Get word count and reading time
        $wordCount = $story->getWordCount();
        $readingTime = $story->getReadingTime();
        
        echo "Story has {$wordCount} words, estimated reading time: {$readingTime} minutes\n";
    }
} catch (Exception $e) {
    echo "Error working with story: " . $e->getMessage() . "\n";
}

// Example 7: Querying with helper methods
try {
    // Get all published stories
    $publishedStories = Story::getPublishedStories(['author', 'category'], 10);
    echo "Found " . count($publishedStories) . " published stories\n";
    
    // Get user's draft stories
    if ($user) {
        $draftStories = Story::getDraftStories($user->id, ['category']);
        echo "User has " . count($draftStories) . " draft stories\n";
    }
    
    // Get stories owned by user
    if ($user) {
        $ownedStories = Story::ownedBy($user->id, ['category']);
        echo "User owns " . count($ownedStories) . " stories\n";
    }
    
} catch (Exception $e) {
    echo "Error querying stories: " . $e->getMessage() . "\n";
}

// Example 8: Working with permissions and roles
try {
    if ($user) {
        // Check various permissions
        if ($user->hasPermission('create_stories')) {
            echo "User can create stories\n";
        }
        
        if ($user->canTranslate()) {
            echo "User can translate stories\n";
        }
        
        if ($user->canModerate()) {
            echo "User can moderate content\n";
        }
        
        if ($user->isActive()) {
            echo "User is active\n";
        }
        
        // Enable 2FA with transaction safety
        if ($user->enable2FA('secret_key_here')) {
            echo "2FA enabled successfully\n";
        }
    }
} catch (Exception $e) {
    echo "Error working with user permissions: " . $e->getMessage() . "\n";
}

echo "\nExample completed!\n";
