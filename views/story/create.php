<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Story - Renal Tales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .story-form {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .media-upload {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            margin-bottom: 20px;
        }
        .media-upload.drag-over {
            border-color: #0d6efd;
            background-color: #e7f3ff;
        }
        .media-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }
        .media-item {
            position: relative;
            width: 100px;
            height: 100px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            overflow: hidden;
        }
        .media-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .media-item .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 10px;
            cursor: pointer;
        }
        .tag-input {
            position: relative;
        }
        .tag-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        .tag-suggestion {
            padding: 8px 12px;
            cursor: pointer;
        }
        .tag-suggestion:hover {
            background-color: #f8f9fa;
        }
        .tag-badge {
            display: inline-block;
            background: #e9ecef;
            color: #495057;
            padding: 4px 8px;
            border-radius: 4px;
            margin: 2px;
            font-size: 12px;
        }
        .tag-badge .remove {
            margin-left: 5px;
            cursor: pointer;
            color: #dc3545;
        }
        .preview-pane {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            height: 100vh;
            background: white;
            box-shadow: -2px 0 5px rgba(0,0,0,0.1);
            transition: right 0.3s ease;
            z-index: 1050;
            overflow-y: auto;
        }
        .preview-pane.open {
            right: 0;
        }
        .preview-header {
            background: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .preview-content {
            padding: 20px;
        }
        .story-actions {
            position: sticky;
            bottom: 0;
            background: white;
            border-top: 1px solid #dee2e6;
            padding: 15px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="story-form">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1><i class="fas fa-pen-nib"></i> Create New Story</h1>
                        <div>
                            <button type="button" class="btn btn-outline-primary" onclick="togglePreview()">
                                <i class="fas fa-eye"></i> Preview
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()">
                                <i class="fas fa-save"></i> Save Draft
                            </button>
                        </div>
                    </div>

                    <form id="storyForm" enctype="multipart/form-data">
                        <!-- CSRF Token -->
                        <?php if (isset($securityManager)): ?>
                            <?= $securityManager->getCSRFTokenField() ?>
                        <?php endif; ?>
                        
                        <!-- Basic Information -->
                        <div class="form-section">
                            <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Story Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" required maxlength="255">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="published" class="form-label">Status</label>
                                        <select class="form-select" id="published" name="published">
                                            <option value="0">Draft</option>
                                            <option value="1">Published</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="form-section">
                            <h3><i class="fas fa-edit"></i> Content</h3>
                            <div class="mb-3">
                                <label for="content" class="form-label">Story Content *</label>
                                <textarea id="content" name="content" class="form-control"></textarea>
                            </div>
                        </div>

                        <!-- Categories and Tags -->
                        <div class="form-section">
                            <h3><i class="fas fa-tags"></i> Categories & Tags</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="categories" class="form-label">Categories</label>
                                        <select class="form-select" id="categories" name="categories[]" multiple>
                                            <!-- Categories will be loaded via JavaScript -->
                                        </select>
                                        <div class="form-text">Hold Ctrl/Cmd to select multiple categories</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tags" class="form-label">Tags</label>
                                        <div class="tag-input">
                                            <input type="text" class="form-control" id="tagInput" placeholder="Type to add tags...">
                                            <div class="tag-suggestions" id="tagSuggestions"></div>
                                        </div>
                                        <div class="mt-2" id="selectedTags"></div>
                                        <div class="form-text">Press Enter or comma to add tags</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Media Upload -->
                        <div class="form-section">
                            <h3><i class="fas fa-images"></i> Media</h3>
                            <div class="media-upload" id="mediaUpload">
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-3"></i>
                                <p class="text-muted mb-3">Drag and drop files here or click to browse</p>
                                <input type="file" id="mediaInput" multiple accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.txt" style="display: none;">
                                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('mediaInput').click()">
                                    <i class="fas fa-folder-open"></i> Choose Files
                                </button>
                            </div>
                            <div class="media-preview" id="mediaPreview"></div>
                        </div>

                        <!-- Story Actions -->
                        <div class="story-actions">
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-secondary" onclick="history.back()">
                                        <i class="fas fa-arrow-left"></i> Cancel
                                    </button>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="button" class="btn btn-outline-primary me-2" onclick="saveDraft()">
                                        <i class="fas fa-save"></i> Save as Draft
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-paper-plane"></i> Create Story
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Pane -->
    <div class="preview-pane" id="previewPane">
        <div class="preview-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-eye"></i> Story Preview</h5>
                <button type="button" class="btn-close" onclick="togglePreview()"></button>
            </div>
        </div>
        <div class="preview-content" id="previewContent">
            <!-- Preview content will be loaded here -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: '#content',
            height: 400,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px }',
            setup: function (editor) {
                editor.on('change', function () {
                    updatePreview();
                });
            }
        });

        // Global variables
        let selectedTags = [];
        let uploadedMedia = [];

        // Load categories
        function loadCategories() {
            // This would typically be loaded from the server
            const categories = ['Medical', 'Educational', 'Research', 'Patient Stories', 'News'];
            const select = document.getElementById('categories');
            
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category;
                option.textContent = category;
                select.appendChild(option);
            });
        }

        // Tag management
        document.getElementById('tagInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                addTag(this.value.trim());
                this.value = '';
            }
        });

        document.getElementById('tagInput').addEventListener('input', function(e) {
            searchTags(this.value);
        });

        function addTag(tagName) {
            if (tagName && !selectedTags.includes(tagName)) {
                selectedTags.push(tagName);
                renderTags();
            }
        }

        function removeTag(tagName) {
            selectedTags = selectedTags.filter(tag => tag !== tagName);
            renderTags();
        }

        function renderTags() {
            const container = document.getElementById('selectedTags');
            container.innerHTML = selectedTags.map(tag => 
                `<span class="tag-badge">${tag}<span class="remove" onclick="removeTag('${tag}')">&times;</span></span>`
            ).join('');
        }

        function searchTags(query) {
            if (query.length < 2) {
                document.getElementById('tagSuggestions').style.display = 'none';
                return;
            }

            // This would typically make an API call
            const suggestions = ['chronic kidney disease', 'dialysis', 'transplant', 'nutrition', 'exercise'];
            const filtered = suggestions.filter(tag => 
                tag.toLowerCase().includes(query.toLowerCase()) && !selectedTags.includes(tag)
            );

            const container = document.getElementById('tagSuggestions');
            container.innerHTML = filtered.map(tag => 
                `<div class="tag-suggestion" onclick="addTag('${tag}'); document.getElementById('tagInput').value = ''">${tag}</div>`
            ).join('');
            
            container.style.display = filtered.length > 0 ? 'block' : 'none';
        }

        // Media upload handling
        const mediaUpload = document.getElementById('mediaUpload');
        const mediaInput = document.getElementById('mediaInput');

        mediaUpload.addEventListener('click', () => mediaInput.click());

        mediaUpload.addEventListener('dragover', (e) => {
            e.preventDefault();
            mediaUpload.classList.add('drag-over');
        });

        mediaUpload.addEventListener('dragleave', () => {
            mediaUpload.classList.remove('drag-over');
        });

        mediaUpload.addEventListener('drop', (e) => {
            e.preventDefault();
            mediaUpload.classList.remove('drag-over');
            handleFiles(e.dataTransfer.files);
        });

        mediaInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const mediaItem = {
                        file: file,
                        url: e.target.result,
                        type: file.type.startsWith('image/') ? 'image' : 'file'
                    };
                    uploadedMedia.push(mediaItem);
                    renderMediaPreview();
                };
                reader.readAsDataURL(file);
            });
        }

        function renderMediaPreview() {
            const container = document.getElementById('mediaPreview');
            container.innerHTML = uploadedMedia.map((media, index) => 
                `<div class="media-item">
                    ${media.type === 'image' ? 
                        `<img src="${media.url}" alt="Preview">` : 
                        `<div class="d-flex align-items-center justify-content-center h-100">
                            <i class="fas fa-file fa-2x text-muted"></i>
                        </div>`
                    }
                    <button type="button" class="remove-btn" onclick="removeMedia(${index})">&times;</button>
                </div>`
            ).join('');
        }

        function removeMedia(index) {
            uploadedMedia.splice(index, 1);
            renderMediaPreview();
        }

        // Preview functionality
        function togglePreview() {
            const previewPane = document.getElementById('previewPane');
            previewPane.classList.toggle('open');
            if (previewPane.classList.contains('open')) {
                updatePreview();
            }
        }

        function updatePreview() {
            const title = document.getElementById('title').value;
            const content = tinymce.get('content').getContent();
            const categories = Array.from(document.getElementById('categories').selectedOptions).map(option => option.value);
            
            const previewContent = document.getElementById('previewContent');
            previewContent.innerHTML = `
                <h2>${title || 'Untitled Story'}</h2>
                <div class="mb-3">
                    <small class="text-muted">
                        Categories: ${categories.join(', ') || 'None'}
                    </small>
                </div>
                <div class="mb-3">
                    <small class="text-muted">
                        Tags: ${selectedTags.join(', ') || 'None'}
                    </small>
                </div>
                <div class="story-content">
                    ${content || '<p class="text-muted">No content yet...</p>'}
                </div>
            `;
        }

        // Form submission
        document.getElementById('storyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('title', document.getElementById('title').value);
            formData.append('content', tinymce.get('content').getContent());
            formData.append('published', document.getElementById('published').value);
            formData.append('categories', JSON.stringify(Array.from(document.getElementById('categories').selectedOptions).map(option => option.value)));
            formData.append('tags', JSON.stringify(selectedTags));
            
            // Add media files
            uploadedMedia.forEach((media, index) => {
                formData.append(`media_${index}`, media.file);
            });
            
            // Submit form (this would typically go to a server endpoint)
            console.log('Submitting story:', formData);
            
            // Show success message
            alert('Story created successfully!');
        });

        function saveDraft() {
            const published = document.getElementById('published');
            published.value = '0';
            document.getElementById('storyForm').dispatchEvent(new Event('submit'));
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadCategories();
            
            // Auto-save functionality
            setInterval(function() {
                if (document.getElementById('title').value || tinymce.get('content').getContent()) {
                    // Auto-save logic here
                    console.log('Auto-saving...');
                }
            }, 30000); // Auto-save every 30 seconds
        });

        // Close tag suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.tag-input')) {
                document.getElementById('tagSuggestions').style.display = 'none';
            }
        });
    </script>
</body>
</html>
