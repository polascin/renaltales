<?php
/**
 * Story Show View - Display individual story
 */
?>

<div class="container mt-4">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Story Header -->
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/stories">Stories</a></li>
                            <?php if (!empty($story['category_name'])): ?>
                                <li class="breadcrumb-item">
                                    <a href="/category/<?= htmlspecialchars($story['category_slug']) ?>">
                                        <?= htmlspecialchars($story['category_name']) ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="breadcrumb-item active" aria-current="page">
                                <?= htmlspecialchars($content['title']) ?>
                            </li>
                        </ol>
                    </nav>
                </div>
                
                <?php if ($can_edit || $can_delete): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" 
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog"></i> Actions
                        </button>
                        <ul class="dropdown-menu">
                            <?php if ($can_edit): ?>
                                <li><a class="dropdown-item" href="/story/<?= $story['id'] ?>/edit">
                                    <i class="fas fa-edit"></i> Edit Story
                                </a></li>
                            <?php endif; ?>
                            <?php if ($can_delete): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="/story/<?= $story['id'] ?>/delete" 
                                          onsubmit="return confirm('Are you sure you want to delete this story? This action cannot be undone.')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-trash"></i> Delete Story
                                        </button>
                                    </form>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Story Title and Meta -->
            <div class="mb-4">
                <h1 class="display-6 mb-3"><?= htmlspecialchars($content['title']) ?></h1>
                
                <div class="d-flex flex-wrap align-items-center text-muted mb-3">
                    <div class="me-4">
                        <i class="fas fa-user"></i>
                        <strong>by <?= htmlspecialchars($story['username'] ?? 'Anonymous') ?></strong>
                        <?php if (!empty($story['full_name'])): ?>
                            (<?= htmlspecialchars($story['full_name']) ?>)
                        <?php endif; ?>
                    </div>
                    <div class="me-4">
                        <i class="fas fa-calendar"></i>
                        <?= date('F j, Y', strtotime($story['published_at'] ?? $story['created_at'])) ?>
                    </div>
                    <div class="me-4">
                        <i class="fas fa-folder"></i>
                        <?= htmlspecialchars($story['category_name'] ?? 'Uncategorized') ?>
                    </div>
                    <div class="me-4">
                        <i class="fas fa-language"></i>
                        <?= strtoupper($content['language']) ?>
                    </div>
                </div>

                <!-- Tags -->
                <?php if (!empty($tags)): ?>
                    <div class="mb-3">
                        <i class="fas fa-tags text-muted me-2"></i>
                        <?php foreach ($tags as $tag): ?>
                            <span class="badge bg-light text-dark me-1"><?= htmlspecialchars($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Translation Navigation -->
                <?php if (count($translations) > 1): ?>
                    <div class="mb-3">
                        <small class="text-muted">Available in:</small>
                        <?php foreach ($translations as $translation): ?>
                            <a href="/story/<?= $story['id'] ?>?lang=<?= htmlspecialchars($translation['language']) ?>" 
                               class="badge <?= ($translation['language'] === $content['language']) ? 'bg-primary' : 'bg-outline-primary' ?> me-1">
                                <?= strtoupper($translation['language']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Story Content -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="story-content">
                        <?= nl2br(htmlspecialchars($content['content'])) ?>
                    </div>
                </div>
            </div>

            <!-- Social Actions -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="shareStory()">
                        <i class="fas fa-share"></i> Share
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="printStory()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
                
                <div class="text-muted">
                    <i class="fas fa-eye"></i> <span id="view-count">Loading...</span> views
                </div>
            </div>

            <!-- Comments Section -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-comments"></i> 
                        Comments (<?= count($comments) ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($currentUser): ?>
                        <!-- Add Comment Form -->
                        <form method="POST" action="/api/comment" class="mb-4">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="story_id" value="<?= $story['id'] ?>">
                            <div class="mb-3">
                                <label for="comment" class="form-label">Add your comment</label>
                                <textarea class="form-control" id="comment" name="comment" rows="3" 
                                          placeholder="Share your thoughts..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-paper-plane"></i> Post Comment
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <a href="/login">Login</a> to post comments and engage with the community.
                        </div>
                    <?php endif; ?>

                    <!-- Comments List -->
                    <?php if (!empty($comments)): ?>
                        <div class="comments-list">
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment mb-3 p-3 bg-light rounded">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <strong><?= htmlspecialchars($comment['username'] ?? 'Anonymous') ?></strong>
                                            <?php if (!empty($comment['full_name'])): ?>
                                                <small class="text-muted">(<?= htmlspecialchars($comment['full_name']) ?>)</small>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            <?= date('M j, Y \a\t g:i A', strtotime($comment['created_at'])) ?>
                                        </small>
                                    </div>
                                    <div class="comment-content">
                                        <?= nl2br(htmlspecialchars($comment['content'])) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-comment fa-2x mb-2"></i>
                            <p>No comments yet. Be the first to share your thoughts!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Author Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-user"></i> About the Author
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-placeholder me-3">
                            <i class="fas fa-user fa-2x text-muted"></i>
                        </div>
                        <div>
                            <h6 class="mb-1"><?= htmlspecialchars($story['username'] ?? 'Anonymous') ?></h6>
                            <?php if (!empty($story['full_name'])): ?>
                                <small class="text-muted"><?= htmlspecialchars($story['full_name']) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="text-center">
                        <a href="/user/<?= $story['author_id'] ?>" class="btn btn-outline-primary btn-sm">
                            View Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Story Stats -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-line"></i> Story Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <i class="fas fa-eye text-primary"></i>
                                <div class="stat-number" id="stats-views">-</div>
                                <small class="text-muted">Views</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <i class="fas fa-comments text-success"></i>
                                <div class="stat-number"><?= count($comments) ?></div>
                                <small class="text-muted">Comments</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <i class="fas fa-language text-info"></i>
                                <div class="stat-number"><?= count($translations) ?></div>
                                <small class="text-muted">Languages</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Stories -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-book"></i> Related Stories
                    </h6>
                </div>
                <div class="card-body" id="related-stories">
                    <div class="text-center text-muted">
                        <i class="fas fa-spinner fa-spin"></i> Loading related stories...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.story-content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #333;
}

.story-content p {
    margin-bottom: 1.5rem;
}

.avatar-placeholder {
    width: 50px;
    height: 50px;
    background-color: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-item {
    padding: 10px 0;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
}

.comment {
    border-left: 4px solid #007bff;
}

.comment-content {
    font-size: 0.95rem;
    line-height: 1.6;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load view count and related stories
    loadStoryStats();
    loadRelatedStories();
});

function shareStory() {
    if (navigator.share) {
        navigator.share({
            title: <?= json_encode($content['title']) ?>,
            url: window.location.href
        });
    } else {
        // Fallback to copying URL to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Story URL copied to clipboard!');
        });
    }
}

function printStory() {
    window.print();
}

function loadStoryStats() {
    // This would typically load from an API endpoint
    const viewCount = Math.floor(Math.random() * 1000) + 50; // Placeholder
    document.getElementById('view-count').textContent = viewCount;
    document.getElementById('stats-views').textContent = viewCount;
}

function loadRelatedStories() {
    // This would typically load from an API endpoint
    setTimeout(() => {
        document.getElementById('related-stories').innerHTML = `
            <div class="text-center text-muted">
                <p>No related stories found.</p>
                <a href="/stories" class="btn btn-outline-primary btn-sm">Browse All Stories</a>
            </div>
        `;
    }, 1000);
}
</script>
