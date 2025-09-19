<?php
/**
 * Edit Material Page
 * Learning Materials Management System
 */

// Include required files
require_once '../config/app.php';
require_once '../classes/Auth.php';
require_once '../classes/Material.php';
require_once '../classes/FileHandler.php';
require_once '../includes/functions.php';

// Start session and require authentication
session_start();
$auth = new Auth();
$auth->requireAuth();

// Initialize classes
$material = new Material();
$fileHandler = new FileHandler();

// Get material ID
$materialId = (int)get('id', 0);

if (!$materialId) {
    redirectWithMessage('/admin/materials.php', 'Invalid material ID.', 'error');
}

// Get material data
try {
    $materialData = $material->getMaterialById($materialId);
    if (!$materialData) {
        redirectWithMessage('/admin/materials.php', 'Material not found.', 'error');
    }
} catch (Exception $e) {
    logActivity('Edit material error: ' . $e->getMessage(), 'ERROR');
    redirectWithMessage('/admin/materials.php', 'Unable to load material.', 'error');
}

$error = '';
$success = '';

// Handle form submission
if (isPost()) {
    $title = post('title', '');
    $description = post('description', '');
    $price = (float)post('price', 0);
    $csrfToken = post('csrf_token', '');
    
    // Validate CSRF token
    if (!$auth->verifyCSRFToken($csrfToken)) {
        $error = 'Invalid request. Please try again.';
    }
    // Validate required fields
    elseif (empty($title)) {
        $error = 'Please enter a material title.';
    }
    // Process update
    else {
        try {
            $updateData = [
                'title' => $title,
                'description' => $description,
                'price' => $price
            ];
            
            // Handle file replacement if new file uploaded
            if (!empty($_FILES['material_file']['name'])) {
                $uploadResult = $fileHandler->uploadFile($_FILES['material_file'], $title);
                
                if (!$uploadResult['success']) {
                    $error = $uploadResult['message'];
                } else {
                    // Delete old file
                    $oldFileName = basename($materialData['file_path']);
                    $fileHandler->deleteFile($oldFileName);
                    
                    // Add new file info to update data
                    $updateData['file_path'] = $uploadResult['file_info']['file_path'];
                    $updateData['file_type'] = $uploadResult['file_info']['file_type'];
                    $updateData['file_size'] = $uploadResult['file_info']['file_size'];
                }
            }
            
            if (!$error) {
                $material->updateMaterial($materialId, $updateData);
                
                logActivity('Material updated', 'INFO', [
                    'material_id' => $materialId,
                    'title' => $title,
                    'file_updated' => !empty($_FILES['material_file']['name'])
                ]);
                
                redirectWithMessage('/admin/materials.php', 'Material "' . $title . '" updated successfully!', 'success');
            }
        } catch (Exception $e) {
            $error = 'Failed to update material: ' . $e->getMessage();
            logActivity('Update material error: ' . $e->getMessage(), 'ERROR', ['material_id' => $materialId]);
        }
    }
}

// Generate CSRF token
$csrfToken = $auth->generateCSRFToken();

// Set page title
$page_title = 'Edit Material';

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header" style="background: var(--bg-white); padding: var(--spacing-xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-bottom: var(--spacing-xl);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--spacing-md);">
        <div>
            <h1 style="color: var(--primary-navy); margin-bottom: var(--spacing-sm);">Edit Material</h1>
            <p style="color: var(--text-secondary); margin: 0;">
                Update information for "<?php echo htmlspecialchars($materialData['title']); ?>"
            </p>
        </div>
        
        <div class="header-actions">
            <a href="/admin/materials.php" class="btn btn-outline">← Back to Materials</a>
        </div>
    </div>
</div>

<!-- Current Material Info -->
<div class="current-material-info" style="background: var(--bg-white); padding: var(--spacing-xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-bottom: var(--spacing-xl);">
    <h2 style="color: var(--primary-navy); margin-bottom: var(--spacing-lg);">Current Material Information</h2>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--spacing-lg);">
        <div class="info-item">
            <strong style="color: var(--primary-blue);">File Type:</strong>
            <span class="meta-tag file-type" style="margin-left: var(--spacing-sm);">
                <?php echo strtoupper($materialData['file_type']); ?>
            </span>
        </div>
        
        <div class="info-item">
            <strong style="color: var(--primary-blue);">File Size:</strong>
            <span style="margin-left: var(--spacing-sm);">
                <?php echo formatFileSize($materialData['file_size']); ?>
            </span>
        </div>
        
        <div class="info-item">
            <strong style="color: var(--primary-blue);">Downloads:</strong>
            <span style="margin-left: var(--spacing-sm);">
                <?php echo number_format($materialData['downloads_count']); ?> times
            </span>
        </div>
        
        <div class="info-item">
            <strong style="color: var(--primary-blue);">Added:</strong>
            <span style="margin-left: var(--spacing-sm);">
                <?php echo timeAgo($materialData['created_at']); ?>
            </span>
        </div>
        
        <div class="info-item">
            <strong style="color: var(--primary-blue);">Last Updated:</strong>
            <span style="margin-left: var(--spacing-sm);">
                <?php echo timeAgo($materialData['updated_at']); ?>
            </span>
        </div>
        
        <div class="info-item">
            <strong style="color: var(--primary-blue);">Current Price:</strong>
            <span style="margin-left: var(--spacing-sm);">
                <?php echo $materialData['price'] > 0 ? formatPrice($materialData['price']) : 'FREE'; ?>
            </span>
        </div>
    </div>
