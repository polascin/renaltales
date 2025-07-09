<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Story Dashboard - Renal Tales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
        }
        .story-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .story-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .story-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .story-meta {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .story-actions {
            display: flex;
            gap: 10px;
        }
        .story-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .story-status.published {
            background: #d4edda;
            color: #155724;
        }
        .story-status.draft {
            background: #fff3cd;
            color: #856404;
        }
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .search-bar {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .tag-cloud {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        .tag-item {
            background: #e9ecef;
            color: #495057;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .tag-item:hover {
            background: #dee2e6;
        }
        .tag-item.active {
            background: #007bff;
            color: white;
        }
        .version-badge {
            background: #f8f9fa;
            color: #6c757d;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.7rem;
        }
        .comment-count {
            color: #28a745;
            font-weight: 500;
        }
        .sidebar {
            position: sticky;
            top: 20px;
            height: calc(100vh - 40px);
            overflow-y: auto;
        }
        .bulk-actions {
            display: none;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .bulk-actions.show {
            display: block;
        }
        .story-checkbox {
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 10;
        }
        .story-card.selected {
            border: 2px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1><i class="fas fa-book-open"></i> Story Dashboard</h1>
                    <p class="lead">Manage your stories, track performance, and publish content</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="sidebar">
                    <!-- Quick Stats -->
                    <div class="stats-card">
                        <h5><i class="fas fa-chart-bar"></i> Quick Stats</h5>
                        <div class="row">
                            <div class="col-6">
                                <div class="stats-number" id="totalStories">0</div>
                                <div class="text-muted">Total Stories</div>
                            </div>
                            <div class="col-6">
                                <div class="stats-number" id="publishedStories">0</div>
                                <div class="text-muted">Published</div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-6">
                                <div class="stats-number" id="totalViews">0</div>
                                <div class="text-muted">Total Views</div>
                            </div>
                            <div class="col-6">
                                <div class="stats-number" id="totalComments">0</div>
                                <div class="text-muted">Comments</div>
                            </div>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="stats-card">
                        <h5><i class="fas fa-folder"></i> Categories</h5>
                        <div id="categoriesList">
                            <!-- Categories will be loaded here -->
                        </div>
                    </div>

                    <!-- Popular Tags -->
                    <div class="stats-card">
                        <h5><i class="fas fa-tags"></i> Popular Tags</h5>
                        <div class="tag-cloud" id="tagCloud">
                            <!-- Tags will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <!-- Search and Filters -->
                <div class="search-bar">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search stories...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="btn-group w-100">
                                <button class="btn btn-outline-secondary" onclick="toggleView('grid')" id="gridView">
                                    <i class="fas fa-th-large"></i>
                                </button>
                                <button class="btn btn-outline-secondary active" onclick="toggleView('list')" id="listView">
                                    <i class="fas fa-list"></i>
                                </button>
                                <a href="create.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> New Story
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div class="bulk-actions" id="bulkActions">
                    <div class="d-flex justify-content-between align-items-center">
                        <span id="selectedCount">0 selected</span>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-success" onclick="bulkAction('publish')">
                                <i class="fas fa-paper-plane"></i> Publish
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="bulkAction('draft')">
                                <i class="fas fa-edit"></i> Make Draft
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stories List -->
                <div id="storiesList">
                    <!-- Stories will be loaded here -->
                </div>

                <!-- Pagination -->
                <nav aria-label="Stories pagination">
                    <ul class="pagination justify-content-center" id="pagination">
                        <!-- Pagination will be loaded here -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Story Actions Modal -->
    <div class="modal fade" id="storyActionsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Story Actions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action" onclick="editStory()">
                            <i class="fas fa-edit"></i> Edit Story
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="viewVersions()">
                            <i class="fas fa-history"></i> View Versions
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="manageMedia()">
                            <i class="fas fa-images"></i> Manage Media
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="viewComments()">
                            <i class="fas fa-comments"></i> View Comments
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="previewStory()">
                            <i class="fas fa-eye"></i> Preview
                        </a>
                        <a href="#" class="list-group-item list-group-item-action text-danger" onclick="deleteStory()">
                            <i class="fas fa-trash"></i> Delete Story
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        let stories = [];
        let selectedStories = [];
        let currentPage = 1;
        let viewMode = 'list';
        let filters = {
            search: '',
            status: '',
            category: '',
            tags: []
        };
        let currentStoryId = null;

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            loadCategories();
            loadTags();
            loadStories();
            
            // Set up event listeners
            setupEventListeners();
        });

        function setupEventListeners() {
            // Search input
            document.getElementById('searchInput').addEventListener('input', function(e) {
                filters.search = e.target.value;
                debounce(loadStories, 300)();
            });

            // Status filter
            document.getElementById('statusFilter').addEventListener('change', function(e) {
                filters.status = e.target.value;
                loadStories();
            });

            // Bulk select checkbox
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('story-checkbox')) {
                    handleStorySelection(e.target);
                }
            });
        }

        function loadStats() {
            // This would typically fetch from server
            const stats = {
                totalStories: 42,
                publishedStories: 38,
                totalViews: 1250,
                totalComments: 89
            };

            document.getElementById('totalStories').textContent = stats.totalStories;
            document.getElementById('publishedStories').textContent = stats.publishedStories;
            document.getElementById('totalViews').textContent = stats.totalViews;
            document.getElementById('totalComments').textContent = stats.totalComments;
        }

        function loadCategories() {
            // This would typically fetch from server
            const categories = [
                { name: 'Medical', count: 15 },
                { name: 'Educational', count: 12 },
                { name: 'Research', count: 8 },
                { name: 'Patient Stories', count: 7 }
            ];

            const container = document.getElementById('categoriesList');
            container.innerHTML = categories.map(cat => `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="category-item" onclick="filterByCategory('${cat.name}')" style="cursor: pointer;">
                        ${cat.name}
                    </span>
                    <span class="badge bg-secondary">${cat.count}</span>
                </div>
            `).join('');
        }

        function loadTags() {
            // This would typically fetch from server
            const tags = [
                { name: 'chronic kidney disease', count: 15 },
                { name: 'dialysis', count: 12 },
                { name: 'transplant', count: 8 },
                { name: 'nutrition', count: 7 },
                { name: 'exercise', count: 5 },
                { name: 'medication', count: 4 }
            ];

            const container = document.getElementById('tagCloud');
            container.innerHTML = tags.map(tag => `
                <span class="tag-item" onclick="toggleTagFilter('${tag.name}')">${tag.name}</span>
            `).join('');
        }

        function loadStories() {
            // This would typically fetch from server with filters
            const sampleStories = [
                {
                    id: 1,
                    title: "Understanding Chronic Kidney Disease",
                    content: "A comprehensive guide to understanding CKD...",
                    published: true,
                    created_at: "2024-01-15",
                    updated_at: "2024-01-20",
                    categories: ["Medical", "Educational"],
                    tags: ["chronic kidney disease", "education"],
                    media: [{ type: 'image', url: 'https://via.placeholder.com/100x100' }],
                    comment_count: 15,
                    version: 3
                },
                {
                    id: 2,
                    title: "Living with Dialysis: A Patient's Journey",
                    content: "Personal experience and tips for dialysis patients...",
                    published: true,
                    created_at: "2024-01-10",
                    updated_at: "2024-01-18",
                    categories: ["Patient Stories"],
                    tags: ["dialysis", "patient experience"],
                    media: [],
                    comment_count: 23,
                    version: 2
                },
                {
                    id: 3,
                    title: "New Research on Kidney Function",
                    content: "Latest findings in kidney research...",
                    published: false,
                    created_at: "2024-01-12",
                    updated_at: "2024-01-22",
                    categories: ["Research"],
                    tags: ["research", "kidney function"],
                    media: [],
                    comment_count: 0,
                    version: 1
                }
            ];

            stories = sampleStories;
            renderStories();
        }

        function renderStories() {
            const container = document.getElementById('storiesList');
            
            if (stories.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No stories found</h4>
                        <p class="text-muted">Create your first story to get started</p>
                        <a href="create.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Story
                        </a>
                    </div>
                `;
                return;
            }

            container.innerHTML = stories.map(story => `
                <div class="story-card ${selectedStories.includes(story.id) ? 'selected' : ''}" data-story-id="${story.id}">
                    <div class="story-checkbox">
                        <input type="checkbox" class="form-check-input story-checkbox" value="${story.id}" ${selectedStories.includes(story.id) ? 'checked' : ''}>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-2">
                            ${story.media.length > 0 ? 
                                `<img src="${story.media[0].url}" class="story-image" alt="Story image">` :
                                `<div class="story-image bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-image fa-2x text-muted"></i>
                                </div>`
                            }
                        </div>
                        <div class="col-md-7">
                            <h5 class="mb-2">${story.title}</h5>
                            <p class="text-muted mb-2">${story.content.substring(0, 150)}...</p>
                            <div class="story-meta">
                                <span class="me-3">
                                    <i class="fas fa-calendar"></i> ${new Date(story.updated_at).toLocaleDateString()}
                                </span>
                                <span class="me-3">
                                    <i class="fas fa-folder"></i> ${story.categories.join(', ')}
                                </span>
                                <span class="me-3">
                                    <i class="fas fa-tags"></i> ${story.tags.join(', ')}
                                </span>
                                <span class="version-badge">v${story.version}</span>
                            </div>
                        </div>
                        <div class="col-md-3 text-end">
                            <div class="mb-2">
                                <span class="story-status ${story.published ? 'published' : 'draft'}">
                                    ${story.published ? 'Published' : 'Draft'}
                                </span>
                            </div>
                            <div class="mb-2">
                                <span class="comment-count">
                                    <i class="fas fa-comments"></i> ${story.comment_count}
                                </span>
                            </div>
                            <div class="story-actions">
                                <button class="btn btn-sm btn-outline-primary" onclick="showStoryActions(${story.id})">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="togglePublish(${story.id})">
                                    <i class="fas fa-${story.published ? 'eye-slash' : 'paper-plane'}"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function toggleView(mode) {
            viewMode = mode;
            document.getElementById('gridView').classList.toggle('active', mode === 'grid');
            document.getElementById('listView').classList.toggle('active', mode === 'list');
            // Implement grid view if needed
        }

        function handleStorySelection(checkbox) {
            const storyId = parseInt(checkbox.value);
            
            if (checkbox.checked) {
                selectedStories.push(storyId);
            } else {
                selectedStories = selectedStories.filter(id => id !== storyId);
            }
            
            updateBulkActions();
            updateStorySelection();
        }

        function updateBulkActions() {
            const bulkActions = document.getElementById('bulkActions');
            const selectedCount = document.getElementById('selectedCount');
            
            if (selectedStories.length > 0) {
                bulkActions.classList.add('show');
                selectedCount.textContent = `${selectedStories.length} selected`;
            } else {
                bulkActions.classList.remove('show');
            }
        }

        function updateStorySelection() {
            const storyCards = document.querySelectorAll('.story-card');
            storyCards.forEach(card => {
                const storyId = parseInt(card.dataset.storyId);
                card.classList.toggle('selected', selectedStories.includes(storyId));
            });
        }

        function filterByCategory(category) {
            filters.category = category;
            loadStories();
        }

        function toggleTagFilter(tag) {
            const tagElement = event.target;
            
            if (filters.tags.includes(tag)) {
                filters.tags = filters.tags.filter(t => t !== tag);
                tagElement.classList.remove('active');
            } else {
                filters.tags.push(tag);
                tagElement.classList.add('active');
            }
            
            loadStories();
        }

        function showStoryActions(storyId) {
            currentStoryId = storyId;
            const modal = new bootstrap.Modal(document.getElementById('storyActionsModal'));
            modal.show();
        }

        function togglePublish(storyId) {
            const story = stories.find(s => s.id === storyId);
            if (story) {
                story.published = !story.published;
                renderStories();
                
                // This would typically make an API call
                console.log(`Story ${storyId} ${story.published ? 'published' : 'unpublished'}`);
            }
        }

        function bulkAction(action) {
            if (selectedStories.length === 0) return;
            
            const confirmMessage = `Are you sure you want to ${action} ${selectedStories.length} story(ies)?`;
            
            if (confirm(confirmMessage)) {
                // This would typically make an API call
                console.log(`Bulk ${action} for stories:`, selectedStories);
                
                // Reset selection
                selectedStories = [];
                updateBulkActions();
                renderStories();
            }
        }

        // Story action functions
        function editStory() {
            if (currentStoryId) {
                window.location.href = `edit.php?id=${currentStoryId}`;
            }
        }

        function viewVersions() {
            if (currentStoryId) {
                window.location.href = `versions.php?id=${currentStoryId}`;
            }
        }

        function manageMedia() {
            if (currentStoryId) {
                window.location.href = `media.php?id=${currentStoryId}`;
            }
        }

        function viewComments() {
            if (currentStoryId) {
                window.location.href = `comments.php?id=${currentStoryId}`;
            }
        }

        function previewStory() {
            if (currentStoryId) {
                window.open(`preview.php?id=${currentStoryId}`, '_blank');
            }
        }

        function deleteStory() {
            if (currentStoryId && confirm('Are you sure you want to delete this story?')) {
                // This would typically make an API call
                console.log(`Deleting story ${currentStoryId}`);
                
                // Remove from local array
                stories = stories.filter(s => s.id !== currentStoryId);
                renderStories();
                
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('storyActionsModal')).hide();
            }
        }

        // Utility functions
        function debounce(func, delay) {
            let timeoutId;
            return function(...args) {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => func.apply(this, args), delay);
            };
        }

        // Auto-refresh functionality
        setInterval(function() {
            // Auto-refresh stories every 5 minutes
            loadStories();
        }, 300000);
    </script>
</body>
</html>
