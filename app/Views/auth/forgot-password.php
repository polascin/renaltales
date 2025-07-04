<?php if (!defined('ROOT_PATH')) exit; ?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1><?= $this->language->get('forgot_password_title', 'Reset Your Password') ?></h1>
            <p><?= $this->language->get('forgot_password_subtitle', 'Enter your email address and we\'ll send you a link to reset your password.') ?></p>
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

        <form method="POST" action="/forgot-password" class="auth-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            
            <div class="form-group">
                <label for="email"><?= $this->language->get('email_label', 'Email Address') ?></label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control <?= isset($errors['email']) ? 'error' : '' ?>"
                    placeholder="<?= $this->language->get('email_placeholder', 'Enter your email address') ?>"
                    required
                    autocomplete="email"
                    aria-describedby="<?= isset($errors['email']) ? 'email-error' : '' ?>"
                >
                <?php if (isset($errors['email'])): ?>
                    <span id="email-error" class="error-message"><?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <?= $this->language->get('send_reset_link_button', 'Send Reset Link') ?>
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
    line-height: 1.5;
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

.alert-success {
    background-color: #efe;
    border: 1px solid #cfc;
    color: #3c3;
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
// Auto-focus email field
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('email').focus();
});
</script>
