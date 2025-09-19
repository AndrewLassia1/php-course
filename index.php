<?php
/**
 * Homepage - Learning Materials Hub
 * Main landing page for browsing and searching materials
 */

// Include required files
require_once 'config/app.php';
require_once 'classes/Material.php';
require_once 'includes/functions.php';

// Start session
session_start();

// Initialize material class
$material = new Material();

// Handle search and filtering
$search = get('search', '');
$fileType = get('type', '');
$page = max(1, (int)get('page', 1));

// Get materials based on filters
try {
    if ($search) {
        $materials = $material->searchMaterials($search, $page);
        $totalMaterials = $material->getTotalMaterials($search, $fileType);
        $pageTitle = "Search Results for: " . htmlspecialchars($search);
    } elseif ($fileType) {
        $materials = $material->getMaterialsByType($fileType, $page);
        $totalMaterials = $material->getTotalMaterials(null, $fileType);
        $pageTitle = strtoupper($fileType) . " Materials";
    } else {
        $materials = $material->getAllMaterials($page);
        $totalMaterials = $material->getTotalMaterials();
        $pageTitle = "Learning Materials Hub";
    }
    
    // Generate pagination
    $pagination = paginate($totalMaterials, AppConfig::ITEMS_PER_PAGE, $page);
    
} catch (Exception $e) {
    logActivity('Error loading materials: ' . $e->getMessage(), 'ERROR');
    $materials = [];
    $totalMaterials = 0;
    $pagination = null;
    setFlashMessage('Unable to load materials. Please try again later.', 'error');
}

// Set page title
$page_title = $pageTitle;

// Include header
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <h1>Premium Learning Materials</h1>
    <p class="hero-subtitle">
        Access high-quality educational resources including PDFs, Word documents, and study guides. 
        Professional learning materials for students, educators, and professionals.
    </p>
    
    <!-- Statistics -->
    <?php
    try {
        $stats = $material->getStatistics();
    ?>
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-number"><?php echo number_format($stats['total_materials']); ?></span>
            <span class="stat-label">Learning Materials</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?php echo number_format($stats['total_downloads']); ?></span>
            <span class="stat-label">Downloads</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?php echo count($stats['by_type']); ?></span>
            <span class="stat-label">File Formats</span>
        </div>
        <div class="stat-card">
            <span class="stat-number">24/7</span>
            <span class="stat-label">Availability</span>
        </div>
    </div>
    <?php } catch (Exception $e) { ?>
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-number">100+</span>
            <span class="stat-label">Learning Materials</span>
        </div>
        <div class="stat-card">
            <span class="stat-number">1,000+</span>
            <span class="stat-label">Downloads</span>
        </div>
        <div class="stat-card">
            <span class="stat-number">3</span>
            <span class="stat-label">File Formats</span>
        </div>
        <div class="stat-card">
            <span class="stat-number">24/7</span>
            <span class="stat-label">Availability</span>
        </div>
    </div>
    <?php } ?>
</section>

<!-- Search and Filter Section -->
<section class="search-container">
    <form method="GET" action="/" class="search-form">
        <input 
            type="text" 
            name="search" 
            class="search-input" 
            placeholder="Search for learning materials, topics, or subjects..." 
            value="<?php echo htmlspecialchars($search); ?>"
            autocomplete="off"
        >
        
        <select name="type" class="filter-select">
            <option value="">All File Types</option>
            <option value="pdf" <?php echo $fileType === 'pdf' ? 'selected' : ''; ?>>PDF Documents</option>
            <option value="doc" <?php echo $fileType === 'doc' ? 'selected' : ''; ?>>Word Documents</option>
            <option value="docx" <?php echo $fileType === 'docx' ? 'selected' : ''; ?>>DOCX Files</option>
        </select>
        
        <button type="submit" class="btn btn-primary">Search</button>
        
        <?php if ($search || $fileType): ?>
            <a href="/" class="btn btn-outline">Clear Filters</a>
        <?php endif; ?>
    </form>
    
    <?php if ($search || $fileType): ?>
    <div class="search-results-info">
        <p>
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
    <?php endif; ?>
</section>

