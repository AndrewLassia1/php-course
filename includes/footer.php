<?php
/**
 * Common Footer Template
 * Learning Materials Management System
 */

$is_admin = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
?>

        </div> <!-- Close container -->
    </main> <!-- Close main content -->
    
    <?php if (!$is_admin): ?>
    <!-- Public Site Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3 class="footer-title"><?php echo AppConfig::APP_NAME; ?></h3>
                    <p class="footer-description">
                        Your trusted source for premium learning materials. Access high-quality educational resources 
                        including PDFs, documents, and study guides designed for students and professionals.
                    </p>
                    <div class="footer-stats">
                        <?php
                        try {
                            require_once __DIR__ . '/../classes/Material.php';
                            $material = new Material();
                            $stats = $material->getStatistics();
                            ?>
                            <div class="stat-item">
                                <span class="stat-number"><?php echo number_format($stats['total_materials']); ?></span>
                                <span class="stat-label">Learning Materials</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?php echo number_format($stats['total_downloads']); ?></span>
                                <span class="stat-label">Downloads</span>
                            </div>
                        <?php } catch (Exception $e) { ?>
                            <div class="stat-item">
                                <span class="stat-number">100+</span>
                                <span class="stat-label">Learning Materials</span>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-subtitle">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="/">Home</a></li>
                        <li><a href="/search.php">Search Materials</a></li>
                        <li><a href="/#materials">Browse Categories</a></li>
                        <li><a href="/#about">About Us</a></li>
                        <li><a href="/#contact">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-subtitle">Categories</h4>
                    <ul class="footer-links">
                        <?php
                        if (isset($stats['by_type']) && is_array($stats['by_type'])):
                            foreach ($stats['by_type'] as $type): ?>
                                <li><a href="/?type=<?php echo urlencode($type['file_type']); ?>">
                                    <?php echo strtoupper($type['file_type']); ?> Files 
                                    <span class="count">(<?php echo $type['count']; ?>)</span>
                                </a></li>
                            <?php endforeach;
                        else: ?>
                            <li><a href="/?type=pdf">PDF Documents</a></li>
                            <li><a href="/?type=doc">Word Documents</a></li>
                            <li><a href="/?type=docx">DOCX Files</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-subtitle">Information</h4>
                    <ul class="footer-links">
                        <li><a href="#privacy">Privacy Policy</a></li>
                        <li><a href="#terms">Terms of Service</a></li>
                        <li><a href="#help">Help Center</a></li>
                        <li><a href="#downloads">Download Guide</a></li>
                    </ul>
                    
                    <div class="footer-contact">
                        <p>Need help? Contact us:</p>
                        <p><strong>support@learninghub.com</strong></p>
                        <p>Available 24/7</p>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-copyright">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo AppConfig::APP_NAME; ?>. All rights reserved.</p>
                    <p class="footer-version">Version <?php echo AppConfig::APP_VERSION; ?> | Powered by Professional Learning Solutions</p>
                </div>
                
                <div class="footer-social">
                    <p class="footer-tagline">Empowering learners worldwide with quality educational resources</p>
                </div>
            </div>
        </div>
    </footer>
    
    <?php else: ?>
    <!-- Admin Footer -->
    <footer class="admin-footer">
        <div class="admin-container">
            <div class="admin-footer-content">
                <p>&copy; <?php echo date('Y'); ?> <?php echo AppConfig::APP_NAME; ?> Admin Panel. All rights reserved.</p>
                <p class="admin-footer-info">
                    Version <?php echo AppConfig::APP_VERSION; ?> | 
                    Last login: <?php echo date('M j, Y \a\t g:i A'); ?>
                </p>
            </div>
        </div>
    </footer>
    <?php endif; ?>
    
    <!-- JavaScript Files -->
    <script src="/assets/js/main.js"></script>
    <?php if ($is_admin): ?>
        <script src="/assets/js/admin.js"></script>
    <?php endif; ?>
    
    <!-- Additional JavaScript -->
    <?php if (isset($additional_js)): ?>
        <?php echo $additional_js; ?>
    <?php endif; ?>
    
    <!-- Analytics and Tracking (if needed) -->
    <script>
        // Page load analytics
        document.addEventListener('DOMContentLoaded', function() {
            // Track page view
            if (typeof gtag !== 'undefined') {
                gtag('event', 'page_view', {
                    page_title: document.title,
                    page_location: window.location.href
                });
            }
            
            // Track performance metrics
            window.addEventListener('load', function() {
                const loadTime = performance.now();
                console.log('Page load time:', loadTime.toFixed(2) + 'ms');
            });
        });
        
        // Error tracking
        window.addEventListener('error', function(e) {
            console.error('JavaScript error:', e.error);
        });
        
        // Service worker registration (for future PWA features)
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                // navigator.serviceWorker.register('/sw.js');
            });
        }
    </script>
    
</body>
</html>