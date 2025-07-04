<?php
/**
 * Stories Index View - List all published stories with pagination and filters
 */
if (!defined('ROOT_PATH')) exit;
?>

<div class="container">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">
            <i class="fas fa-book text-kidney"></i> 
            <?= htmlspecialchars($page_title ?? ($t['stories.title'] ?? 'RenalTales Stories')) ?>
        </h1>
        <?php if (isset($currentUser) && $currentUser): ?>
            <a href="<?= Router::url('story/create') ?>" class="btn btn-kidney">
                <i class="fas fa-plus"></i> <?= $t['stories.create'] ?? 'Create New Story' ?>
            </a>
        <?php endif; ?>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label"><?= $t['btn.search'] ?? 'Search' ?></label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($currentSearch ?? '') ?>" 
                                   placeholder="<?= $t['stories.search_placeholder'] ?? 'Search stories...' ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="form-label"><?= $t['stories.category'] ?? 'Category' ?></label>
                            <select class="form-select" id="category" name="category">
                                <option value=""><?= $t['stories.all_categories'] ?? 'All Categories' ?></option>
                                <?php if (isset($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= htmlspecialchars($category['slug']) ?>" 
                                                <?= ($currentCategory ?? '') === $category['slug'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="language" class="form-label"><?= $t['stories.language'] ?? 'Language' ?></label>
                            <select class="form-select" id="language" name="language">
                                <option value=""><?= $t['stories.all_languages'] ?? 'All Languages' ?></option>
                                <?php if (isset($languages)): ?>
                                    <?php foreach ($languages as $code => $name): ?>
                                        <option value="<?= htmlspecialchars($code) ?>" 
                                                <?= ($currentLanguage ?? '') === $code ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-kidney">
                                    <i class="fas fa-search"></i> <?= $t['btn.search'] ?? 'Filter' ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Stories Grid -->
    <?php if (!empty($stories)): ?>
        <div class="row">
            <?php foreach ($stories as $story): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 story-card shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-kidney-light text-kidney">
                                    <?= htmlspecialchars($story['category_name'] ?? ($t['stories.uncategorized'] ?? 'Uncategorized')) ?>
                                </span>
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt"></i> 
                                    <?= date('M j, Y', strtotime($story['published_at'] ?? $story['created_at'])) ?>
                                </small>
                            </div>
                            
                            <h5 class="card-title mb-3">
                                <a href="<?= Router::url('story/' . $story['id']) ?>" class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($story['title']) ?>
                                </a>
                            </h5>
                            
                            <p class="card-text text-muted flex-grow-1">
                                <?= htmlspecialchars(substr($story['excerpt'] ?? $story['content'] ?? '', 0, 150)) ?>
                                <?php if (strlen($story['excerpt'] ?? $story['content'] ?? '') > 150): ?>...<?php endif; ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-kidney text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                        <?= strtoupper(substr($story['username'] ?? 'A', 0, 1)) ?>
                                    </div>
                                    <small class="text-muted">
                                        <?= $t['stories.by'] ?? 'by' ?> <strong><?= htmlspecialchars($story['username'] ?? 'Anonymous') ?></strong>
                                    </small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-comments text-muted me-1"></i>
                                        <small class="text-muted"><?= (int)($story['comment_count'] ?? 0) ?></small>
                                    </div>
                                    <?php if (!empty($story['language'])): ?>
                                        <span class="badge bg-light text-dark">
                                            <?= strtoupper($story['language']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <a href="<?= Router::url('story/' . $story['id']) ?>" class="btn btn-outline-kidney btn-sm w-100">
                                    <i class="fas fa-book-open"></i> <?= $t['stories.read_more'] ?? 'Read More' ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['pages'] > 1): ?>
            <nav aria-label="Stories pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <!-- Previous Page -->
                    <?php if ($pagination['has_prev']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?><?= $currentSearch ? '&search=' . urlencode($currentSearch) : '' ?><?= $currentCategory ? '&category=' . urlencode($currentCategory) : '' ?><?= $currentLanguage ? '&language=' . urlencode($currentLanguage) : '' ?>">
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
                            <a class="page-link" href="?page=1<?= $currentSearch ? '&search=' . urlencode($currentSearch) : '' ?><?= $currentCategory ? '&category=' . urlencode($currentCategory) : '' ?><?= $currentLanguage ? '&language=' . urlencode($currentLanguage) : '' ?>">1</a>
                        </li>
                        <?php if ($start > 2): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?= ($i === $pagination['current_page']) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?><?= $currentSearch ? '&search=' . urlencode($currentSearch) : '' ?><?= $currentCategory ? '&category=' . urlencode($currentCategory) : '' ?><?= $currentLanguage ? '&language=' . urlencode($currentLanguage) : '' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($end < $pagination['pages']): ?>
                        <?php if ($end < $pagination['pages'] - 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $pagination['pages'] ?><?= $currentSearch ? '&search=' . urlencode($currentSearch) : '' ?><?= $currentCategory ? '&category=' . urlencode($currentCategory) : '' ?><?= $currentLanguage ? '&language=' . urlencode($currentLanguage) : '' ?>">
                                <?= $pagination['pages'] ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Next Page -->
                    <?php if ($pagination['has_next']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?><?= $currentSearch ? '&search=' . urlencode($currentSearch) : '' ?><?= $currentCategory ? '&category=' . urlencode($currentCategory) : '' ?><?= $currentLanguage ? '&language=' . urlencode($currentLanguage) : '' ?>">
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
                of <?= $pagination['total'] ?> stories
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- No Stories Found -->
        <div class="text-center py-5">
            <i class="fas fa-book fa-3x text-muted mb-3"></i>
            <h3 class="text-muted"><?= $t['stories.no_stories'] ?? 'No stories found' ?></h3>
            <p class="text-muted"><?= $t['stories.no_stories_desc'] ?? 'Try adjusting your search criteria or browse all stories.' ?></p>
            <?php if (isset($currentUser) && $currentUser): ?>
                <a href="<?= Router::url('story/create') ?>" class="btn btn-kidney mt-3">
                    <i class="fas fa-plus"></i> <?= $t['stories.create_first'] ?? 'Create the First Story' ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.story-card {
    transition: all 0.3s ease;
    border: none;
}

.story-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(44, 82, 130, 0.15);
}

.story-card .card-title a {
    color: inherit;
    transition: color 0.3s ease;
}

.story-card .card-title a:hover {
    color: #2c5282;
}

.bg-kidney-light {
    background-color: rgba(44, 82, 130, 0.1);
}

.btn-outline-kidney {
    border-color: #2c5282;
    color: #2c5282;
    transition: all 0.3s ease;
}

.btn-outline-kidney:hover {
    background-color: #2c5282;
    border-color: #2c5282;
    color: white;
    transform: translateY(-1px);
}

.pagination .page-link {
    color: #2c5282;
}

.pagination .page-item.active .page-link {
    background-color: #2c5282;
    border-color: #2c5282;
}

.pagination .page-link:hover {
    color: #1a3a52;
    background-color: rgba(44, 82, 130, 0.1);
}
</style>
