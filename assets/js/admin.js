/**
 * Admin Panel JavaScript
 * Learning Materials Management System
 */

document.addEventListener('DOMContentLoaded', function() {
    initFileUpload();
    initFormValidation();
    initDeleteConfirmation();
    initDataTable();
    initDashboardCharts();
    initProgressBars();
});

/**
 * File upload functionality
 */
function initFileUpload() {
    const fileInput = document.querySelector('.file-input');
    const fileUploadArea = document.querySelector('.file-upload-area');
    const fileInfo = document.querySelector('.file-info');
    const progressBar = document.querySelector('.progress-bar');
    
    if (!fileInput || !fileUploadArea) return;
    
    // Handle drag and drop
    fileUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });
    
    fileUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });
    
    fileUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelection(files[0]);
        }
    });
    
    // Handle file input change
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            handleFileSelection(this.files[0]);
        }
    });
}

/**
 * Handle file selection and validation
 */
function handleFileSelection(file) {
    const fileInfo = document.querySelector('.file-info');
    const fileName = document.querySelector('.file-name');
    const fileSize = document.querySelector('.file-size');
    
    // Validate file type
    const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if (!allowedTypes.includes(file.type)) {
        showAlert('Invalid file type. Please select a PDF, DOC, or DOCX file.', 'error');
        return;
    }
    
    // Validate file size (50MB max)
    const maxSize = 50 * 1024 * 1024;
    if (file.size > maxSize) {
        showAlert('File size exceeds 50MB limit. Please select a smaller file.', 'error');
        return;
    }
    
    // Show file information
    if (fileInfo && fileName && fileSize) {
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        fileInfo.classList.add('show');
    }
    
    // Validate and preview if possible
    validateFile(file);
}

/**
 * Validate uploaded file
 */
function validateFile(file) {
    const reader = new FileReader();
    
    reader.onload = function(e) {
        // Basic file validation - check if file starts with expected headers
        const arrayBuffer = e.target.result;
        const uint8Array = new Uint8Array(arrayBuffer);
        
        // PDF signature: %PDF-
        if (file.type === 'application/pdf') {
            const pdfSignature = [0x25, 0x50, 0x44, 0x46, 0x2D];
            const isValidPDF = pdfSignature.every((byte, index) => uint8Array[index] === byte);
            
            if (!isValidPDF) {
                showAlert('Invalid PDF file format.', 'error');
                return;
            }
        }
        
        showAlert('File validated successfully!', 'success');
    };
    
    reader.onerror = function() {
        showAlert('Error reading file. Please try again.', 'error');
    };
    
    // Read first few bytes for validation
    reader.readAsArrayBuffer(file.slice(0, 1024));
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner"></span>Processing...';
                submitBtn.disabled = true;
                
                // Reset button if form validation fails
                setTimeout(() => {
                    if (!this.checkValidity()) {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                }, 100);
            }
        });
        
        // Real-time validation for inputs
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('error')) {
                    validateField(this);
                }
            });
        });
    });
}

/**
 * Validate individual form field
 */
function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let message = '';
    
    // Remove existing error states
    field.classList.remove('error');
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        message = 'This field is required.';
    }
    
    // Email validation
    else if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            message = 'Please enter a valid email address.';
        }
    }
    
    // Price validation
    else if (field.name === 'price' && value) {
        const price = parseFloat(value);
        if (isNaN(price) || price < 0) {
            isValid = false;
            message = 'Please enter a valid price.';
        }
    }
    
    // Title validation
    else if (field.name === 'title' && value) {
        if (value.length < 3) {
            isValid = false;
            message = 'Title must be at least 3 characters long.';
        } else if (value.length > 255) {
            isValid = false;
            message = 'Title must be less than 255 characters.';
        }
    }
    
    // Show error if invalid
    if (!isValid) {
        field.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.style.color = 'var(--accent-red)';
        errorDiv.style.fontSize = '0.875rem';
        errorDiv.style.marginTop = '0.25rem';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }
    
    return isValid;
}

/**
 * Validate entire form
 */
function validateForm(form) {
    const fields = form.querySelectorAll('input, textarea, select');
    let isValid = true;
    
    fields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Initialize delete confirmation dialogs
 */
function initDeleteConfirmation() {
    const deleteButtons = document.querySelectorAll('.btn-delete, .delete-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const itemName = this.dataset.itemName || 'item';
            const deleteUrl = this.href || this.dataset.deleteUrl;
            
            showDeleteConfirmation(itemName, deleteUrl);
        });
    });
}

