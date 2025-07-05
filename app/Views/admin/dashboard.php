<?php if (!defined('ROOT_PATH')) exit; ?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-kidney">
                        <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                    </h1>
                    <p class="text-muted mb-0">Overview of your RenalTales platform</p>
                </div>
                <div>
                    <span class="badge bg-success fs-6">
                        <i class="fas fa-circle me-1"></i> System Online
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health Alert -->
    <?php if (isset($system_health['storage']) && $system_health['storage'] === 'warning'): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Storage Warning:</strong> Disk usage is at <?= $system_health['storage_usage'] ?>%. 
            Consider cleaning up old files or expanding storage.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Users Card -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                                <i class="fas fa-users fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="h4 mb-1"><?= number_format($stats['users']['total']) ?></h3>
                            <p class="text-muted mb-0">Total Users</p>
                            <small class="text-success">
                                <i class="fas fa-arrow-up"></i> 
                                +<?= $stats['users']['new_this_week'] ?> this week
                            </small>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="row text-center">
                            <div class="col">
                                <small class="text-muted d-block">Verified</small>
                                <strong><?= $stats['users']['verified'] ?></strong>
                            </div>
                            <div class="col">
                                <small class="text-muted d-block">Admins</small>
                                <strong><?= $stats['users']['admins'] ?></strong>
                            </div>
                            <div class="col">
                                <small class="text-muted d-block">Moderators</small>
                                <strong><?= $stats['users']['moderators'] ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stories Card -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle p-3">
                                <i class="fas fa-book fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="h4 mb-1"><?= number_format($stats['stories']['total']) ?></h3>
                            <p class="text-muted mb-0">Total Stories</p>
                            <small class="text-success">
                                <i class="fas fa-arrow-up"></i> 
                                +<?= $stats['stories']['new_this_week'] ?> this week
                            </small>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="row text-center">
                            <div class="col">
                                <small class="text-muted d-block">Published</small>
                                <strong class="text-success"><?= $stats['stories']['published'] ?></strong>
                            </div>
                            <div class="col">
                                <small class="text-muted d-block">Pending</small>
                                <strong class="text-warning"><?= $stats['stories']['pending'] ?></strong>
                            </div>
                            <div class="col">
                                <small class="text-muted d-block">Drafts</small>
                                <strong class="text-secondary"><?= $stats['stories']['drafts'] ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comments Card -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 text-info rounded-circle p-3">
                                <i class="fas fa-comments fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="h4 mb-1"><?= number_format($stats['comments']['total']) ?></h3>
                            <p class="text-muted mb-0">Total Comments</p>
                            <small class="text-success">
                                <i class="fas fa-arrow-up"></i> 
                                +<?= $stats['comments']['last_24h'] ?> last 24h
                            </small>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="row text-center">
                            <div class="col">
                                <small class="text-muted d-block">Approved</small>
                                <strong class="text-success"><?= $stats['comments']['approved'] ?></strong>
                            </div>
                            <div class="col">
                                <small class="text-muted d-block">Pending</small>
                                <strong class="text-warning"><?= $stats['comments']['pending'] ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Health Card -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3">
                                <i class="fas fa-server fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="h4 mb-1">
                                <?php if ($system_health['database'] === 'healthy'): ?>
                                    <span class="text-success">Healthy</span>
                                <?php else: ?>
                                    <span class="text-danger">Issues</span>
                                <?php endif; ?>
                            </h3>
                            <p class="text-muted mb-0">System Status</p>
                            <small class="text-muted">
                                DB: <?= round($stats['system']['database_size'], 1) ?>MB
                            </small>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="row text-center">
                            <div class="col">
                                <small class="text-muted d-block">Sessions</small>
                                <strong><?= $stats['system']['active_sessions'] ?></strong>
                            </div>
                            <div class="col">
                                <small class="text-muted d-block">Errors (24h)</small>
                                <strong class="<?= $stats['system']['error_count_24h'] > 0 ? 'text-danger' : 'text-success' ?>">
                                    <?= $stats['system']['error_count_24h'] ?>
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Pending Content Review -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clock text-warning me-2"></i>
                            Pending Content Review
                        </h5>
                        <a href="<?= Router::url('admin/moderation') ?>" class="btn btn-sm btn-outline-primary">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Pending Stories -->
                    <?php if (!empty($pending_content['stories'])): ?>
                        <h6 class="text-muted mb-3">Stories Awaiting Review</h6>
                        <?php foreach ($pending_content['stories'] as $story): ?>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <a href="<?= Router::url("story/{$story['id']}") ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($story['title'] ?? 'Untitled') ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        by <?= htmlspecialchars($story['username']) ?> • 
                                        <?= date('M j, Y', strtotime($story['created_at'])) ?>
                                    </small>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-success btn-sm approve-story" data-story-id="<?= $story['id'] ?>">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm reject-story" data-story-id="<?= $story['id'] ?>">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                            <p class="text-muted mb-0">No stories pending review</p>
                        </div>
                    <?php endif; ?>

                    <!-- Pending Comments -->
                    <?php if (!empty($pending_content['comments'])): ?>
                        <h6 class="text-muted mb-3 mt-4">Comments Awaiting Moderation</h6>
                        <?php foreach ($pending_content['comments'] as $comment): ?>
                            <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                                <div class="flex-grow-1">
                                    <p class="mb-1"><?= htmlspecialchars(substr($comment['content'], 0, 100)) ?>...</p>
                                    <small class="text-muted">
                                        by <?= htmlspecialchars($comment['username']) ?> on 
                                        <a href="<?= Router::url("story/{$comment['story_id']}") ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($comment['story_title'] ?? 'Story') ?>
                                        </a>
                                    </small>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-success btn-sm approve-comment" data-comment-id="<?= $comment['id'] ?>">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm reject-comment" data-comment-id="<?= $comment['id'] ?>">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history text-info me-2"></i>
                        Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_activity)): ?>
                        <div class="timeline">
                            <?php foreach ($recent_activity as $activity): ?>
                                <div class="d-flex py-2">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-light rounded-circle p-2 text-center" style="width: 32px; height: 32px;">
                                            <?php
                                            $icon = match($activity['action']) {
                                                'story_created', 'story_updated' => 'fas fa-book',
                                                'story_approved', 'story_published' => 'fas fa-check-circle',
                                                'story_rejected' => 'fas fa-times-circle',
                                                'user_registered' => 'fas fa-user-plus',
                                                'user_role_changed' => 'fas fa-user-cog',
                                                'comment_posted' => 'fas fa-comment',
                                                'login' => 'fas fa-sign-in-alt',
                                                default => 'fas fa-info-circle'
                                            };
                                            ?>
                                            <i class="<?= $icon ?> fa-sm"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <p class="mb-1 text-sm">
                                                    <?= htmlspecialchars($activity['description']) ?>
                                                </p>
                                                <small class="text-muted">
                                                    by <?= htmlspecialchars($activity['username'] ?? 'System') ?> • 
                                                    <?= date('M j, H:i', strtotime($activity['created_at'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-history text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">No recent activity</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt text-warning me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="<?= Router::url('admin/moderation') ?>" class="btn btn-outline-primary w-100">
                                <i class="fas fa-gavel me-2"></i>
                                Content Moderation
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= Router::url('admin/users') ?>" class="btn btn-outline-success w-100">
                                <i class="fas fa-users-cog me-2"></i>
                                Manage Users
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= Router::url('admin/statistics') ?>" class="btn btn-outline-info w-100">
                                <i class="fas fa-chart-bar me-2"></i>
                                View Analytics
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= Router::url('admin/settings') ?>" class="btn btn-outline-warning w-100">
                                <i class="fas fa-cog me-2"></i>
                                System Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quick approve/reject stories
    document.querySelectorAll('.approve-story').forEach(btn => {
        btn.addEventListener('click', function() {
            const storyId = this.dataset.storyId;
            approveStory(storyId);
        });
    });

    document.querySelectorAll('.reject-story').forEach(btn => {
        btn.addEventListener('click', function() {
            const storyId = this.dataset.storyId;
            rejectStory(storyId);
        });
    });

    function approveStory(storyId) {
        if (!confirm('Approve this story?')) return;

        fetch('<?= Router::url("admin/approve-story") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            },
            body: `story_id=${storyId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Story approved successfully!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert(data.message || 'Failed to approve story', 'error');
            }
        })
        .catch(error => {
            showAlert('Network error occurred', 'error');
        });
    }

    function rejectStory(storyId) {
        const reason = prompt('Please provide a reason for rejection:');
        if (!reason) return;

        fetch('<?= Router::url("admin/reject-story") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            },
            body: `story_id=${storyId}&reason=${encodeURIComponent(reason)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Story rejected', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert(data.message || 'Failed to reject story', 'error');
            }
        })
        .catch(error => {
            showAlert('Network error occurred', 'error');
        });
    }

    function showAlert(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', alertHtml);
    }
});
</script>

<style>
.timeline {
    max-height: 400px;
    overflow-y: auto;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.text-sm {
    font-size: 0.875rem;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
</style>
