<?php if (!defined('ROOT_PATH')) exit; ?>

<div class="users-index-container">
    <div class="users-header">
        <h1>Community Members</h1>
        <p>Connect with other members of our kidney health community</p>
    </div>

    <div class="users-search">
        <form method="GET" action="/users" class="search-form">
            <div class="search-input-container">
                <input 
                    type="text" 
                    name="search" 
                    value="<?= htmlspecialchars($search) ?>" 
                    placeholder="Search by username or name..."
                    class="search-input"
                >
                <button type="submit" class="search-btn">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="/users" class="clear-search">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="users-grid">
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
                <div class="user-card">
                    <div class="user-avatar">
                        <div class="avatar-placeholder">
                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                        </div>
                    </div>
                    
                    <div class="user-info">
                        <h3><?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></h3>
                        <p class="username">@<?= htmlspecialchars($user['username']) ?></p>
                        <p class="role"><?= ucfirst(str_replace('_', ' ', $user['role'])) ?></p>
                        
                        <div class="user-stats">
                            <span class="stat">
                                <strong>Joined:</strong> <?= date('M Y', strtotime($user['created_at'])) ?>
                            </span>
                            <?php if ($user['last_login_at']): ?>
                                <span class="stat">
                                    <strong>Last seen:</strong> <?= date('M d, Y', strtotime($user['last_login_at'])) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="user-actions">
                        <a href="/user/<?= $user['id'] ?>" class="btn btn-primary btn-sm">View Profile</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-users">
                <?php if (!empty($search)): ?>
                    <div class="no-results-icon">🔍</div>
                    <h3>No users found</h3>
                    <p>No users match your search criteria "<?= htmlspecialchars($search) ?>"</p>
                    <a href="/users" class="btn btn-primary">View All Users</a>
                <?php else: ?>
                    <div class="no-results-icon">👥</div>
                    <h3>No users yet</h3>
                    <p>Be the first to join our community!</p>
                    <a href="/register" class="btn btn-primary">Join Now</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($users) && $pagination['pages'] > 1): ?>
        <div class="pagination">
            <?php if ($pagination['has_prev']): ?>
                <a href="/users?page=<?= $pagination['current_page'] - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="btn btn-secondary">Previous</a>
            <?php endif; ?>
            
            <div class="page-numbers">
                <?php
                $startPage = max(1, $pagination['current_page'] - 2);
                $endPage = min($pagination['pages'], $pagination['current_page'] + 2);
                
                if ($startPage > 1): ?>
                    <a href="/users?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="page-number">1</a>
                    <?php if ($startPage > 2): ?>
                        <span class="page-ellipsis">...</span>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <?php if ($i === $pagination['current_page']): ?>
                        <span class="page-number active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="/users?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="page-number"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($endPage < $pagination['pages']): ?>
                    <?php if ($endPage < $pagination['pages'] - 1): ?>
                        <span class="page-ellipsis">...</span>
                    <?php endif; ?>
                    <a href="/users?page=<?= $pagination['pages'] ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="page-number"><?= $pagination['pages'] ?></a>
                <?php endif; ?>
            </div>
            
            <?php if ($pagination['has_next']): ?>
                <a href="/users?page=<?= $pagination['current_page'] + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="btn btn-secondary">Next</a>
            <?php endif; ?>
        </div>
        
        <div class="pagination-info">
            Showing <?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?> to 
            <?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) ?> of 
            <?= $pagination['total'] ?> users
        </div>
    <?php endif; ?>
</div>

<style>
.users-index-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.users-header {
    text-align: center;
    margin-bottom: 2rem;
}

.users-header h1 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.users-header p {
    color: #7f8c8d;
    margin: 0;
}

.users-search {
    margin-bottom: 2rem;
}

.search-form {
    max-width: 500px;
    margin: 0 auto;
}

.search-input-container {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.search-input {
    flex: 1;
    padding: 0.75rem;
    border: 2px solid #e1e8ed;
    border-radius: 8px;
    font-size: 1rem;
}

.search-input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.search-btn {
    padding: 0.75rem 1.5rem;
    background: #3498db;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: background 0.3s ease;
}

.search-btn:hover {
    background: #2980b9;
}

.clear-search {
    color: #7f8c8d;
    text-decoration: none;
    font-size: 0.9rem;
}

.clear-search:hover {
    color: #e74c3c;
}

.users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.user-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.user-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.user-avatar {
    text-align: center;
    margin-bottom: 1rem;
}

.avatar-placeholder {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    font-weight: bold;
}

.user-info {
    text-align: center;
    margin-bottom: 1rem;
}

.user-info h3 {
    color: #2c3e50;
    margin: 0 0 0.25rem 0;
    font-size: 1.1rem;
}

.username {
    color: #7f8c8d;
    margin: 0 0 0.25rem 0;
    font-weight: 500;
}

.role {
    color: #3498db;
    margin: 0 0 1rem 0;
    font-size: 0.9rem;
    font-weight: 500;
}

.user-stats {
    font-size: 0.85rem;
    color: #7f8c8d;
}

.stat {
    display: block;
    margin-bottom: 0.25rem;
}

.user-actions {
    text-align: center;
}

.no-users {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem 1rem;
    color: #7f8c8d;
}

.no-results-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.no-users h3 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.no-users p {
    margin-bottom: 1.5rem;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.page-numbers {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.page-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border: 1px solid #e1e8ed;
    border-radius: 6px;
    text-decoration: none;
    color: #2c3e50;
    font-weight: 500;
    transition: all 0.3s ease;
}

.page-number:hover {
    background: #3498db;
    color: white;
    border-color: #3498db;
}

.page-number.active {
    background: #3498db;
    color: white;
    border-color: #3498db;
}

.page-ellipsis {
    color: #7f8c8d;
    font-weight: 500;
}

.pagination-info {
    text-align: center;
    color: #7f8c8d;
    font-size: 0.9rem;
}

.btn {
    display: inline-block;
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    font-weight: 500;
    text-align: center;
    text-decoration: none;
    border: none;
    border-radius: 6px;
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

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.8rem;
}

@media (max-width: 768px) {
    .users-index-container {
        padding: 1rem;
    }
    
    .users-grid {
        grid-template-columns: 1fr;
    }
    
    .search-input-container {
        flex-direction: column;
    }
    
    .search-input-container .search-btn {
        width: 100%;
    }
    
    .pagination {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .page-numbers {
        order: -1;
    }
}
</style>