</div>

<!-- Edit Material Form -->
<div class="material-form-container">
    <h2 style="color: var(--primary-navy); margin-bottom: var(--spacing-xl); text-align: center;">Update Material Information</h2>
    
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
    
    <form method="POST" action="/admin/edit-material.php?id=<?php echo $materialId; ?>" enctype="multipart/form-data" class="material-form" id="materialForm">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        
        <!-- Basic Information -->
        <div class="form-section">
            <h3 style="color: var(--primary-blue); margin-bottom: var(--spacing-lg);">Basic Information</h3>
            
            <div class="form-group">
                <label for="title" class="form-label">Material Title *</label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    class="form-input" 
                    placeholder="Enter a descriptive title for the learning material"
                    value="<?php echo htmlspecialchars(post('title', $materialData['title'])); ?>"
                    required
                    maxlength="255"
                >
                <small style="color: var(--text-secondary);">
                    Use a clear, searchable title that describes the content (3-255 characters)
                </small>
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea 
                    id="description" 
                    name="description" 
                    class="form-textarea" 
                    placeholder="Provide a detailed description of the material content, learning objectives, and target audience..."
                    rows="5"
                ><?php echo htmlspecialchars(post('description', $materialData['description'])); ?></textarea>
                <small style="color: var(--text-secondary);">
                    Detailed description helps users find and understand the material
                </small>
            </div>
            
            <div class="form-group">
                <label for="price" class="form-label">Price (USD) *</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); font-weight: 600;">$</span>
                    <input 
                        type="number" 
                        id="price" 
                        name="price" 
                        class="form-input" 
                        placeholder="0.00"
                        value="<?php echo number_format(post('price', $materialData['price']), 2); ?>"
                        min="0" 
                        step="0.01"
                        style="padding-left: 30px;"
                        required
                    >
                </div>
                <small style="color: var(--text-secondary);">
                    Set to 0.00 for free materials. Use format: 29.99
                </small>
            </div>
        </div>
        
        <!-- File Replacement Section -->
        <div class="form-section">
            <h3 style="color: var(--primary-blue); margin-bottom: var(--spacing-lg);">File Replacement (Optional)</h3>
            
            <div class="alert alert-info">
                <strong>Note:</strong> Only upload a new file if you want to replace the current one. 
                The old file will be permanently deleted if you upload a new one.
            </div>
            
            <div class="form-group">
                <label for="material_file" class="form-label">Replace Material File (Optional)</label>
                <div class="file-upload-area" id="fileUploadArea">
                    <input 
                        type="file" 
                        id="material_file" 
                        name="material_file" 
                        class="file-input" 
                        accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                    >
                    <div class="file-upload-content">
                        <div style="font-size: 3rem; color: var(--primary-blue); margin-bottom: var(--spacing-md);">🔄</div>
                        <div class="file-upload-text">
                            <strong>Click to select replacement file or drag and drop</strong>
                        </div>
                        <div class="file-upload-subtext">
                            Supported formats: PDF, DOC, DOCX<br>
                            Maximum size: <?php echo formatFileSize(AppConfig::MAX_FILE_SIZE); ?><br>
                            <em>Leave empty to keep current file</em>
                        </div>
                    </div>
                </div>
                
                <!-- File Information Display -->
                <div class="file-info" id="fileInfo">
                    <div class="file-details">
                        <div class="file-name" id="fileName"></div>
                        <div class="file-size" id="fileSize"></div>
                        <div class="file-type" id="fileType"></div>
                    </div>
                    <div class="file-actions">
                        <button type="button" class="btn btn-sm btn-outline" onclick="clearFile()">Remove File</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Form Actions -->
        <div class="form-actions" style="text-align: center; padding-top: var(--spacing-xl); border-top: 1px solid var(--border-color);">
            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                Update Material
            </button>
            <a href="/admin/materials.php" class="btn btn-outline btn-lg">Cancel</a>
        </div>
        
        <!-- Upload Progress -->
        <div class="upload-progress" id="uploadProgress" style="display: none;">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill" style="width: 0%;"></div>
            </div>
            <div class="progress-text" id="progressText">Updating...</div>
        </div>
    </form>
