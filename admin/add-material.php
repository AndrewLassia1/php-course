<?php
/**
 * Add Material Page
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
    elseif (empty($_FILES['material_file']['name'])) {
        $error = 'Please select a file to upload.';
    }
    // Process file upload and create material
    else {
        try {
            // Upload file
            $uploadResult = $fileHandler->uploadFile($_FILES['material_file'], $title);
            
            if (!$uploadResult['success']) {
                $error = $uploadResult['message'];
            } else {
                // Create material record
                $materialData = [
                    'title' => $title,
                    'description' => $description,
                    'file_path' => $uploadResult['file_info']['file_path'],
                    'file_type' => $uploadResult['file_info']['file_type'],
                    'file_size' => $uploadResult['file_info']['file_size'],
                    'price' => $price
                ];
                
                $materialId = $material->addMaterial($materialData);
                
                logActivity('Material added', 'INFO', [
                    'material_id' => $materialId,
                    'title' => $title,
                    'file_type' => $uploadResult['file_info']['file_type']
                ]);
                
                redirectWithMessage('/admin/materials.php', 'Material "' . $title . '" added successfully!', 'success');
            }
        } catch (Exception $e) {
            $error = 'Failed to add material: ' . $e->getMessage();
            logActivity('Add material error: ' . $e->getMessage(), 'ERROR', ['title' => $title]);
        }
    }
}

// Generate CSRF token
$csrfToken = $auth->generateCSRFToken();

// Set page title
$page_title = 'Add New Material';

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header" style="background: var(--bg-white); padding: var(--spacing-xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-bottom: var(--spacing-xl);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--spacing-md);">
        <div>
            <h1 style="color: var(--primary-navy); margin-bottom: var(--spacing-sm);">Add New Material</h1>
            <p style="color: var(--text-secondary); margin: 0;">
                Upload a new learning material to the system
            </p>
        </div>
        
        <div class="header-actions">
            <a href="/admin/materials.php" class="btn btn-outline">← Back to Materials</a>
        </div>
    </div>
</div>

<!-- Upload Guidelines -->
<div class="upload-guidelines" style="background: linear-gradient(135deg, var(--primary-blue), var(--primary-navy)); color: var(--text-white); padding: var(--spacing-xl); border-radius: var(--radius-lg); margin-bottom: var(--spacing-xl);">
    <h2 style="color: var(--text-white); margin-bottom: var(--spacing-lg);">📋 Upload Guidelines</h2>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--spacing-lg);">
        <div>
            <h3 style="color: rgba(255, 255, 255, 0.9); margin-bottom: var(--spacing-sm);">📄 Supported Formats</h3>
            <p style="color: rgba(255, 255, 255, 0.8); margin: 0;">
                PDF, DOC, and DOCX files are supported
            </p>
        </div>
        
        <div>
            <h3 style="color: rgba(255, 255, 255, 0.9); margin-bottom: var(--spacing-sm);">💾 File Size Limit</h3>
            <p style="color: rgba(255, 255, 255, 0.8); margin: 0;">
                Maximum file size: <?php echo formatFileSize(AppConfig::MAX_FILE_SIZE); ?>
            </p>
        </div>
        
        <div>
            <h3 style="color: rgba(255, 255, 255, 0.9); margin-bottom: var(--spacing-sm);">✏️ Title Requirements</h3>
            <p style="color: rgba(255, 255, 255, 0.8); margin: 0;">
                Use descriptive, searchable titles (3-255 characters)
            </p>
        </div>
        
        <div>
            <h3 style="color: rgba(255, 255, 255, 0.9); margin-bottom: var(--spacing-sm);">💰 Pricing Options</h3>
            <p style="color: rgba(255, 255, 255, 0.8); margin: 0;">
                Set to $0.00 for free materials, or enter price in USD
            </p>
        </div>
    </div>
</div>

<!-- Add Material Form -->
<div class="material-form-container">
    <h2 style="color: var(--primary-navy); margin-bottom: var(--spacing-xl); text-align: center;">Material Information</h2>
    
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
    
    <form method="POST" action="/admin/add-material.php" enctype="multipart/form-data" class="material-form" id="materialForm">
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
                    value="<?php echo htmlspecialchars(post('title', '')); ?>"
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
                ><?php echo htmlspecialchars(post('description', '')); ?></textarea>
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
                        value="<?php echo post('price', '0.00'); ?>"
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
        
        <!-- File Upload Section -->
        <div class="form-section">
            <h3 style="color: var(--primary-blue); margin-bottom: var(--spacing-lg);">File Upload</h3>
            
            <div class="form-group">
                <label for="material_file" class="form-label">Learning Material File *</label>
                <div class="file-upload-area" id="fileUploadArea">
                    <input 
                        type="file" 
                        id="material_file" 
                        name="material_file" 
                        class="file-input" 
                        accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                        required
                    >
                    <div class="file-upload-content">
                        <div style="font-size: 3rem; color: var(--primary-blue); margin-bottom: var(--spacing-md);">📁</div>
                        <div class="file-upload-text">
                            <strong>Click to select file or drag and drop</strong>
                        </div>
                        <div class="file-upload-subtext">
                            Supported formats: PDF, DOC, DOCX<br>
                            Maximum size: <?php echo formatFileSize(AppConfig::MAX_FILE_SIZE); ?>
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
                Add Material to System
            </button>
            <a href="/admin/materials.php" class="btn btn-outline btn-lg">Cancel</a>
        </div>
        
        <!-- Upload Progress -->
        <div class="upload-progress" id="uploadProgress" style="display: none;">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill" style="width: 0%;"></div>
            </div>
            <div class="progress-text" id="progressText">Uploading...</div>
        </div>
    </form>
</div>

<!-- Upload Tips -->
<div class="upload-tips" style="background: var(--bg-white); padding: var(--spacing-xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-top: var(--spacing-xl);">
    <h3 style="color: var(--primary-navy); margin-bottom: var(--spacing-lg);">💡 Tips for Better Materials</h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--spacing-lg);">
        <div class="tip-item">
            <h4 style="color: var(--primary-blue); margin-bottom: var(--spacing-sm);">Clear Titles</h4>
            <p style="color: var(--text-secondary); margin: 0;">
                Use descriptive titles that clearly indicate the subject matter and target audience.
            </p>
        </div>
        
        <div class="tip-item">
            <h4 style="color: var(--primary-blue); margin-bottom: var(--spacing-sm);">Detailed Descriptions</h4>
            <p style="color: var(--text-secondary); margin: 0;">
                Include learning objectives, prerequisites, and what users will gain from the material.
            </p>
        </div>
        
        <div class="tip-item">
            <h4 style="color: var(--primary-blue); margin-bottom: var(--spacing-sm);">Quality Files</h4>
            <p style="color: var(--text-secondary); margin: 0;">
                Ensure files are properly formatted, readable, and free of errors before uploading.
            </p>
        </div>
        
        <div class="tip-item">
            <h4 style="color: var(--primary-blue); margin-bottom: var(--spacing-sm);">Fair Pricing</h4>
            <p style="color: var(--text-secondary); margin: 0;">
                Consider the material's value, length, and complexity when setting prices.
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('material_file');
    const fileUploadArea = document.getElementById('fileUploadArea');
    const fileInfo = document.getElementById('fileInfo');
    const form = document.getElementById('materialForm');
    
    // File selection and drag & drop handling
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
        submitBtn.innerHTML = '<span class="spinner"></span>Adding Material...';
        submitBtn.disabled = true;
        uploadProgress.style.display = 'block';
        
        // Simulate upload progress (replace with actual progress tracking)
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += 10;
            document.getElementById('progressFill').style.width = progress + '%';
            
            if (progress >= 90) {
                clearInterval(progressInterval);
                document.getElementById('progressText').textContent = 'Processing...';
            }
        }, 200);
    });
    
    // Auto-fill title from filename
    fileInput.addEventListener('change', function() {
        const titleInput = document.getElementById('title');
        if (!titleInput.value && this.files.length > 0) {
            const filename = this.files[0].name;
            const nameWithoutExt = filename.replace(/\.[^/.]+$/, "");
            const cleanTitle = nameWithoutExt.replace(/[-_]/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            titleInput.value = cleanTitle;
        }
    });
    
    // Real-time character counting for title
    const titleInput = document.getElementById('title');
    titleInput.addEventListener('input', function() {
        const length = this.value.length;
        const maxLength = 255;
        
        // Add character counter if it doesn't exist
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