<?php if (!defined('ROOT_PATH')) exit; ?>

<div class="container">
    <!-- Profile Header -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-lg bg-kidney text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                                <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?>
                            </div>
                        </div>
                        <div class="col">
                            <h1 class="h3 mb-1"><?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></h1>
                            <p class="text-muted mb-1">@<?= htmlspecialchars($user['username']) ?></p>
                            <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'moderator' ? 'warning' : 'secondary') ?>">
                                <?= ucfirst(str_replace('_', ' ', $user['role'])) ?>
                            </span>
                            <p class="text-muted small mt-2 mb-0">
                                <i class="fas fa-calendar-alt"></i> 
                                <?= $t['profile.member_since'] ?? 'Member since' ?> <?= date('F Y', strtotime($user['created_at'])) ?>
                            </p>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex gap-2">
                                <a href="<?= Router::url('profile/edit') ?>" class="btn btn-outline-kidney">
                                    <i class="fas fa-edit"></i> <?= $t['profile.edit'] ?? 'Edit Profile' ?>
                                </a>
                                <a href="<?= Router::url('profile/change-password') ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-lock"></i> <?= $t['profile.change_password'] ?? 'Change Password' ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($success) && $success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="row">
        <!-- Left Column - Statistics and Activity -->
        <div class="col-lg-8">
            <!-- Statistics -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar text-kidney"></i> 
                        <?= $t['profile.statistics'] ?? 'Statistics' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <div class="h2 text-kidney mb-1"><?= $story_count ?? 0 ?></div>
                                <div class="text-muted small"><?= $t['profile.stories_published'] ?? 'Stories Published' ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <div class="h2 text-kidney mb-1"><?= count($recent_activity ?? []) ?></div>
                                <div class="text-muted small"><?= $t['profile.recent_activities'] ?? 'Recent Activities' ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <div class="h6 text-kidney mb-1">
                                    <?= isset($user['last_login_at']) && $user['last_login_at'] ? date('M d', strtotime($user['last_login_at'])) : ($t['profile.never'] ?? 'Never') ?>
                                </div>
                                <div class="text-muted small"><?= $t['profile.last_login'] ?? 'Last Login' ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history text-kidney"></i> 
                        <?= $t['profile.recent_activity'] ?? 'Recent Activity' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_activity)): ?>
                        <div class="timeline">
                            <?php foreach ($recent_activity as $activity): ?>
                                <div class="d-flex mb-3 pb-3 border-bottom">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-kidney rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-<?= match($activity['action'] ?? 'default') {
                                                'login' => 'sign-in-alt',
                                                'story_create' => 'plus',
                                                'story_update' => 'edit',
                                                'profile_update' => 'user-edit',
                                                'password_change' => 'lock',
                                                default => 'circle'
                                            } ?> text-white fa-sm"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1"><?= htmlspecialchars($activity['description']) ?></p>
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> 
                                            <?= date('M d, Y \a\t g:i A', strtotime($activity['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                            <p class="text-muted"><?= $t['profile.no_activity'] ?? 'No recent activity found.' ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column - Settings and Actions -->
        <div class="col-lg-4">
            <!-- Quick Settings -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cog text-kidney"></i> 
                        <?= $t['profile.quick_settings'] ?? 'Quick Settings' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Language Preference -->
                    <div class="mb-3">
                        <label for="language-select" class="form-label">
                            <?= $t['profile.preferred_language'] ?? 'Preferred Language' ?>
                        </label>
                        <select id="language-select" class="form-select" onchange="changeLanguage(this.value)">
                            <?php if (isset($supported_languages)): ?>
                                <?php foreach ($supported_languages as $code => $name): ?>
                                    <option value="<?= $code ?>" <?= ($user['language_preference'] ?? 'en') === $code ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Email Verification Status -->
                    <div class="mb-3">
                        <label class="form-label"><?= $t['profile.email_verification'] ?? 'Email Verification' ?></label>
                        <div class="d-flex align-items-center justify-content-between">
                            <?php if (isset($user['email_verified_at']) && $user['email_verified_at']): ?>
                                <span class="text-success">
                                    <i class="fas fa-check-circle"></i> <?= $t['profile.verified'] ?? 'Verified' ?>
                                </span>
                            <?php else: ?>
                                <span class="text-warning">
                                    <i class="fas fa-exclamation-triangle"></i> <?= $t['profile.not_verified'] ?? 'Not Verified' ?>
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-kidney" onclick="resendVerification()">
                                    <?= $t['profile.resend'] ?? 'Resend' ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Two-Factor Authentication -->
                    <?php if (isset($user['two_factor_enabled'])): ?>
                        <div class="mb-3">
                            <label class="form-label"><?= $t['profile.two_factor_auth'] ?? 'Two-Factor Authentication' ?></label>
                            <div class="d-flex align-items-center justify-content-between">
                                <?php if ($user['two_factor_enabled']): ?>
                                    <span class="text-success">
                                        <i class="fas fa-shield-alt"></i> <?= $t['profile.enabled'] ?? 'Enabled' ?>
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="disable2FA()">
                                        <?= $t['profile.disable'] ?? 'Disable' ?>
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">
                                        <i class="fas fa-times-circle"></i> <?= $t['profile.disabled'] ?? 'Disabled' ?>
                                    </span>
                                    <button type="button" class="btn btn-sm btn-kidney" onclick="enable2FA()">
                                        <?= $t['profile.enable'] ?? 'Enable' ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card shadow-sm border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <?= $t['profile.danger_zone'] ?? 'Danger Zone' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-danger w-100" onclick="showDeleteConfirmation()">
                        <i class="fas fa-trash-alt"></i> <?= $t['profile.delete_account'] ?? 'Delete Account' ?>
                    </button>
                    <p class="small text-muted mt-2 mb-0">
                        <?= $t['profile.delete_warning'] ?? 'This action cannot be undone. All your stories and data will be permanently deleted.' ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-danger"></i> 
                    <?= $t['profile.delete_account'] ?? 'Delete Account' ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <strong><?= $t['profile.warning'] ?? 'Warning:' ?></strong> 
                    <?= $t['profile.delete_warning'] ?? 'This will permanently delete your account and all associated data.' ?>
                </div>
                <form method="POST" action="<?= Router::url('profile/delete') ?>" onsubmit="return confirmDeletion()">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    
                    <div class="mb-3">
                        <label for="delete-password" class="form-label"><?= $t['profile.enter_password'] ?? 'Enter your password to confirm:' ?></label>
                        <input type="password" id="delete-password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="delete-confirmation" class="form-label"><?= $t['profile.type_delete'] ?? 'Type "DELETE" to confirm:' ?></label>
                        <input type="text" id="delete-confirmation" name="confirmation" class="form-control" required>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <?= $t['btn.cancel'] ?? 'Cancel' ?>
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash-alt"></i> <?= $t['profile.delete_account'] ?? 'Delete Account' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.btn-outline-kidney {
    border-color: #2c5282;
    color: #2c5282;
}
.btn-outline-kidney:hover {
    background-color: #2c5282;
    border-color: #2c5282;
    color: white;
}
</style>

<script>
function changeLanguage(language) {
    fetch('<?= Router::url('profile/set-language') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `language=${language}&csrf_token=<?= htmlspecialchars($csrf_token ?? '') ?>`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to apply language changes
        } else {
            alert('Error updating language preference: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating language preference.');
    });
}

function showDeleteConfirmation() {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

function confirmDeletion() {
    const confirmation = document.getElementById('delete-confirmation').value;
    if (confirmation !== 'DELETE') {
        alert('<?= $t['profile.type_delete_exactly'] ?? 'Please type "DELETE" exactly to confirm account deletion.' ?>');
        return false;
    }
    return confirm('<?= $t['profile.final_confirmation'] ?? 'Are you absolutely sure you want to delete your account? This action cannot be undone.' ?>');
}

function resendVerification() {
    // Implementation for resending verification email
    alert('<?= $t['profile.verification_functionality'] ?? 'Verification email functionality would be implemented here.' ?>');
}

function enable2FA() {
    // Implementation for enabling 2FA
    alert('<?= $t['profile.2fa_setup'] ?? 'Two-factor authentication setup would be implemented here.' ?>');
}

function disable2FA() {
    // Implementation for disabling 2FA
    if (confirm('<?= $t['profile.disable_2fa_confirm'] ?? 'Are you sure you want to disable two-factor authentication?' ?>')) {
        alert('<?= $t['profile.2fa_disable'] ?? 'Two-factor authentication disable functionality would be implemented here.' ?>');
    }
}
</script>
