<?php
/**
 * Advanced Search Page
 * Learning Materials Management System
 */

// Include required files
require_once 'config/app.php';
require_once 'classes/Material.php';
require_once 'includes/functions.php';

// Start session
session_start();

// Initialize material class
$material = new Material();

// Handle search parameters
$search = get('search', '');
$fileType = get('type', '');
$sortBy = get('sort', 'created_at');
$sortOrder = get('order', 'DESC');
$page = max(1, (int)get('page', 1));

// Validate sort parameters
$allowedSortFields = ['title', 'created_at', 'price', 'downloads_count'];
$allowedSortOrders = ['ASC', 'DESC'];

if (!in_array($sortBy, $allowedSortFields)) {
    $sortBy = 'created_at';
}

if (!in_array($sortOrder, $allowedSortOrders)) {
    $sortOrder = 'DESC';
}

// Get materials based on search
$materials = [];
$totalMaterials = 0;
$pagination = null;

if ($search || $fileType) {
    try {
        if ($search && $fileType) {
            // Search with file type filter
            $materials = $material->searchMaterials($search, $page);
            $materials = array_filter($materials, function($mat) use ($fileType) {
                return $mat['file_type'] === $fileType;
            });
            $totalMaterials = $material->getTotalMaterials($search, $fileType);
        } elseif ($search) {
            // Search only
            $materials = $material->searchMaterials($search, $page);
            $totalMaterials = $material->getTotalMaterials($search);
        } elseif ($fileType) {
            // Filter by type only
            $materials = $material->getMaterialsByType($fileType, $page);
            $totalMaterials = $material->getTotalMaterials(null, $fileType);
        }
        
        // Generate pagination
        $pagination = paginate($totalMaterials, AppConfig::ITEMS_PER_PAGE, $page);
        
    } catch (Exception $e) {
        logActivity('Search error: ' . $e->getMessage(), 'ERROR');
        setFlashMessage('Search failed. Please try again.', 'error');
    }
}

// Get statistics for search page
try {
    $stats = $material->getStatistics();
} catch (Exception $e) {
    $stats = ['total_materials' => 0, 'by_type' => []];
}

// Set page title
$page_title = "Advanced Search - Learning Materials";

// Include header
include 'includes/header.php';
?>

<!-- Search Header -->
<section class="search-header">
    <div style="background: linear-gradient(135deg, var(--primary-navy) 0%, var(--primary-blue) 100%); color: var(--text-white); padding: var(--spacing-2xl) 0; margin-bottom: var(--spacing-xl);">
        <h1 style="color: var(--text-white); text-align: center; margin-bottom: var(--spacing-md);">Advanced Search</h1>
        <p style="text-align: center; font-size: 1.125rem; color: rgba(255, 255, 255, 0.9); max-width: 600px; margin: 0 auto;">
            Find exactly what you need from our extensive collection of learning materials
        </p>
    </div>
</section>

<!-- Advanced Search Form -->
<section class="search-container">
    <div style="background: var(--bg-white); padding: var(--spacing-2xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); margin-bottom: var(--spacing-xl);">
        <form method="GET" action="/search.php" class="advanced-search-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="search" class="form-label">Search Keywords</label>
                    <input 
                        type="text" 
                        id="search"
                        name="search" 
                        class="form-input" 
                        placeholder="Enter keywords, topics, or subjects..." 
                        value="<?php echo htmlspecialchars($search); ?>"
                        autocomplete="off"
                    >
                </div>
                
                <div class="form-group">
                    <label for="type" class="form-label">File Type</label>
                    <select id="type" name="type" class="form-select">
                        <option value="">All File Types</option>
                        <option value="pdf" <?php echo $fileType === 'pdf' ? 'selected' : ''; ?>>PDF Documents</option>
                        <option value="doc" <?php echo $fileType === 'doc' ? 'selected' : ''; ?>>Word Documents (.doc)</option>
                        <option value="docx" <?php echo $fileType === 'docx' ? 'selected' : ''; ?>>Word Documents (.docx)</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="sort" class="form-label">Sort By</label>
                    <select id="sort" name="sort" class="form-select">
                        <option value="created_at" <?php echo $sortBy === 'created_at' ? 'selected' : ''; ?>>Date Added</option>
                        <option value="title" <?php echo $sortBy === 'title' ? 'selected' : ''; ?>>Title</option>
                        <option value="price" <?php echo $sortBy === 'price' ? 'selected' : ''; ?>>Price</option>
                        <option value="downloads_count" <?php echo $sortBy === 'downloads_count' ? 'selected' : ''; ?>>Popularity</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="order" class="form-label">Order</label>
                    <select id="order" name="order" class="form-select">
                        <option value="DESC" <?php echo $sortOrder === 'DESC' ? 'selected' : ''; ?>>Descending</option>
                        <option value="ASC" <?php echo $sortOrder === 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                    </select>
                </div>
            </div>
            
            <div class="search-actions" style="display: flex; gap: var(--spacing-md); justify-content: center; margin-top: var(--spacing-lg);">
                <button type="submit" class="btn btn-primary">Search Materials</button>
                <a href="/search.php" class="btn btn-outline">Clear All</a>
                <a href="/" class="btn btn-outline">Back to Home</a>
            </div>
        </form>
    </div>
