<?php if (!defined('ROOT_PATH')) exit; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-kidney text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-pen"></i> <?= $t['stories.create'] ?? 'Share Your Story' ?>
                    </h3>
                    <p class="mb-0 opacity-75"><?= $t['stories.create_subtitle'] ?? 'Share your experience and inspire others in the kidney community' ?></p>
                </div>
                <div class="card-body">
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= Router::url('story/create') ?>" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        
                        <div class="mb-4">
                            <label for="title" class="form-label">
                                <?= $t['stories.title'] ?? 'Story Title' ?> <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="title" 
                                name="title" 
                                class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                                value="<?= htmlspecialchars($old_input['title'] ?? '') ?>"
                                placeholder="<?= $t['stories.title_placeholder'] ?? 'Give your story a compelling title' ?>"
                                required
                                maxlength="255"
                            >
                            <?php if (isset($errors['title'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['title']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">
                                    <?= $t['stories.category'] ?? 'Category' ?> <span class="text-danger">*</span>
                                </label>
                                <select 
                                    id="category_id" 
                                    name="category_id" 
                                    class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>"
                                    required
                                >
                                    <option value=""><?= $t['stories.select_category'] ?? 'Select a category' ?></option>
                                    <?php if (isset($categories)): ?>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>" <?= ($old_input['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($category['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <?php if (isset($errors['category_id'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['category_id']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="language" class="form-label">
                                    <?= $t['stories.language'] ?? 'Language' ?> <span class="text-danger">*</span>
                                </label>
                                <select 
                                    id="language" 
                                    name="language" 
                                    class="form-select <?= isset($errors['language']) ? 'is-invalid' : '' ?>"
                                    required
                                >
                                    <?php if (isset($supported_languages)): ?>
                                        <?php foreach ($supported_languages as $code => $name): ?>
                                            <option value="<?= $code ?>" <?= ($old_input['language'] ?? $lang ?? 'en') === $code ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="en" selected>English</option>
                                        <option value="sk">Slovak</option>
                                    <?php endif; ?>
                                </select>
                                <?php if (isset($errors['language'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['language']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="excerpt" class="form-label">
                                <?= $t['stories.excerpt'] ?? 'Story Summary' ?>
                            </label>
                            <textarea 
                                id="excerpt" 
                                name="excerpt" 
                                class="form-control <?= isset($errors['excerpt']) ? 'is-invalid' : '' ?>"
                                rows="3"
                                maxlength="500"
                                placeholder="<?= $t['stories.excerpt_placeholder'] ?? 'Brief summary of your story (optional, but recommended)' ?>"
                            ><?= htmlspecialchars($old_input['excerpt'] ?? '') ?></textarea>
                            <div class="form-text">
                                <span id="excerpt-count">0</span>/500 <?= $t['characters'] ?? 'characters' ?>
                            </div>
                            <?php if (isset($errors['excerpt'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['excerpt']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label for="content" class="form-label">
                                <?= $t['stories.content'] ?? 'Your Story' ?> <span class="text-danger">*</span>
                            </label>
                            <textarea 
                                id="content" 
                                name="content" 
                                class="form-control <?= isset($errors['content']) ? 'is-invalid' : '' ?>"
                                rows="15"
                                required
                                placeholder="<?= $t['stories.content_placeholder'] ?? 'Share your journey, experiences, challenges, and victories. Your story can inspire and help others facing similar situations.' ?>"
                            ><?= htmlspecialchars($old_input['content'] ?? '') ?></textarea>
                            <div class="form-text">
                                <?= $t['stories.content_help'] ?? 'Take your time to share your story. Be authentic and honest - your experiences matter.' ?>
                            </div>
                            <?php if (isset($errors['content'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['content']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label for="tags" class="form-label">
                                <?= $t['stories.tags'] ?? 'Tags' ?>
                            </label>
                            <input 
                                type="text" 
                                id="tags" 
                                name="tags" 
                                class="form-control <?= isset($errors['tags']) ? 'is-invalid' : '' ?>"
                                value="<?= htmlspecialchars($old_input['tags'] ?? '') ?>"
                                placeholder="<?= $t['stories.tags_placeholder'] ?? 'dialysis, transplant, hope, family (separate with commas)' ?>"
                            >
                            <div class="form-text">
                                <?= $t['stories.tags_help'] ?? 'Add relevant tags to help others find your story. Separate tags with commas.' ?>
                            </div>
                            <?php if (isset($errors['tags'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($errors['tags']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input 
                                    type="checkbox" 
                                    id="anonymous" 
                                    name="anonymous" 
                                    class="form-check-input"
                                    value="1"
                                    <?= ($old_input['anonymous'] ?? false) ? 'checked' : '' ?>
                                >
                                <label class="form-check-label" for="anonymous">
                                    <?= $t['stories.anonymous'] ?? 'Publish anonymously' ?>
                                </label>
                                <div class="form-text">
                                    <?= $t['stories.anonymous_help'] ?? 'Your username will not be displayed publicly with this story.' ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input 
                                    type="checkbox" 
                                    id="allow_comments" 
                                    name="allow_comments" 
                                    class="form-check-input"
                                    value="1"
                                    <?= ($old_input['allow_comments'] ?? true) ? 'checked' : '' ?>
                                >
                                <label class="form-check-label" for="allow_comments">
                                    <?= $t['stories.allow_comments'] ?? 'Allow comments on this story' ?>
                                </label>
                                <div class="form-text">
                                    <?= $t['stories.comments_help'] ?? 'Let others share their thoughts and support on your story.' ?>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= Router::url('stories') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> <?= $t['btn.cancel'] ?? 'Cancel' ?>
                            </a>
                            <div>
                                <button type="submit" name="action" value="draft" class="btn btn-outline-kidney me-2">
                                    <i class="fas fa-save"></i> <?= $t['stories.save_draft'] ?? 'Save as Draft' ?>
                                </button>
                                <button type="submit" name="action" value="publish" class="btn btn-kidney">
                                    <i class="fas fa-share"></i> <?= $t['stories.publish'] ?? 'Publish Story' ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card-header.bg-kidney {
    background-color: #2c5282 !important;
}

.btn-kidney {
    background-color: #2c5282;
    border-color: #2c5282;
    color: white;
}

.btn-kidney:hover {
    background-color: #2a4d7a;
    border-color: #2a4d7a;
    color: white;
}

.btn-outline-kidney {
    border-color: #2c5282;
    color: #2c5282;
}

.btn-outline-kidney:hover {
    background-color: #2c5282;
    border-color: #2c5282;
    color: white;
}

#content {
    min-height: 300px;
    resize: vertical;
}

.form-text {
    font-size: 0.875rem;
}
</style>

<script>
// Character counter for excerpt
document.addEventListener('DOMContentLoaded', function() {
    const excerptTextarea = document.getElementById('excerpt');
    const excerptCount = document.getElementById('excerpt-count');
    
    function updateExcerptCount() {
        const count = excerptTextarea.value.length;
        excerptCount.textContent = count;
        
        if (count > 450) {
            excerptCount.style.color = '#dc3545';
        } else if (count > 400) {
            excerptCount.style.color = '#fd7e14';
        } else {
            excerptCount.style.color = '#6c757d';
        }
    }
    
    excerptTextarea.addEventListener('input', updateExcerptCount);
    updateExcerptCount(); // Initialize count
    
    // Auto-focus title field
    document.getElementById('title').focus();
    
    // Form validation feedback
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const content = document.getElementById('content').value.trim();
        const category = document.getElementById('category_id').value;
        
        if (!title || !content || !category) {
            e.preventDefault();
            alert('<?= $t['stories.validation_error'] ?? 'Please fill in all required fields before submitting.' ?>');
            return false;
        }
        
        // Show loading state
        const submitButtons = form.querySelectorAll('button[type="submit"]');
        submitButtons.forEach(btn => {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + (btn.name === 'draft' ? '<?= $t['stories.saving'] ?? 'Saving...' ?>' : '<?= $t['stories.publishing'] ?? 'Publishing...' ?>');
        });
    });
});
</script>
