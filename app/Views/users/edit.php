<?php if (!defined('ROOT_PATH')) exit; ?>

<div class="edit-profile-container">
    <div class="edit-profile-header">
        <h1>Edit Profile</h1>
        <p>Update your personal information and preferences</p>
    </div>

    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/profile/update" class="edit-profile-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        
        <div class="form-sections">
            <!-- Basic Information Section -->
            <div class="form-section">
                <h2>Basic Information</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            value="<?= htmlspecialchars($old_input['username'] ?? $user['username']) ?>"
                            class="form-control <?= isset($errors['username']) ? 'error' : '' ?>"
                            required
                            minlength="3"
                            maxlength="50"
                            pattern="[a-zA-Z0-9_]+"
                            aria-describedby="<?= isset($errors['username']) ? 'username-error' : 'username-help' ?>"
                        >
                        <?php if (isset($errors['username'])): ?>
                            <span id="username-error" class="error-message"><?= htmlspecialchars($errors['username']) ?></span>
                        <?php else: ?>
                            <span id="username-help" class="help-text">3-50 characters, letters, numbers, and underscores only</span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?= htmlspecialchars($old_input['email'] ?? $user['email']) ?>"
                            class="form-control <?= isset($errors['email']) ? 'error' : '' ?>"
                            required
                            aria-describedby="<?= isset($errors['email']) ? 'email-error' : 'email-help' ?>"
                        >
                        <?php if (isset($errors['email'])): ?>
                            <span id="email-error" class="error-message"><?= htmlspecialchars($errors['email']) ?></span>
                        <?php else: ?>
                            <span id="email-help" class="help-text">Your email address for notifications and password recovery</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input 
                        type="text" 
                        id="full_name" 
                        name="full_name" 
                        value="<?= htmlspecialchars($old_input['full_name'] ?? $user['full_name']) ?>"
                        class="form-control <?= isset($errors['full_name']) ? 'error' : '' ?>"
                        maxlength="100"
                        aria-describedby="<?= isset($errors['full_name']) ? 'full_name-error' : 'full_name-help' ?>"
                    >
                    <?php if (isset($errors['full_name'])): ?>
                        <span id="full_name-error" class="error-message"><?= htmlspecialchars($errors['full_name']) ?></span>
                    <?php else: ?>
                        <span id="full_name-help" class="help-text">Optional - your display name on the platform</span>
                    <?php endif; ?>
                </div>

                <?php 
                // Check if bio field exists in the user data or old input
                $showBio = isset($old_input['bio']) || (isset($user['bio']) && array_key_exists('bio', $user));
                ?>
                <?php if ($showBio): ?>
                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea 
                        id="bio" 
                        name="bio" 
                        rows="4"
                        class="form-control <?= isset($errors['bio']) ? 'error' : '' ?>"
                        maxlength="500"
                        aria-describedby="<?= isset($errors['bio']) ? 'bio-error' : 'bio-help' ?>"
                        placeholder="Tell us a bit about yourself..."
                    ><?= htmlspecialchars($old_input['bio'] ?? $user['bio'] ?? '') ?></textarea>
                    <?php if (isset($errors['bio'])): ?>
                        <span id="bio-error" class="error-message"><?= htmlspecialchars($errors['bio']) ?></span>
                    <?php else: ?>
                        <span id="bio-help" class="help-text">Optional - a short description about yourself (max 500 characters)</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Preferences Section -->
            <div class="form-section">
                <h2>Preferences</h2>
                
                <div class="form-group">
                    <label for="language_preference">Preferred Language *</label>
                    <select 
                        id="language_preference" 
                        name="language_preference" 
                        class="form-control <?= isset($errors['language_preference']) ? 'error' : '' ?>"
                        required
                        aria-describedby="<?= isset($errors['language_preference']) ? 'language-error' : 'language-help' ?>"
                    >
                        <?php foreach ($supported_languages as $code => $name): ?>
                            <option value="<?= $code ?>" 
                                <?= ($old_input['language_preference'] ?? $user['language_preference']) === $code ? 'selected' : '' ?>>
                                <?= htmlspecialchars($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['language_preference'])): ?>
                        <span id="language-error" class="error-message"><?= htmlspecialchars($errors['language_preference']) ?></span>
                    <?php else: ?>
                        <span id="language-help" class="help-text">Your preferred language for the interface and content</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Security Section -->
            <div class="form-section security-section">
                <h2>Security Verification</h2>
                <p class="security-notice">
                    <strong>Note:</strong> You'll need to enter your current password if you're changing your username or email address for security purposes.
                </p>
                
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <div class="password-input-container">
                        <input 
                            type="password" 
                            id="current_password" 
                            name="current_password" 
                            class="form-control <?= isset($errors['current_password']) ? 'error' : '' ?>"
                            placeholder="Enter your current password"
                            aria-describedby="<?= isset($errors['current_password']) ? 'current-password-error' : 'current-password-help' ?>"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                            <span class="show-text">Show</span>
                            <span class="hide-text" style="display: none;">Hide</span>
                        </button>
                    </div>
                    <?php if (isset($errors['current_password'])): ?>
                        <span id="current-password-error" class="error-message"><?= htmlspecialchars($errors['current_password']) ?></span>
                    <?php else: ?>
                        <span id="current-password-help" class="help-text">Required only when changing username or email</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="history.back()">Cancel</button>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </div>
    </form>