</section>

<!-- Search Statistics -->
<section class="search-stats">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-lg); margin-bottom: var(--spacing-xl);">
        <div class="stat-card">
            <span class="stat-number"><?php echo number_format($stats['total_materials']); ?></span>
            <span class="stat-label">Total Materials</span>
        </div>
        
        <?php foreach ($stats['by_type'] as $typeData): ?>
        <div class="stat-card">
            <span class="stat-number"><?php echo number_format($typeData['count']); ?></span>
            <span class="stat-label"><?php echo strtoupper($typeData['file_type']); ?> Files</span>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Search Results -->
<?php if ($search || $fileType): ?>
    <section class="search-results">
        <?php if ($totalMaterials > 0): ?>
            <!-- Results Header -->
            <div class="results-header" style="background: var(--bg-white); padding: var(--spacing-lg); border-radius: var(--radius-md); margin-bottom: var(--spacing-lg); box-shadow: var(--shadow-sm);">
                <h2>Search Results</h2>
                <p class="results-summary">
                    Found <strong><?php echo number_format($totalMaterials); ?></strong> 
                    <?php echo $totalMaterials === 1 ? 'material' : 'materials'; ?>
                    <?php if ($search): ?>
                        for "<strong><?php echo htmlspecialchars($search); ?></strong>"
                    <?php endif; ?>
                    <?php if ($fileType): ?>
                        in <strong><?php echo strtoupper($fileType); ?></strong> format
                    <?php endif; ?>
                </p>
            </div>
            
            <!-- Materials Grid -->
            <div class="materials-grid">
                <?php foreach ($materials as $mat): ?>
                    <article class="material-card" data-material-id="<?php echo $mat['id']; ?>">
                        <!-- Material Header -->
                        <div class="material-header">
                            <div class="material-image">
                                <?php
                                $imageDesc = urlencode("Educational learning material thumbnail for " . $mat['title'] . " professional document design");
                                ?>
                                <img src="https://placehold.co/350x200?text=<?php echo $imageDesc; ?>" 
                                     alt="<?php echo htmlspecialchars($mat['title']); ?> - Learning Material"
                                     class="material-thumbnail"
                                     loading="lazy">
                            </div>
                            
                            <h3 class="material-title"><?php echo htmlspecialchars($mat['title']); ?></h3>
                            
                            <div class="material-meta">
                                <span class="meta-tag file-type"><?php echo strtoupper($mat['file_type']); ?></span>
                                <span class="meta-tag"><?php echo formatFileSize($mat['file_size']); ?></span>
                                <span class="meta-tag"><?php echo $mat['downloads_count']; ?> downloads</span>
                                <span class="meta-tag"><?php echo timeAgo($mat['created_at']); ?></span>
                            </div>
                            
                            <p class="material-description">
                                <?php echo htmlspecialchars(truncateText($mat['description'], 150)); ?>
                            </p>
                        </div>
                        
                        <!-- Material Footer -->
                        <div class="material-footer">
                            <div class="material-price-container">
                                <?php if ($mat['price'] > 0): ?>
                                    <span class="material-price"><?php echo formatPrice($mat['price']); ?></span>
                                <?php else: ?>
                                    <span class="material-price free">FREE</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="material-actions">
                                <button 
                                    class="btn btn-primary download-btn" 
                                    data-material-id="<?php echo $mat['id']; ?>"
                                    data-material-title="<?php echo htmlspecialchars($mat['title']); ?>"
                                    data-tooltip="Download <?php echo htmlspecialchars($mat['title']); ?>"
                                >
                                    Download Now
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($pagination && $pagination['total_pages'] > 1): ?>
                <div class="pagination-container">
                    <?php
                    $baseUrl = '/search.php?';
                    if ($search) $baseUrl .= 'search=' . urlencode($search) . '&';
                    if ($fileType) $baseUrl .= 'type=' . urlencode($fileType) . '&';
                    if ($sortBy !== 'created_at') $baseUrl .= 'sort=' . urlencode($sortBy) . '&';
                    if ($sortOrder !== 'DESC') $baseUrl .= 'order=' . urlencode($sortOrder) . '&';
                    $baseUrl = rtrim($baseUrl, '&?');
                    
                    echo generatePaginationHTML($pagination, $baseUrl);
                    ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- No Results -->
            <div class="no-results" style="text-align: center; padding: var(--spacing-2xl); background: var(--bg-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
                <img src="https://placehold.co/300x200?text=No+Search+Results+Found" alt="No search results found" style="opacity: 0.6; margin-bottom: var(--spacing-lg); border-radius: var(--radius-md);">
                <h3>No materials found</h3>
                <p style="color: var(--text-secondary); margin-bottom: var(--spacing-lg);">
                    We couldn't find any materials matching your search criteria. 
                    Try adjusting your search terms or filters.
                </p>
                <div style="display: flex; gap: var(--spacing-md); justify-content: center; flex-wrap: wrap;">
                    <a href="/search.php" class="btn btn-outline">Clear Search</a>
                    <a href="/" class="btn btn-primary">Browse All Materials</a>
                </div>
            </div>
        <?php endif; ?>
    </section>
    
