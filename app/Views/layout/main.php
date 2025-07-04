<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) . ' - ' : '' ?>RenalTales</title>
    <meta name="description" content="<?= isset($description) ? htmlspecialchars($description) : 'RenalTales - A community platform for people with kidney disorders' ?>">
    
    <!-- Security Meta Tags -->
    <?= RenalTales\Security\CSRFProtection::generateMetaTag() ?>
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= Router::asset('images/favicon.ico') ?>">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= Router::asset('css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= Router::asset('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS for language flags -->
    <style>
        .language-selector .flag {
            width: 20px;
            height: 15px;
            object-fit: cover;
            margin-right: 5px;
        }
        .navbar-brand {
            color: #2c5282 !important;
            font-weight: bold;
        }
        .text-kidney {
            color: #2c5282;
        }
        .bg-kidney {
            background-color: #2c5282;
        }
        .btn-kidney {
            background-color: #2c5282;
            border-color: #2c5282;
            color: white;
        }
        .btn-kidney:hover {
            background-color: #2a4d7a;
            border-color: #2a4d7a;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="<?= Router::url() ?>">
                <i class="fas fa-heart text-kidney"></i>
                RenalTales
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Router::url() ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Router::url('stories') ?>">Stories</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Categories
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($GLOBALS['STORY_CATEGORIES'] as $slug => $name): ?>
                                <li><a class="dropdown-item" href="<?= Router::url("category/{$slug}") ?>"><?= htmlspecialchars($name) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Router::url('about') ?>">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Router::url('contact') ?>">Contact</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <!-- Language Selector -->
                    <li class="nav-item dropdown language-selector">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="<?= Router::asset("images/flags/{$lang}.png") ?>" alt="<?= $lang ?>" class="flag">
                            <?= strtoupper($lang) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($supportedLanguages as $code => $name): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= Router::url("lang/{$code}") ?>">
                                        <img src="<?= Router::asset("images/flags/{$code}.png") ?>" alt="<?= $code ?>" class="flag">
                                        <?= htmlspecialchars($name) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    
                    <?php if ($currentUser): ?>
                        <!-- Authenticated User Menu -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i>
                                <?= htmlspecialchars($currentUser['first_name'] ?? $currentUser['username']) ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= Router::url('profile') ?>">
                                    <i class="fas fa-user-edit"></i> My Profile
                                </a></li>
                                <li><a class="dropdown-item" href="<?= Router::url('story/create') ?>">
                                    <i class="fas fa-pen"></i> Write Story
                                </a></li>
                                <?php if ($currentUser['role'] === 'translator' || $currentUser['role'] === 'moderator' || $currentUser['role'] === 'admin'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?= Router::url('admin/pending') ?>">
                                        <i class="fas fa-tasks"></i> Moderation
                                    </a></li>
                                <?php endif; ?>
                                <?php if ($currentUser['role'] === 'admin'): ?>
                                    <li><a class="dropdown-item" href="<?= Router::url('admin/users') ?>">
                                        <i class="fas fa-users"></i> Manage Users
                                    </a></li>
                                    <li><a class="dropdown-item" href="<?= Router::url('admin/statistics') ?>">
                                        <i class="fas fa-chart-bar"></i> Statistics
                                    </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= Router::url('logout') ?>">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Guest User Menu -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= Router::url('login') ?>">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-kidney btn-sm ms-2" href="<?= Router::url('register') ?>">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (isset($flash_messages) && !empty($flash_messages)): ?>
        <div class="container mt-3">
            <?= RenalTales\Core\FlashMessages::render() ?>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="py-4">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-heart text-danger"></i> RenalTales</h5>
                    <p>A supportive community platform for people with kidney disorders, sharing stories of hope, courage, and resilience.</p>
                </div>
                <div class="col-md-2">
                    <h6>Platform</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= Router::url('stories') ?>" class="text-light">Stories</a></li>
                        <li><a href="<?= Router::url('users') ?>" class="text-light">Community</a></li>
                        <li><a href="<?= Router::url('about') ?>" class="text-light">About</a></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h6>Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= Router::url('contact') ?>" class="text-light">Contact</a></li>
                        <li><a href="<?= Router::url('privacy') ?>" class="text-light">Privacy</a></li>
                        <li><a href="<?= Router::url('terms') ?>" class="text-light">Terms</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6>Languages Supported</h6>
                    <p class="small">Stories available in <?= count($supportedLanguages) ?> languages including English, Slovak, Czech, German, and many more.</p>
                    <div class="d-flex flex-wrap">
                        <?php $displayLanguages = array_slice($supportedLanguages, 0, 12, true); ?>
                        <?php foreach ($displayLanguages as $code => $name): ?>
                            <img src="<?= Router::asset("images/flags/{$code}.png") ?>" 
                                 alt="<?= $name ?>" 
                                 title="<?= $name ?>"
                                 class="flag me-1 mb-1" 
                                 style="width: 24px; height: 18px;">
                        <?php endforeach; ?>
                        <?php if (count($supportedLanguages) > 12): ?>
                            <span class="text-muted small align-self-end">+<?= count($supportedLanguages) - 12 ?> more</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?= date('Y') ?> RenalTales. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 small">Built with ❤️ for the kidney community</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= Router::asset('js/app.js') ?>"></script>
    
    <!-- CSRF Protection for AJAX -->
    <?= RenalTales\Security\CSRFProtection::generateAjaxScript() ?>
    
    <!-- Additional page-specific JavaScript -->
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= Router::asset("js/{$script}") ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Security and form enhancement script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Form validation enhancement
        var forms = document.querySelectorAll('form[novalidate]');
        forms.forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
        
        // Password strength indicator
        var passwordFields = document.querySelectorAll('input[type="password"][name="password"]');
        passwordFields.forEach(function(field) {
            field.addEventListener('input', function() {
                var strength = checkPasswordStrength(this.value);
                updatePasswordStrengthIndicator(this, strength);
            });
        });
    });
    
    function checkPasswordStrength(password) {
        var score = 0;
        if (password.length >= 12) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/\d/.test(password)) score++;
        if (/[^a-zA-Z\d]/.test(password)) score++;
        if (password.length >= 16) score++;
        
        if (score < 3) return 'weak';
        if (score < 5) return 'medium';
        return 'strong';
    }
    
    function updatePasswordStrengthIndicator(field, strength) {
        var indicator = field.parentNode.querySelector('.password-strength');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.className = 'password-strength mt-1';
            field.parentNode.appendChild(indicator);
        }
        
        var colors = {
            'weak': 'danger',
            'medium': 'warning', 
            'strong': 'success'
        };
        
        indicator.innerHTML = '<small class="text-' + colors[strength] + '">Password strength: ' + strength + '</small>';
    }
    </script>
</body>
</html>
