<?php
/**
 * Secure Download Handler
 * Learning Materials Management System
 */

// Include required files
require_once 'config/app.php';
require_once 'classes/Material.php';
require_once 'classes/FileHandler.php';
require_once 'includes/functions.php';

// Start session
session_start();

// Get material ID from request
$materialId = (int)get('id', 0);

if (!$materialId) {
    http_response_code(400);
    die('Invalid material ID');
}

try {
    // Initialize classes
    $material = new Material();
    $fileHandler = new FileHandler();
    
    // Get material information
    $materialInfo = $material->getMaterialById($materialId);
    
    if (!$materialInfo) {
        http_response_code(404);
        die('Material not found');
    }
    
    // Check if material is active
    if (!$materialInfo['is_active']) {
        http_response_code(403);
        die('Material is not available for download');
    }
    
    // Get file path
    $filePath = $materialInfo['file_path'];
    $fileName = basename($filePath);
    
    // Validate file exists
    $fileInfo = $fileHandler->getFileInfo($fileName);
    if (!$fileInfo) {
        http_response_code(404);
        die('File not found');
    }
    
    // Log download attempt
    $clientIP = getClientIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    logActivity("Download initiated", 'INFO', [
        'material_id' => $materialId,
        'material_title' => $materialInfo['title'],
        'file_name' => $fileName,
        'client_ip' => $clientIP,
        'user_agent' => $userAgent
    ]);
    
    // Increment download counter
    $material->incrementDownloads($materialId);
    
    // Log download in database
    $material->logDownload($materialId, $clientIP, $userAgent);
    
    // Generate download filename
    $downloadName = sanitizeFilename($materialInfo['title']) . '.' . getFileExtension($fileName);
    
    // Serve file for download
    $fileHandler->serveFile($fileName, $downloadName);
    
} catch (Exception $e) {
    // Log error
    logActivity("Download error: " . $e->getMessage(), 'ERROR', [
        'material_id' => $materialId,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    http_response_code(500);
    die('Download failed. Please try again later.');
}

/**
 * Sanitize filename for download
 */
function sanitizeFilename($filename) {
    // Remove or replace unsafe characters
    $filename = preg_replace('/[^a-zA-Z0-9\-_\s]/', '', $filename);
    $filename = preg_replace('/\s+/', '-', $filename);
    $filename = trim($filename, '-');
    
    // Limit length
    if (strlen($filename) > 100) {
        $filename = substr($filename, 0, 100);
    }
    
    return $filename ?: 'download';
}
?>