<?php else: ?>
    <!-- Search Tips -->
    <section class="search-tips">
        <div style="background: var(--bg-white); padding: var(--spacing-2xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <h2 style="color: var(--primary-navy); margin-bottom: var(--spacing-lg); text-align: center;">Search Tips</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--spacing-xl);">
                <div class="tip-item">
                    <h3 style="color: var(--primary-blue); margin-bottom: var(--spacing-md);">🔍 Keyword Search</h3>
                    <p>Enter specific topics, subjects, or keywords to find relevant materials. Use multiple words for better results.</p>
                    <p><strong>Examples:</strong> "web development", "data analysis", "project management"</p>
                </div>
                
                <div class="tip-item">
                    <h3 style="color: var(--primary-blue); margin-bottom: var(--spacing-md);">📄 File Type Filter</h3>
                    <p>Filter results by file format to find materials in your preferred format.</p>
                    <p><strong>Available formats:</strong> PDF, DOC, DOCX</p>
                </div>
                
                <div class="tip-item">
                    <h3 style="color: var(--primary-blue); margin-bottom: var(--spacing-md);">📊 Sort Options</h3>
                    <p>Sort results by date, title, price, or popularity to find what you need quickly.</p>
                    <p><strong>Popular sorts:</strong> Latest first, Most downloaded, Alphabetical</p>
                </div>
                
                <div class="tip-item">
                    <h3 style="color: var(--primary-blue); margin-bottom: var(--spacing-md);">⚡ Quick Access</h3>
                    <p>Bookmark your favorite searches or use our quick links to access popular categories.</p>
                    <p><strong>Quick links:</strong> Popular materials, Recent additions, Free resources</p>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Popular Materials -->
<section class="popular-materials">
    <div style="background: var(--bg-white); padding: var(--spacing-2xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-top: var(--spacing-xl);">
        <h2 style="color: var(--primary-navy); text-align: center; margin-bottom: var(--spacing-lg);">Popular Materials</h2>
        
        <?php
        try {
            $popularMaterials = $material->getAllMaterials(1, 6); // Get first 6 materials
            if (!empty($popularMaterials)): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-lg);">
                    <?php foreach (array_slice($popularMaterials, 0, 6) as $pop): ?>
                        <div class="popular-item" style="text-align: center; padding: var(--spacing-md); border: 1px solid var(--border-color); border-radius: var(--radius-md); transition: transform 0.3s ease;">
                            <h4 style="margin-bottom: var(--spacing-sm); color: var(--primary-navy); font-size: 0.9rem;">
                                <?php echo htmlspecialchars(truncateText($pop['title'], 40)); ?>
                            </h4>
                            <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: var(--spacing-sm);">
                                <?php echo strtoupper($pop['file_type']); ?> • <?php echo $pop['downloads_count']; ?> downloads
                            </p>
                            <a href="/?search=<?php echo urlencode($pop['title']); ?>" class="btn btn-sm btn-outline">View</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif;
        } catch (Exception $e) {
            // Silently handle error
        }
        ?>
    </div>
</section>

<script>
// Enhanced search functionality
document.addEventListener('DOMContentLoaded', function() {
    // Search form enhancements
    const searchForm = document.querySelector('.advanced-search-form');
    const searchInput = document.querySelector('#search');
    
    // Auto-focus search input
    if (searchInput && !searchInput.value) {
        searchInput.focus();
    }
    
    // Search suggestions (placeholder for future implementation)
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length >= 2) {
                // Could implement live search suggestions here
                console.log('Search suggestions for:', query);
            }
        });
    }
    
    // Popular item hover effects
    const popularItems = document.querySelectorAll('.popular-item');
    popularItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
            this.style.boxShadow = 'var(--shadow-md)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?>