/**
 * Main JavaScript for Learning Materials Hub
 * Handles search, filtering, and user interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initSearch();
    initFilters();
    initDownloads();
    initAnimations();
    initLazyLoading();
});

/**
 * Search functionality
 */
function initSearch() {
    const searchForm = document.querySelector('.search-form');
    const searchInput = document.querySelector('.search-input');
    const materialsContainer = document.querySelector('.materials-grid');
    
    if (!searchForm || !searchInput) return;
    
    let searchTimeout;
    
    // Real-time search with debounce
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        searchTimeout = setTimeout(() => {
            if (query.length >= 2 || query.length === 0) {
                performSearch(query);
            }
        }, 300);
    });
    
    // Handle form submission
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const query = searchInput.value.trim();
        performSearch(query);
    });
}

/**
 * Perform search request
 */
function performSearch(query) {
    const materialsContainer = document.querySelector('.materials-grid');
    const loadingSpinner = createLoadingSpinner();
    
    // Show loading state
    if (materialsContainer) {
        materialsContainer.style.opacity = '0.6';
        materialsContainer.insertBefore(loadingSpinner, materialsContainer.firstChild);
    }
    
    // Build search URL
    const url = new URL(window.location);
    if (query) {
        url.searchParams.set('search', query);
    } else {
        url.searchParams.delete('search');
    }
    url.searchParams.delete('page'); // Reset pagination
    
    // Update browser history
    history.pushState(null, '', url);
    
    // Reload page with new search parameters
    setTimeout(() => {
        window.location.reload();
    }, 500);
}

/**
 * Filter functionality
 */
function initFilters() {
    const filterSelect = document.querySelector('.filter-select');
    
    if (!filterSelect) return;
    
    filterSelect.addEventListener('change', function() {
        const selectedType = this.value;
        applyFilter(selectedType);
    });
}

/**
 * Apply file type filter
 */
function applyFilter(fileType) {
    const url = new URL(window.location);
    
    if (fileType && fileType !== 'all') {
        url.searchParams.set('type', fileType);
    } else {
        url.searchParams.delete('type');
    }
    url.searchParams.delete('page'); // Reset pagination
    
    // Update browser history and reload
    history.pushState(null, '', url);
    window.location.reload();
}

/**
 * Download functionality
 */
function initDownloads() {
    const downloadButtons = document.querySelectorAll('.download-btn');
    
    downloadButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const materialId = this.dataset.materialId;
            const materialTitle = this.dataset.materialTitle || 'Learning Material';
            
            if (materialId) {
                handleDownload(materialId, materialTitle, this);
            }
        });
    });
}

/**
 * Handle file download
 */
function handleDownload(materialId, materialTitle, button) {
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<span class="spinner"></span>Preparing...';
    button.disabled = true;
    button.classList.add('loading');
    
    // Create download URL
    const downloadUrl = `${window.location.origin}/download.php?id=${materialId}`;
    
    // Create hidden link and trigger download
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = materialTitle;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Show success state
    setTimeout(() => {
        button.innerHTML = '✓ Downloaded';
        button.classList.remove('loading');
        button.classList.add('btn-success');
        
        // Reset button after delay
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
            button.classList.remove('btn-success');
        }, 2000);
    }, 1000);
    
    // Track download analytics
    trackDownload(materialId, materialTitle);
}

/**
 * Track download for analytics
 */
function trackDownload(materialId, materialTitle) {
    // Send analytics data to server
    fetch('/api/track-download.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            material_id: materialId,
            timestamp: Date.now(),
            user_agent: navigator.userAgent
        })
    }).catch(error => {
        console.log('Analytics tracking failed:', error);
    });
}

/**
 * Initialize smooth animations
 */
function initAnimations() {
    // Animate material cards on scroll
    const cards = document.querySelectorAll('.material-card');
    
    if ('IntersectionObserver' in window) {
        const cardObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    cardObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
            cardObserver.observe(card);
        });
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Initialize lazy loading for images
 */
function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for older browsers
        images.forEach(img => {
            img.src = img.dataset.src;
        });
    }
}

/**
 * Utility function to create loading spinner
 */
function createLoadingSpinner() {
    const spinner = document.createElement('div');
    spinner.className = 'text-center mb-4';
    spinner.innerHTML = '<div class="spinner"></div>Loading materials...';
    return spinner;
}

/**
 * Show notification message
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.innerHTML = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '1000';
    notification.style.minWidth = '300px';
    notification.style.opacity = '0';
    notification.style.transform = 'translateY(-20px)';
    notification.style.transition = 'all 0.3s ease';
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateY(0)';
    }, 100);
    
    // Remove after delay
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 4000);
}

/**
 * Handle responsive navigation
 */
function initMobileMenu() {
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileToggle && navMenu) {
        mobileToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            this.classList.toggle('active');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!mobileToggle.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
                mobileToggle.classList.remove('active');
            }
        });
    }
}

/**
 * Initialize tooltips
 */
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.innerHTML = e.target.dataset.tooltip;
    tooltip.style.position = 'absolute';
    tooltip.style.backgroundColor = 'var(--primary-navy)';
    tooltip.style.color = 'white';
    tooltip.style.padding = '8px 12px';
    tooltip.style.borderRadius = '4px';
    tooltip.style.fontSize = '14px';
    tooltip.style.zIndex = '1000';
    tooltip.style.opacity = '0';
    tooltip.style.transition = 'opacity 0.3s ease';
    
    document.body.appendChild(tooltip);
    
    const rect = e.target.getBoundingClientRect();
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
    tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
    
    setTimeout(() => tooltip.style.opacity = '1', 10);
    
    e.target._tooltip = tooltip;
}

function hideTooltip(e) {
    if (e.target._tooltip) {
        e.target._tooltip.style.opacity = '0';
        setTimeout(() => {
            if (e.target._tooltip && e.target._tooltip.parentNode) {
                e.target._tooltip.parentNode.removeChild(e.target._tooltip);
            }
            e.target._tooltip = null;
        }, 300);
    }
}

/**
 * Initialize all mobile and accessibility features
 */
function initAccessibility() {
    // Add keyboard navigation support
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-navigation');
        }
    });
    
    document.addEventListener('mousedown', function() {
        document.body.classList.remove('keyboard-navigation');
    });
    
    // Initialize mobile menu and tooltips
    initMobileMenu();
    initTooltips();
}

// Initialize accessibility features
document.addEventListener('DOMContentLoaded', initAccessibility);