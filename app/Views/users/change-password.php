<?php if (!defined('ROOT_PATH')) exit; ?>

<div class="change-password-container">
    <div class="change-password-header">
        <h1>Change Password</h1>
        <p>Update your account password for better security</p>
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

    <?php if (isset($success) && $success): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="change-password-form-container">
        <form method="POST" action="/profile/change-password" class="change-password-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            
            <div class="security-notice">
                <div class="notice-icon">🔒</div>
                <div class="notice-content">
                    <h3>Security Guidelines</h3>
                    <ul>
                        <li>Use at least <?= $GLOBALS['CONFIG']['security']['password_min_length'] ?> characters</li>
                        <li>Include uppercase and lowercase letters</li>
                        <li>Add numbers and special characters</li>
                        <li>Avoid using personal information</li>
                        <li>Don't reuse old passwords</li>
                    </ul>
                </div>
            </div>

            <div class="form-group">
                <label for="current_password">Current Password *</label>
                <div class="password-input-container">
                    <input 
                        type="password" 
                        id="current_password" 
                        name="current_password" 
                        class="form-control <?= isset($errors['current_password']) ? 'error' : '' ?>"
                        required
                        placeholder="Enter your current password"
                        aria-describedby="<?= isset($errors['current_password']) ? 'current-password-error' : '' ?>"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                        <span class="show-text">Show</span>
                        <span class="hide-text" style="display: none;">Hide</span>
                    </button>
                </div>
                <?php if (isset($errors['current_password'])): ?>
                    <span id="current-password-error" class="error-message"><?= htmlspecialchars($errors['current_password']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="new_password">New Password *</label>
                <div class="password-input-container">
                    <input 
                        type="password" 
                        id="new_password" 
                        name="new_password" 
                        class="form-control <?= isset($errors['new_password']) ? 'error' : '' ?>"
                        required
                        minlength="<?= $GLOBALS['CONFIG']['security']['password_min_length'] ?>"
                        placeholder="Enter your new password"
                        aria-describedby="<?= isset($errors['new_password']) ? 'new-password-error' : 'password-strength' ?>"
                        oninput="checkPasswordStrength(this.value)"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                        <span class="show-text">Show</span>
                        <span class="hide-text" style="display: none;">Hide</span>
                    </button>
                </div>
                <?php if (isset($errors['new_password'])): ?>
                    <span id="new-password-error" class="error-message"><?= htmlspecialchars($errors['new_password']) ?></span>
                <?php endif; ?>
                <div id="password-strength" class="password-strength">
                    <div class="strength-bar">
                        <div class="strength-fill"></div>
                    </div>
                    <span class="strength-text">Password strength: <span class="strength-level">Not entered</span></span>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password *</label>
                <div class="password-input-container">
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="form-control <?= isset($errors['confirm_password']) ? 'error' : '' ?>"
                        required
                        placeholder="Confirm your new password"
                        aria-describedby="<?= isset($errors['confirm_password']) ? 'confirm-password-error' : 'confirm-password-help' ?>"
                        oninput="checkPasswordMatch()"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                        <span class="show-text">Show</span>
                        <span class="hide-text" style="display: none;">Hide</span>
                    </button>
                </div>
                <?php if (isset($errors['confirm_password'])): ?>
                    <span id="confirm-password-error" class="error-message"><?= htmlspecialchars($errors['confirm_password']) ?></span>
                <?php else: ?>
                    <span id="confirm-password-help" class="help-text">Re-enter your new password to confirm</span>
                <?php endif; ?>
                <div id="password-match" class="password-match" style="display: none;">
                    <span class="match-text"></span>
                </div>
            </div>

            <div class="form-actions">
                <a href="/profile" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" id="submit-btn" disabled>Change Password</button>
            </div>
        </form>

        <div class="security-tips">
            <h3>Security Tips</h3>
            <div class="tips-grid">
                <div class="tip-item">
                    <div class="tip-icon">🔄</div>
                    <div class="tip-content">
                        <h4>Regular Updates</h4>
                        <p>Change your password every 3-6 months for optimal security.</p>
                    </div>
                </div>
                <div class="tip-item">
                    <div class="tip-icon">🔒</div>
                    <div class="tip-content">
                        <h4>Unique Passwords</h4>
                        <p>Use different passwords for different accounts and services.</p>
                    </div>
                </div>
                <div class="tip-item">
                    <div class="tip-icon">🛡️</div>
                    <div class="tip-content">
                        <h4>Two-Factor Auth</h4>
                        <p>Enable 2FA for an additional layer of account protection.</p>
                    </div>
                </div>
                <div class="tip-item">
                    <div class="tip-icon">🔐</div>
                    <div class="tip-content">
                        <h4>Password Manager</h4>
                        <p>Consider using a password manager for secure storage.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.change-password-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 2rem;
}

.change-password-header {
    text-align: center;
    margin-bottom: 2rem;
}

.change-password-header h1 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.change-password-header p {
    color: #7f8c8d;
    margin: 0;
}

