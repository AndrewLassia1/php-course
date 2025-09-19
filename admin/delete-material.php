<?php
/**
 * Delete Material Handler
 * Learning Materials Management System
 */

// Include required files
require_once '../config/app.php';
require_once '../classes/Auth.php';
require_once '../classes/Material.php';
require_once '../classes/FileHandler.php';
require_once '../includes/functions.php';

// Start session and require authentication
session_start();
$auth = new Auth();
$auth->requireAuth();

// Initialize classes
$material = new Material();
$fileHandler = new FileHandler();

// Get material ID
$materialId = (int)get('id', 0);

if (!$materialId) {
    redirectWithMessage('/admin/materials.php', 'Invalid material ID.', 'error');
}

// Get material data
try {
    $materialData = $material->getMaterialById($materialId);
    if (!$materialData) {
        redirectWithMessage('/admin/materials.php', 'Material not found.', 'error');
    }
} catch (Exception $e) {
    logActivity('Delete material error: ' . $e->getMessage(), 'ERROR');
    redirectWithMessage('/admin/materials.php', 'Unable to load material.', 'error');
}

// Handle CSRF protection
$csrfToken = get('token', '');
if (!$auth->verifyCSRFToken($csrfToken)) {
    redirectWithMessage('/admin/materials.php', 'Invalid security token. Please try again.', 'error');
}

// Perform deletion
try {
    // Delete the file first
    $fileName = basename($materialData['file_path']);
    $fileDeleted = $fileHandler->deleteFile($fileName);
    
    // Delete the database record
    $material->deleteMaterial($materialId);
    
    // Log the deletion
    logActivity('Material deleted', 'INFO', [
        'material_id' => $materialId,
        'title' => $materialData['title'],
        'file_name' => $fileName,
        'file_deleted' => $fileDeleted
    ]);
    
    $message = 'Material "' . htmlspecialchars($materialData['title']) . '" has been successfully deleted.';
    if (!$fileDeleted) {
        $message .= ' (Note: Associated file could not be deleted from storage.)';
    }
    
    redirectWithMessage('/admin/materials.php', $message, 'success');
    
} catch (Exception $e) {
    logActivity('Delete material error: ' . $e->getMessage(), 'ERROR', [
        'material_id' => $materialId,
        'title' => $materialData['title']
    ]);
    
    redirectWithMessage('/admin/materials.php', 'Failed to delete material. Please try again.', 'error');
}
?>