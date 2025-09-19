<?php
/**
 * Materials Management Page
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

// Initialize material class
$material = new Material();

// Handle filtering and pagination
$search = get('search', '');
$fileType = get('type', '');
$page = max(1, (int)get('page', 1));
$perPage = AppConfig::ADMIN_ITEMS_PER_PAGE;

// Get materials based on filters
try {
    if ($search) {
        $materials = $material->searchMaterials($search, $page, $perPage);
        $totalMaterials = $material->getTotalMaterials($search, $fileType);
    } elseif ($fileType) {
        $materials = $material->getMaterialsByType($fileType, $page, $perPage);
        $totalMaterials = $material->getTotalMaterials(null, $fileType);
    } else {
        $materials = $material->getAllMaterials($page, $perPage);
        $totalMaterials = $material->getTotalMaterials();
    }
    
    // Generate pagination
    $pagination = paginate($totalMaterials, $perPage, $page);
    
} catch (Exception $e) {
    logActivity('Materials page error: ' . $e->getMessage(), 'ERROR');
    $materials = [];
    $totalMaterials = 0;
    $pagination = null;
    setFlashMessage('Unable to load materials. Please try again.', 'error');
}

// Set page title
$page_title = 'Manage Materials';

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header" style="background: var(--bg-white); padding: var(--spacing-xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-bottom: var(--spacing-xl);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--spacing-md);">
        <div>
            <h1 style="color: var(--primary-navy); margin-bottom: var(--spacing-sm);">Materials Management</h1>
            <p style="color: var(--text-secondary); margin: 0;">
                Manage all learning materials in the system
                <?php if ($totalMaterials > 0): ?>
                    (<?php echo number_format($totalMaterials); ?> total)
                <?php endif; ?>
            </p>
        </div>
        
        <div class="header-actions">
            <a href="/admin/add-material.php" class="btn btn-primary">Add New Material</a>
        </div>
    </div>
</div>

<!-- Search and Filter -->
<div class="materials-filter" style="background: var(--bg-white); padding: var(--spacing-lg); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-bottom: var(--spacing-xl);">
    <form method="GET" action="/admin/materials.php" class="filter-form">
        <div style="display: grid; grid-template-columns: 1fr auto auto auto; gap: var(--spacing-md); align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="search" class="form-label">Search Materials</label>
                <input 
                    type="text" 
                    id="search" 
                    name="search" 
                    class="form-input" 
                    placeholder="Search by title or description..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label for="type" class="form-label">File Type</label>
                <select id="type" name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="pdf" <?php echo $fileType === 'pdf' ? 'selected' : ''; ?>>PDF</option>
                    <option value="doc" <?php echo $fileType === 'doc' ? 'selected' : ''; ?>>DOC</option>
                    <option value="docx" <?php echo $fileType === 'docx' ? 'selected' : ''; ?>>DOCX</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Filter</button>
            
            <?php if ($search || $fileType): ?>
                <a href="/admin/materials.php" class="btn btn-outline">Clear</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Materials Table -->
<div class="admin-table-container">
    <div class="admin-table-header">
        <h2 class="admin-table-title">Learning Materials</h2>
        <div class="admin-table-actions">
            <?php if ($search || $fileType): ?>
                <span class="filter-info" style="color: var(--text-secondary); font-size: 0.875rem;">
                    Showing <?php echo number_format(count($materials)); ?> of <?php echo number_format($totalMaterials); ?> materials
                </span>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (empty($materials)): ?>
        <div class="no-materials" style="text-align: center; padding: var(--spacing-2xl);">
            <div style="color: var(--text-secondary);">
                <div style="font-size: 3rem; margin-bottom: var(--spacing-md);">📋</div>
                <h3>No materials found</h3>
                <p>
                    <?php if ($search || $fileType): ?>
                        No materials match your current filters. Try adjusting your search criteria.
                    <?php else: ?>
                        No materials have been added to the system yet.
                    <?php endif; ?>
                </p>
                <div style="margin-top: var(--spacing-lg);">
                    <?php if ($search || $fileType): ?>
                        <a href="/admin/materials.php" class="btn btn-outline">Clear Filters</a>
                    <?php endif; ?>
                    <a href="/admin/add-material.php" class="btn btn-primary">Add First Material</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Title</th>
                    <th style="width: 10%;">Type</th>
                    <th style="width: 10%;">Size</th>
                    <th style="width: 10%;">Price</th>
                    <th style="width: 10%;">Downloads</th>
                    <th style="width: 10%;">Added</th>
                    <th style="width: 10%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($materials as $mat): ?>
                    <tr>
                        <td>
                            <div>
                                <strong style="color: var(--primary-navy);">
                                    <?php echo htmlspecialchars($mat['title']); ?>
                                </strong>
                                <?php if ($mat['description']): ?>
                                    <div style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 2px;">
                                        <?php echo htmlspecialchars(truncateText($mat['description'], 100)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        
                        <td>
                            <span class="meta-tag file-type" style="font-size: 0.75rem;">
                                <?php echo strtoupper($mat['file_type']); ?>
                            </span>
                        </td>
                        
                        <td>
                            <span style="color: var(--text-secondary); font-size: 0.875rem;">
                                <?php echo formatFileSize($mat['file_size']); ?>
                            </span>
                        </td>
                        
                        <td>
                            <?php if ($mat['price'] > 0): ?>
                                <span style="color: var(--accent-emerald); font-weight: 600;">
                                    <?php echo formatPrice($mat['price']); ?>
                                </span>
                            <?php else: ?>
                                <span style="color: var(--primary-blue); font-weight: 600;">FREE</span>
                            <?php endif; ?>
                        </td>
                        
                        <td>
                            <span style="color: var(--text-secondary);">
                                <?php echo number_format($mat['downloads_count']); ?>
                            </span>
                        </td>
                        
                        <td>
                            <span style="color: var(--text-secondary); font-size: 0.875rem;">
                                <?php echo timeAgo($mat['created_at']); ?>
                            </span>
                        </td>
                        
                        <td>
                            <div class="btn-group">
                                <a href="/admin/edit-material.php?id=<?php echo $mat['id']; ?>" 
                                   class="btn btn-sm btn-edit" 
                                   data-tooltip="Edit material">
                                    Edit
                                </a>
                                <a href="/admin/delete-material.php?id=<?php echo $mat['id']; ?>" 
                                   class="btn btn-sm btn-delete" 
                                   data-tooltip="Delete material"
                                   data-item-name="<?php echo htmlspecialchars($mat['title']); ?>"
                                   onclick="return confirmDelete('<?php echo htmlspecialchars($mat['title']); ?>')">
                                    Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($pagination && $pagination['total_pages'] > 1): ?>
            <div class="pagination-container" style="margin-top: var(--spacing-xl);">
                <?php
                $baseUrl = '/admin/materials.php?';
                if ($search) $baseUrl .= 'search=' . urlencode($search) . '&';
                if ($fileType) $baseUrl .= 'type=' . urlencode($fileType) . '&';
                $baseUrl = rtrim($baseUrl, '&?');
                
                echo generatePaginationHTML($pagination, $baseUrl);
                ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Bulk Actions (Future Enhancement) -->
<div class="bulk-actions" style="background: var(--bg-white); padding: var(--spacing-lg); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-top: var(--spacing-xl);">
    <h3 style="color: var(--primary-navy); margin-bottom: var(--spacing-md);">Bulk Operations</h3>
    <p style="color: var(--text-secondary); margin-bottom: var(--spacing-md);">
        Select multiple materials to perform bulk operations (coming soon).
    </p>
    <div class="bulk-buttons" style="opacity: 0.5;">
        <button class="btn btn-outline" disabled>Select All</button>
        <button class="btn btn-outline" disabled>Bulk Edit</button>
        <button class="btn btn-danger" disabled>Bulk Delete</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Confirm delete function
    window.confirmDelete = function(itemName) {
        return confirm(`Are you sure you want to delete "${itemName}"? This action cannot be undone.`);
    };
    
    // Auto-submit search form on Enter
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    }
    
    // Real-time search with debounce
    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            searchTimeout = setTimeout(() => {
                if (query.length >= 3 || query.length === 0) {
                    // Could implement AJAX search here
                    console.log('Live search:', query);
                }
            }, 500);
        });
    }
    
    // Enhanced table interactions
    const tableRows = document.querySelectorAll('.admin-table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'var(--bg-light)';
            this.style.transform = 'scale(1.01)';
            this.style.transition = 'all 0.2s ease';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
            this.style.transform = 'scale(1)';
        });
    });
    
    // Action button enhancements
    const actionButtons = document.querySelectorAll('.btn-sm');
    actionButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>

<?php
// Include footer
include '../includes/footer.php';
?>