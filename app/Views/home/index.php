<!-- Hero Section -->
<section class="bg-kidney text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold">Welcome to RenalTales</h1>
                <p class="lead">A supportive community where people with kidney disorders share their stories, experiences, and hope. Connect with others who understand your journey.</p>
                <div class="d-flex gap-3 mt-4">
                    <a href="<?= Router::url('stories') ?>" class="btn btn-light btn-lg">
                        <i class="fas fa-book-open"></i> Read Stories
                    </a>
                    <?php if (!$currentUser): ?>
                        <a href="<?= Router::url('register') ?>" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-user-plus"></i> Join Community
                        </a>
                    <?php else: ?>
                        <a href="<?= Router::url('story/create') ?>" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-pen"></i> Share Your Story
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <i class="fas fa-heart display-1 opacity-25"></i>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="fas fa-book text-kidney display-4 mb-3"></i>
                        <h3 class="fw-bold text-kidney"><?= number_format($stats['total_stories']) ?></h3>
                        <p class="text-muted mb-0">Stories Shared</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="fas fa-users text-kidney display-4 mb-3"></i>
                        <h3 class="fw-bold text-kidney"><?= number_format($stats['total_users']) ?></h3>
                        <p class="text-muted mb-0">Community Members</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="fas fa-comments text-kidney display-4 mb-3"></i>
                        <h3 class="fw-bold text-kidney"><?= number_format($stats['total_comments']) ?></h3>
                        <p class="text-muted mb-0">Comments & Support</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="fas fa-globe text-kidney display-4 mb-3"></i>
                        <h3 class="fw-bold text-kidney"><?= $stats['supported_languages'] ?></h3>
                        <p class="text-muted mb-0">Languages Supported</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Stories Section -->
