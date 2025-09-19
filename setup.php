<?php
/**
 * Database Setup Script
 * Learning Materials Management System
 * 
 * This script initializes the database and creates required tables
 * Run this once during initial installation
 */

// Include required files
require_once 'config/database.php';
require_once 'config/app.php';

// Disable error reporting for production (remove in development)
// error_reporting(0);
// ini_set('display_errors', 0);

// Check if already installed
$lockFile = __DIR__ . '/.installed';
if (file_exists($lockFile)) {
    die('System already installed. Remove .installed file to reinstall.');
}

$messages = [];
$errors = [];

/**
 * Execute SQL file
 */
function executeSQLFile($pdo, $filename) {
    global $messages, $errors;
    
    if (!file_exists($filename)) {
        $errors[] = "SQL file not found: $filename";
        return false;
    }
    
    $sql = file_get_contents($filename);
    
    try {
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        
        $messages[] = "Successfully executed: " . basename($filename);
        return true;
    } catch (PDOException $e) {
        $errors[] = "Error executing $filename: " . $e->getMessage();
        return false;
    }
}

/**
 * Create upload directory
 */
function createUploadDirectory() {
    global $messages, $errors;
    
    $uploadDir = AppConfig::getUploadPath();
    
    if (!is_dir($uploadDir)) {
        if (mkdir($uploadDir, 0755, true)) {
            $messages[] = "Created upload directory: $uploadDir";
        } else {
            $errors[] = "Failed to create upload directory: $uploadDir";
            return false;
        }
    } else {
        $messages[] = "Upload directory already exists: $uploadDir";
    }
    
    // Create .htaccess for security
    $htaccessPath = $uploadDir . '.htaccess';
    $htaccessContent = "# Prevent direct access to uploaded files\n";
    $htaccessContent .= "Options -Indexes\n";
    $htaccessContent .= "RewriteEngine On\n";
    $htaccessContent .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
    $htaccessContent .= "RewriteRule ^.*$ ../download.php [L,QSA]\n";
    
    if (file_put_contents($htaccessPath, $htaccessContent)) {
        $messages[] = "Created upload security file: .htaccess";
    } else {
        $errors[] = "Failed to create upload security file";
    }
    
    return true;
}

/**
 * Create logs directory
 */
function createLogsDirectory() {
    global $messages, $errors;
    
    $logsDir = __DIR__ . '/logs';
    
    if (!is_dir($logsDir)) {
        if (mkdir($logsDir, 0755, true)) {
            $messages[] = "Created logs directory: $logsDir";
        } else {
            $errors[] = "Failed to create logs directory: $logsDir";
            return false;
        }
    } else {
        $messages[] = "Logs directory already exists: $logsDir";
    }
    
    // Create .htaccess to deny access
    $htaccessPath = $logsDir . '/.htaccess';
    $htaccessContent = "# Deny access to log files\n";
    $htaccessContent .= "Deny from all\n";
    
    if (file_put_contents($htaccessPath, $htaccessContent)) {
        $messages[] = "Created logs security file: .htaccess";
    }
    
    return true;
}

// Start setup process
if (isset($_POST['install'])) {
    try {
        // Test database connection
        $config = DatabaseConfig::getConfig();
        $dsn = DatabaseConfig::getDSN();
        $options = DatabaseConfig::getPDOOptions();
        
        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        $messages[] = "Database connection successful";
        
        // Execute database schema
        if (executeSQLFile($pdo, 'database/schema.sql')) {
            $messages[] = "Database tables created successfully";
        }
        
        // Create directories
        createUploadDirectory();
        createLogsDirectory();
        
        // Create installation lock file
        if (empty($errors)) {
            $lockContent = json_encode([
                'installed_at' => date('Y-m-d H:i:s'),
                'version' => AppConfig::APP_VERSION,
                'php_version' => PHP_VERSION
            ]);
            
            if (file_put_contents($lockFile, $lockContent)) {
                $messages[] = "Installation completed successfully!";
                $messages[] = "System is now ready for use.";
                $messages[] = "Default admin credentials: admin / admin123";
                $messages[] = "Please change the default password after first login.";
            }
        }
        
    } catch (Exception $e) {
        $errors[] = "Setup failed: " . $e->getMessage();
    }
}

