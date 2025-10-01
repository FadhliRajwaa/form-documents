// Global variables
let selectedFiles = [];

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initFileUpload();
    initFormSubmit();
    initFormReset();
});

/**
 * Initialize file upload functionality
 */
function initFileUpload() {
    const fileInput = document.getElementById('dokumen');
    const fileList = document.getElementById('fileList');
    
    fileInput.addEventListener('change', function(e) {
        handleFileSelect(e.target.files);
    });
    
    // Drag and drop functionality
    const fileUploadLabel = document.querySelector('.file-upload-label');
    
    fileUploadLabel.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = 'var(--primary-color)';
        this.style.background = 'rgba(66, 133, 244, 0.05)';
    });
    
    fileUploadLabel.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.style.borderColor = 'var(--border-color)';
        this.style.background = 'var(--bg-light)';
    });
    
    fileUploadLabel.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = 'var(--border-color)';
        this.style.background = 'var(--bg-light)';
        
        const files = e.dataTransfer.files;
        handleFileSelect(files);
    });
}

/**
 * Handle file selection
 */
function handleFileSelect(files) {
    const fileList = document.getElementById('fileList');
    const fileInput = document.getElementById('dokumen');
    
    // Add new files to selectedFiles array
    Array.from(files).forEach(file => {
        // Check if file already exists
        const exists = selectedFiles.some(f => f.name === file.name && f.size === file.size);
        if (!exists) {
            selectedFiles.push(file);
        }
    });
    
    // Update file list display
    displayFileList();
    
    // Update file input (create new DataTransfer to update input files)
    updateFileInput();
}

/**
 * Display file list
 */
function displayFileList() {
    const fileList = document.getElementById('fileList');
    
    if (selectedFiles.length === 0) {
        fileList.innerHTML = '';
        return;
    }
    
    let html = '';
    selectedFiles.forEach((file, index) => {
        const fileSize = formatFileSize(file.size);
        const fileIcon = getFileIcon(file.name);
        
        html += `
            <div class="file-item" data-index="${index}">
                <div class="file-item-info">
                    <i class="${fileIcon}"></i>
                    <div class="file-item-details">
                        <div class="file-item-name">${escapeHtml(file.name)}</div>
                        <div class="file-item-size">${fileSize}</div>
                    </div>
                </div>
                <button type="button" class="file-item-remove" onclick="removeFile(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    });
    
    fileList.innerHTML = html;
}

/**
 * Remove file from selection
 */
function removeFile(index) {
    selectedFiles.splice(index, 1);
    displayFileList();
    updateFileInput();
}

/**
 * Update file input with selectedFiles
 */
function updateFileInput() {
    const fileInput = document.getElementById('dokumen');
    const dataTransfer = new DataTransfer();
    
    selectedFiles.forEach(file => {
        dataTransfer.items.add(file);
    });
    
    fileInput.files = dataTransfer.files;
}

/**
 * Format file size
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
}

/**
 * Get file icon based on extension
 */
function getFileIcon(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    
    const iconMap = {
        'pdf': 'fas fa-file-pdf',
        'doc': 'fas fa-file-word',
        'docx': 'fas fa-file-word',
        'xls': 'fas fa-file-excel',
        'xlsx': 'fas fa-file-excel',
        'jpg': 'fas fa-file-image',
        'jpeg': 'fas fa-file-image',
        'png': 'fas fa-file-image',
        'gif': 'fas fa-file-image'
    };
    
    return iconMap[ext] || 'fas fa-file';
}

/**
 * Initialize form submit
 */
function initFormSubmit() {
    const form = document.getElementById('documentForm');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validate form
        if (!validateForm()) {
            return;
        }
        
        // Show loading
        showLoading();
        
        // Prepare form data
        const formData = new FormData(form);
        
        try {
            const response = await fetch('process.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            hideLoading();
            
            if (result.success) {
                showSuccessModal(result.data);
                form.reset();
                selectedFiles = [];
                displayFileList();
            } else {
                showAlert('error', result.message);
            }
        } catch (error) {
            hideLoading();
            showAlert('error', 'Terjadi kesalahan saat mengupload: ' + error.message);
        }
    });
}

/**
 * Validate form
 */
function validateForm() {
    const nama = document.getElementById('nama').value.trim();
    const email = document.getElementById('email').value.trim();
    const telepon = document.getElementById('telepon').value.trim();
    
    if (!nama) {
        showAlert('error', 'Nama lengkap wajib diisi');
        return false;
    }
    
    if (!email) {
        showAlert('error', 'Email wajib diisi');
        return false;
    }
    
    if (!isValidEmail(email)) {
        showAlert('error', 'Format email tidak valid');
        return false;
    }
    
    if (!telepon) {
        showAlert('error', 'Nomor telepon wajib diisi');
        return false;
    }
    
    if (selectedFiles.length === 0) {
        showAlert('error', 'Minimal harus upload 1 dokumen');
        return false;
    }
    
    return true;
}

/**
 * Validate email format
 */
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Show alert message
 */
function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer');
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alertHtml = `
        <div class="alert alert-${type}">
            <i class="fas ${icon}"></i>
            <span>${escapeHtml(message)}</span>
        </div>
    `;
    
    alertContainer.innerHTML = alertHtml;
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        alertContainer.innerHTML = '';
    }, 5000);
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * Show loading overlay
 */
function showLoading() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    const submitBtn = document.getElementById('submitBtn');
    
    loadingOverlay.style.display = 'flex';
    submitBtn.disabled = true;
}

/**
 * Hide loading overlay
 */
function hideLoading() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    const submitBtn = document.getElementById('submitBtn');
    
    loadingOverlay.style.display = 'none';
    submitBtn.disabled = false;
}

/**
 * Show success modal
 */
function showSuccessModal(data) {
    const modal = document.getElementById('successModal');
    const driveLink = document.getElementById('driveLink');
    const sheetsLink = document.getElementById('sheetsLink');
    
    driveLink.href = data.folder_url;
    sheetsLink.href = data.sheets_url;
    
    modal.style.display = 'flex';
}

/**
 * Close modal
 */
function closeModal() {
    const modal = document.getElementById('successModal');
    modal.style.display = 'none';
    
    // Clear alert
    document.getElementById('alertContainer').innerHTML = '';
}

/**
 * Initialize form reset
 */
function initFormReset() {
    const form = document.getElementById('documentForm');
    
    form.addEventListener('reset', function() {
        selectedFiles = [];
        displayFileList();
        document.getElementById('alertContainer').innerHTML = '';
    });
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

/**
 * Close modal when clicking outside
 */
window.addEventListener('click', function(e) {
    const modal = document.getElementById('successModal');
    if (e.target === modal) {
        closeModal();
    }
});
