# Story Content Management System Documentation

## Overview

The Story Content Management System is a comprehensive solution for managing medical and educational stories in the Renal Tales application. It provides a complete workflow for creating, editing, publishing, and managing story content with advanced features like versioning, media management, and user engagement through comments.

## Features

### Core Features
- **Story CRUD Operations**: Create, Read, Update, Delete stories
- **Rich Text Editor**: TinyMCE integration for content editing
- **Media Management**: Upload and manage images, videos, documents
- **Story Versioning**: Track all changes with version history
- **Category & Tag System**: Organize stories with flexible categorization
- **Search & Filtering**: Advanced search capabilities
- **Publishing Workflow**: Draft/Published status management
- **Comment System**: User engagement with threaded comments
- **Bulk Operations**: Manage multiple stories simultaneously

### Advanced Features
- **Auto-save**: Automatic draft saving every 30 seconds
- **Preview Mode**: Real-time preview while editing
- **Drag & Drop**: File upload with drag-and-drop support
- **Responsive Design**: Mobile-friendly interface
- **REST API**: Full API for programmatic access
- **Statistics Dashboard**: Analytics and insights

## System Architecture

### Database Schema
```sql
-- Core tables
stories
story_versions
categories
tags
story_categories
story_tags
story_media
story_comments
```

### File Structure
```
models/
├── Story.php          # Story model with CRUD operations
├── Category.php       # Category management
├── Tag.php           # Tag management
├── Media.php         # Media file handling
└── Comment.php       # Comment system

controllers/
└── StoryController.php # Main story controller

views/story/
├── create.php        # Story creation form
├── dashboard.php     # Story management dashboard
├── edit.php          # Story editing (similar to create)
└── preview.php       # Story preview

api/
└── stories.php       # REST API endpoints
```

## Quick Start Guide

### 1. Database Setup

First, run the database migration to create the required tables:

```sql
-- Run the updated setup_database.sql
mysql -u root -p renaltales < database/setup_database.sql
```

### 2. Creating a Story

#### Via Web Interface
1. Navigate to `/views/story/create.php`
2. Fill in the story title and content
3. Select categories and add tags
4. Upload media files (optional)
5. Choose to save as draft or publish directly

#### Via API
```javascript
// Create a new story
fetch('/api/stories.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        title: 'My New Story',
        content: '<p>Story content here...</p>',
        categories: ['Medical', 'Educational'],
        tags: ['kidney', 'health'],
        published: false
    })
});
```

### 3. Managing Stories

Access the story dashboard at `/views/story/dashboard.php` to:
- View all stories with filtering
- Search stories by title, content, or tags
- Bulk publish/unpublish stories
- Access story actions (edit, delete, view versions)

## API Documentation

### Base URL
```
/api/stories.php
```

### Authentication
Currently uses session-based authentication. In production, implement proper API key or JWT authentication.

### Endpoints

#### Stories

**GET /api/stories.php**
- Get list of stories with filtering
- Query parameters: `search`, `published`, `categories`, `tags`, `limit`, `offset`

**POST /api/stories.php**
- Create new story
- Body: JSON with story data

**GET /api/stories.php/{id}**
- Get single story by ID

**PUT /api/stories.php/{id}**
- Update story
- Body: JSON with updated data

**DELETE /api/stories.php/{id}**
- Delete story

#### Story Versions

**GET /api/stories.php/{id}/versions**
- Get all versions of a story

**GET /api/stories.php/{id}/versions/{version}**
- Get specific version

#### Media Management

**POST /api/stories.php/{id}/media**
- Upload media file
- Content-Type: multipart/form-data

**GET /api/stories.php/{id}/media**
- Get story media files

**DELETE /api/stories.php/{id}/media/{mediaId}**
- Delete media file

#### Comments

**GET /api/stories.php/{id}/comments**
- Get story comments
- Query parameter: `threaded=true` for threaded view

**POST /api/stories.php/{id}/comments**
- Create comment
- Body: JSON with comment data

#### Publishing

**POST /api/stories.php/{id}/publish**
- Publish story

**POST /api/stories.php/{id}/unpublish**
- Unpublish story

#### Categories and Tags

**GET /api/stories.php/categories**
- Get all categories

**GET /api/stories.php/tags**
- Get all tags
- Query parameters: `search`, `popular`, `cloud`