.change-password-form-container {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.security-notice {
    display: flex;
    gap: 1rem;
    background: #e8f4fd;
    border: 1px solid #bee5eb;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.notice-icon {
    font-size: 2rem;
    flex-shrink: 0;
}

.notice-content h3 {
    color: #2c3e50;
    margin: 0 0 1rem 0;
    font-size: 1.1rem;
}

.notice-content ul {
    margin: 0;
    padding-left: 1.5rem;
    color: #495057;
}

.notice-content li {
    margin-bottom: 0.5rem;
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

.password-strength {
    margin-top: 0.5rem;
}

.strength-bar {
    width: 100%;
    height: 6px;
    background: #ecf0f1;
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.strength-fill {
    height: 100%;
    width: 0%;
    transition: width 0.3s ease, background-color 0.3s ease;
    border-radius: 3px;
}

.strength-text {
    font-size: 0.875rem;
    color: #7f8c8d;
}

.strength-level {
    font-weight: 500;
}

.password-match {
    margin-top: 0.5rem;
    font-size: 0.875rem;
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

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #ecf0f1;
}

.security-tips {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #ecf0f1;
}

.security-tips h3 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
    text-align: center;
}

.tips-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.tip-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.tip-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.tip-content h4 {
    color: #2c3e50;
    margin: 0 0 0.5rem 0;
    font-size: 0.95rem;
}

.tip-content p {
    color: #6c757d;
    margin: 0;
    font-size: 0.875rem;
    line-height: 1.4;
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

.alert-success {
    background: #f0f9ff;
    border: 1px solid #bfdbfe;
    color: #1e40af;
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

.btn-primary:hover:not(:disabled) {
    background: #2980b9;
}

.btn-primary:disabled {
    background: #bdc3c7;
    cursor: not-allowed;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

/* Strength level colors */
.strength-weak .strength-fill {
    background: #e74c3c;
    width: 25%;
}

.strength-fair .strength-fill {
    background: #f39c12;
    width: 50%;
}

.strength-good .strength-fill {
    background: #f1c40f;
    width: 75%;
}

.strength-strong .strength-fill {
    background: #27ae60;
    width: 100%;
}

.match-success .match-text {
    color: #27ae60;
}

.match-error .match-text {
    color: #e74c3c;
}

@media (max-width: 768px) {
    .change-password-container {
        padding: 1rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
    }
    
    .tips-grid {
        grid-template-columns: 1fr;
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

function checkPasswordStrength(password) {
    const strengthContainer = document.getElementById('password-strength');
    const strengthLevel = strengthContainer.querySelector('.strength-level');
    
    // Remove all strength classes
    strengthContainer.classList.remove('strength-weak', 'strength-fair', 'strength-good', 'strength-strong');
    
    if (!password) {
        strengthLevel.textContent = 'Not entered';
        checkFormValidity();
        return;
    }
    
    let strength = 0;
    let feedback = [];
    
    // Length check
    if (password.length >= <?= $GLOBALS['CONFIG']['security']['password_min_length'] ?>) {
        strength += 1;
    } else {
        feedback.push('too short');
    }
    
    // Character variety checks
    if (/[a-z]/.test(password)) strength += 1;
    if (/[A-Z]/.test(password)) strength += 1;
    if (/[0-9]/.test(password)) strength += 1;
    if (/[^A-Za-z0-9]/.test(password)) strength += 1;
    
    // Determine strength level
    let levelText = '';
    let levelClass = '';
    
    if (strength <= 2) {
        levelText = 'Weak';
        levelClass = 'strength-weak';
    } else if (strength === 3) {
        levelText = 'Fair';
        levelClass = 'strength-fair';
    } else if (strength === 4) {
        levelText = 'Good';
        levelClass = 'strength-good';
    } else if (strength >= 5) {
        levelText = 'Strong';
        levelClass = 'strength-strong';
    }
    
    strengthLevel.textContent = levelText;
    strengthContainer.classList.add(levelClass);
    
    checkPasswordMatch();
    checkFormValidity();
}

function checkPasswordMatch() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const matchContainer = document.getElementById('password-match');
    const matchText = matchContainer.querySelector('.match-text');
    
    if (!confirmPassword) {
        matchContainer.style.display = 'none';
        checkFormValidity();
        return;
    }
    
    matchContainer.style.display = 'block';
    matchContainer.classList.remove('match-success', 'match-error');
    
    if (newPassword === confirmPassword) {
        matchText.textContent = '✓ Passwords match';
        matchContainer.classList.add('match-success');
    } else {
        matchText.textContent = '✗ Passwords do not match';
        matchContainer.classList.add('match-error');
    }
    
    checkFormValidity();
}

function checkFormValidity() {
    const currentPassword = document.getElementById('current_password').value;
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const submitBtn = document.getElementById('submit-btn');
    
    const isValid = currentPassword && 
                   newPassword && 
                   confirmPassword && 
                   newPassword === confirmPassword &&
                   newPassword.length >= <?= $GLOBALS['CONFIG']['security']['password_min_length'] ?>;
    
    submitBtn.disabled = !isValid;
}

// Add event listeners
document.addEventListener('DOMContentLoaded', function() {
    const currentPassword = document.getElementById('current_password');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    currentPassword.addEventListener('input', checkFormValidity);
    newPassword.addEventListener('input', function() {
        checkPasswordStrength(this.value);
    });
    confirmPassword.addEventListener('input', checkPasswordMatch);
    
    // Form submission validation
    document.querySelector('.change-password-form').addEventListener('submit', function(e) {
        const newPasswordValue = newPassword.value;
        const confirmPasswordValue = confirmPassword.value;
        
        if (newPasswordValue !== confirmPasswordValue) {
            alert('Passwords do not match.');
            e.preventDefault();
            return;
        }
        
        if (newPasswordValue.length < <?= $GLOBALS['CONFIG']['security']['password_min_length'] ?>) {
            alert('Password must be at least <?= $GLOBALS['CONFIG']['security']['password_min_length'] ?> characters long.');
            e.preventDefault();
            return;
        }
    });
});
</script>
