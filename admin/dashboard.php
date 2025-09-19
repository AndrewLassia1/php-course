<?php
/**
 * Admin Dashboard
 * Learning Materials Management System
 */

// Include required files
require_once '../config/app.php';
require_once '../classes/Auth.php';
require_once '../classes/Material.php';
require_once '../includes/functions.php';

// Start session and require authentication
session_start();
$auth = new Auth();
$auth->requireAuth();

// Initialize classes
$material = new Material();

// Get dashboard statistics
try {
    $stats = $material->getStatistics();
} catch (Exception $e) {
    logActivity('Dashboard stats error: ' . $e->getMessage(), 'ERROR');
    $stats = [
        'total_materials' => 0,
        'total_downloads' => 0,
        'by_type' => [],
        'recent_downloads' => []
    ];
}

// Get current user
$user = $auth->getCurrentUser();

// Set page title
$page_title = 'Dashboard';

// Include header
include '../includes/header.php';
?>

<!-- Dashboard Welcome -->
<div class="dashboard-welcome" style="background: var(--bg-white); padding: var(--spacing-xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-bottom: var(--spacing-xl);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
        <div>
            <h1 style="color: var(--primary-navy); margin-bottom: var(--spacing-sm);">
                Welcome back, <?php echo htmlspecialchars($user['username']); ?>!
            </h1>
            <p style="color: var(--text-secondary); margin: 0; font-size: 1.125rem;">
                Manage your learning materials and monitor system performance
            </p>
        </div>
        
        <div class="dashboard-actions" style="display: flex; gap: var(--spacing-md); margin-top: var(--spacing-md);">
            <a href="/admin/add-material.php" class="btn btn-primary">Add New Material</a>
            <a href="/" target="_blank" class="btn btn-outline">View Website</a>
        </div>
    </div>
</div>

<!-- Dashboard Statistics -->
<div class="dashboard-stats">
    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <h3 class="dashboard-card-title">Total Materials</h3>
        </div>
        <div class="dashboard-card-value"><?php echo number_format($stats['total_materials']); ?></div>
        <div class="dashboard-card-trend">
            <span style="color: var(--accent-emerald);">↗ Active materials in system</span>
        </div>
    </div>
    
    <div class="dashboard-card success">
        <div class="dashboard-card-header">
            <h3 class="dashboard-card-title">Total Downloads</h3>
        </div>
        <div class="dashboard-card-value"><?php echo number_format($stats['total_downloads']); ?></div>
        <div class="dashboard-card-trend">
            <span style="color: var(--accent-emerald);">↗ All-time downloads</span>
        </div>
    </div>
    
    <div class="dashboard-card warning">
        <div class="dashboard-card-header">
            <h3 class="dashboard-card-title">File Types</h3>
        </div>
        <div class="dashboard-card-value"><?php echo count($stats['by_type']); ?></div>
        <div class="dashboard-card-trend">
            <span style="color: var(--accent-amber);">Available formats</span>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <h3 class="dashboard-card-title">System Status</h3>
        </div>
        <div class="dashboard-card-value" style="font-size: 1.5rem; color: var(--accent-emerald);">Online</div>
        <div class="dashboard-card-trend">
            <span style="color: var(--accent-emerald);">↗ All systems operational</span>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions" style="background: var(--bg-white); padding: var(--spacing-xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-bottom: var(--spacing-xl);">
    <h2 style="color: var(--primary-navy); margin-bottom: var(--spacing-lg);">Quick Actions</h2>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--spacing-lg);">
        <div class="action-card" style="padding: var(--spacing-lg); border: 1px solid var(--border-color); border-radius: var(--radius-md); text-align: center; transition: transform 0.3s ease;">
            <div style="width: 60px; height: 60px; background: var(--primary-blue); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-md); color: white; font-size: 1.5rem;">+</div>
            <h3 style="color: var(--primary-navy); margin-bottom: var(--spacing-sm);">Add Material</h3>
            <p style="color: var(--text-secondary); margin-bottom: var(--spacing-md); font-size: 0.875rem;">Upload new learning materials to the system</p>
            <a href="/admin/add-material.php" class="btn btn-primary btn-sm">Add New</a>
        </div>
        
        <div class="action-card" style="padding: var(--spacing-lg); border: 1px solid var(--border-color); border-radius: var(--radius-md); text-align: center; transition: transform 0.3s ease;">
            <div style="width: 60px; height: 60px; background: var(--accent-emerald); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-md); color: white; font-size: 1.5rem;">📋</div>
            <h3 style="color: var(--primary-navy); margin-bottom: var(--spacing-sm);">Manage Materials</h3>
            <p style="color: var(--text-secondary); margin-bottom: var(--spacing-md); font-size: 0.875rem;">View, edit, or delete existing materials</p>
            <a href="/admin/materials.php" class="btn btn-success btn-sm">Manage</a>
        </div>
        
        <div class="action-card" style="padding: var(--spacing-lg); border: 1px solid var(--border-color); border-radius: var(--radius-md); text-align: center; transition: transform 0.3s ease;">
            <div style="width: 60px; height: 60px; background: var(--accent-amber); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-md); color: white; font-size: 1.5rem;">👁</div>
            <h3 style="color: var(--primary-navy); margin-bottom: var(--spacing-sm);">View Website</h3>
            <p style="color: var(--text-secondary); margin-bottom: var(--spacing-md); font-size: 0.875rem;">Preview the public website as users see it</p>
            <a href="/" target="_blank" class="btn btn-outline btn-sm">View Site</a>
        </div>
        
        <div class="action-card" style="padding: var(--spacing-lg); border: 1px solid var(--border-color); border-radius: var(--radius-md); text-align: center; transition: transform 0.3s ease;">
            <div style="width: 60px; height: 60px; background: var(--primary-navy); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-md); color: white; font-size: 1.5rem;">📊</div>
            <h3 style="color: var(--primary-navy); margin-bottom: var(--spacing-sm);">Analytics</h3>
            <p style="color: var(--text-secondary); margin-bottom: var(--spacing-md); font-size: 0.875rem;">View download statistics and reports</p>
            <a href="#analytics" class="btn btn-outline btn-sm">View Stats</a>
        </div>
    </div>
