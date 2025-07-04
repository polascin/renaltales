<?php
/**
 * Story Edit View - Edit existing story
 */
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="page-title"><?= htmlspecialchars($page_title) ?></h1>
                <div>
                    <a href="/story/<?= $story['id'] ?>" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-eye"></i> View Story
                    </a>
                    <a href="/stories" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Stories
                    </a>
                </div>
            </div>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <h6 class="alert-heading">Please fix the following errors:</h6>
                    <ul class="mb-0">
                        <?php foreach ($errors as $field => $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Story Information Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Story Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">Status:</small>
                            <div class="mb-2">
                                <span class="badge <?= $story['status'] === 'published' ? 'bg-success' : ($story['status'] === 'pending_review' ? 'bg-warning' : 'bg-secondary') ?>">
                                    <?= ucfirst(str_replace('_', ' ', $story['status'])) ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Original Language:</small>
                            <div class="mb-2">
                                <span class="badge bg-light text-dark">
                                    <?= strtoupper($story['original_language']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Created:</small>
                            <div><?= date('F j, Y \a\t g:i A', strtotime($story['created_at'])) ?></div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Last Updated:</small>
                            <div><?= date('F j, Y \a\t g:i A', strtotime($story['updated_at'])) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Story Edit Form -->
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="/story/<?= $story['id'] ?>/update">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" 
                                   id="title" name="title" maxlength="255" required
                                   value="<?= htmlspecialchars($old_input['title'] ?? $content['title']) ?>"
                                   placeholder="Enter your story title">
                            <?php if (isset($errors['title'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['title']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Category and Access Level Row -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" 
                                        id="category_id" name="category_id" required>
                                    <option value="">Select a category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>" 
                                                <?= (($old_input['category_id'] ?? $story['category_id']) == $category['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['category_id'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['category_id']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="access_level" class="form-label">Access Level</label>
                                <select class="form-select <?= isset($errors['access_level']) ? 'is-invalid' : '' ?>" 
                                        id="access_level" name="access_level">
                                    <?php foreach ($access_levels as $level => $description): ?>
                                        <option value="<?= htmlspecialchars($level) ?>" 
                                                <?= (($old_input['access_level'] ?? $story['access_level']) === $level) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($description['name']) ?> - <?= htmlspecialchars($description['description']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['access_level'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['access_level']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="mb-3">
                            <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea class="form-control <?= isset($errors['content']) ? 'is-invalid' : '' ?>" 
                                      id="content" name="content" rows="15" required
                                      placeholder="Write your story here... (minimum 100 characters)"><?= htmlspecialchars($old_input['content'] ?? $content['content']) ?></textarea>
                            <div class="form-text">
                                <span id="word-count">0</span> words | 
                                <span id="char-count">0</span> characters
                            </div>
                            <?php if (isset($errors['content'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['content']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Tags -->
                        <div class="mb-3">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text" class="form-control" id="tags" name="tags" 
                                   value="<?= htmlspecialchars($old_input['tags'] ?? $tags) ?>"
                                   placeholder="Enter tags separated by commas (e.g., dialysis, motivation, health)">
                            <div class="form-text">Add relevant tags to help others find your story</div>
                        </div>

                        <!-- Revision Notes -->
                        <div class="mb-3">
                            <label for="revision_notes" class="form-label">Revision Notes</label>
                            <textarea class="form-control" id="revision_notes" name="revision_notes" rows="3"
                                      placeholder="Describe what you changed in this revision (optional)..."><?= htmlspecialchars($old_input['revision_notes'] ?? '') ?></textarea>
                            <div class="form-text">These notes help track changes and are visible in the revision history</div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <?php if ($story['status'] === 'draft'): ?>
                                    <button type="submit" name="submit_for_review" class="btn btn-warning">
                                        <i class="fas fa-paper-plane"></i> Submit for Review
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div>
                                <a href="/story/<?= $story['id'] ?>" class="btn btn-light me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Story
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Revision History -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history"></i> Revision History
                    </h6>
                </div>
                <div class="card-body">
                    <div id="revision-history" class="text-center text-muted">
                        <i class="fas fa-spinner fa-spin"></i> Loading revision history...
                    </div>
                </div>
            </div>

            <!-- Help Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-question-circle"></i> Editing Guidelines
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Content Updates:</h6>
                            <ul class="small">
                                <li>Major changes will create a revision</li>
                                <li>Grammar fixes don't require revision notes</li>
                                <li>Content changes should be documented</li>
                                <li>Maintain the story's original intent</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Status Information:</h6>
                            <ul class="small">
                                <li><strong>Draft:</strong> Only you can see it</li>
                                <li><strong>Pending Review:</strong> Awaiting moderation</li>
                                <li><strong>Published:</strong> Visible to readers</li>
                                <li><strong>Rejected:</strong> Needs changes before review</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contentTextarea = document.getElementById('content');
    const wordCountSpan = document.getElementById('word-count');
    const charCountSpan = document.getElementById('char-count');

    function updateCounts() {
        const text = contentTextarea.value;
        const words = text.trim() === '' ? 0 : text.trim().split(/\s+/).length;
        const chars = text.length;
        
        wordCountSpan.textContent = words;
        charCountSpan.textContent = chars;
        
        // Add visual feedback for minimum length
        if (chars < 100) {
            charCountSpan.parentElement.classList.add('text-warning');
            charCountSpan.parentElement.classList.remove('text-success');
        } else {
            charCountSpan.parentElement.classList.add('text-success');
            charCountSpan.parentElement.classList.remove('text-warning');
        }
    }

    contentTextarea.addEventListener('input', updateCounts);
    updateCounts(); // Initial count

    // Load revision history
    loadRevisionHistory();

    // Simple rich text editor enhancement (optional)
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#content',
            height: 400,
            menubar: false,
            plugins: [
                'advlist autolink lists link charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            setup: function (editor) {
                editor.on('keyup', function () {
                    updateCounts();
                });
            }
        });
    }
});

function loadRevisionHistory() {
    // This would typically load from an API endpoint
    setTimeout(() => {
        document.getElementById('revision-history').innerHTML = `
            <div class="text-center text-muted">
                <p>No revision history available yet.</p>
                <small>Revisions will appear here after you make changes to your story.</small>
            </div>
        `;
    }, 1000);
}
</script>
