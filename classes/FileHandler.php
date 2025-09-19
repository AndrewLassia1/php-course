<?php
/**
 * File Handler Class
 * Learning Materials Management System
 */

require_once __DIR__ . '/../config/app.php';

class FileHandler {
    private $uploadDir;
    
    public function __construct() {
        $this->uploadDir = AppConfig::getUploadPath();
        $this->createUploadDir();
    }
    
    /**
     * Create upload directory if it doesn't exist
     */
    private function createUploadDir() {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
        
        // Create .htaccess file for security
        $htaccessPath = $this->uploadDir . '.htaccess';
        if (!file_exists($htaccessPath)) {
            $htaccessContent = "# Prevent direct access to uploaded files\n";
            $htaccessContent .= "Options -Indexes\n";
            $htaccessContent .= "RewriteEngine On\n";
            $htaccessContent .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
            $htaccessContent .= "RewriteRule ^.*$ /download.php [L,QSA]\n";
            file_put_contents($htaccessPath, $htaccessContent);
        }
    }
    
    /**
     * Upload file with validation
     */
    public function uploadFile($file, $title = '') {
        $result = [
            'success' => false,
            'message' => '',
            'file_info' => null
        ];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $result['message'] = $this->getUploadErrorMessage($file['error']);
            return $result;
        }
        
        // Validate file size
        if ($file['size'] > AppConfig::MAX_FILE_SIZE) {
            $result['message'] = 'File size exceeds maximum limit of ' . AppConfig::formatFileSize(AppConfig::MAX_FILE_SIZE);
            return $result;
        }
        
        // Get file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Validate file type
        if (!AppConfig::isAllowedFileType($extension)) {
            $result['message'] = 'File type not allowed. Allowed types: ' . implode(', ', AppConfig::ALLOWED_FILE_TYPES);
            return $result;
        }
        
        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $expectedMimeType = AppConfig::getMimeType($extension);
        if ($mimeType !== $expectedMimeType) {
            $result['message'] = 'Invalid file type. File content does not match extension.';
            return $result;
        }
        
        // Generate unique filename
        $filename = $this->generateUniqueFilename($file['name'], $title);
        $filepath = $this->uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $result['success'] = true;
            $result['message'] = 'File uploaded successfully';
            $result['file_info'] = [
                'original_name' => $file['name'],
                'filename' => $filename,
                'file_path' => 'uploads/' . $filename,
                'file_type' => $extension,
                'file_size' => $file['size'],
                'mime_type' => $mimeType
            ];
        } else {
            $result['message'] = 'Failed to move uploaded file';
        }
        
        return $result;
    }
    
    /**
     * Generate unique filename
     */
    private function generateUniqueFilename($originalName, $title = '') {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        
        // Create base filename from title or original name
        $baseName = !empty($title) ? $title : pathinfo($originalName, PATHINFO_FILENAME);
        $baseName = $this->sanitizeFilename($baseName);
        
        // Add timestamp and random string for uniqueness
        $timestamp = date('Y-m-d_H-i-s');
        $randomString = substr(md5(uniqid(rand(), true)), 0, 8);
        
        return $baseName . '_' . $timestamp . '_' . $randomString . '.' . $extension;
    }
    
    /**
     * Sanitize filename
     */
    private function sanitizeFilename($filename) {
        // Remove special characters and spaces
        $filename = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $filename);
        $filename = preg_replace('/-+/', '-', $filename);
        $filename = trim($filename, '-');
        
        // Limit length
        if (strlen($filename) > 50) {
            $filename = substr($filename, 0, 50);
        }
        
        return $filename ?: 'file';
    }
    
    /**
     * Delete file
     */
    public function deleteFile($filename) {
        $filepath = $this->uploadDir . $filename;
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return true; // File doesn't exist, consider it deleted
    }
    
    /**
     * Get file info
     */
    public function getFileInfo($filename) {
        $filepath = $this->uploadDir . $filename;
        
        if (!file_exists($filepath)) {
            return null;
        }
        
        return [
            'filename' => $filename,
            'filepath' => $filepath,
            'size' => filesize($filepath),
            'mime_type' => mime_content_type($filepath),
            'modified' => filemtime($filepath)
        ];
    }
    
    /**
     * Serve file for download
     */
    public function serveFile($filename, $originalName = null) {
        $filepath = $this->uploadDir . $filename;
        
        if (!file_exists($filepath)) {
            http_response_code(404);
            die('File not found');
        }
        
        $fileInfo = $this->getFileInfo($filename);
        $downloadName = $originalName ?: $filename;
        
        // Set headers for file download
        header('Content-Type: ' . $fileInfo['mime_type']);
        header('Content-Length: ' . $fileInfo['size']);
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Output file
        readfile($filepath);
        exit;
    }
    
    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File size exceeds maximum limit';
            case UPLOAD_ERR_PARTIAL:
                return 'File upload was incomplete';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Temporary directory is missing';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
    
    /**
     * Get upload directory path
     */
    public function getUploadDir() {
        return $this->uploadDir;
    }
}
?>