// Check system requirements
function checkRequirements() {
    $requirements = [];
    
    // PHP version
    $requirements[] = [
        'name' => 'PHP Version',
        'required' => '7.4+',
        'current' => PHP_VERSION,
        'status' => version_compare(PHP_VERSION, '7.4', '>=')
    ];
    
    // PDO extension
    $requirements[] = [
        'name' => 'PDO Extension',
        'required' => 'Required',
        'current' => extension_loaded('pdo') ? 'Available' : 'Not Available',
        'status' => extension_loaded('pdo')
    ];
    
    // PDO MySQL
    $requirements[] = [
        'name' => 'PDO MySQL',
        'required' => 'Required',
        'current' => extension_loaded('pdo_mysql') ? 'Available' : 'Not Available',
        'status' => extension_loaded('pdo_mysql')
    ];
    
    // File uploads
    $requirements[] = [
        'name' => 'File Uploads',
        'required' => 'Enabled',
        'current' => ini_get('file_uploads') ? 'Enabled' : 'Disabled',
        'status' => ini_get('file_uploads')
    ];
    
    // Upload max filesize
    $uploadMax = ini_get('upload_max_filesize');
    $requirements[] = [
        'name' => 'Upload Max Filesize',
        'required' => '50M+',
        'current' => $uploadMax,
        'status' => (int)$uploadMax >= 50
    ];
    
    // Memory limit
    $memoryLimit = ini_get('memory_limit');
    $requirements[] = [
        'name' => 'Memory Limit',
        'required' => '128M+',
        'current' => $memoryLimit,
        'status' => (int)$memoryLimit >= 128 || $memoryLimit === '-1'
    ];
    
    return $requirements;
}

