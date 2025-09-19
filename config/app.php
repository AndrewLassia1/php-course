<?php
/**
 * Application Configuration
 * Learning Materials Management System
 */

class AppConfig {
    // Application Settings
    public const APP_NAME = 'Learning Materials Hub';
    public const APP_VERSION = '1.0.0';
    public const APP_URL = 'http://localhost';
    
    // File Upload Settings
    public const UPLOAD_DIR = 'uploads/';
    public const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB
    public const ALLOWED_FILE_TYPES = ['pdf', 'doc', 'docx'];
    public const ALLOWED_MIME_TYPES = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    // Security Settings
    public const SESSION_LIFETIME = 3600; // 1 hour
    public const CSRF_TOKEN_LENGTH = 32;
    public const PASSWORD_MIN_LENGTH = 8;
    
    // Pagination Settings
    public const ITEMS_PER_PAGE = 12;
    public const ADMIN_ITEMS_PER_PAGE = 20;
    
    // Currency Settings
    public const CURRENCY_SYMBOL = '$';
    public const CURRENCY_CODE = 'USD';
    
    /**
     * Get upload directory path
     */
    public static function getUploadPath() {
        return __DIR__ . '/../' . self::UPLOAD_DIR;
    }
    
    /**
     * Get full URL for uploaded files
     */
    public static function getFileUrl($filename) {
        return self::APP_URL . '/' . self::UPLOAD_DIR . $filename;
    }
    
    /**
     * Check if file type is allowed
     */
    public static function isAllowedFileType($extension) {
        return in_array(strtolower($extension), self::ALLOWED_FILE_TYPES);
    }
    
    /**
     * Get MIME type for file extension
     */
    public static function getMimeType($extension) {
        return self::ALLOWED_MIME_TYPES[strtolower($extension)] ?? null;
    }
    
    /**
     * Format price with currency
     */
    public static function formatPrice($price) {
        return self::CURRENCY_SYMBOL . number_format($price, 2);
    }
    
    /**
     * Format file size in human readable format
     */
    public static function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
?>