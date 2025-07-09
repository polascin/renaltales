# RenalTales API Documentation

## Overview

The RenalTales API provides RESTful endpoints for managing stories, users, categories, tags, and media content. All endpoints return JSON responses and follow standard HTTP status codes.

**Base URL**: `http://localhost/renaltales/api/`

**Version**: 2025.v1.0

## Authentication

Most endpoints require authentication. Authentication is handled through session-based authentication with CSRF protection.

### Headers

All requests should include:
- `Content-Type: application/json` (for POST/PUT requests)
- `X-CSRF-Token: {token}` (for state-changing requests)

### Rate Limiting

API endpoints are rate-limited to prevent abuse:
- 100 requests per minute per IP address
- 1000 requests per hour per authenticated user

## Response Format

All responses follow this structure:

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data
  }
}
```

### Error Response
```json
{
  "error": true,
  "message": "Error description",
  "details": {
    // Additional error details
  }
}
```

## Status Codes

- `200 OK` - Success
- `201 Created` - Resource created successfully
- `400 Bad Request` - Invalid request
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Access denied
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation error
- `429 Too Many Requests` - Rate limit exceeded
- `500 Internal Server Error` - Server error

---

## Stories API

### Get All Stories

Retrieve a list of stories with optional filtering and pagination.

**Endpoint**: `GET /stories`

**Parameters**:
- `search` (string, optional) - Search term for title/content
- `published` (boolean, optional) - Filter by published status
- `categories` (string, optional) - Comma-separated category names
- `tags` (string, optional) - Comma-separated tag names
- `limit` (integer, optional) - Number of results per page (default: 10)
- `offset` (integer, optional) - Number of results to skip (default: 0)

**Example Request**:
```bash
GET /api/stories?search=kidney&published=true&limit=20&offset=0
```

**Example Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "My Kidney Journey",
      "content": "This is my story about...",
      "author_id": 1,
      "author_name": "John Doe",
      "status": "published",
      "featured": false,
      "language": "en",
      "created_at": "2025-01-01T12:00:00Z",
      "updated_at": "2025-01-01T12:00:00Z",
      "published_at": "2025-01-01T12:00:00Z",
      "categories": [
        {
          "id": 1,
          "name": "Health",
          "slug": "health"
        }
      ],
      "tags": [
        {
          "id": 1,
          "name": "kidney",
          "slug": "kidney"
        }
      ],
      "featured_image": {
        "id": 1,
        "url": "/uploads/image.jpg",
        "alt_text": "Story image"
      },
      "comment_count": 5,
      "view_count": 150
    }
  ],
  "pagination": {
    "total": 100,
    "limit": 20,
    "offset": 0,
    "has_more": true
  }
}
```

### Get Single Story

Retrieve a specific story by ID.

**Endpoint**: `GET /stories/{id}`

**Example Request**:
```bash
GET /api/stories/1
```

**Example Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "My Kidney Journey",
    "content": "This is my story about...",
    "author_id": 1,
    "author_name": "John Doe",
    "status": "published",
    "featured": false,
    "language": "en",
    "created_at": "2025-01-01T12:00:00Z",
    "updated_at": "2025-01-01T12:00:00Z",
    "published_at": "2025-01-01T12:00:00Z",
    "categories": [...],
    "tags": [...],
    "media": [...],
    "comments": [...]
  }
}
```

### Create Story

Create a new story.

**Endpoint**: `POST /stories`

**Authentication**: Required

**Request Body**:
```json
{
  "title": "My New Story",
  "content": "This is the content of my story...",
  "categories": ["Health", "Personal"],
  "tags": ["kidney", "health", "story"],
  "status": "draft",
  "featured": false,
  "language": "en"
}
```

**Example Response**:
```json
{
  "success": true,
  "message": "Story created successfully",
  "story_id": 1,
  "data": {
    "id": 1,
    "title": "My New Story",
    "content": "This is the content of my story...",
    "author_id": 1,
    "status": "draft",
    "created_at": "2025-01-01T12:00:00Z",
    "updated_at": "2025-01-01T12:00:00Z"
  }
}
```

### Update Story

Update an existing story.

**Endpoint**: `PUT /stories/{id}`

**Authentication**: Required (story owner or admin)

**Request Body**:
```json
{
  "title": "Updated Story Title",
  "content": "Updated story content...",
  "categories": ["Health"],
  "tags": ["kidney", "health"],
  "status": "published"
}
```

**Example Response**:
```json
{
  "success": true,
  "message": "Story updated successfully",
  "data": {
    "id": 1,
    "title": "Updated Story Title",
    "content": "Updated story content...",
    "updated_at": "2025-01-01T13:00:00Z"
  }
}
```

### Delete Story

Delete a story.

**Endpoint**: `DELETE /stories/{id}`

**Authentication**: Required (story owner or admin)

**Example Response**:
```json
{
  "success": true,
  "message": "Story deleted successfully"
}
```

### Publish Story

Publish a draft story.

**Endpoint**: `POST /stories/{id}/publish`

**Authentication**: Required (story owner or admin)

**Example Response**:
```json
{
  "success": true,
  "message": "Story published successfully"
}
```

### Unpublish Story

Unpublish a published story.

**Endpoint**: `POST /stories/{id}/unpublish`

**Authentication**: Required (story owner or admin)

**Example Response**:
```json
{
  "success": true,
  "message": "Story unpublished successfully"
}
```

---

## Story Media API

### Get Story Media

Retrieve media files for a story.

**Endpoint**: `GET /stories/{id}/media`

**Parameters**:
- `type` (string, optional) - Filter by media type (image, video, document)

**Example Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "story_id": 1,
      "filename": "image.jpg",
      "original_filename": "my-photo.jpg",
      "mime_type": "image/jpeg",
      "size": 1024000,
      "url": "/uploads/stories/1/image.jpg",
      "thumbnail_url": "/uploads/stories/1/thumbs/image.jpg",
      "alt_text": "Story image",
      "caption": "This is my story image",
      "sort_order": 1,
      "created_at": "2025-01-01T12:00:00Z"
    }
  ]
}
```

