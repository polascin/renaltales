<?php

/**
 * FileUploadManager - Secure file upload handling
 * 
 * Provides secure file upload functionality with validation, sanitization, and security checks
 * 
 * @author Ľubomír Polaščín
 * @version 2025.v1.0
 */

class FileUploadManager {
    
    private $config;
    private $errors = [];
    private $uploadedFiles = [];
    
    /**
     * Constructor
     * 
     * @param array $config Upload configuration
     */
    public function __construct($config = []) {
        $this->config = array_merge([
            'upload_path' => 'uploads/',
            'max_file_size' => 10 * 1024 * 1024, // 10MB
            'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'],
            'allowed_mimes' => [
                'image/jpeg',
                'image/png',
                'image/gif',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain'
            ],
            'forbidden_extensions' => [
                'php', 'php3', 'php4', 'php5', 'phtml', 'pl', 'py', 'jsp', 'asp', 'sh', 'cgi',
                'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar', 'htm', 'html'
            ],
            'max_filename_length' => 100,
            'create_thumbnails' => true,
            'thumbnail_size' => 150,
            'virus_scan' => false,
            'quarantine_path' => 'quarantine/',
            'scan_command' => 'clamscan',
            'image_quality' => 85,
            'resize_large_images' => true,
            'max_image_width' => 1920,
            'max_image_height' => 1080
        ], $config);
        
        // Ensure upload directory exists
        $this->ensureDirectoryExists($this->config['upload_path']);
        
