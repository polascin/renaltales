<?php
/**
 * Modern Home Page for RenalTales
 * Showcases the new design system
 */
?>

<div id="main-content">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="hero-title fade-in">
                Welcome to RenalTales
            </h1>
            <p class="hero-subtitle fade-in" style="animation-delay: 0.2s;">
                A supportive community where people with kidney disorders share their stories, experiences, and hope. 
                Connect with others who understand your journey.
            </p>
            <div class="flex justify-center gap-4 mt-8 fade-in" style="animation-delay: 0.4s;">
                <a href="<?= Router::url('stories') ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-book-open mr-2"></i>
                    Read Stories
                </a>
                <a href="<?= Router::url('story/create') ?>" class="btn btn-secondary btn-lg">
                    <i class="fas fa-pen mr-2"></i>
                    Share Your Story
                </a>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="container">
        <div class="stats-grid">
            <div class="stat-card fade-in" style="animation-delay: 0.1s;">
                <span class="stat-number">2,847</span>
                <span class="stat-label">Stories Shared</span>
            </div>
            <div class="stat-card fade-in" style="animation-delay: 0.2s;">
                <span class="stat-number">15,293</span>
                <span class="stat-label">Community Members</span>
            </div>
            <div class="stat-card fade-in" style="animation-delay: 0.3s;">
                <span class="stat-number">42,156</span>
                <span class="stat-label">Comments & Support</span>
            </div>
            <div class="stat-card fade-in" style="animation-delay: 0.4s;">
                <span class="stat-number"><?= count($supportedLanguages) ?></span>
                <span class="stat-label">Languages Supported</span>
            </div>
        </div>
    </section>

    <!-- Featured Stories Section -->
    <section class="container">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold mb-4">Featured Stories</h2>
            <p class="lead">Highlighted stories that inspire and connect our community</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" data-infinite-scroll data-page="1">
            <!-- Sample Featured Story -->
            <article class="story-card">
                <div class="story-meta">
                    <span class="category-badge">
                        <i class="fas fa-heart mr-1"></i>
                        Hope & Recovery
                    </span>
                    <time datetime="2024-12-15">Dec 15, 2024</time>
                </div>
                <h3 class="text-xl font-semibold mb-3">
                    <a href="/stories/1" class="text-primary hover:text-primary-700">
                        My Journey Back to Health
                    </a>
                </h3>
                <p class="text-gray-600 mb-4">
                    After years of dialysis, I never thought I'd feel normal again. But with the right support 
                    and medical care, I'm now living my best life. Here's my story of hope and recovery...
                </p>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button data-story-action="like" data-story-id="1" class="btn btn-sm btn-secondary">
                            <i class="far fa-heart mr-1"></i>
                            <span class="btn-text">23</span>
                        </button>
                        <span class="text-sm text-muted">
                            <i class="fas fa-eye mr-1"></i>
                            432 views
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <button data-share="facebook" class="btn btn-sm btn-secondary" title="Share on Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </button>
                        <button data-share="twitter" class="btn btn-sm btn-secondary" title="Share on Twitter">
                            <i class="fab fa-twitter"></i>
                        </button>
                        <button data-share="copy" class="btn btn-sm btn-secondary" title="Copy link">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                </div>
            </article>

            <!-- Sample Community Story -->
            <article class="story-card">
                <div class="story-meta">
                    <span class="category-badge">
                        <i class="fas fa-users mr-1"></i>
                        Community Support
                    </span>
                    <time datetime="2024-12-14">Dec 14, 2024</time>
                </div>
                <h3 class="text-xl font-semibold mb-3">
                    <a href="/stories/2" class="text-primary hover:text-primary-700">
                        Finding My Support Network
                    </a>
                </h3>
                <p class="text-gray-600 mb-4">
                    When I was first diagnosed, I felt alone and scared. Discovering this community 
                    changed everything. The support and understanding I found here has been incredible...
                </p>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button data-story-action="like" data-story-id="2" class="btn btn-sm btn-secondary">
                            <i class="far fa-heart mr-1"></i>
                            <span class="btn-text">41</span>
                        </button>
                        <span class="text-sm text-muted">
                            <i class="fas fa-eye mr-1"></i>
                            687 views
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <button data-share="facebook" class="btn btn-sm btn-secondary" title="Share on Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </button>
                        <button data-share="twitter" class="btn btn-sm btn-secondary" title="Share on Twitter">
                            <i class="fab fa-twitter"></i>
                        </button>
                        <button data-share="copy" class="btn btn-sm btn-secondary" title="Copy link">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                </div>
            </article>

            <!-- Sample Treatment Story -->
            <article class="story-card">
                <div class="story-meta">
                    <span class="category-badge">
                        <i class="fas fa-hospital mr-1"></i>
                        Treatment Experience
                    </span>
                    <time datetime="2024-12-13">Dec 13, 2024</time>
                </div>
                <h3 class="text-xl font-semibold mb-3">
                    <a href="/stories/3" class="text-primary hover:text-primary-700">
                        Navigating Dialysis with Confidence
                    </a>
                </h3>
                <p class="text-gray-600 mb-4">
                    Starting dialysis was overwhelming, but I learned so much along the way. 
                    Here are my tips for making the process easier and maintaining a positive outlook...
                </p>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button data-story-action="like" data-story-id="3" class="btn btn-sm btn-secondary">
                            <i class="far fa-heart mr-1"></i>
                            <span class="btn-text">67</span>
                        </button>
                        <span class="text-sm text-muted">
                            <i class="fas fa-eye mr-1"></i>
                            1,203 views
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <button data-share="facebook" class="btn btn-sm btn-secondary" title="Share on Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </button>
                        <button data-share="twitter" class="btn btn-sm btn-secondary" title="Share on Twitter">
                            <i class="fab fa-twitter"></i>
                        </button>
                        <button data-share="copy" class="btn btn-sm btn-secondary" title="Copy link">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                </div>
            </article>
        </div>

        <div class="text-center mt-12">
            <a href="<?= Router::url('stories') ?>" class="btn btn-primary">
                <i class="fas fa-arrow-right mr-2"></i>
                View All Stories
            </a>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="bg-gray-50 py-16 mt-16">
        <div class="container">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold mb-4">Explore by Category</h2>
                <p class="lead">Discover stories that resonate with your experience</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php 
                $categories = [
                    ['slug' => 'hope-recovery', 'name' => 'Hope & Recovery', 'icon' => 'heart', 'count' => 342],
                    ['slug' => 'dialysis', 'name' => 'Dialysis Experience', 'icon' => 'hospital', 'count' => 198],
                    ['slug' => 'transplant', 'name' => 'Transplant Journey', 'icon' => 'hand-holding-heart', 'count' => 156],
                    ['slug' => 'family-support', 'name' => 'Family Support', 'icon' => 'users', 'count' => 234],
                    ['slug' => 'lifestyle', 'name' => 'Lifestyle & Diet', 'icon' => 'apple-alt', 'count' => 187],
                    ['slug' => 'mental-health', 'name' => 'Mental Health', 'icon' => 'brain', 'count' => 123],
                    ['slug' => 'treatment', 'name' => 'Treatment Options', 'icon' => 'pills', 'count' => 167],
                    ['slug' => 'advocacy', 'name' => 'Advocacy', 'icon' => 'bullhorn', 'count' => 89]
                ];
                ?>
                
                <?php foreach ($categories as $category): ?>
                <a href="<?= Router::url("category/{$category['slug']}") ?>" 
                   class="card p-6 text-center hover:shadow-lg transition-all duration-300 no-print"
                   data-filterable="<?= $category['slug'] ?>">
                    <div class="text-4xl text-primary-600 mb-4">
                        <i class="fas fa-<?= $category['icon'] ?>"></i>
                    </div>
                    <h3 class="font-semibold text-lg mb-2"><?= htmlspecialchars($category['name']) ?></h3>
                    <p class="text-muted text-sm"><?= $category['count'] ?> stories</p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-16">
        <div class="container text-center">
            <div class="max-w-2xl mx-auto">
                <h2 class="text-3xl font-bold mb-6">Ready to Share Your Story?</h2>
                <p class="text-lg text-gray-600 mb-8">
                    Your experience could inspire and help others going through similar challenges. 
                    Join our supportive community and make a difference.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="<?= Router::url('register') ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-user-plus mr-2"></i>
                        Join the Community
                    </a>
                    <a href="<?= Router::url('story/create') ?>" class="btn btn-secondary btn-lg">
                        <i class="fas fa-pen mr-2"></i>
                        Write Your Story
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Search Section -->
    <section class="bg-primary-50 py-16">
        <div class="container">
            <div class="max-w-2xl mx-auto text-center">
                <h2 class="text-3xl font-bold mb-6">Find Stories That Matter to You</h2>
                <form class="flex gap-4" data-search>
                    <div class="flex-1 relative">
                        <input type="search" 
                               class="form-control pr-12" 
                               placeholder="Search stories, topics, or keywords..." 
                               data-search-input
                               data-autocomplete="stories">
                        <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-primary-600">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <!-- Filter buttons -->
                <div class="flex flex-wrap justify-center gap-2 mt-6">
                    <button class="btn btn-sm btn-secondary" data-filter="hope-recovery">Hope & Recovery</button>
                    <button class="btn btn-sm btn-secondary" data-filter="dialysis">Dialysis</button>
                    <button class="btn btn-sm btn-secondary" data-filter="transplant">Transplant</button>
                    <button class="btn btn-sm btn-secondary" data-filter="family-support">Family Support</button>
                </div>
                
                <!-- Search results -->
                <div class="mt-8" data-search-results></div>
            </div>
        </div>
    </section>
</div>

<style>
/* Page-specific styles */
.hero-section {
    background: linear-gradient(135deg, var(--primary-600) 0%, var(--secondary-600) 100%);
    color: white;
    padding: 5rem 0;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="90" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    pointer-events: none;
}

.max-w-2xl {
    max-width: 42rem;
}

@media (max-width: 768px) {
    .hero-section {
        padding: 3rem 0;
    }
    
    .hero-title {
        font-size: 2.5rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
}
</style>