$requirements = checkRequirements();
$canInstall = !in_array(false, array_column($requirements, 'status'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - <?php echo AppConfig::APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, var(--primary-navy) 0%, var(--primary-blue) 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .setup-container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--bg-white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }
        
        .setup-header {
            background: var(--primary-navy);
            color: var(--text-white);
            padding: var(--spacing-2xl);
            text-align: center;
        }
        
        .setup-content {
            padding: var(--spacing-2xl);
        }
        
        .requirements-table {
            width: 100%;
            border-collapse: collapse;
            margin: var(--spacing-lg) 0;
        }
        
        .requirements-table th,
        .requirements-table td {
            padding: var(--spacing-md);
            border: 1px solid var(--border-color);
            text-align: left;
        }
        
        .requirements-table th {
            background: var(--bg-gray);
            font-weight: 600;
        }
        
        .status-ok {
            color: var(--accent-emerald);
            font-weight: 600;
        }
        
        .status-error {
            color: var(--accent-red);
            font-weight: 600;
        }
        
        .install-section {
            background: var(--bg-light);
            padding: var(--spacing-xl);
            border-radius: var(--radius-md);
            margin-top: var(--spacing-xl);
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1><?php echo AppConfig::APP_NAME; ?> Setup</h1>
            <p>Initial system installation and configuration</p>
        </div>
        
        <div class="setup-content">
            <!-- System Requirements -->
            <section class="requirements-section">
                <h2 style="color: var(--primary-navy); margin-bottom: var(--spacing-lg);">System Requirements</h2>
                
                <table class="requirements-table">
                    <thead>
                        <tr>
                            <th>Requirement</th>
                            <th>Required</th>
                            <th>Current</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requirements as $req): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($req['name']); ?></td>
                                <td><?php echo htmlspecialchars($req['required']); ?></td>
                                <td><?php echo htmlspecialchars($req['current']); ?></td>
                                <td class="<?php echo $req['status'] ? 'status-ok' : 'status-error'; ?>">
                                    <?php echo $req['status'] ? '✓ OK' : '✗ FAIL'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if (!$canInstall): ?>
                    <div class="alert alert-error">
                        <strong>Cannot proceed with installation.</strong> 
                        Please fix the failed requirements above and refresh this page.
                    </div>
                <?php endif; ?>
            </section>
            
            <!-- Installation Section -->
            <?php if ($canInstall): ?>
                <section class="install-section">
                    <h2 style="color: var(--primary-navy); margin-bottom: var(--spacing-lg);">Installation</h2>
                    
                    <?php if (!empty($messages)): ?>
                        <div class="alert alert-success">
                            <ul style="margin: 0; padding-left: 20px;">
                                <?php foreach ($messages as $message): ?>
                                    <li><?php echo htmlspecialchars($message); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-error">
                            <ul style="margin: 0; padding-left: 20px;">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (empty($messages) || !empty($errors)): ?>
                        <p style="margin-bottom: var(--spacing-lg);">
                            This will create the database tables, upload directories, and configure the system for first use.
                        </p>
                        
                        <form method="POST" action="">
                            <button type="submit" name="install" class="btn btn-primary btn-lg">
                                Install <?php echo AppConfig::APP_NAME; ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <div style="text-align: center; margin-top: var(--spacing-xl);">
                            <h3 style="color: var(--accent-emerald);">Installation Complete!</h3>
                            <p>Your learning materials management system is ready to use.</p>
                            
                            <div style="display: flex; gap: var(--spacing-md); justify-content: center; margin-top: var(--spacing-lg);">
                                <a href="/admin/login.php" class="btn btn-primary">Access Admin Panel</a>
                                <a href="/" class="btn btn-outline">View Website</a>
                            </div>
                            
                            <div style="background: var(--bg-gray); padding: var(--spacing-lg); border-radius: var(--radius-md); margin-top: var(--spacing-xl); text-align: left;">
                                <h4 style="color: var(--primary-navy);">Default Admin Credentials:</h4>
                                <p><strong>Username:</strong> admin</p>
                                <p><strong>Password:</strong> admin123</p>
                                <p style="color: var(--accent-red); margin: 0;">
                                    <strong>Security Notice:</strong> Please change these credentials immediately after first login!
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
            
            <!-- Database Configuration Info -->
            <section class="config-info" style="margin-top: var(--spacing-xl);">
                <h2 style="color: var(--primary-navy); margin-bottom: var(--spacing-lg);">Configuration</h2>
                
                <div style="background: var(--bg-light); padding: var(--spacing-lg); border-radius: var(--radius-md);">
                    <h3 style="color: var(--primary-blue);">Database Configuration</h3>
                    <p><strong>Database Name:</strong> <?php echo DatabaseConfig::getConfig()['dbname']; ?></p>
                    <p><strong>Host:</strong> <?php echo DatabaseConfig::getConfig()['host']; ?></p>
                    <p><strong>Username:</strong> <?php echo DatabaseConfig::getConfig()['username']; ?></p>
                    
                    <h3 style="color: var(--primary-blue); margin-top: var(--spacing-lg);">Application Settings</h3>
                    <p><strong>Upload Directory:</strong> <?php echo AppConfig::UPLOAD_DIR; ?></p>
                    <p><strong>Max File Size:</strong> <?php echo formatFileSize(AppConfig::MAX_FILE_SIZE); ?></p>
                    <p><strong>Allowed File Types:</strong> <?php echo implode(', ', AppConfig::ALLOWED_FILE_TYPES); ?></p>
                </div>
                
                <small style="color: var(--text-secondary); display: block; margin-top: var(--spacing-md);">
                    <strong>Note:</strong> Update database credentials in <code>config/database.php</code> if needed.
                </small>
            </section>
        </div>
    </div>
    
    <script>
        // Auto-refresh if installation is in progress
        <?php if (isset($_POST['install']) && !empty($errors)): ?>
            setTimeout(() => {
                location.reload();
            }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>

<?php
/**
 * Helper function for file size formatting
 */
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 B';
    $sizes = array('B', 'KB', 'MB', 'GB', 'TB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.2f %s", $bytes / pow(1024, $factor), $sizes[$factor]);
}
?>