</div>

<style>
.edit-profile-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
}

.edit-profile-header {
    text-align: center;
    margin-bottom: 2rem;
}

.edit-profile-header h1 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.edit-profile-header p {
    color: #7f8c8d;
    margin: 0;
}

.edit-profile-form {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-sections {
    margin-bottom: 2rem;
}

.form-section {
    margin-bottom: 2.5rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #ecf0f1;
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.form-section h2 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
    font-size: 1.25rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #2c3e50;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e1e8ed;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-control.error {
    border-color: #e74c3c;
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

.password-input-container {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #7f8c8d;
    cursor: pointer;
    font-size: 0.875rem;
}

.password-toggle:hover {
    color: #3498db;
}

.error-message {
    display: block;
    color: #e74c3c;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.help-text {
    display: block;
    color: #7f8c8d;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.security-section {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.security-notice {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    color: #856404;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding-top: 1.5rem;
    border-top: 1px solid #ecf0f1;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.alert-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
}

.alert ul {
    margin: 0;
    padding-left: 1.5rem;
}

.alert li {
    margin-bottom: 0.25rem;
}

.alert li:last-child {
    margin-bottom: 0;
}

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    font-weight: 500;
    text-align: center;
    text-decoration: none;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

@media (max-width: 768px) {
    .edit-profile-container {
        padding: 1rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
    }
}
</style>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggle = field.nextElementSibling;
    const showText = toggle.querySelector('.show-text');
    const hideText = toggle.querySelector('.hide-text');
    
    if (field.type === 'password') {
        field.type = 'text';
        showText.style.display = 'none';
        hideText.style.display = 'inline';
    } else {
        field.type = 'password';
        showText.style.display = 'inline';
        hideText.style.display = 'none';
    }
}

// Show/hide current password requirement based on username/email changes
document.addEventListener('DOMContentLoaded', function() {
    const originalUsername = '<?= htmlspecialchars($user['username']) ?>';
    const originalEmail = '<?= htmlspecialchars($user['email']) ?>';
    const usernameField = document.getElementById('username');
    const emailField = document.getElementById('email');
    const passwordField = document.getElementById('current_password');
    const securitySection = document.querySelector('.security-section');
    
    function checkSecurityRequirement() {
        const usernameChanged = usernameField.value !== originalUsername;
        const emailChanged = emailField.value !== originalEmail;
        
        if (usernameChanged || emailChanged) {
            securitySection.style.display = 'block';
            passwordField.setAttribute('required', 'required');
        } else {
            securitySection.style.display = 'none';
            passwordField.removeAttribute('required');
            passwordField.value = '';
        }
    }
    
    usernameField.addEventListener('input', checkSecurityRequirement);
    emailField.addEventListener('input', checkSecurityRequirement);
    
    // Initial check
    checkSecurityRequirement();
});

// Form validation
document.querySelector('.edit-profile-form').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    
    // Username validation
    if (username.length < 3) {
        alert('Username must be at least 3 characters long.');
        e.preventDefault();
        return;
    }
    
    if (!/^[a-zA-Z0-9_]+$/.test(username)) {
        alert('Username can only contain letters, numbers, and underscores.');
        e.preventDefault();
        return;
    }
    
    // Email validation
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert('Please enter a valid email address.');
        e.preventDefault();
        return;
    }
});
</script>
