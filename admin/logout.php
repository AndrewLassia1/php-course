<?php
/**
 * Admin Logout
 * Learning Materials Management System
 */

// Include required files
require_once '../config/app.php';
require_once '../classes/Auth.php';
require_once '../includes/functions.php';

// Start session
session_start();

// Initialize auth
$auth = new Auth();

// Get current user before logout
$user = $auth->getCurrentUser();

// Perform logout
if ($user) {
    logActivity('Admin logout', 'INFO', ['username' => $user['username']]);
    $auth->logout();
    redirectWithMessage('/admin/login.php', 'You have been successfully logged out.', 'success');
} else {
    // Already logged out
    header('Location: /admin/login.php');
    exit;
}
?>