<!-- Materials Grid -->
<section class="materials-section" id="materials">
    <?php if (empty($materials)): ?>
        <div class="no-materials">
            <div style="text-align: center; padding: 60px 20px;">
                <img src="https://placehold.co/300x200?text=No+Materials+Found" alt="No materials found" style="opacity: 0.6; margin-bottom: 20px; border-radius: 8px;">
                <h3>No materials found</h3>
                <p class="text-secondary">
                    <?php if ($search): ?>
                        Try searching with different keywords or remove filters to see more results.
                    <?php else: ?>
                        Learning materials will appear here once they are added by administrators.
                    <?php endif; ?>
                </p>
                <?php if ($search || $fileType): ?>
                    <a href="/" class="btn btn-primary mt-3">Browse All Materials</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="materials-grid">
            <?php foreach ($materials as $mat): ?>
                <article class="material-card" data-material-id="<?php echo $mat['id']; ?>">
                    <!-- Material Header -->
                    <div class="material-header">
                        <div class="material-image">
                            <?php
                            $imageDesc = urlencode("Professional learning material cover for " . $mat['title'] . " educational resource book design");
                            ?>
                            <img src="https://placehold.co/350x200?text=<?php echo $imageDesc; ?>" 
                                 alt="<?php echo htmlspecialchars($mat['title']); ?> - Learning Material Cover"
                                 class="material-cover-image"
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
                $baseUrl = '/?';
                if ($search) $baseUrl .= 'search=' . urlencode($search) . '&';
                if ($fileType) $baseUrl .= 'type=' . urlencode($fileType) . '&';
                $baseUrl = rtrim($baseUrl, '&?');
                
                echo generatePaginationHTML($pagination, $baseUrl);
                ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>

<!-- Additional Information Section -->
<section class="info-section">
    <div style="background: var(--bg-white); padding: var(--spacing-2xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-bottom: var(--spacing-xl);">
        <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--spacing-xl);">
            <div class="info-item">
                <h3 style="color: var(--primary-blue); margin-bottom: var(--spacing-md);">📚 High-Quality Content</h3>
                <p>All learning materials are carefully curated and reviewed for quality, accuracy, and educational value.</p>
            </div>
            
            <div class="info-item">
                <h3 style="color: var(--primary-blue); margin-bottom: var(--spacing-md);">⚡ Instant Download</h3>
                <p>Access your materials immediately after selection. No waiting, no complicated processes - just instant access.</p>
            </div>
            
            <div class="info-item">
                <h3 style="color: var(--primary-blue); margin-bottom: var(--spacing-md);">🔒 Secure Platform</h3>
                <p>Your downloads are secure and protected. We maintain the highest standards of digital security.</p>
            </div>
            
            <div class="info-item">
                <h3 style="color: var(--primary-blue); margin-bottom: var(--spacing-md);">📱 Multiple Formats</h3>
                <p>Materials available in PDF, DOC, and DOCX formats for maximum compatibility with your devices.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section">
    <div style="background: linear-gradient(135deg, var(--primary-navy) 0%, var(--primary-blue) 100%); color: var(--text-white); padding: var(--spacing-2xl); border-radius: var(--radius-lg); text-align: center;">
        <h2 style="color: var(--text-white); margin-bottom: var(--spacing-md);">Ready to Start Learning?</h2>
        <p style="font-size: 1.125rem; margin-bottom: var(--spacing-xl); color: rgba(255, 255, 255, 0.9);">
            Join thousands of learners who trust our platform for their educational needs. 
            Browse our extensive collection of premium learning materials.
        </p>
        <div style="display: flex; gap: var(--spacing-md); justify-content: center; flex-wrap: wrap;">
            <a href="#materials" class="btn" style="background: var(--text-white); color: var(--primary-navy); font-weight: 600;">Browse Materials</a>
            <a href="/search.php" class="btn btn-outline" style="border-color: var(--text-white); color: var(--text-white);">Advanced Search</a>
        </div>
    </div>
</section>

<script>
// Add smooth scrolling and enhanced interactivity
document.addEventListener('DOMContentLoaded', function() {
    // Enhance material cards with hover effects
    const materialCards = document.querySelectorAll('.material-card');
    materialCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
            this.style.boxShadow = 'var(--shadow-lg)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'var(--shadow-md)';
        });
    });
    
    // Real-time search suggestions (basic implementation)
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    // Here you could implement AJAX search suggestions
                    console.log('Search suggestions for:', query);
                }, 300);
            }
        });
    }
    
    // Download tracking and analytics
    const downloadButtons = document.querySelectorAll('.download-btn');
    downloadButtons.forEach(button => {
        button.addEventListener('click', function() {
            const materialId = this.dataset.materialId;
            const materialTitle = this.dataset.materialTitle;
            
            // Track download event
            if (typeof gtag !== 'undefined') {
                gtag('event', 'download', {
                    'material_id': materialId,
                    'material_title': materialTitle
                });
            }
        });
    });
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?>