### Upload Media

Upload a media file for a story.

**Endpoint**: `POST /stories/{id}/media`

**Authentication**: Required (story owner or admin)

**Content-Type**: `multipart/form-data`

**Form Fields**:
- `file` (file) - The media file to upload
- `alt_text` (string, optional) - Alternative text for accessibility
- `caption` (string, optional) - Caption for the media

**Example Response**:
```json
{
  "success": true,
  "message": "Media uploaded successfully",
  "media_id": 1,
  "data": {
    "id": 1,
    "filename": "image.jpg",
    "url": "/uploads/stories/1/image.jpg",
    "alt_text": "Story image",
    "caption": "This is my story image"
  }
}
```

### Delete Media

Delete a media file.

**Endpoint**: `DELETE /stories/{story_id}/media/{media_id}`

**Authentication**: Required (story owner or admin)

**Example Response**:
```json
{
  "success": true,
  "message": "Media deleted successfully"
}
```

---

## Story Comments API

### Get Story Comments

Retrieve comments for a story.

**Endpoint**: `GET /stories/{id}/comments`

**Parameters**:
- `threaded` (boolean, optional) - Return threaded comments structure

**Example Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "story_id": 1,
      "user_id": 2,
      "user_name": "Jane Doe",
      "content": "Great story! Thanks for sharing.",
      "parent_id": null,
      "status": "approved",
      "created_at": "2025-01-01T12:00:00Z",
      "updated_at": "2025-01-01T12:00:00Z",
      "replies": [
        {
          "id": 2,
          "content": "Thank you for reading!",
          "parent_id": 1,
          "created_at": "2025-01-01T12:30:00Z"
        }
      ]
    }
  ]
}
```

### Create Comment

Create a new comment on a story.

**Endpoint**: `POST /stories/{id}/comments`

**Authentication**: Required

**Request Body**:
```json
{
  "content": "This is my comment on the story.",
  "parent_id": null
}
```

**Example Response**:
```json
{
  "success": true,
  "message": "Comment created successfully",
  "comment_id": 1,
  "data": {
    "id": 1,
    "story_id": 1,
    "user_id": 2,
    "content": "This is my comment on the story.",
    "status": "pending",
    "created_at": "2025-01-01T12:00:00Z"
  }
}
```

---

## Categories API

### Get Categories

Retrieve all categories with story counts.

**Endpoint**: `GET /stories/categories`

**Example Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Health",
      "slug": "health",
      "description": "Health-related stories",
      "story_count": 25,
      "created_at": "2025-01-01T12:00:00Z"
    },
    {
      "id": 2,
      "name": "Personal",
      "slug": "personal",
      "description": "Personal experience stories",
      "story_count": 15,
      "created_at": "2025-01-01T12:00:00Z"
    }
  ]
}
```

---

## Tags API

### Get Tags

Retrieve all tags with various filtering options.

**Endpoint**: `GET /stories/tags`

**Parameters**:
- `search` (string, optional) - Search for tags by name
- `popular` (boolean, optional) - Return only popular tags
- `cloud` (boolean, optional) - Return tags formatted for tag cloud
- `limit` (integer, optional) - Number of results to return

**Example Request**:
```bash
GET /api/stories/tags?popular=true&limit=10
```

