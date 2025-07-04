<?php if (!defined('ROOT_PATH')) exit; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h1 class="h3 mb-3 fw-normal">
                            <?= $t['auth.login.title'] ?? 'Login to RenalTales' ?>
                        </h1>
                        <p class="text-muted">
                            <?= $t['auth.login.subtitle'] ?? 'Welcome back! Please enter your credentials.' ?>
                        </p>
                    </div>

                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
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

                    <form method="POST" action="<?= Router::url('login') ?>" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <?= $t['auth.login.email'] ?? 'Email Address' ?>
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="<?= htmlspecialchars($old_input['email'] ?? '') ?>"
                                class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                placeholder="<?= $t['auth.login.email_placeholder'] ?? 'Enter your email address' ?>"
                                required
                                autocomplete="email"
                            >
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['email']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <?= $t['auth.login.password'] ?? 'Password' ?>
                            </label>
                            <div class="input-group">
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                    placeholder="<?= $t['auth.login.password_placeholder'] ?? 'Enter your password' ?>"
                                    required
                                    autocomplete="current-password"
                                >
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="fas fa-eye" id="password-toggle-icon"></i>
                                </button>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['password']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input type="checkbox" id="remember_me" name="remember_me" class="form-check-input">
                                <label class="form-check-label" for="remember_me">
                                    <?= $t['auth.login.remember'] ?? 'Remember me' ?>
                                </label>
                            </div>
                            <a href="<?= Router::url('forgot-password') ?>" class="text-decoration-none">
                                <?= $t['auth.login.forgot'] ?? 'Forgot password?' ?>
                            </a>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-kidney btn-lg">
                                <i class="fas fa-sign-in-alt"></i> <?= $t['auth.login.button'] ?? 'Sign In' ?>
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="mb-0">
                            <?= $t['auth.login.no_account'] ?? "Don't have an account?" ?>
                            <a href="<?= Router::url('register') ?>" class="fw-bold text-decoration-none">
                                <?= $t['auth.login.register_link'] ?? 'Create one here' ?>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = document.getElementById(inputId + '-toggle-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'fas fa-eye';
    }
}

// Auto-focus first empty field
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    
    if (!emailInput.value) {
        emailInput.focus();
    } else {
        passwordInput.focus();
    }
});
</script>
