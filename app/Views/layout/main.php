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
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= Router::asset('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Additional CSS for password strength meter -->
    <style>
        .password-strength-bar {
            height: 4px;
            background: var(--color-border-light);
            border-radius: var(--radius-full);
            overflow: hidden;
        }
        .password-strength-bar::before {
            content: ''; 
            display: block;
            height: 100%;
            border-radius: var(--radius-full);
            transition: width 0.3s ease;
        }
        .strength-weak::before {
            width: 33%;
            background: var(--color-error);
        }
        .strength-medium::before {
            width: 66%;
            background: var(--color-warning);
        }
        .strength-strong::before {
            width: 100%;
            background: var(--color-success);
        }
        .autocomplete-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--color-bg-primary);
            border: 1px solid var(--color-border-light);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            z-index: var(--z-dropdown);
            max-height: 200px;
            overflow-y: auto;
            display: none;
        }
        .autocomplete-item {
            padding: var(--space-3);
            cursor: pointer;
            transition: background-color var(--transition-fast);
        }
        .autocomplete-item:hover {
            background: var(--color-bg-secondary);
        }
        .sr-only-focusable:focus {
            position: static;
            width: auto;
            height: auto;
            padding: inherit;
            margin: inherit;
            overflow: visible;
            clip: auto;
            white-space: normal;
        }
        .keyboard-focused {
            outline: 2px solid var(--primary-500) !important;
            outline-offset: 2px !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container flex justify-between items-center py-4">
            <a class="navbar-brand" href="<?= Router::url() ?>">
                <i class="fas fa-heart mr-2" style="color: var(--primary-600);"></i>
                RenalTales
            </a>
            
            <!-- Mobile menu button -->
            <button class="navbar-toggler md:hidden btn btn-secondary" type="button" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Desktop Navigation -->
            <div class="navbar-collapse hidden md:flex">
                <ul class="navbar-nav flex items-center gap-6">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Router::url() ?>">
                            <i class="fas fa-home mr-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Router::url('stories') ?>">
                            <i class="fas fa-book-open mr-1"></i> Stories
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" aria-expanded="false">
                            <i class="fas fa-folder mr-1"></i> Categories
                        </a>
                        <div class="dropdown-menu">
                            <?php foreach ($GLOBALS['STORY_CATEGORIES'] as $slug => $name): ?>
                                <a class="dropdown-item" href="<?= Router::url("category/{$slug}") ?>">
                                    <?= htmlspecialchars($name) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Router::url('about') ?>">
                            <i class="fas fa-info-circle mr-1"></i> About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Router::url('contact') ?>">
                            <i class="fas fa-envelope mr-1"></i> Contact
                        </a>
                    </li>
                </ul>
                
                <div class="flex items-center gap-4 ml-6">
                    <!-- Language Selector -->
                    <div class="dropdown language-selector">
                        <button class="nav-link dropdown-toggle btn btn-secondary" aria-expanded="false">
                            <?php 
                                // Map language codes to flag codes
                                $flagMap = [
                                    'am' => 'et', 'ar' => 'sa', 'cs' => 'cz', 'da' => 'dk', 'el' => 'gr',
                                    'en' => 'gb', 'et' => 'ee', 'hi' => 'in', 'ja' => 'jp', 'ko' => 'kr',
                                    'sl' => 'si', 'sv' => 'se', 'zh' => 'cn', 'tl' => 'ph', 'yo' => 'ng',
                                    'zu' => 'za', 'sw' => 'tz'
                                ];
                                $flagCode = $flagMap[$lang] ?? $lang;
                            ?>
                            <img src="<?= Router::asset("assets/images/flags/{$flagCode}.webp") ?>" alt="<?= $lang ?>" class="language-flag">
                            <?= strtoupper($lang) ?>
                        </button>
                        <div class="dropdown-menu">
                            <?php foreach ($supportedLanguages as $code => $name): ?>
                                <?php $dropdownFlagCode = $flagMap[$code] ?? $code; ?>
                                <a class="dropdown-item" href="<?= Router::url("lang/{$code}") ?>">
                                    <img src="<?= Router::asset("assets/images/flags/{$dropdownFlagCode}.webp") ?>" alt="<?= $code ?>" class="language-flag">
                                    <?= htmlspecialchars($name) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <?php if ($currentUser): ?>
                        <!-- Authenticated User Menu -->
                        <div class="dropdown">
                            <button class="nav-link dropdown-toggle btn btn-secondary" aria-expanded="false">
                                <i class="fas fa-user mr-1"></i>
                                <?= htmlspecialchars($currentUser['first_name'] ?? $currentUser['username']) ?>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="<?= Router::url('profile') ?>">
                                    <i class="fas fa-user-edit mr-2"></i> My Profile
                                </a>
                                <a class="dropdown-item" href="<?= Router::url('story/create') ?>">
                                    <i class="fas fa-pen mr-2"></i> Write Story
                                </a>
                                <?php if ($currentUser['role'] === 'translator' || $currentUser['role'] === 'moderator' || $currentUser['role'] === 'admin'): ?>
                                    <hr class="my-2 border-gray-200">
                                    <a class="dropdown-item" href="<?= Router::url('admin/pending') ?>">
                                        <i class="fas fa-tasks mr-2"></i> Moderation
                                    </a>
                                <?php endif; ?>
                                <?php if ($currentUser['role'] === 'admin'): ?>
                                    <a class="dropdown-item" href="<?= Router::url('admin/users') ?>">
                                        <i class="fas fa-users mr-2"></i> Manage Users
                                    </a>
                                    <a class="dropdown-item" href="<?= Router::url('admin/statistics') ?>">
                                        <i class="fas fa-chart-bar mr-2"></i> Statistics
                                    </a>
                                <?php endif; ?>
                                <hr class="my-2 border-gray-200">
                                <a class="dropdown-item" href="<?= Router::url('logout') ?>">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Guest User Menu -->
                        <a class="nav-link" href="<?= Router::url('login') ?>">
                            <i class="fas fa-sign-in-alt mr-1"></i> Login
                        </a>
                        <a class="btn btn-primary" href="<?= Router::url('register') ?>">
                            <i class="fas fa-user-plus mr-1"></i> Register
                        </a>
                    <?php endif; ?>
                </div>
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
    <footer style="background: var(--gray-900); color: white; padding: 3rem 0; margin-top: 4rem;">
        <div class="container">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2 mb-4">
                        <i class="fas fa-heart" style="color: var(--primary-400); font-size: 1.25rem;"></i>
                        <h3 class="text-xl font-bold">RenalTales</h3>
                    </div>
                    <p style="color: var(--gray-300); margin-bottom: 1.5rem; max-width: 28rem;">A supportive community platform for people with kidney disorders, sharing stories of hope, courage, and resilience.</p>
                    
                    <!-- Language flags -->
                    <div class="mb-6">
                        <h6 class="font-semibold mb-3">Available in <?= count($supportedLanguages) ?> Languages</h6>
                        <div class="flex flex-wrap gap-2">
                            <?php 
                                $displayLanguages = array_slice($supportedLanguages, 0, 12, true);
                                $footerFlagMap = [
                                    'am' => 'et', 'ar' => 'sa', 'cs' => 'cz', 'da' => 'dk', 'el' => 'gr',
                                    'en' => 'gb', 'et' => 'ee', 'hi' => 'in', 'ja' => 'jp', 'ko' => 'kr',
                                    'sl' => 'si', 'sv' => 'se', 'zh' => 'cn', 'tl' => 'ph', 'yo' => 'ng',
                                    'zu' => 'za', 'sw' => 'tz'
                                ];
                            ?>
                            <?php foreach ($displayLanguages as $code => $name): ?>
                                <?php $footerFlagCode = $footerFlagMap[$code] ?? $code; ?>
                                <img src="<?= Router::asset("assets/images/flags/{$footerFlagCode}.webp") ?>" alt="<?= $name ?>" title="<?= $name ?>" class="language-flag" style="opacity: 0.75; transition: opacity 0.3s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.75'" onclick="window.location.href='<?= Router::url("lang/{$code}") ?>'">
                            <?php endforeach; ?>
                            <?php if (count($supportedLanguages) > 12): ?>
                                <span style="color: var(--gray-400); font-size: 0.875rem; align-self: center;">+<?= count($supportedLanguages) - 12 ?> more</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h6 class="font-semibold mb-4">Platform</h6>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.5rem;">
                        <li><a href="<?= Router::url('stories') ?>" style="color: var(--gray-300); transition: color 0.3s;" onmouseover="this.style.color='var(--primary-400)'" onmouseout="this.style.color='var(--gray-300)'">Stories</a></li>
                        <li><a href="<?= Router::url('users') ?>" style="color: var(--gray-300); transition: color 0.3s;" onmouseover="this.style.color='var(--primary-400)'" onmouseout="this.style.color='var(--gray-300)'">Community</a></li>
                        <li><a href="<?= Router::url('about') ?>" style="color: var(--gray-300); transition: color 0.3s;" onmouseover="this.style.color='var(--primary-400)'" onmouseout="this.style.color='var(--gray-300)'">About</a></li>
                        <li><a href="<?= Router::url('story/create') ?>" style="color: var(--gray-300); transition: color 0.3s;" onmouseover="this.style.color='var(--primary-400)'" onmouseout="this.style.color='var(--gray-300)'">Share Your Story</a></li>
                    </ul>
                </div>
                
                <div>
                    <h6 class="font-semibold mb-4">Support</h6>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.5rem;">
                        <li><a href="<?= Router::url('contact') ?>" style="color: var(--gray-300); transition: color 0.3s;" onmouseover="this.style.color='var(--primary-400)'" onmouseout="this.style.color='var(--gray-300)'">Contact</a></li>
                        <li><a href="<?= Router::url('privacy') ?>" style="color: var(--gray-300); transition: color 0.3s;" onmouseover="this.style.color='var(--primary-400)'" onmouseout="this.style.color='var(--gray-300)'">Privacy Policy</a></li>
                        <li><a href="<?= Router::url('terms') ?>" style="color: var(--gray-300); transition: color 0.3s;" onmouseover="this.style.color='var(--primary-400)'" onmouseout="this.style.color='var(--gray-300)'">Terms of Service</a></li>
                        <li><a href="<?= Router::url('help') ?>" style="color: var(--gray-300); transition: color 0.3s;" onmouseover="this.style.color='var(--primary-400)'" onmouseout="this.style.color='var(--gray-300)'">Help Center</a></li>
                    </ul>
                </div>
            </div>
            
            <hr style="border-color: var(--gray-700); margin: 2rem 0;">
            
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <p style="color: var(--gray-400); font-size: 0.875rem; margin: 0;">&copy; <?= date('Y') ?> RenalTales. All rights reserved.</p>
                <p style="color: var(--gray-400); font-size: 0.875rem; margin: 0; display: flex; align-items: center; gap: 0.25rem;">
                    Built with <i class="fas fa-heart" style="color: #ef4444; font-size: 0.75rem;"></i> for the kidney community
                </p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="<?= Router::asset('js/app.js') ?>"></script>
    
    <!-- CSRF Protection for AJAX -->
    <?= RenalTales\Security\CSRFProtection::generateAjaxScript() ?>
    
    <!-- Additional page-specific JavaScript -->
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= Router::asset("js/{$script}") ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