**Example Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "kidney",
      "slug": "kidney",
      "story_count": 50,
      "weight": 100
    },
    {
      "id": 2,
      "name": "dialysis",
      "slug": "dialysis",
      "story_count": 30,
      "weight": 60
    }
  ]
}
```

---

## Story Statistics API

### Get Story Statistics

Retrieve comprehensive statistics about stories.

**Endpoint**: `GET /stories/stats`

**Authentication**: Required (admin)

**Example Response**:
```json
{
  "success": true,
  "data": {
    "total_stories": 100,
    "published_stories": 85,
    "draft_stories": 15,
    "total_categories": 10,
    "total_tags": 50,
    "total_media": 200,
    "total_comments": 300,
    "media_stats": {
      "images": 150,
      "videos": 30,
      "documents": 20,
      "total_size": "50MB"
    },
    "comment_stats": {
      "approved": 280,
      "pending": 15,
      "rejected": 5
    },
    "recent_activity": {
      "stories_today": 5,
      "comments_today": 20,
      "media_today": 8
    }
  }
}
```

---

## Story Versions API

### Get Story Versions

Retrieve version history for a story.

**Endpoint**: `GET /stories/{id}/versions`

**Authentication**: Required (story owner or admin)

**Example Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "story_id": 1,
      "version": 1,
      "title": "My Kidney Journey",
      "content": "Original content...",
      "changes": "Initial version",
      "created_by": 1,
      "created_at": "2025-01-01T12:00:00Z"
    },
    {
      "id": 2,
      "story_id": 1,
      "version": 2,
      "title": "My Kidney Journey - Updated",
      "content": "Updated content...",
      "changes": "Updated title and content",
      "created_by": 1,
      "created_at": "2025-01-01T13:00:00Z"
    }
  ]
}
```

### Get Specific Version

Retrieve a specific version of a story.

**Endpoint**: `GET /stories/{id}/versions/{version}`

**Authentication**: Required (story owner or admin)

**Example Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "story_id": 1,
    "version": 1,
    "title": "My Kidney Journey",
    "content": "Original content...",
    "changes": "Initial version",
    "created_by": 1,
    "created_at": "2025-01-01T12:00:00Z"
  }
}
```

---

## Story Preview API

### Generate Story Preview

Generate a preview of how a story will look when published.

**Endpoint**: `GET /stories/{id}/preview`

**Authentication**: Required (story owner or admin)

**Example Response**:
```json
{
  "success": true,
  "preview": {
    "title": "My Kidney Journey",
    "content": "This is my story about...",
    "categories": [...],
    "tags": [...],
    "media": [...],
    "created_at": "2025-01-01T12:00:00Z",
    "updated_at": "2025-01-01T12:00:00Z",
    "published": false
  }
}
```

---

## Error Handling

### Common Error Responses

#### Validation Error (422)
```json
{
  "error": true,
  "message": "Validation failed",
  "details": {
    "title": ["Title is required"],
    "content": ["Content must be at least 10 characters"]
  }
}
```

#### Authentication Error (401)
```json
{
  "error": true,
  "message": "Authentication required",
  "details": {
    "reason": "No valid session found"
  }
}
```

#### Authorization Error (403)
```json
{
  "error": true,
  "message": "Access denied",
  "details": {
    "reason": "You don't have permission to access this resource"
  }
}
```

#### Rate Limit Error (429)
```json
{
  "error": true,
  "message": "Rate limit exceeded",
  "details": {
    "reason": "Too many requests",
    "retry_after": 60
  }
}
```

---

## Security

### CSRF Protection

All state-changing requests (POST, PUT, DELETE) require a valid CSRF token. The token can be obtained from the session and must be included in the `X-CSRF-Token` header.

### Input Validation

All input is validated and sanitized to prevent:
- SQL injection
- XSS attacks
- File upload vulnerabilities
- Data type mismatches

### Rate Limiting

API endpoints are protected by rate limiting to prevent abuse:
- IP-based rate limiting
- User-based rate limiting
- Endpoint-specific limits

### File Upload Security

File uploads are secured with:
- File type validation
- Size limits
- Virus scanning
- Secure storage outside web root
- Content type verification

---

## Development and Testing

### Environment Variables

Required environment variables:
- `APP_ENV` - Application environment (development, testing, production)
- `APP_DEBUG` - Debug mode (true/false)
- `DB_HOST` - Database host
- `DB_DATABASE` - Database name
- `DB_USERNAME` - Database username
- `DB_PASSWORD` - Database password

### Testing

Run API tests using PHPUnit:
```bash
vendor/bin/phpunit tests/API/
```

### API Client Examples

#### JavaScript/Fetch
```javascript
fetch('/api/stories', {
  method: 'GET',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data));
```

#### cURL
```bash
curl -X GET \
  'http://localhost/renaltales/api/stories' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json'
```

#### PHP
```php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/renaltales/api/stories');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);
```

---

## Changelog

### Version 2025.v1.0
- Initial API implementation
- Story CRUD operations
- Media upload functionality
- Comment system
- Category and tag management
- Version control for stories
- Comprehensive security measures
- Rate limiting
- CSRF protection
- Input validation and sanitization

---

## Support

For API support and questions:
- Documentation: This file
- Issues: Check application logs
- Testing: Use provided test suite
- Development: See development guidelines in project README

---

**Last Updated**: January 2025
**API Version**: 2025.v1.0
**Documentation Version**: 1.0