</div>

<!-- Material Actions -->
<div class="material-actions" style="background: var(--bg-white); padding: var(--spacing-xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-top: var(--spacing-xl);">
    <h3 style="color: var(--primary-navy); margin-bottom: var(--spacing-lg);">Additional Actions</h3>
    
    <div style="display: flex; gap: var(--spacing-lg); flex-wrap: wrap; justify-content: center;">
        <a href="/download.php?id=<?php echo $materialId; ?>" 
           class="btn btn-success" 
           target="_blank">
            📥 Download Current File
        </a>
        
        <a href="/admin/delete-material.php?id=<?php echo $materialId; ?>" 
           class="btn btn-danger"
           onclick="return confirm('Are you sure you want to delete this material? This action cannot be undone.')">
            🗑️ Delete Material
        </a>
        
        <a href="/?search=<?php echo urlencode($materialData['title']); ?>" 
           class="btn btn-outline" 
           target="_blank">
            👁️ View on Website
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('material_file');
    const fileUploadArea = document.getElementById('fileUploadArea');
    const fileInfo = document.getElementById('fileInfo');
    const form = document.getElementById('materialForm');
    
    // File handling functions (same as add-material.php)
    function handleFile(file) {
        if (!file) return;
        
        // Validate file type
        const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!allowedTypes.includes(file.type)) {
            showAlert('Invalid file type. Please select a PDF, DOC, or DOCX file.', 'error');
            return;
        }
        
        // Validate file size
        const maxSize = <?php echo AppConfig::MAX_FILE_SIZE; ?>;
        if (file.size > maxSize) {
            showAlert('File size exceeds the maximum limit of <?php echo formatFileSize(AppConfig::MAX_FILE_SIZE); ?>.', 'error');
            return;
        }
        
        // Display file information
        displayFileInfo(file);
    }
    
    function displayFileInfo(file) {
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = formatFileSize(file.size);
        document.getElementById('fileType').textContent = file.type.split('/')[1].toUpperCase();
        
        fileInfo.classList.add('show');
        fileUploadArea.style.borderColor = 'var(--accent-emerald)';
        fileUploadArea.style.backgroundColor = 'rgba(16, 185, 129, 0.05)';
    }
    
    // File input change handler
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            handleFile(this.files[0]);
        }
    });
    
    // Drag and drop handlers
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
            handleFile(files[0]);
        }
    });
    
    // Clear file function
    window.clearFile = function() {
        fileInput.value = '';
        fileInfo.classList.remove('show');
        fileUploadArea.style.borderColor = '';
        fileUploadArea.style.backgroundColor = '';
    };
    
    // Form submission handling
    form.addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        const uploadProgress = document.getElementById('uploadProgress');
        
        // Show loading state
        submitBtn.innerHTML = '<span class="spinner"></span>Updating Material...';
        submitBtn.disabled = true;
        
        // Show progress if file is being uploaded
        if (fileInput.files.length > 0) {
            uploadProgress.style.display = 'block';
            
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += 10;
                document.getElementById('progressFill').style.width = progress + '%';
                
                if (progress >= 90) {
                    clearInterval(progressInterval);
                    document.getElementById('progressText').textContent = 'Processing...';
                }
            }, 200);
        }
    });
    
    // Real-time character counting for title
    const titleInput = document.getElementById('title');
    titleInput.addEventListener('input', function() {
        const length = this.value.length;
        const maxLength = 255;
        
        let counter = this.parentNode.querySelector('.char-counter');
        if (!counter) {
            counter = document.createElement('small');
            counter.className = 'char-counter';
            counter.style.color = 'var(--text-secondary)';
            counter.style.fontSize = '0.75rem';
            counter.style.float = 'right';
            this.parentNode.appendChild(counter);
        }
        
        counter.textContent = `${length}/${maxLength}`;
        counter.style.color = length > maxLength * 0.9 ? 'var(--accent-red)' : 'var(--text-secondary)';
    });
    
    // Price input formatting
    const priceInput = document.getElementById('price');
    priceInput.addEventListener('blur', function() {
        const value = parseFloat(this.value) || 0;
        this.value = value.toFixed(2);
    });
    
    // Confirmation for file replacement
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            const confirmed = confirm('Are you sure you want to replace the current file? The old file will be permanently deleted.');
            if (!confirmed) {
                this.value = '';
                clearFile();
            }
        }
    });
});

// Utility functions
function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

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
        setTimeout(() => alert.remove(), 300);
    }, 5000);
}
</script>

<?php
// Include footer
include '../includes/footer.php';
?>