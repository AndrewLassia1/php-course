<?php
/**
 * Admin Login Page
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

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';
$success = '';

// Handle login form submission
if (isPost()) {
    $username = post('username', '');
    $password = post('password', '');
    $csrfToken = post('csrf_token', '');
    
    // Validate CSRF token
    if (!$auth->verifyCSRFToken($csrfToken)) {
        $error = 'Invalid request. Please try again.';
    } 
    // Validate input
    elseif (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    }
    // Attempt login
    else {
        try {
            if ($auth->login($username, $password)) {
                logActivity('Admin login successful', 'INFO', ['username' => $username]);
                redirectWithMessage('/admin/dashboard.php', 'Welcome back, ' . htmlspecialchars($username) . '!', 'success');
            } else {
                $error = 'Invalid username or password.';
                logActivity('Admin login failed', 'WARNING', ['username' => $username]);
            }
        } catch (Exception $e) {
            $error = 'Login failed. Please try again later.';
            logActivity('Admin login error: ' . $e->getMessage(), 'ERROR', ['username' => $username]);
        }
    }
}

// Generate CSRF token
$csrfToken = $auth->generateCSRFToken();

// Set page title
$page_title = 'Admin Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title . ' - ' . AppConfig::APP_NAME; ?></title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-navy) 0%, var(--primary-blue) 100%);
        }
        
        .login-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://placehold.co/1920x1080?text=Professional+learning+management+system+background+with+modern+geometric+patterns');
            background-size: cover;
            background-position: center;
            opacity: 0.1;
            z-index: 1;
        }
        
        .login-container {
            position: relative;
            z-index: 2;
        }
    </style>
</head>
<body class="admin-body">
    <!-- Background Image -->
    <div class="login-background"></div>
    
    <!-- Login Container -->
    <div class="login-container">
        <div class="login-card">
            <!-- Login Header -->
            <div class="login-header">
                <div class="login-brand">
                    <img src="https://placehold.co/80x80?text=LMS+Logo" alt="<?php echo AppConfig::APP_NAME; ?> Logo" style="width: 60px; height: 60px; border-radius: 50%; margin-bottom: 16px;">
                </div>
                <h1 class="login-title"><?php echo AppConfig::APP_NAME; ?></h1>
                <p class="login-subtitle">Admin Panel Access</p>
            </div>
            
            <!-- Error/Success Messages -->
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form method="POST" action="/admin/login.php" class="login-form" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input" 
                        placeholder="Enter your username"
                        value="<?php echo htmlspecialchars(post('username', '')); ?>"
                        required 
                        autocomplete="username"
                        autofocus
                    >
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="Enter your password"
                        required 
                        autocomplete="current-password"
                    >
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary login-btn">
                        Sign In to Admin Panel
                    </button>
                </div>
            </form>
            
            <!-- Login Info -->
            <div class="login-footer">
                <p style="text-align: center; color: var(--text-secondary); font-size: 0.875rem; margin-top: var(--spacing-lg);">
                    <strong>Demo Credentials:</strong><br>
                    Username: <code>admin</code><br>
                    Password: <code>admin123</code>
                </p>
                
                <div style="text-align: center; margin-top: var(--spacing-lg); padding-top: var(--spacing-lg); border-top: 1px solid var(--border-color);">
                    <a href="/" style="color: var(--primary-blue); text-decoration: none; font-size: 0.875rem;">
                        ← Back to Website
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- System Info -->
    <div style="position: fixed; bottom: 20px; left: 20px; color: rgba(255, 255, 255, 0.6); font-size: 0.75rem; z-index: 3;">
        <?php echo AppConfig::APP_NAME . ' v' . AppConfig::APP_VERSION; ?>
    </div>
    
    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Focus management
            const usernameField = document.getElementById('username');
            const passwordField = document.getElementById('password');
            
            // Auto-focus first empty field
            if (usernameField && !usernameField.value) {
                usernameField.focus();
            } else if (passwordField) {
                passwordField.focus();
            }
            
            // Form validation
            const loginForm = document.querySelector('.login-form');
            loginForm.addEventListener('submit', function(e) {
                const username = usernameField.value.trim();
                const password = passwordField.value;
                
                if (!username || !password) {
                    e.preventDefault();
                    showAlert('Please enter both username and password.', 'error');
                    return false;
                }
                
                if (password.length < 6) {
                    e.preventDefault();
                    showAlert('Password must be at least 6 characters long.', 'error');
                    return false;
                }
                
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner"></span>Signing In...';
                submitBtn.disabled = true;
                
                // Reset button if there's an error (handled by browser reload)
                setTimeout(() => {
                    if (document.querySelector('.alert-error')) {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                }, 100);
            });
            
            // Enter key handling
            document.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && e.target.matches('input')) {
                    e.preventDefault();
                    
                    if (e.target === usernameField) {
                        passwordField.focus();
                    } else if (e.target === passwordField) {
                        loginForm.submit();
                    }
                }
            });
            
            // Security: Clear password on page hide/unload
            window.addEventListener('beforeunload', function() {
                if (passwordField) {
                    passwordField.value = '';
                }
            });
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
        });
        
        // Simple alert function
        function showAlert(message, type = 'info') {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = message;
            alert.style.position = 'fixed';
            alert.style.top = '20px';
            alert.style.right = '20px';
            alert.style.zIndex = '1000';
            alert.style.minWidth = '300px';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            alert.style.transition = 'all 0.3s ease';
            
            document.body.appendChild(alert);
            
            setTimeout(() => {
                alert.style.opacity = '1';
                alert.style.transform = 'translateY(0)';
            }, 100);
            
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }, 4000);
        }
    </script>
</body>
</html>