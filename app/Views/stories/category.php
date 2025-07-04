<?php
/**
 * Stories Category View - List stories by category with pagination
 */
?>

<div class="container mt-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/stories">Stories</a></li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?= htmlspecialchars($category['name']) ?>
                    </li>
                </ol>
            </nav>
            <h1 class="page-title">
                <?= htmlspecialchars($page_title) ?>
                <span class="badge bg-secondary ms-2"><?= $pagination['total'] ?> stories</span>
            </h1>
            <?php if (!empty($category['description'])): ?>
                <p class="text-muted"><?= htmlspecialchars($category['description']) ?></p>
            <?php endif; ?>
        </div>
        <?php if ($currentUser): ?>
            <a href="/story/create?category=<?= htmlspecialchars($category['slug']) ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Story
            </a>
        <?php endif; ?>
    </div>

    <!-- Category Stories -->
    <?php if (!empty($stories)): ?>
        <div class="row">
            <?php foreach ($stories as $story): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 story-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary">
                                    <?= htmlspecialchars($category['name']) ?>
                                </span>
                                <small class="text-muted">
                                    <?= date('M j, Y', strtotime($story['published_at'] ?? $story['created_at'])) ?>
                                </small>
                            </div>
                            
                            <h5 class="card-title">
                                <a href="/story/<?= $story['id'] ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($story['title']) ?>
                                </a>
                            </h5>
                            
                            <p class="card-text text-muted">
                                <?= htmlspecialchars(substr($story['excerpt'] ?? '', 0, 150)) ?>
                                <?php if (strlen($story['excerpt'] ?? '') > 150): ?>...<?php endif; ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    by <?= htmlspecialchars($story['username'] ?? 'Anonymous') ?>
                                </small>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-comments text-muted me-1"></i>
                                    <small class="text-muted"><?= (int)($story['comment_count'] ?? 0) ?></small>
                                    <?php if (!empty($story['language'])): ?>
                                        <span class="badge bg-light text-dark ms-2">
                                            <?= strtoupper($story['language']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['pages'] > 1): ?>
            <nav aria-label="Category stories pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <!-- Previous Page -->
                    <?php if ($pagination['has_prev']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>">
                                Previous
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Page Numbers -->
                    <?php
                    $start = max(1, $pagination['current_page'] - 2);
                    $end = min($pagination['pages'], $pagination['current_page'] + 2);
                    ?>
                    
                    <?php if ($start > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1">1</a>
                        </li>
                        <?php if ($start > 2): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?= ($i === $pagination['current_page']) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($end < $pagination['pages']): ?>
                        <?php if ($end < $pagination['pages'] - 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $pagination['pages'] ?>">
                                <?= $pagination['pages'] ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Next Page -->
                    <?php if ($pagination['has_next']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>">
                                Next
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>

            <!-- Pagination Info -->
            <div class="text-center text-muted mt-2">
                Showing <?= ($pagination['current_page'] - 1) * $pagination['per_page'] + 1 ?> to 
                <?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) ?> 
                of <?= $pagination['total'] ?> stories in <?= htmlspecialchars($category['name']) ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- No Stories Found -->
        <div class="text-center py-5">
            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
            <h3 class="text-muted">No stories in this category yet</h3>
            <p class="text-muted">Be the first to share a story in <?= htmlspecialchars($category['name']) ?>!</p>
            <?php if ($currentUser): ?>
                <a href="/story/create?category=<?= htmlspecialchars($category['slug']) ?>" class="btn btn-primary mt-3">
                    <i class="fas fa-plus"></i> Create First Story
                </a>
            <?php else: ?>
                <div class="mt-3">
                    <a href="/login" class="btn btn-outline-primary me-2">Login</a>
                    <a href="/register" class="btn btn-primary">Register</a>
                </div>
                <small class="text-muted d-block mt-2">to share your story</small>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Other Categories -->
    <div class="card mt-5">
        <div class="card-header">
            <h6 class="card-title mb-0">
                <i class="fas fa-folder"></i> Browse Other Categories
            </h6>
        </div>
        <div class="card-body">
            <div id="other-categories" class="text-center text-muted">
                <i class="fas fa-spinner fa-spin"></i> Loading categories...
            </div>
        </div>
    </div>
</div>

<style>
.story-card {
    transition: transform 0.2s ease-in-out;
}

.story-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.story-card .card-title a {
    color: inherit;
}

.story-card .card-title a:hover {
    color: #007bff;
}

.breadcrumb {
    background: none;
    padding: 0;
    margin-bottom: 1rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadOtherCategories();
});

function loadOtherCategories() {
    // This would typically load from an API endpoint
    setTimeout(() => {
        const currentCategory = <?= json_encode($category['slug']) ?>;
        const categories = [
            { name: 'Personal Journey', slug: 'personal-journey', count: 45 },
            { name: 'Treatment Experiences', slug: 'treatment-experiences', count: 32 },
            { name: 'Family Support', slug: 'family-support', count: 28 },
            { name: 'Living with CKD', slug: 'living-with-ckd', count: 67 },
            { name: 'Transplant Stories', slug: 'transplant-stories', count: 23 },
            { name: 'Motivation', slug: 'motivation', count: 56 }
        ];
        
        const otherCategories = categories.filter(cat => cat.slug !== currentCategory);
        
        let html = '<div class="row">';
        otherCategories.forEach(category => {
            html += `
                <div class="col-md-4 mb-3">
                    <div class="card border-light">
                        <div class="card-body text-center py-3">
                            <h6 class="card-title mb-1">
                                <a href="/category/${category.slug}" class="text-decoration-none">
                                    ${category.name}
                                </a>
                            </h6>
                            <small class="text-muted">${category.count} stories</small>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        
        if (otherCategories.length === 0) {
            html = '<div class="text-center text-muted">All categories loaded</div>';
        }
        
        document.getElementById('other-categories').innerHTML = html;
    }, 800);
}
</script>