/**
 * Show delete confirmation modal
 */
function showDeleteConfirmation(itemName, deleteUrl) {
    const modal = createModal({
        title: 'Confirm Deletion',
        message: `Are you sure you want to delete "${itemName}"? This action cannot be undone.`,
        type: 'danger',
        confirmText: 'Delete',
        cancelText: 'Cancel',
        onConfirm: () => {
            window.location.href = deleteUrl;
        }
    });
    
    showModal(modal);
}

/**
 * Create modal dialog
 */
function createModal(options) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">${options.title}</h3>
            </div>
            <div class="modal-body">
                <p>${options.message}</p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline cancel-btn">${options.cancelText || 'Cancel'}</button>
                <button type="button" class="btn btn-${options.type || 'primary'} confirm-btn">${options.confirmText || 'Confirm'}</button>
            </div>
        </div>
    `;
    
    // Add event listeners
    const cancelBtn = modal.querySelector('.cancel-btn');
    const confirmBtn = modal.querySelector('.confirm-btn');
    
    cancelBtn.addEventListener('click', () => {
        hideModal(modal);
    });
    
    confirmBtn.addEventListener('click', () => {
        if (options.onConfirm) {
            options.onConfirm();
        }
        hideModal(modal);
    });
    
    // Close on overlay click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            hideModal(modal);
        }
    });
    
    return modal;
}

/**
 * Show modal
 */
function showModal(modal) {
    document.body.appendChild(modal);
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);
}

/**
 * Hide modal
 */
function hideModal(modal) {
    modal.classList.remove('show');
    setTimeout(() => {
        if (modal.parentNode) {
            modal.parentNode.removeChild(modal);
        }
    }, 300);
}

/**
 * Initialize data table functionality
 */
function initDataTable() {
    const tables = document.querySelectorAll('.admin-table');
    
    tables.forEach(table => {
        // Add sorting functionality
        const headers = table.querySelectorAll('th[data-sort]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                sortTable(table, this.dataset.sort);
            });
        });
        
        // Add row hover effects
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = 'var(--bg-light)';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    });
}

/**
 * Sort table by column
 */
function sortTable(table, column) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aValue = a.querySelector(`[data-value="${column}"]`)?.textContent || '';
        const bValue = b.querySelector(`[data-value="${column}"]`)?.textContent || '';
        
        return aValue.localeCompare(bValue);
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

/**
 * Initialize dashboard charts (placeholder for future implementation)
 */
function initDashboardCharts() {
    // Placeholder for chart initialization
    // Could integrate Chart.js or similar library here
    console.log('Dashboard charts initialized');
}

/**
 * Initialize progress bars
 */
function initProgressBars() {
    const progressBars = document.querySelectorAll('.progress-fill');
    
    progressBars.forEach(bar => {
        const targetWidth = bar.dataset.progress || '0';
        bar.style.width = '0%';
        
        setTimeout(() => {
            bar.style.width = targetWidth + '%';
        }, 500);
    });
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info') {
    const alertContainer = document.querySelector('.alert-container') || createAlertContainer();
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <span>${message}</span>
        <button type="button" class="alert-close" onclick="this.parentNode.remove()">×</button>
    `;
    alert.style.opacity = '0';
    alert.style.transform = 'translateY(-10px)';
    alert.style.transition = 'all 0.3s ease';
    
    alertContainer.appendChild(alert);
    
    setTimeout(() => {
        alert.style.opacity = '1';
        alert.style.transform = 'translateY(0)';
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 300);
        }
    }, 5000);
}

/**
 * Create alert container if it doesn't exist
 */
function createAlertContainer() {
    const container = document.createElement('div');
    container.className = 'alert-container';
    container.style.position = 'fixed';
    container.style.top = '20px';
    container.style.right = '20px';
    container.style.zIndex = '1000';
    container.style.maxWidth = '400px';
    
    document.body.appendChild(container);
    return container;
}

/**
 * Format file size in human readable format
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Initialize keyboard shortcuts
 */
function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl+S or Cmd+S to save form
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            const form = document.querySelector('form');
            if (form) {
                form.submit();
            }
        }
        
        // Escape to close modals
        if (e.key === 'Escape') {
            const modal = document.querySelector('.modal-overlay.show');
            if (modal) {
                hideModal(modal);
            }
        }
    });
}

// Initialize keyboard shortcuts
document.addEventListener('DOMContentLoaded', initKeyboardShortcuts);