</div>

<!-- Materials by Type -->
<?php if (!empty($stats['by_type'])): ?>
<div class="materials-by-type" style="background: var(--bg-white); padding: var(--spacing-xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-bottom: var(--spacing-xl);">
    <h2 style="color: var(--primary-navy); margin-bottom: var(--spacing-lg);">Materials by File Type</h2>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-lg);">
        <?php foreach ($stats['by_type'] as $type): ?>
            <div class="type-card" style="padding: var(--spacing-lg); border: 2px solid var(--border-color); border-radius: var(--radius-md); text-align: center; transition: border-color 0.3s ease;">
                <div class="type-icon" style="font-size: 2rem; margin-bottom: var(--spacing-md);">
                    <?php
                    switch ($type['file_type']) {
                        case 'pdf': echo '📄'; break;
                        case 'doc': echo '📝'; break;
                        case 'docx': echo '📄'; break;
                        default: echo '📋'; break;
                    }
                    ?>
                </div>
                <h3 style="color: var(--primary-navy); margin-bottom: var(--spacing-sm); text-transform: uppercase;">
                    <?php echo htmlspecialchars($type['file_type']); ?> Files
                </h3>
                <div style="font-size: 1.5rem; font-weight: 600; color: var(--primary-blue); margin-bottom: var(--spacing-sm);">
                    <?php echo number_format($type['count']); ?>
                </div>
                <a href="/admin/materials.php?type=<?php echo urlencode($type['file_type']); ?>" class="btn btn-sm btn-outline">View All</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Recent Downloads -->
