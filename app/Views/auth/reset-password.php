<?php if (!defined('ROOT_PATH')) exit; ?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1><?= $this->language->get('reset_password_title', 'Set New Password') ?></h1>
            <p><?= $this->language->get('reset_password_subtitle', 'Enter your new password below.') ?></p>
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

        <form method="POST" action="/reset-password" class="auth-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            
            <div class="form-group">
                <label for="password"><?= $this->language->get('new_password_label', 'New Password') ?></label>
                <div class="password-input-container">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control <?= isset($errors['password']) ? 'error' : '' ?>"
                        placeholder="<?= $this->language->get('password_placeholder', 'Enter your new password') ?>"
                        required
                        autocomplete="new-password"
                        minlength="8"
                        aria-describedby="<?= isset($errors['password']) ? 'password-error' : 'password-help' ?>"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <span class="show-text"><?= $this->language->get('show', 'Show') ?></span>
                        <span class="hide-text" style="display: none;"><?= $this->language->get('hide', 'Hide') ?></span>
                    </button>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <span id="password-error" class="error-message"><?= htmlspecialchars($errors['password']) ?></span>
                <?php else: ?>
                    <span id="password-help" class="help-text"><?= $this->language->get('password_help', 'At least 8 characters with a mix of letters, numbers, and symbols.') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password_confirmation"><?= $this->language->get('confirm_new_password_label', 'Confirm New Password') ?></label>
                <div class="password-input-container">
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        class="form-control <?= isset($errors['password_confirmation']) ? 'error' : '' ?>"
                        placeholder="<?= $this->language->get('password_confirmation_placeholder', 'Re-enter your new password') ?>"
                        required
                        autocomplete="new-password"
                        aria-describedby="<?= isset($errors['password_confirmation']) ? 'password_confirmation-error' : '' ?>"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                        <span class="show-text"><?= $this->language->get('show', 'Show') ?></span>
                        <span class="hide-text" style="display: none;"><?= $this->language->get('hide', 'Hide') ?></span>
                    </button>
                </div>
                <?php if (isset($errors['password_confirmation'])): ?>
                    <span id="password_confirmation-error" class="error-message"><?= htmlspecialchars($errors['password_confirmation']) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <?= $this->language->get('reset_password_button', 'Reset Password') ?>
            </button>
        </form>

        <div class="auth-footer">
            <p>
                <?= $this->language->get('remember_password', 'Remember your password?') ?>
                <a href="/login"><?= $this->language->get('back_to_login', 'Back to login') ?></a>
            </p>
        </div>
    </div>
</div>

<style>
.auth-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 70vh;
    padding: 2rem;
}

.auth-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    width: 100%;
    max-width: 400px;
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-header h1 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 1.75rem;
}

.auth-header p {
    color: #7f8c8d;
    margin: 0;
}

.auth-form .form-group {
    margin-bottom: 1.5rem;
}

.auth-form label {
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

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 500;
    text-decoration: none;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: #3498db;
    color: white;
}

.btn-primary:hover {
    background-color: #2980b9;
    transform: translateY(-1px);
}

.btn-block {
    width: 100%;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.alert-error {
    background-color: #fee;
    border: 1px solid #fcc;
    color: #c33;
}

.alert ul {
    margin: 0;
    padding-left: 1rem;
}

.error-message {
    color: #e74c3c;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block;
}

.help-text {
    color: #7f8c8d;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block;
}

.auth-footer {
    text-align: center;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #ecf0f1;
}

.auth-footer a {
    color: #3498db;
    text-decoration: none;
    font-weight: 500;
}

.auth-footer a:hover {
    text-decoration: underline;
}
</style>

<script>
function togglePassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const toggleButton = passwordInput.nextElementSibling;
    const showText = toggleButton.querySelector('.show-text');
    const hideText = toggleButton.querySelector('.hide-text');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        showText.style.display = 'none';
        hideText.style.display = 'inline';
    } else {
        passwordInput.type = 'password';
        showText.style.display = 'inline';
        hideText.style.display = 'none';
    }
}

// Password confirmation matching
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');
    
    function checkPasswordMatch() {
        if (passwordConfirmation.value && password.value !== passwordConfirmation.value) {
            passwordConfirmation.setCustomValidity('Passwords do not match');
        } else {
            passwordConfirmation.setCustomValidity('');
        }
    }
    
    password.addEventListener('input', checkPasswordMatch);
    passwordConfirmation.addEventListener('input', checkPasswordMatch);
    
    // Auto-focus first field
    password.focus();
});
</script>
