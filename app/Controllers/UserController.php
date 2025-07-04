<?php
/**
 * User Controller
 * Handles user profile management, settings, and profile-related operations
 */

require_once APP_PATH . '/Core/Controller.php';

class UserController extends Controller {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Show user profile page
     */
    public function profile() {
        $this->requireAuth();
        
        $user = $this->currentUser;
        
        // Get user's latest activity
        $recentActivity = $this->db->fetchAll(
            "SELECT action, description, created_at 
             FROM activity_logs 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT 10",
            [$user['id']]
        );
        
        // Get user's story count
        $storyCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM stories WHERE user_id = ?",
            [$user['id']]
        )['count'];
        
        $this->view('users/profile', [
            'user' => $user,
            'recent_activity' => $recentActivity,
            'story_count' => $storyCount,
            'csrf_token' => $this->generateCsrf(),
            'errors' => $this->flash('errors'),
            'success' => $this->flash('success'),
            'supported_languages' => $GLOBALS['SUPPORTED_STORY_LANGUAGES']
        ]);
    }
    
    /**
     * Show profile edit form
     */
    public function edit() {
        $this->requireAuth();
        
        $user = $this->currentUser;
        
        $this->view('users/edit', [
            'user' => $user,
            'csrf_token' => $this->generateCsrf(),
            'errors' => $this->flash('errors'),
            'old_input' => $this->flash('old_input'),
            'supported_languages' => $GLOBALS['SUPPORTED_STORY_LANGUAGES']
        ]);
    }
    
    /**
     * Update user profile
     */
    public function updateProfile() {
        $this->requireAuth();
        
        try {
            $this->validateCsrf();
            
            $input = [
                'username' => $this->sanitize($_POST['username'] ?? ''),
                'email' => $this->sanitize($_POST['email'] ?? ''),
                'full_name' => $this->sanitize($_POST['full_name'] ?? ''),
                'language_preference' => $this->sanitize($_POST['language_preference'] ?? 'en'),
                'bio' => $this->sanitize($_POST['bio'] ?? ''),
                'current_password' => $_POST['current_password'] ?? ''
            ];
            
            // Validate input
            $errors = $this->validateProfileUpdate($input);
            
            if (!empty($errors)) {
                $this->flash('errors', $errors);
                $this->flash('old_input', [
                    'username' => $input['username'],
                    'email' => $input['email'],
                    'full_name' => $input['full_name'],
                    'language_preference' => $input['language_preference'],
                    'bio' => $input['bio']
                ]);
                $this->redirect('/profile/edit');
            }
            
            // Check if email or username changed (requires current password)
            $emailChanged = $input['email'] !== $this->currentUser['email'];
            $usernameChanged = $input['username'] !== $this->currentUser['username'];
            
            if ($emailChanged || $usernameChanged) {
                if (empty($input['current_password'])) {
                    $this->flash('errors', ['current_password' => 'Current password is required to change email or username.']);
                    $this->flash('old_input', [
                        'username' => $input['username'],
                        'email' => $input['email'],
                        'full_name' => $input['full_name'],
                        'language_preference' => $input['language_preference'],
                        'bio' => $input['bio']
                    ]);
                    $this->redirect('/profile/edit');
                }
                
                // Verify current password
                if (!password_verify($input['current_password'], $this->currentUser['password_hash'])) {
                    $this->flash('errors', ['current_password' => 'Current password is incorrect.']);
                    $this->flash('old_input', [
                        'username' => $input['username'],
                        'email' => $input['email'],
                        'full_name' => $input['full_name'],
                        'language_preference' => $input['language_preference'],
                        'bio' => $input['bio']
                    ]);
                    $this->redirect('/profile/edit');
                }
            }
            
            // Check if username/email is taken by another user
            if ($usernameChanged) {
                $existingUser = $this->db->fetch(
                    "SELECT id FROM users WHERE username = ? AND id != ?",
                    [$input['username'], $this->currentUser['id']]
                );
                if ($existingUser) {
                    $this->flash('errors', ['username' => 'Username is already taken.']);
                    $this->flash('old_input', [
                        'username' => $input['username'],
                        'email' => $input['email'],
                        'full_name' => $input['full_name'],
                        'language_preference' => $input['language_preference'],
                        'bio' => $input['bio']
                    ]);
                    $this->redirect('/profile/edit');
                }
            }
            
            if ($emailChanged) {
                $existingUser = $this->db->fetch(
                    "SELECT id FROM users WHERE email = ? AND id != ?",
                    [$input['email'], $this->currentUser['id']]
                );
                if ($existingUser) {
                    $this->flash('errors', ['email' => 'Email address is already registered.']);
                    $this->flash('old_input', [
                        'username' => $input['username'],
                        'email' => $input['email'],
                        'full_name' => $input['full_name'],
                        'language_preference' => $input['language_preference'],
                        'bio' => $input['bio']
                    ]);
                    $this->redirect('/profile/edit');
                }
            }
            
            // Prepare update data
            $updateData = [
                'username' => $input['username'],
                'email' => $input['email'],
                'full_name' => $input['full_name'],
                'language_preference' => $input['language_preference'],
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Add bio if it exists in users table
            $tableColumns = $this->db->fetchAll("DESCRIBE users");
            $hasSpecialColumn = false;
            foreach ($tableColumns as $column) {
                if ($column['Field'] === 'bio') {
                    $updateData['bio'] = $input['bio'];
                    $hasSpecialColumn = true;
                    break;
                }
            }
            
            // Update user profile
            $this->db->update('users', $updateData, ['id' => $this->currentUser['id']]);
            
            // If email changed, mark as unverified and send verification email
            if ($emailChanged) {
                $this->db->execute(
                    "UPDATE users SET email_verified_at = NULL WHERE id = ?",
                    [$this->currentUser['id']]
                );
                
                $this->sendVerificationEmail($this->currentUser['id'], $input['email']);
                $this->flash('success', 'Profile updated successfully! Please check your email to verify your new email address.');
            } else {
                $this->flash('success', 'Profile updated successfully!');
            }
            
            // Update session username if changed
            if ($usernameChanged) {
                $_SESSION['username'] = $input['username'];
            }
            
            // Log profile update
            $this->logActivity('profile_update', 'User updated their profile');
            
            $this->redirect('/profile');
            
        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            $this->flash('errors', ['general' => 'An error occurred while updating your profile. Please try again.']);
            $this->redirect('/profile/edit');
        }
    }
    
    /**
     * Show change password form
     */
    public function showChangePassword() {
        $this->requireAuth();
        
        $this->view('users/change-password', [
            'csrf_token' => $this->generateCsrf(),
            'errors' => $this->flash('errors'),
            'success' => $this->flash('success')
        ]);
    }
    
    /**
     * Handle password change
     */
    public function changePassword() {
        $this->requireAuth();
        
        try {
            $this->validateCsrf();
            
            $input = [
                'current_password' => $_POST['current_password'] ?? '',
                'new_password' => $_POST['new_password'] ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? ''
            ];
            
            // Validate input
            $errors = $this->validatePasswordChange($input);
            
            if (!empty($errors)) {
                $this->flash('errors', $errors);
                $this->redirect('/profile/change-password');
            }
            
            // Verify current password
            if (!password_verify($input['current_password'], $this->currentUser['password_hash'])) {
                $this->flash('errors', ['current_password' => 'Current password is incorrect.']);
                $this->redirect('/profile/change-password');
            }
            
            // Hash new password
            $hashedPassword = password_hash($input['new_password'], PASSWORD_ARGON2ID);
            
            // Update password
            $this->db->execute(
                "UPDATE users SET password_hash = ?, last_password_change = NOW(), updated_at = NOW() WHERE id = ?",
                [$hashedPassword, $this->currentUser['id']]
            );
            
            // Invalidate all other sessions for security
            $this->db->execute(
                "DELETE FROM user_sessions WHERE user_id = ? AND session_token != ?",
                [$this->currentUser['id'], session_id()]
            );
            
            // Log password change
            $this->logActivity('password_change', 'User changed their password');
            
            $this->flash('success', 'Password changed successfully! All other sessions have been terminated for security.');
            $this->redirect('/profile');
            
        } catch (Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            $this->flash('errors', ['general' => 'An error occurred while changing your password. Please try again.']);
            $this->redirect('/profile/change-password');
        }
    }
    
    /**
     * Update language preference via AJAX
     */
    public function setLanguage() {
        $this->requireAuth();
        
        try {
            $this->validateCsrf();
            
            $language = $this->sanitize($_POST['language'] ?? '');
            
            if (!array_key_exists($language, $GLOBALS['SUPPORTED_STORY_LANGUAGES'])) {
                $this->json(['success' => false, 'message' => 'Invalid language selected.'], 400);
            }
            
            // Update user's language preference
            $this->db->execute(
                "UPDATE users SET language_preference = ?, updated_at = NOW() WHERE id = ?",
                [$language, $this->currentUser['id']]
            );
            
            // Update session language
            $_SESSION['language'] = $language;
            
            // Log language change
            $this->logActivity('language_change', "User changed language preference to {$language}");
            
            $this->json([
                'success' => true, 
                'message' => 'Language preference updated successfully.',
                'language' => $language
            ]);
            
        } catch (Exception $e) {
            error_log("Language change error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'An error occurred while updating language preference.'], 500);
        }
    }
    
    /**
     * Show list of users (public profiles)
     */
    public function index() {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = $GLOBALS['CONFIG']['pagination']['users_per_page'];
        $search = $this->sanitize($_GET['search'] ?? '');
        
        $query = "SELECT id, username, full_name, role, created_at, last_login_at 
                  FROM users 
                  WHERE role != 'banned'";
        $params = [];
        
        if (!empty($search)) {
            $query .= " AND (username LIKE ? OR full_name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = [$searchTerm, $searchTerm];
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $pagination = $this->paginate($query, $params, $page, $perPage);
        
        $this->view('users/index', [
            'users' => $pagination['items'],
            'pagination' => $pagination,
            'search' => $search
        ]);
    }
    
    /**
     * Show individual user profile (public view)
     */
    public function show($id) {
        $user = $this->db->fetch(
            "SELECT id, username, full_name, role, created_at, last_login_at 
             FROM users 
             WHERE id = ? AND role != 'banned'",
            [$id]
        );
        
        if (!$user) {
            $this->notFound();
        }
        
        // Get user's public stories
        $stories = $this->db->fetchAll(
            "SELECT s.id, sc.title, s.created_at, s.status, cat.name as category_name
             FROM stories s
             JOIN story_contents sc ON s.id = sc.story_id 
             JOIN story_categories cat ON s.category_id = cat.id
             WHERE s.user_id = ? AND s.status = 'published' AND s.access_level IN ('public', 'registered')
             ORDER BY s.created_at DESC
             LIMIT 10",
            [$id]
        );
        
        $storyCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM stories WHERE user_id = ? AND status = 'published'",
            [$id]
        )['count'];
        
        $this->view('users/show', [
            'user' => $user,
            'stories' => $stories,
            'story_count' => $storyCount,
            'is_own_profile' => $this->currentUser && $this->currentUser['id'] == $user['id']
        ]);
    }
    
    /**
     * Delete user account (with confirmation)
     */
    public function deleteAccount() {
        $this->requireAuth();
        
        try {
            $this->validateCsrf();
            
            $password = $_POST['password'] ?? '';
            $confirmation = $_POST['confirmation'] ?? '';
            
            if ($confirmation !== 'DELETE') {
                $this->flash('errors', ['confirmation' => 'Please type "DELETE" to confirm account deletion.']);
                $this->redirect('/profile');
            }
            
            if (!password_verify($password, $this->currentUser['password_hash'])) {
                $this->flash('errors', ['password' => 'Password is incorrect.']);
                $this->redirect('/profile');
            }
            
            $userId = $this->currentUser['id'];
            
            // Log account deletion
            $this->logActivity('account_deletion', 'User deleted their account', $userId);
            
            // Delete user account (cascading deletes will handle related data)
            $this->db->execute("DELETE FROM users WHERE id = ?", [$userId]);
            
            // Destroy session
            $this->destroyUserSession();
            
            $this->flash('success', 'Your account has been successfully deleted.');
            $this->redirect('/');
            
        } catch (Exception $e) {
            error_log("Account deletion error: " . $e->getMessage());
            $this->flash('errors', ['general' => 'An error occurred while deleting your account. Please try again.']);
            $this->redirect('/profile');
        }
    }
    
    /**
     * Validate profile update input
     */
    private function validateProfileUpdate($input) {
        $errors = [];
        
        // Username validation
        if (empty($input['username'])) {
            $errors['username'] = 'Username is required.';
        } elseif (strlen($input['username']) < 3) {
            $errors['username'] = 'Username must be at least 3 characters long.';
        } elseif (strlen($input['username']) > 50) {
            $errors['username'] = 'Username must not exceed 50 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $input['username'])) {
            $errors['username'] = 'Username can only contain letters, numbers, and underscores.';
        }
        
        // Email validation
        if (empty($input['email'])) {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        
        // Full name validation
        if (!empty($input['full_name']) && strlen($input['full_name']) > 100) {
            $errors['full_name'] = 'Full name must not exceed 100 characters.';
        }
        
        // Language preference validation
        if (!array_key_exists($input['language_preference'], $GLOBALS['SUPPORTED_STORY_LANGUAGES'])) {
            $errors['language_preference'] = 'Invalid language preference.';
        }
        
        // Bio validation (if provided)
        if (!empty($input['bio']) && strlen($input['bio']) > 500) {
            $errors['bio'] = 'Bio must not exceed 500 characters.';
        }
        
        return $errors;
    }
    
    /**
     * Validate password change input
     */
    private function validatePasswordChange($input) {
        $errors = [];
        
        $minLength = $GLOBALS['CONFIG']['security']['password_min_length'];
        
        if (empty($input['current_password'])) {
            $errors['current_password'] = 'Current password is required.';
        }
        
        if (empty($input['new_password'])) {
            $errors['new_password'] = 'New password is required.';
        } elseif (strlen($input['new_password']) < $minLength) {
            $errors['new_password'] = "New password must be at least {$minLength} characters long.";
        }
        
        if (empty($input['confirm_password'])) {
            $errors['confirm_password'] = 'Password confirmation is required.';
        } elseif ($input['new_password'] !== $input['confirm_password']) {
            $errors['confirm_password'] = 'Password confirmation does not match.';
        }
        
        // Check if new password is same as current
        if (!empty($input['current_password']) && !empty($input['new_password']) && 
            $input['current_password'] === $input['new_password']) {
            $errors['new_password'] = 'New password must be different from current password.';
        }
        
        return $errors;
    }
    
    /**
     * Send email verification email
     */
    private function sendVerificationEmail($userId, $email) {
        $token = bin2hex(random_bytes(32));
        
        // Clean up old tokens
        $this->db->execute("DELETE FROM email_verification_tokens WHERE user_id = ?", [$userId]);
        
        // Store verification token
        $this->db->insert('email_verification_tokens', [
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', time() + 3600) // 1 hour
        ]);
        
        // In a real application, you would send an actual email here
        // For now, we'll just log it
        error_log("Email verification would be sent to {$email} with token: {$token}");
    }
    
    /**
     * Destroy user session (from AuthController for consistency)
     */
    private function destroyUserSession() {
        // Clear remember me cookie and token
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
            
            if ($this->currentUser) {
                $this->db->execute(
                    "UPDATE users SET remember_token = NULL WHERE id = ?",
                    [$this->currentUser['id']]
                );
            }
        }
        
        // Clear session data
        session_unset();
        session_destroy();
        
        // Start new session
        session_start();
        session_regenerate_id(true);
    }
}