<?php if (!empty($stats['recent_downloads'])): ?>
<div class="recent-downloads" style="background: var(--bg-white); padding: var(--spacing-xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-bottom: var(--spacing-xl);">
    <h2 style="color: var(--primary-navy); margin-bottom: var(--spacing-lg);">Recent Downloads</h2>
    
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Download Time</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($stats['recent_downloads'], 0, 10) as $download): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars(truncateText($download['title'], 50)); ?></strong>
                        </td>
                        <td>
                            <span style="color: var(--text-secondary);">
                                <?php echo timeAgo($download['downloaded_at']); ?>
                            </span>
                        </td>
                        <td>
                            <code style="background: var(--bg-gray); padding: 2px 6px; border-radius: 3px; font-size: 0.8rem;">
                                <?php echo htmlspecialchars($download['ip_address']); ?>
                            </code>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div style="text-align: center; margin-top: var(--spacing-lg);">
        <a href="/admin/materials.php" class="btn btn-outline">View All Materials</a>
    </div>
</div>
<?php endif; ?>

<!-- System Information -->
<div class="system-info" style="background: var(--bg-white); padding: var(--spacing-xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-bottom: var(--spacing-xl);">
    <h2 style="color: var(--primary-navy); margin-bottom: var(--spacing-lg);">System Information</h2>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--spacing-xl);">
        <div class="info-section">
            <h3 style="color: var(--primary-blue); margin-bottom: var(--spacing-md);">Application Info</h3>
            <ul style="list-style: none; padding: 0;">
                <li style="display: flex; justify-content: space-between; padding: var(--spacing-sm) 0; border-bottom: 1px solid var(--border-color);">
                    <span>Application Name:</span>
                    <strong><?php echo AppConfig::APP_NAME; ?></strong>
                </li>
                <li style="display: flex; justify-content: space-between; padding: var(--spacing-sm) 0; border-bottom: 1px solid var(--border-color);">
                    <span>Version:</span>
                    <strong><?php echo AppConfig::APP_VERSION; ?></strong>
                </li>
                <li style="display: flex; justify-content: space-between; padding: var(--spacing-sm) 0; border-bottom: 1px solid var(--border-color);">
                    <span>Upload Directory:</span>
                    <strong><?php echo AppConfig::UPLOAD_DIR; ?></strong>
                </li>
                <li style="display: flex; justify-content: space-between; padding: var(--spacing-sm) 0;">
                    <span>Max File Size:</span>
                    <strong><?php echo formatFileSize(AppConfig::MAX_FILE_SIZE); ?></strong>
                </li>
            </ul>
        </div>
        
        <div class="info-section">
            <h3 style="color: var(--primary-blue); margin-bottom: var(--spacing-md);">Server Info</h3>
            <ul style="list-style: none; padding: 0;">
                <li style="display: flex; justify-content: space-between; padding: var(--spacing-sm) 0; border-bottom: 1px solid var(--border-color);">
                    <span>PHP Version:</span>
                    <strong><?php echo PHP_VERSION; ?></strong>
                </li>
                <li style="display: flex; justify-content: space-between; padding: var(--spacing-sm) 0; border-bottom: 1px solid var(--border-color);">
                    <span>Server Software:</span>
                    <strong><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></strong>
                </li>
                <li style="display: flex; justify-content: space-between; padding: var(--spacing-sm) 0; border-bottom: 1px solid var(--border-color);">
                    <span>Upload Max Size:</span>
                    <strong><?php echo ini_get('upload_max_filesize'); ?></strong>
                </li>
                <li style="display: flex; justify-content: space-between; padding: var(--spacing-sm) 0;">
                    <span>Memory Limit:</span>
                    <strong><?php echo ini_get('memory_limit'); ?></strong>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to action cards
    const actionCards = document.querySelectorAll('.action-card');
    actionCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = 'var(--shadow-md)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
    
    // Add hover effects to type cards
    const typeCards = document.querySelectorAll('.type-card');
    typeCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.borderColor = 'var(--primary-blue)';
            this.style.transform = 'scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.borderColor = 'var(--border-color)';
            this.style.transform = 'scale(1)';
        });
    });
    
    // Auto-refresh dashboard stats every 5 minutes
    setInterval(function() {
        // Uncomment to enable auto-refresh
        // window.location.reload();
    }, 300000); // 5 minutes
    
    // Dashboard analytics tracking
    if (typeof gtag !== 'undefined') {
        gtag('event', 'admin_dashboard_view', {
            'admin_user': '<?php echo htmlspecialchars($user['username']); ?>'
        });
    }
});
</script>

<?php
// Include footer
include '../includes/footer.php';
?>