        if ($this->config['virus_scan']) {
            $this->ensureDirectoryExists($this->config['quarantine_path']);
        }
    }
    
    /**
     * Upload single file
     * 
     * @param array $fileData $_FILES array data
     * @param string $fieldName Field name
     * @return array Upload result
     */
    public function uploadFile($fileData, $fieldName = 'file') {
        $this->errors = [];
        
        // Basic validation
        if (!$this->validateUpload($fileData)) {
            return [
                'success' => false,
                'errors' => $this->errors
            ];
        }
        
        // Security checks
        if (!$this->performSecurityChecks($fileData)) {
            return [
                'success' => false,
                'errors' => $this->errors
            ];
        }
        
        // Generate secure filename
        $filename = $this->generateSecureFilename($fileData['name']);
        $filepath = $this->config['upload_path'] . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($fileData['tmp_name'], $filepath)) {
            $this->errors[] = 'Failed to move uploaded file';
            return [
                'success' => false,
                'errors' => $this->errors
            ];
        }
        
        // Post-upload processing
        $fileInfo = $this->processUploadedFile($filepath, $fileData);
        
        // Virus scan if enabled
        if ($this->config['virus_scan']) {
            if (!$this->scanForVirus($filepath)) {
                unlink($filepath);
                return [
                    'success' => false,
                    'errors' => ['File failed virus scan']
                ];
            }
        }
        
        $this->uploadedFiles[] = $fileInfo;
        
        return [
            'success' => true,
            'file' => $fileInfo,
            'files' => $this->uploadedFiles
        ];
    }
    
    /**
     * Upload multiple files
     * 
     * @param array $filesData Multiple files data
     * @return array Upload results
     */
    public function uploadMultipleFiles($filesData) {
        $results = [];
        $allSuccess = true;
        
        foreach ($filesData as $fieldName => $fileData) {
            if (is_array($fileData['name'])) {
                // Handle multiple files with same field name
                for ($i = 0; $i < count($fileData['name']); $i++) {
                    $singleFile = [
                        'name' => $fileData['name'][$i],
                        'type' => $fileData['type'][$i],
                        'tmp_name' => $fileData['tmp_name'][$i],
                        'error' => $fileData['error'][$i],
                        'size' => $fileData['size'][$i]
                    ];
                    
                    $result = $this->uploadFile($singleFile, $fieldName);
                    $results[] = $result;
                    
                    if (!$result['success']) {
                        $allSuccess = false;
                    }
                }
            } else {
                // Single file
                $result = $this->uploadFile($fileData, $fieldName);
                $results[] = $result;
                
                if (!$result['success']) {
                    $allSuccess = false;
                }
            }
        }
        
        return [
            'success' => $allSuccess,
            'results' => $results,
            'uploaded_files' => $this->uploadedFiles
        ];
    }
    
    /**
     * Validate file upload
     * 
     * @param array $fileData
     * @return bool
     */
    private function validateUpload($fileData) {
        // Check for upload errors
        if ($fileData['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($fileData['error']);
            return false;
        }
        
        // Check file size
        if ($fileData['size'] > $this->config['max_file_size']) {
            $this->errors[] = 'File size exceeds maximum allowed size of ' . $this->formatBytes($this->config['max_file_size']);
            return false;
        }
        
        // Check if file is empty
        if ($fileData['size'] === 0) {
            $this->errors[] = 'File is empty';
            return false;
        }
        
        // Check filename length
        if (strlen($fileData['name']) > $this->config['max_filename_length']) {
            $this->errors[] = 'Filename is too long';
            return false;
        }
        
        return true;
    }
    
    /**
     * Perform security checks
     * 
     * @param array $fileData
     * @return bool
     */
    private function performSecurityChecks($fileData) {
        // Check file extension
        $extension = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
        
        if (in_array($extension, $this->config['forbidden_extensions'])) {
            $this->errors[] = 'File type is not allowed';
            return false;
        }
        
        if (!in_array($extension, $this->config['allowed_types'])) {
            $this->errors[] = 'File type is not allowed';
            return false;
        }
        
        // Check MIME type
        $mimeType = $this->getMimeType($fileData['tmp_name']);
        if (!in_array($mimeType, $this->config['allowed_mimes'])) {
            $this->errors[] = 'File MIME type is not allowed';
            return false;
        }
        
        // Check if file extension matches MIME type
        if (!$this->validateMimeExtensionMatch($extension, $mimeType)) {
            $this->errors[] = 'File extension does not match content type';
            return false;
        }
        
        // Check for double extensions
        if ($this->hasDoubleExtension($fileData['name'])) {
            $this->errors[] = 'Double file extensions are not allowed';
            return false;
        }
        
        // Check for executable headers
        if ($this->isExecutableFile($fileData['tmp_name'])) {
            $this->errors[] = 'Executable files are not allowed';
            return false;
        }
        
        // Check for embedded scripts in images
        if ($this->isImageFile($mimeType) && $this->hasEmbeddedScripts($fileData['tmp_name'])) {
            $this->errors[] = 'File contains embedded scripts';
            return false;
        }
        
        // Check for PHP code in files
        if ($this->containsPHPCode($fileData['tmp_name'])) {
            $this->errors[] = 'File contains PHP code';
            return false;
        }
        
        return true;
    }
    
    /**
     * Generate secure filename
     * 
     * @param string $originalName
     * @return string
     */
    private function generateSecureFilename($originalName) {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Sanitize basename
        $basename = preg_replace('/[^a-zA-Z0-9._-]/', '', $basename);
        $basename = trim($basename, '.-');
        
        // Ensure basename is not empty
        if (empty($basename)) {
            $basename = 'file';
        }
        
        // Generate unique filename
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        $filename = $basename . '_' . $timestamp . '_' . $random . '.' . $extension;
        
        // Ensure filename is not too long
        if (strlen($filename) > $this->config['max_filename_length']) {
            $maxBasenameLength = $this->config['max_filename_length'] - strlen($extension) - 20; // Reserve space for timestamp and random
            $basename = substr($basename, 0, $maxBasenameLength);
            $filename = $basename . '_' . $timestamp . '_' . $random . '.' . $extension;
        }
        
        return $filename;
    }
    
    /**
     * Process uploaded file
     * 
     * @param string $filepath
     * @param array $fileData
     * @return array
     */
    private function processUploadedFile($filepath, $fileData) {
        $fileInfo = [
            'original_name' => $fileData['name'],
            'filename' => basename($filepath),
            'filepath' => $filepath,
            'size' => $fileData['size'],
            'mime_type' => $this->getMimeType($filepath),
            'extension' => strtolower(pathinfo($filepath, PATHINFO_EXTENSION)),
            'upload_time' => date('Y-m-d H:i:s'),
            'is_image' => $this->isImageFile($this->getMimeType($filepath)),
            'thumbnail' => null,
            'width' => null,
            'height' => null
        ];
        
        // Process images
        if ($fileInfo['is_image']) {
            $imageInfo = getimagesize($filepath);
            if ($imageInfo) {
                $fileInfo['width'] = $imageInfo[0];
                $fileInfo['height'] = $imageInfo[1];
                
                // Resize large images
                if ($this->config['resize_large_images']) {
                    $this->resizeImage($filepath, $fileInfo);
                }
                
                // Create thumbnail
                if ($this->config['create_thumbnails']) {
                    $fileInfo['thumbnail'] = $this->createThumbnail($filepath, $fileInfo);
                }
            }
        }
        
        return $fileInfo;
    }
    
    /**
     * Get MIME type
     * 
     * @param string $filepath
     * @return string
     */
    private function getMimeType($filepath) {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filepath);
            finfo_close($finfo);
            return $mimeType;
        }
        
        return mime_content_type($filepath);
    }
    
    /**
     * Check if file extension matches MIME type
     * 
     * @param string $extension
     * @param string $mimeType
     * @return bool
     */
    private function validateMimeExtensionMatch($extension, $mimeType) {
        $validMatches = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'txt' => ['text/plain']
        ];
        
        return isset($validMatches[$extension]) && in_array($mimeType, $validMatches[$extension]);
    }
    
    /**
     * Check for double extensions
     * 
     * @param string $filename
     * @return bool
     */
    private function hasDoubleExtension($filename) {
        $parts = explode('.', $filename);
        return count($parts) > 2;
    }
    
    /**
     * Check if file is executable
     * 
     * @param string $filepath
     * @return bool
     */
    private function isExecutableFile($filepath) {
        $handle = fopen($filepath, 'rb');
        if (!$handle) {
            return false;
        }
        
        $header = fread($handle, 4);
        fclose($handle);
        
        // Check for common executable headers
        $executableHeaders = [
            "\x7fELF", // ELF
            "MZ", // DOS/Windows executable
            "\xfe\xed\xfa\xce", // Mach-O
            "\xcf\xfa\xed\xfe", // Mach-O
        ];
        
        foreach ($executableHeaders as $execHeader) {
            if (strpos($header, $execHeader) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if file is an image
     * 
     * @param string $mimeType
     * @return bool
     */
    private function isImageFile($mimeType) {
        return strpos($mimeType, 'image/') === 0;
    }
    
    /**
     * Check for embedded scripts in images
     * 
     * @param string $filepath
     * @return bool
     */
    private function hasEmbeddedScripts($filepath) {
        $content = file_get_contents($filepath);
        
        $scriptPatterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/<\?php.*?\?>/is',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload=/i',
            '/onerror=/i'
        ];
        
        foreach ($scriptPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if file contains PHP code
     * 
     * @param string $filepath
     * @return bool
     */
    private function containsPHPCode($filepath) {
        $content = file_get_contents($filepath);
        
        $phpPatterns = [
            '/<\?php/i',
            '/<\?=/i',
            '/<\?[^x]/i',
            '/<script[^>]*language\s*=\s*["\']?php["\']?[^>]*>/i'
        ];
        
        foreach ($phpPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Scan file for viruses
     * 
     * @param string $filepath
     * @return bool
     */
    private function scanForVirus($filepath) {
        if (!$this->config['virus_scan']) {
            return true;
        }
        
        $command = $this->config['scan_command'] . ' ' . escapeshellarg($filepath);
        $output = [];
        $returnCode = 0;
        
        exec($command, $output, $returnCode);
        
        // ClamAV returns 0 for clean files, 1 for infected files
        if ($returnCode === 0) {
            return true;
        }
        
        // Move infected file to quarantine
        $quarantineFile = $this->config['quarantine_path'] . basename($filepath) . '_' . time();
        move_uploaded_file($filepath, $quarantineFile);
        
        // Log virus detection
        $this->logSecurityEvent('virus_detected', [
            'file' => $filepath,
            'quarantine_file' => $quarantineFile,
            'scanner_output' => $output
        ]);
        
        return false;
    }
    
    /**
     * Resize image if too large
     * 
     * @param string $filepath
     * @param array $fileInfo
     */
    private function resizeImage($filepath, &$fileInfo) {
        if ($fileInfo['width'] <= $this->config['max_image_width'] && 
            $fileInfo['height'] <= $this->config['max_image_height']) {
            return;
        }
        
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        
        // Create image resource
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($filepath);
                break;
            case 'png':
                $image = imagecreatefrompng($filepath);
                break;
            case 'gif':
                $image = imagecreatefromgif($filepath);
                break;
            default:
                return;
        }
        
        if (!$image) {
            return;
        }
        
        // Calculate new dimensions
        $ratio = min(
            $this->config['max_image_width'] / $fileInfo['width'],
            $this->config['max_image_height'] / $fileInfo['height']
        );
        
        $newWidth = (int)($fileInfo['width'] * $ratio);
        $newHeight = (int)($fileInfo['height'] * $ratio);
        
        // Create resized image
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and GIF
        if ($extension === 'png' || $extension === 'gif') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }
        
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $fileInfo['width'], $fileInfo['height']);
        
        // Save resized image
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($resized, $filepath, $this->config['image_quality']);
                break;
            case 'png':
                imagepng($resized, $filepath);
                break;
            case 'gif':
                imagegif($resized, $filepath);
                break;
        }
        
        // Update file info
        $fileInfo['width'] = $newWidth;
        $fileInfo['height'] = $newHeight;
        $fileInfo['size'] = filesize($filepath);
        
        // Clean up
        imagedestroy($image);
        imagedestroy($resized);
    }
    
    /**
     * Create thumbnail
     * 
     * @param string $filepath
     * @param array $fileInfo
     * @return string|null
     */
    private function createThumbnail($filepath, $fileInfo) {
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        
        // Create image resource
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($filepath);
                break;
            case 'png':
                $image = imagecreatefrompng($filepath);
                break;
            case 'gif':
                $image = imagecreatefromgif($filepath);
                break;
            default:
                return null;
        }
        
        if (!$image) {
            return null;
        }
        
        // Calculate thumbnail dimensions
        $size = $this->config['thumbnail_size'];
        $ratio = min($size / $fileInfo['width'], $size / $fileInfo['height']);
        $thumbWidth = (int)($fileInfo['width'] * $ratio);
        $thumbHeight = (int)($fileInfo['height'] * $ratio);
        
        // Create thumbnail
        $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
        
        // Preserve transparency
        if ($extension === 'png' || $extension === 'gif') {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
        }
        
        imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $fileInfo['width'], $fileInfo['height']);
        
        // Save thumbnail
        $thumbPath = $this->config['upload_path'] . 'thumb_' . $fileInfo['filename'];
        
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($thumbnail, $thumbPath, $this->config['image_quality']);
                break;
            case 'png':
                imagepng($thumbnail, $thumbPath);
                break;
            case 'gif':
                imagegif($thumbnail, $thumbPath);
                break;
        }
        
        // Clean up
        imagedestroy($image);
        imagedestroy($thumbnail);
        
        return $thumbPath;
    }
    
    /**
     * Ensure directory exists
     * 
     * @param string $path
     */
    private function ensureDirectoryExists($path) {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
    
    /**
     * Get upload error message
     * 
     * @param int $errorCode
     * @return string
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File exceeds the upload_max_filesize directive';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File exceeds the MAX_FILE_SIZE directive';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
    
    /**
     * Format bytes to human readable format
     * 
     * @param int $bytes
     * @return string
     */
    private function formatBytes($bytes) {
        if ($bytes >= 1024 * 1024 * 1024) {
            return round($bytes / (1024 * 1024 * 1024), 2) . ' GB';
        } elseif ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    /**
     * Log security event
     * 
     * @param string $event
     * @param array $context
     */
    private function logSecurityEvent($event, $context = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'context' => $context
        ];
        
        $logDir = APP_DIR . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/file_upload_security.log';
        $logData = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
        
        file_put_contents($logFile, $logData, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get upload errors
     * 
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get uploaded files
     * 
     * @return array
     */
    public function getUploadedFiles() {
        return $this->uploadedFiles;
    }
}

?>
