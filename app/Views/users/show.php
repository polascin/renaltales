<?php if (!defined('ROOT_PATH')) exit; ?>

<div class="user-profile-container">
    <div class="user-profile-header">
        <div class="user-profile-info">
            <div class="user-avatar">
                <div class="avatar-placeholder">
                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                </div>
            </div>
            <div class="user-details">
                <h1><?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></h1>
                <p class="username">@<?= htmlspecialchars($user['username']) ?></p>
                <p class="role"><?= ucfirst(str_replace('_', ' ', $user['role'])) ?></p>
                <div class="user-meta">
                    <span class="meta-item">
                        <strong>Member since:</strong> <?= date('F Y', strtotime($user['created_at'])) ?>
                    </span>
                    <?php if ($user['last_login_at']): ?>
                        <span class="meta-item">
                            <strong>Last active:</strong> <?= date('F j, Y', strtotime($user['last_login_at'])) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if ($is_own_profile): ?>
            <div class="profile-actions">
                <a href="/profile/edit" class="btn btn-primary">Edit Profile</a>
                <a href="/profile/change-password" class="btn btn-secondary">Change Password</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="user-profile-content">
        <div class="main-content">
            <!-- User Statistics -->
            <div class="stats-section">
                <h2>Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?= $story_count ?></div>
                        <div class="stat-label">Stories Published</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= count($stories) ?></div>
                        <div class="stat-label">Recent Stories</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= date('M Y', strtotime($user['created_at'])) ?></div>
                        <div class="stat-label">Joined</div>
                    </div>
                </div>
            </div>

            <!-- User Stories -->
            <div class="stories-section">
                <h2>
                    <?= $is_own_profile ? 'Your Stories' : htmlspecialchars($user['username']) . "'s Stories" ?>
                    <?php if ($story_count > 0): ?>
                        <span class="story-count">(<?= $story_count ?>)</span>
                    <?php endif; ?>
                </h2>
                
                <?php if (!empty($stories)): ?>
                    <div class="stories-grid">
                        <?php foreach ($stories as $story): ?>
                            <div class="story-card">
                                <div class="story-header">
                                    <h3><a href="/story/<?= $story['id'] ?>"><?= htmlspecialchars($story['title']) ?></a></h3>
                                    <span class="story-category"><?= htmlspecialchars($story['category_name']) ?></span>
                                </div>
                                <div class="story-meta">
                                    <span class="story-date">
                                        Published <?= date('M j, Y', strtotime($story['created_at'])) ?>
                                    </span>
                                    <span class="story-status status-<?= $story['status'] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $story['status'])) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($story_count > count($stories)): ?>
                        <div class="view-all-stories">
                            <a href="/stories?author=<?= $user['id'] ?>" class="btn btn-outline">
                                View All Stories (<?= $story_count ?>)
                            </a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-stories">
                        <div class="no-stories-icon">📝</div>
                        <h3>No stories yet</h3>
                        <?php if ($is_own_profile): ?>
                            <p>Start sharing your kidney health journey with the community.</p>
                            <a href="/story/create" class="btn btn-primary">Write Your First Story</a>
                        <?php else: ?>
                            <p><?= htmlspecialchars($user['username']) ?> hasn't shared any stories yet.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="sidebar">
            <!-- Contact/Interaction Section -->
            <?php if (!$is_own_profile): ?>
                <div class="interaction-section">
                    <h3>Connect</h3>
                    <div class="interaction-buttons">
                        <button class="btn btn-primary btn-block" onclick="sendMessage()">Send Message</button>
                        <button class="btn btn-secondary btn-block" onclick="followUser()">Follow</button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Quick Info -->
            <div class="quick-info-section">
                <h3>Quick Info</h3>
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Username:</span>
                        <span class="info-value">@<?= htmlspecialchars($user['username']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Role:</span>
                        <span class="info-value"><?= ucfirst(str_replace('_', ' ', $user['role'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Member since:</span>
                        <span class="info-value"><?= date('F j, Y', strtotime($user['created_at'])) ?></span>
                    </div>
                    <?php if ($user['last_login_at']): ?>
                        <div class="info-item">
                            <span class="info-label">Last active:</span>
                            <span class="info-value"><?= date('F j, Y', strtotime($user['last_login_at'])) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Activity Badge -->
            <div class="activity-badge-section">
                <h3>Community Badge</h3>
                <div class="badge">
                    <?php
                    $badgeIcon = '👤';
                    $badgeText = 'Community Member';
                    $badgeColor = '#95a5a6';
                    
                    switch ($user['role']) {
                        case 'verified_user':
                            $badgeIcon = '✅';
                            $badgeText = 'Verified Member';
                            $badgeColor = '#27ae60';
                            break;
                        case 'translator':
                            $badgeIcon = '🌐';
                            $badgeText = 'Translator';
                            $badgeColor = '#3498db';
                            break;
                        case 'moderator':
                            $badgeIcon = '🛡️';
                            $badgeText = 'Moderator';
                            $badgeColor = '#e67e22';
                            break;
                        case 'admin':
                            $badgeIcon = '👑';
                            $badgeText = 'Administrator';
                            $badgeColor = '#e74c3c';
                            break;
                    }
                    ?>
                    <div class="badge-icon" style="background-color: <?= $badgeColor ?>">
                        <?= $badgeIcon ?>
                    </div>
                    <span class="badge-text"><?= $badgeText ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.user-profile-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.user-profile-header {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.user-profile-info {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.user-avatar {
    flex-shrink: 0;
}

.avatar-placeholder {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2.5rem;
    font-weight: bold;
}

.user-details h1 {
    color: #2c3e50;
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
}

.username {
    color: #7f8c8d;
    margin: 0 0 0.25rem 0;
    font-size: 1.1rem;
    font-weight: 500;
}

.role {
    color: #3498db;
    margin: 0 0 1rem 0;
    font-weight: 500;
}

.user-meta {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.meta-item {
    color: #7f8c8d;
    font-size: 0.9rem;
}

.profile-actions {
    display: flex;
    gap: 1rem;
}

.user-profile-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
}

.stats-section, .stories-section, .interaction-section, .quick-info-section, .activity-badge-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}

.stats-section h2, .stories-section h2 {
    color: #2c3e50;
    margin: 0 0 1.5rem 0;
    font-size: 1.5rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
}

.stat-card {
    text-align: center;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #7f8c8d;
    font-size: 0.9rem;
}

.story-count {
    color: #7f8c8d;
    font-weight: normal;
    font-size: 1rem;
}

.stories-grid {
    display: grid;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.story-card {
    border: 1px solid #e1e8ed;
    border-radius: 8px;
    padding: 1rem;
    transition: border-color 0.3s ease;
}

.story-card:hover {
    border-color: #3498db;
}

.story-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
}

.story-header h3 {
    margin: 0;
    font-size: 1.1rem;
}

.story-header a {
    color: #2c3e50;
    text-decoration: none;
}

.story-header a:hover {
    color: #3498db;
}

.story-category {
    background: #ecf0f1;
    color: #2c3e50;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}

.story-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.85rem;
    color: #7f8c8d;
}

.story-status {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-weight: 500;
    text-transform: capitalize;
}

.status-published {
    background: #d5f4e6;
    color: #27ae60;
}

.status-draft {
    background: #fdf2e9;
    color: #e67e22;
}

.status-pending_review {
    background: #ebf3fd;
    color: #3498db;
}

.view-all-stories {
    text-align: center;
}

.no-stories {
    text-align: center;
    padding: 3rem 1rem;
    color: #7f8c8d;
}

.no-stories-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.no-stories h3 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.no-stories p {
    margin-bottom: 1.5rem;
}

.interaction-section h3, .quick-info-section h3, .activity-badge-section h3 {
    color: #2c3e50;
    margin: 0 0 1rem 0;
    font-size: 1.2rem;
}

.interaction-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #ecf0f1;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    color: #7f8c8d;
    font-size: 0.9rem;
}

.info-value {
    color: #2c3e50;
    font-weight: 500;
    font-size: 0.9rem;
}

.badge {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.badge-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: white;
}

.badge-text {
    color: #2c3e50;
    font-weight: 500;
}

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    font-weight: 500;
    text-align: center;
    text-decoration: none;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.btn-outline {
    background: transparent;
    color: #3498db;
    border: 2px solid #3498db;
}

.btn-outline:hover {
    background: #3498db;
    color: white;
}

.btn-block {
    width: 100%;
}

@media (max-width: 768px) {
    .user-profile-container {
        padding: 1rem;
    }
    
    .user-profile-header {
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .user-profile-info {
        flex-direction: column;
        text-align: center;
    }
    
    .user-profile-content {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    }
    
    .story-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .story-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
    
    .profile-actions {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
function sendMessage() {
    // Implementation for sending message functionality
    alert('Message functionality would be implemented here.');
}

function followUser() {
    // Implementation for follow/unfollow functionality
    alert('Follow functionality would be implemented here.');
}
</script>
