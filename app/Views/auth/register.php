<?php if (!defined('ROOT_PATH')) exit; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h1 class="h3 mb-3 fw-normal">
                            <?= $t['auth.register.title'] ?? 'Join RenalTales' ?>
                        </h1>
                        <p class="text-muted">
                            <?= $t['auth.register.subtitle'] ?? 'Join our community and share your story.' ?>
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

                    <form method="POST" action="<?= Router::url('register') ?>" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">
                                    <?= $t['auth.register.username'] ?? 'Username' ?>
                                </label>
                                <input 
                                    type="text" 
                                    id="username" 
                                    name="username" 
                                    value="<?= htmlspecialchars($old_input['username'] ?? '') ?>"
                                    class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                                    placeholder="<?= $t['auth.register.username_placeholder'] ?? 'Choose a unique username' ?>"
                                    required
                                    autocomplete="username"
                                    pattern="[a-zA-Z0-9_]+"
                                    minlength="3"
                                    maxlength="50"
                                >
                                <?php if (isset($errors['username'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['username']) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="form-text">
                                        <?= $t['auth.register.username_help'] ?? 'Only letters, numbers, and underscores. 3-50 characters.' ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <?= $t['auth.register.email'] ?? 'Email Address' ?>
                                </label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    value="<?= htmlspecialchars($old_input['email'] ?? '') ?>"
                                    class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                    placeholder="<?= $t['auth.register.email_placeholder'] ?? 'Enter your email address' ?>"
                                    required
                                    autocomplete="email"
                                >
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['email']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="full_name" class="form-label">
                                <?= $t['auth.register.name'] ?? 'Full Name' ?> 
                                <span class="text-muted">(<?= $t['auth.register.optional'] ?? 'optional' ?>)</span>
                            </label>
                            <input 
                                type="text" 
                                id="full_name" 
                                name="full_name" 
                                value="<?= htmlspecialchars($old_input['full_name'] ?? '') ?>"
                                class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
                                placeholder="<?= $t['auth.register.name_placeholder'] ?? 'Your full name' ?>"
                                autocomplete="name"
                                maxlength="100"
                            >
                            <?php if (isset($errors['full_name'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['full_name']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="language_preference" class="form-label">
                                <?= $t['auth.register.language'] ?? 'Preferred Language' ?>
                            </label>
                            <select 
                                id="language_preference" 
                                name="language_preference" 
                                class="form-select <?= isset($errors['language_preference']) ? 'is-invalid' : '' ?>"
                                required
                            >
                                <?php if (isset($supported_languages)): ?>
                                    <?php foreach ($supported_languages as $code => $name): ?>
                                        <option value="<?= htmlspecialchars($code) ?>" <?= ($old_input['language_preference'] ?? 'sk') === $code ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="sk" selected>Slovenčina</option>
                                    <option value="en">English</option>
                                <?php endif; ?>
                            </select>
                            <?php if (isset($errors['language_preference'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['language_preference']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    <?= $t['auth.register.password'] ?? 'Password' ?>
                                </label>
                                <div class="input-group">
                                    <input 
                                        type="password" 
                                        id="password" 
                                        name="password" 
                                        class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                        placeholder="<?= $t['auth.register.password_placeholder'] ?? 'Create a secure password' ?>"
                                        required
                                        autocomplete="new-password"
                                        minlength="8"
                                    >
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="password-toggle-icon"></i>
                                    </button>
                                </div>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['password']) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="form-text">
                                        <?= $t['auth.register.password_help'] ?? 'At least 8 characters with a mix of letters, numbers, and symbols.' ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">
                                    <?= $t['auth.register.confirm'] ?? 'Confirm Password' ?>
                                </label>
                                <div class="input-group">
                                    <input 
                                        type="password" 
                                        id="password_confirmation" 
                                        name="password_confirmation" 
                                        class="form-control <?= isset($errors['password_confirmation']) ? 'is-invalid' : '' ?>"
                                        placeholder="<?= $t['auth.register.confirm_placeholder'] ?? 'Re-enter your password' ?>"
                                        required
                                        autocomplete="new-password"
                                    >
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                        <i class="fas fa-eye" id="password_confirmation-toggle-icon"></i>
                                    </button>
                                </div>
                                <?php if (isset($errors['password_confirmation'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['password_confirmation']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input 
                                    type="checkbox" 
                                    id="agree_terms" 
                                    name="agree_terms" 
                                    class="form-check-input <?= isset($errors['agree_terms']) ? 'is-invalid' : '' ?>"
                                    required
                                >
                                <label class="form-check-label" for="agree_terms">
                                    <?= $t['auth.register.agree_start'] ?? 'I agree to the' ?>
                                    <a href="<?= Router::url('terms') ?>" target="_blank" class="text-decoration-none">
                                        <?= $t['auth.register.terms'] ?? 'Terms and Conditions' ?>
                                    </a>
                                    <?= $t['auth.register.and'] ?? 'and' ?>
                                    <a href="<?= Router::url('privacy') ?>" target="_blank" class="text-decoration-none">
                                        <?= $t['auth.register.privacy'] ?? 'Privacy Policy' ?>
                                    </a>
                                </label>
                                <?php if (isset($errors['agree_terms'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['agree_terms']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-kidney btn-lg">
                                <i class="fas fa-user-plus"></i> <?= $t['auth.register.button'] ?? 'Create Account' ?>
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="mb-0">
                            <?= $t['auth.register.have_account'] ?? 'Already have an account?' ?>
                            <a href="<?= Router::url('login') ?>" class="fw-bold text-decoration-none">
                                <?= $t['auth.register.login_link'] ?? 'Sign in here' ?>
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
    document.getElementById('username').focus();
});
</script>
