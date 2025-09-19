<?php
/**
 * Common Header Template
 * Learning Materials Management System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current page info
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$is_admin = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Access premium learning materials including PDFs, Word documents and educational resources. Professional learning hub for students and professionals.">
    <meta name="keywords" content="learning materials, PDF downloads, educational resources, study guides, professional development">
    <meta name="author" content="Learning Materials Hub">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo AppConfig::APP_NAME; ?> - Premium Learning Materials">
    <meta property="og:description" content="Access high-quality learning materials and educational resources">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo AppConfig::APP_URL; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
    
    <!-- Title -->
    <title><?php 
        if (isset($page_title)) {
            echo htmlspecialchars($page_title) . ' - ' . AppConfig::APP_NAME;
        } else {
            echo AppConfig::APP_NAME . ' - Premium Learning Materials';
        }
    ?></title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <?php if ($is_admin): ?>
        <link rel="stylesheet" href="/assets/css/admin.css">
    <?php endif; ?>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Additional head content -->
    <?php if (isset($additional_head)): ?>
        <?php echo $additional_head; ?>
    <?php endif; ?>
</head>
<body class="<?php echo $is_admin ? 'admin-body' : 'public-body'; ?>">
    
    <?php if (!$is_admin): ?>
    <!-- Public Site Header -->
    <header class="header">
        <div class="header-container container">
            <div class="logo">
                <a href="/" style="color: inherit; text-decoration: none;">
                    <?php echo AppConfig::APP_NAME; ?>
                </a>
            </div>
            
            <nav class="nav">
                <ul class="nav-menu">
                    <li><a href="/" class="<?php echo ($current_page === 'index') ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="/search.php" class="<?php echo ($current_page === 'search') ? 'active' : ''; ?>">Search</a></li>
                    <li><a href="/#materials" class="<?php echo ($current_page === 'materials') ? 'active' : ''; ?>">Materials</a></li>
                    <li><a href="/#about">About</a></li>
                    <li><a href="/#contact">Contact</a></li>
                </ul>
            </nav>
            
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" aria-label="Toggle navigation">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>
    
    <?php else: ?>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-container">
            <div class="admin-header-content">
                <div class="admin-logo">
                    <a href="/admin/dashboard.php" style="color: inherit; text-decoration: none;">
                        <?php echo AppConfig::APP_NAME; ?> Admin
                    </a>
                </div>
                
                <div class="admin-user-info">
                    <?php
                    require_once __DIR__ . '/../classes/Auth.php';
                    $auth = new Auth();
                    $user = $auth->getCurrentUser();
                    
                    if ($user): ?>
                        <span class="admin-username">Welcome, <?php echo htmlspecialchars($user['username']); ?></span>
                        <a href="/admin/logout.php" class="logout-btn">Logout</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Admin Navigation -->
    <nav class="admin-nav">
        <div class="admin-container">
            <ul class="admin-nav-list">
                <li class="admin-nav-item">
                    <a href="/admin/dashboard.php" class="<?php echo ($current_page === 'dashboard') ? 'active' : ''; ?>">
                        Dashboard
                    </a>
                </li>
                <li class="admin-nav-item">
                    <a href="/admin/materials.php" class="<?php echo ($current_page === 'materials') ? 'active' : ''; ?>">
                        Materials
                    </a>
                </li>
                <li class="admin-nav-item">
                    <a href="/admin/add-material.php" class="<?php echo ($current_page === 'add-material') ? 'active' : ''; ?>">
                        Add Material
                    </a>
                </li>
                <li class="admin-nav-item">
                    <a href="/" target="_blank">
                        View Site
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    <?php endif; ?>
    
    <!-- Main Content Container -->
    <main class="main-content">
        <?php if (!$is_admin): ?>
        <div class="container">
        <?php else: ?>
        <div class="admin-container">
        <?php endif; ?>
            
            <!-- Flash Messages -->
            <?php
            if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo htmlspecialchars($_SESSION['success_message']);
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif;
            
            if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error">
                    <?php 
                    echo htmlspecialchars($_SESSION['error_message']);
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif;
            
            if (isset($_SESSION['info_message'])): ?>
                <div class="alert alert-info">
                    <?php 
                    echo htmlspecialchars($_SESSION['info_message']);
                    unset($_SESSION['info_message']);
                    ?>
                </div>
            <?php endif; ?>