<?php if (!empty($featuredStories)): ?>
<section class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="fw-bold text-kidney">Featured Stories</h2>
                <p class="text-muted">Highlighted stories that inspire and connect our community</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="<?= Router::url('stories?featured=1') ?>" class="btn btn-outline-kidney">
                    View All Featured <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        
        <div class="row">
            <?php foreach ($featuredStories as $story): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <?php if (!empty($story['featured_image'])): ?>
                            <img src="<?= Router::url('storage/uploads/' . $story['featured_image']) ?>" 
                                 class="card-img-top" alt="<?= htmlspecialchars($story['title']) ?>"
                                 style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-kidney text-uppercase small">
                                    <?= htmlspecialchars($GLOBALS['STORY_CATEGORIES'][$story['category']] ?? $story['category']) ?>
                                </span>
                                <small class="text-muted">
                                    <i class="fas fa-star text-warning"></i> Featured
                                </small>
                            </div>
                            <h5 class="card-title">
                                <a href="<?= Router::url('story/' . $story['id']) ?>" class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($story['title']) ?>
                                </a>
                            </h5>
                            <p class="card-text text-muted flex-grow-1">
                                <?= htmlspecialchars(substr(strip_tags($story['content']), 0, 120)) ?>...
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <small class="text-muted">
                                    <i class="fas fa-user"></i> 
                                    <?= htmlspecialchars($story['first_name'] ? $story['first_name'] . ' ' . substr($story['last_name'], 0, 1) . '.' : $story['username']) ?>
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-comments"></i> <?= $story['comment_count'] ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Recent Stories Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="fw-bold text-kidney">Recent Stories</h2>
                <p class="text-muted">Latest stories from our community members</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="<?= Router::url('stories') ?>" class="btn btn-outline-kidney">
                    View All Stories <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        
        <div class="row">
            <?php foreach (array_slice($recentStories, 0, 8) as $story): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-secondary text-uppercase small">
                                    <?= htmlspecialchars($GLOBALS['STORY_CATEGORIES'][$story['category']] ?? $story['category']) ?>
                                </span>
                                <small class="text-muted">
                                    <?= date('M d', strtotime($story['created_at'])) ?>
                                </small>
                            </div>
                            <h6 class="card-title">
                                <a href="<?= Router::url('story/' . $story['id']) ?>" class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($story['title']) ?>
                                </a>
                            </h6>
                            <p class="card-text text-muted small flex-grow-1">
                                <?= htmlspecialchars(substr(strip_tags($story['content']), 0, 80)) ?>...
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <small class="text-muted">
                                    <i class="fas fa-user"></i> 
                                    <?= htmlspecialchars($story['first_name'] ? $story['first_name'] . ' ' . substr($story['last_name'], 0, 1) . '.' : $story['username']) ?>
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-comments"></i> <?= $story['comment_count'] ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-kidney">Explore by Category</h2>
            <p class="text-muted">Discover stories that resonate with your experience</p>
        </div>
        
        <div class="row">
            <?php foreach (array_slice($categories, 0, 8) as $category): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="<?= Router::url('category/' . $category['slug']) ?>" class="text-decoration-none">
                        <div class="card border-0 shadow-sm h-100 category-card">
                            <div class="card-body text-center">
                                <div class="category-icon mb-3">
                                    <?php 
                                    $icons = [
                                        'general' => 'fas fa-book',
                                        'diagnosis' => 'fas fa-stethoscope',
                                        'dialysis' => 'fas fa-heartbeat',
                                        'pre_transplant' => 'fas fa-hourglass-half',
                                        'post_transplant' => 'fas fa-heart',
                                        'lifestyle' => 'fas fa-running',
                                        'nutrition' => 'fas fa-apple-alt',
                                        'mental_health' => 'fas fa-brain',
                                        'success_stories' => 'fas fa-star',
                                        'family' => 'fas fa-users',
                                        'coping' => 'fas fa-hands-helping',
                                        'medical' => 'fas fa-user-md',
                                        'hope' => 'fas fa-sun',
                                        'challenges' => 'fas fa-mountain',
                                        'community' => 'fas fa-handshake'
                                    ];
                                    $icon = $icons[$category['slug']] ?? 'fas fa-book';
                                    ?>
                                    <i class="<?= $icon ?> text-kidney display-6"></i>
                                </div>
                                <h5 class="card-title text-kidney"><?= htmlspecialchars($category['name']) ?></h5>
                                <p class="text-muted mb-0">
                                    <?= number_format($category['count']) ?> 
                                    <?= $category['count'] == 1 ? 'story' : 'stories' ?>
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="py-5 bg-kidney text-white">
    <div class="container text-center">
        <h2 class="fw-bold mb-3">Ready to Share Your Story?</h2>
        <p class="lead mb-4">Your experience matters. Help others by sharing your journey, challenges, and triumphs.</p>
        <?php if (!$currentUser): ?>
            <a href="<?= Router::url('register') ?>" class="btn btn-light btn-lg me-3">
                <i class="fas fa-user-plus"></i> Join Our Community
            </a>
            <a href="<?= Router::url('about') ?>" class="btn btn-outline-light btn-lg">
                <i class="fas fa-info-circle"></i> Learn More
            </a>
        <?php else: ?>
            <a href="<?= Router::url('story/create') ?>" class="btn btn-light btn-lg me-3">
                <i class="fas fa-pen"></i> Write Your Story
            </a>
            <a href="<?= Router::url('stories') ?>" class="btn btn-outline-light btn-lg">
                <i class="fas fa-book-open"></i> Read More Stories
            </a>
        <?php endif; ?>
    </div>
</section>

<style>
.btn-outline-kidney {
    color: #2c5282;
    border-color: #2c5282;
}
.btn-outline-kidney:hover {
    background-color: #2c5282;
    border-color: #2c5282;
    color: white;
}
.category-card:hover {
    transform: translateY(-5px);
    transition: transform 0.3s ease;
}
.category-card .category-icon {
    transition: color 0.3s ease;
}
.category-card:hover .category-icon i {
    color: #2a4d7a !important;
}
</style>