## Configuration

### Media Upload Settings
In `models/Media.php`:
```php
const UPLOAD_DIR = 'storage/uploads/';
const MAX_FILE_SIZE = 10485760; // 10MB

const ALLOWED_TYPES = [
    'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    'video' => ['mp4', 'webm', 'ogg'],
    'audio' => ['mp3', 'wav', 'ogg'],
    'document' => ['pdf', 'doc', 'docx', 'txt'],
];
```

### TinyMCE Configuration
In `views/story/create.php`:
```javascript
tinymce.init({
    selector: '#content',
    height: 400,
    plugins: [
        'advlist autolink lists link image charmap print preview anchor',
        'searchreplace visualblocks code fullscreen',
        'insertdatetime media table paste code help wordcount'
    ],
    toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
});
```

## Security Considerations

### Input Validation
- All inputs are validated in model classes
- SQL injection prevention using prepared statements
- XSS protection through proper output escaping

### File Upload Security
- File type validation
- Size limits
- Unique filename generation
- Secure storage location

### Authentication
- Session-based authentication (basic implementation)
- User permission checking for operations
- CSRF protection (recommended for production)

## Performance Optimization

### Database Indexes
Key indexes are created for:
- Story searches by title/content
- Category and tag filtering
- User-specific queries
- Date-based sorting

### Caching Strategy
Consider implementing:
- Redis/Memcached for frequently accessed data
- Database query result caching
- Static file caching for media

### File Storage
- Implement CDN for media files in production
- Consider cloud storage (AWS S3, Google Cloud Storage)
- Image optimization and thumbnail generation

## Error Handling

### API Errors
```json
{
    "error": true,
    "message": "Error description",
    "details": "Additional error details"
}
```

### Common Error Codes
- 400: Bad Request (validation errors)
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 500: Internal Server Error

## Testing

### Unit Tests
Create tests for:
- Model validation
- CRUD operations
- Media upload functionality
- Version management

### Integration Tests
- API endpoint testing
- Database operations
- File upload/download

### Example Test Structure
```php
// tests/StoryModelTest.php
class StoryModelTest extends PHPUnit\Framework\TestCase {
    public function testCreateStory() {
        $story = new Story();
        $result = $story->createStory([
            'title' => 'Test Story',
            'content' => 'Test content'
        ], 1);
        
        $this->assertNotFalse($result);
    }
}
```

## Deployment

### Production Checklist
- [ ] Update database configuration
- [ ] Configure proper authentication
- [ ] Set up file upload security
- [ ] Enable SSL/HTTPS
- [ ] Configure caching
- [ ] Set up monitoring
- [ ] Configure backup strategy

### Environment Variables
```env
DB_HOST=localhost
DB_NAME=renaltales
DB_USER=root
DB_PASS=your_password
UPLOAD_MAX_SIZE=10M
MEDIA_CDN_URL=https://cdn.example.com
```

## Maintenance

### Regular Tasks
- Clean up old story versions
- Archive old comments
- Optimize database tables
- Update media file permissions
- Monitor disk usage

### Database Maintenance
```sql
-- Clean up old versions (keep last 10)
DELETE FROM story_versions 
WHERE story_id IN (
    SELECT story_id FROM (
        SELECT story_id, version_number,
        ROW_NUMBER() OVER (PARTITION BY story_id ORDER BY version_number DESC) as rn
        FROM story_versions
    ) tmp WHERE rn > 10
);
```

## Support and Troubleshooting

### Common Issues

**Media Upload Fails**
- Check file permissions on upload directory
- Verify file size limits
- Ensure allowed file types

**Stories Not Saving**
- Check database connection
- Verify user permissions
- Review error logs

**Search Not Working**
- Check database indexes
- Verify search terms
- Review query performance

### Logging
Enable detailed logging in production:
```php
error_log('Story creation failed: ' . $e->getMessage());
```

## Changelog

### Version 1.0.0 (2025-01-09)
- Initial implementation
- Basic CRUD operations
- Media management
- Version tracking
- Comment system
- REST API
- Dashboard interface

## Contributing

When contributing to the story management system:
1. Follow existing code patterns
2. Add unit tests for new features
3. Update documentation
4. Ensure backward compatibility
5. Test thoroughly before deployment

## License

This documentation is part of the Renal Tales project.
