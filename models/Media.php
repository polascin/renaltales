<?php

/**
 * Media Model - Media management for stories
 * 
 * Handles media file operations for story attachments
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0test
 */

require_once 'BaseModel.php';

class Media extends BaseModel {
    
    protected $table = 'story_media';
    
    const UPLOAD_DIR = 'storage/uploads/';
    const MAX_FILE_SIZE = 10485760; // 10MB
    
    const ALLOWED_TYPES = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'video' => ['mp4', 'webm', 'ogg'],
        'audio' => ['mp3', 'wav', 'ogg'],
        'document' => ['pdf', 'doc', 'docx', 'txt'],
        'other' => []
    ];
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->ensureUploadDir();
    }
    
    /**
     * Validate media data
     * 
     * @param array $data
     * @return array Validation errors
     */
    protected function validate($data) {
        $errors = [];
        
        // Story ID validation
        if (empty($data['story_id'])) {
            $errors['story_id'] = 'Story ID is required';
        }
        
        // Filename validation
        if (empty($data['filename'])) {
            $errors['filename'] = 'Filename is required';
        }
        
        // File path validation
        if (empty($data['file_path'])) {
            $errors['file_path'] = 'File path is required';
        }
        
        // File size validation
        if (empty($data['file_size']) || $data['file_size'] > self::MAX_FILE_SIZE) {
            $errors['file_size'] = 'File size must be less than 10MB';
        }
        
        // MIME type validation
        if (empty($data['mime_type'])) {
            $errors['mime_type'] = 'MIME type is required';
        }
        
        // Media type validation
        if (empty($data['media_type']) || !in_array($data['media_type'], ['image', 'video', 'audio', 'document', 'other'])) {
            $errors['media_type'] = 'Valid media type is required';
        }
        
        return $errors;
    }
    
    /**
     * Upload and create media record
     * 
     * @param int $storyId
     * @param array $file $_FILES array element
     * @param array $metadata Additional metadata
     * @return string|false Media ID on success, false on failure
     */
    public function uploadMedia($storyId, $file, $metadata = []) {
        // Validate file upload
        if (!$this->validateUpload($file)) {
            return false;
        }
        
        // Get file information
        $fileInfo = $this->getFileInfo($file);
        $mediaType = $this->getMediaType($fileInfo['extension']);
        
        // Generate unique filename
        $filename = $this->generateFilename($fileInfo['extension']);
        $filePath = self::UPLOAD_DIR . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            error_log('Failed to move uploaded file');
            return false;
        }
        
        // Create media record
        $mediaData = [
            'story_id' => $storyId,
            'filename' => $filename,
            'original_filename' => $fileInfo['original_name'],
            'file_path' => $filePath,
            'file_size' => $fileInfo['size'],
            'mime_type' => $fileInfo['mime_type'],
            'media_type' => $mediaType,
            'alt_text' => $metadata['alt_text'] ?? null,
            'caption' => $metadata['caption'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $errors = $this->validate($mediaData);
        if (!empty($errors)) {
            // Clean up uploaded file
            unlink($filePath);
            return false;
        }
        
        return $this->create($mediaData);
    }
    
    /**
     * Update media metadata
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateMedia($id, $data) {
        $allowedFields = ['alt_text', 'caption'];
        $updateData = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (empty($updateData)) {
            return false;
        }
        
        return $this->update($id, $updateData);
    }
    
    /**
     * Delete media file and record
     * 
     * @param int $id
     * @return bool
     */
    public function deleteMedia($id) {
        $media = $this->find($id);
        if (!$media) {
            return false;
        }
        
        // Delete file if exists
        if (file_exists($media['file_path'])) {
            unlink($media['file_path']);
        }
        
        // Delete database record
        return $this->delete($id);
    }
    
    /**
     * Get media by story
     * 
     * @param int $storyId
     * @param string $mediaType Optional media type filter
     * @return array
     */
    public function getByStory($storyId, $mediaType = null) {
        $sql = "SELECT * FROM {$this->table} WHERE story_id = ?";
        $params = [$storyId];
        
        if ($mediaType) {
            $sql .= " AND media_type = ?";
            $params[] = $mediaType;
        }
        
        $sql .= " ORDER BY created_at ASC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get images for story
     * 
     * @param int $storyId
     * @return array
     */
    public function getImages($storyId) {
        return $this->getByStory($storyId, 'image');
    }
    
    /**
     * Get featured image for story
     * 
     * @param int $storyId
     * @return array|false
     */
    public function getFeaturedImage($storyId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE story_id = ? AND media_type = 'image' 
                ORDER BY created_at ASC 
                LIMIT 1";
        return $this->db->selectOne($sql, [$storyId]);
    }
    
    /**
     * Validate file upload
     * 
     * @param array $file
     * @return bool
     */
    private function validateUpload($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_log('File upload error: ' . $file['error']);
            return false;
        }
        
        // Check file size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            error_log('File too large: ' . $file['size']);
            return false;
        }
        
        // Check if file was actually uploaded
        if (!is_uploaded_file($file['tmp_name'])) {
            error_log('File not uploaded via HTTP POST');
            return false;
        }
        
        // Get file extension
        $fileInfo = $this->getFileInfo($file);
        $mediaType = $this->getMediaType($fileInfo['extension']);
        
        // Check if file type is allowed
        if (!$this->isAllowedType($fileInfo['extension'], $mediaType)) {
            error_log('File type not allowed: ' . $fileInfo['extension']);
            return false;
        }
        
        return true;
    }
    
    /**
     * Get file information
     * 
     * @param array $file
     * @return array
     */
    private function getFileInfo($file) {
        $pathInfo = pathinfo($file['name']);
        
        return [
            'original_name' => $file['name'],
            'extension' => strtolower($pathInfo['extension'] ?? ''),
            'size' => $file['size'],
            'mime_type' => $file['type'],
            'tmp_name' => $file['tmp_name']
        ];
    }
    
    /**
     * Get media type from file extension
     * 
     * @param string $extension
     * @return string
     */
    private function getMediaType($extension) {
        foreach (self::ALLOWED_TYPES as $type => $extensions) {
            if (in_array($extension, $extensions)) {
                return $type;
            }
        }
        return 'other';
    }
    
    /**
     * Check if file type is allowed
     * 
     * @param string $extension
     * @param string $mediaType
     * @return bool
     */
    private function isAllowedType($extension, $mediaType) {
        if ($mediaType === 'other') {
            return false; // Don't allow unrecognized types
        }
        
        return in_array($extension, self::ALLOWED_TYPES[$mediaType]);
    }
    
    /**
     * Generate unique filename
     * 
     * @param string $extension
     * @return string
     */
    private function generateFilename($extension) {
        return uniqid() . '_' . time() . '.' . $extension;
    }
    
    /**
     * Ensure upload directory exists
     * 
     * @return bool
     */
    private function ensureUploadDir() {
        $dir = self::UPLOAD_DIR;
        if (!is_dir($dir)) {
            return mkdir($dir, 0755, true);
        }
        return true;
    }
    
    /**
     * Get media URL
     * 
     * @param array $media
     * @return string
     */
    public function getMediaUrl($media) {
        return '/' . $media['file_path'];
    }
    
    /**
     * Get thumbnail URL (for images)
     * 
     * @param array $media
     * @param int $width
     * @param int $height
     * @return string
     */
    public function getThumbnailUrl($media, $width = 150, $height = 150) {
        if ($media['media_type'] !== 'image') {
            return $this->getMediaUrl($media);
        }
        
        // For now, return original image
        // In a production environment, you'd implement thumbnail generation
        return $this->getMediaUrl($media);
    }
    
    /**
     * Get media statistics
     * 
     * @return array
     */
    public function getStats() {
        $sql = "SELECT 
                    media_type,
                    COUNT(*) as count,
                    SUM(file_size) as total_size
                FROM {$this->table} 
                GROUP BY media_type";
        
        return $this->db->select($sql);
    }
}

?>
