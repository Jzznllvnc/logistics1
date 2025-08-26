// Logistic1/assets/js/dtrs.js

// --- Document Details Modal Functions ---
function openDocumentDetails(docData) {
    const modal = document.getElementById('documentDetailsModal');
    const content = document.getElementById('documentDetailsContent');
    const downloadBtn = document.getElementById('downloadDocumentBtn');
    
    if (!modal || !content) return;
    
    // Format expiry date
    const expiryDate = docData.expiry_date ? 
        new Date(docData.expiry_date).toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        }) : 'N/A';
    
    // Format upload date
    const uploadDate = new Date(docData.upload_date).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    // Get file extension for display
    const fileExtension = docData.file_name.split('.').pop().toUpperCase();
    
    // Determine file type icon and color (more visible in light mode)
    let bgColor = 'bg-gray-600 dark:bg-gray-700';
    let textColor = 'text-white font-bold dark:text-gray-300';
    
    switch(fileExtension.toLowerCase()) {
        case 'pdf':
            bgColor = 'bg-red-600 dark:bg-red-900/30';
            textColor = 'text-white font-bold dark:text-red-400';
            break;
        case 'doc':
        case 'docx':
            bgColor = 'bg-blue-600 dark:bg-blue-900/30';
            textColor = 'text-white font-bold dark:text-blue-400';
            break;
        case 'xls':
        case 'xlsx':
            bgColor = 'bg-green-600 dark:bg-green-900/30';
            textColor = 'text-white font-bold dark:text-green-400';
            break;
        case 'jpg':
        case 'jpeg':
        case 'png':
            bgColor = 'bg-purple-600 dark:bg-purple-900/30';
            textColor = 'text-white font-bold dark:text-purple-400';
            break;
    }
    
    // Populate the modal content
    content.innerHTML = `
        <div class="flex items-start space-x-6">
            <!-- File Icon -->
            <div class="flex-shrink-0">
                <div class="w-16 h-16 rounded-xl ${bgColor} flex items-center justify-center">
                    <i data-lucide="file-text" class="w-8 h-8 ${textColor}"></i>
                </div>
            </div>
            
            <!-- Document Information -->
            <div class="flex-1 min-w-0">
                <h3 class="text-xl font-semibold text-[var(--text-color)] mb-4">${docData.document_type}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">File Name</label>
                            <p class="text-[var(--placeholder-color)] break-all">${docData.file_name}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">Document Type</label>
                            <p class="text-[var(--placeholder-color)]">${docData.document_type}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">File Type</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${bgColor} ${textColor}">
                                ${fileExtension}
                            </span>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">Reference Number</label>
                            <p class="text-[var(--placeholder-color)]">${docData.reference_number || 'N/A'}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">Upload Date</label>
                            <p class="text-[var(--placeholder-color)]">${uploadDate}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">Expiry Date</label>
                            <p class="text-[var(--placeholder-color)]">${expiryDate}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Set up download button
    if (downloadBtn) {
        downloadBtn.onclick = function() {
            window.open('../' + docData.file_path, '_blank');
        };
    }
    
    // Reinitialize Lucide icons for the new content
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Open the modal
    if (window.openModal) {
        window.openModal(modal);
    }
}

// --- File Upload Drag & Drop Functions ---
function handleFileSelect(input) {
    const file = input.files[0];
    if (file) {
        displaySelectedFile(file);
    }
}

function handleFileDrop(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const dropZone = document.getElementById('dropZone');
    dropZone.classList.remove('!border-blue-400', '!bg-blue-50', 'dark:!bg-blue-900/20');
    dropZone.classList.add('border-[var(--card-border)]', 'bg-[var(--input-bg)]');
    
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        const input = document.getElementById('documentFile');
        input.files = files;
        displaySelectedFile(files[0]);
    }
}

function handleDragOver(event) {
    event.preventDefault();
    event.stopPropagation();
}

function handleDragEnter(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const dropZone = document.getElementById('dropZone');
    dropZone.classList.remove('border-[var(--card-border)]', 'bg-[var(--input-bg)]');
    dropZone.classList.add('!border-blue-400', '!bg-blue-50', 'dark:!bg-blue-900/20');
}

function handleDragLeave(event) {
    event.preventDefault();
    event.stopPropagation();
    
    // Only remove highlight if we're leaving the drop zone entirely
    if (!event.currentTarget.contains(event.relatedTarget)) {
        const dropZone = document.getElementById('dropZone');
        dropZone.classList.remove('!border-blue-400', '!bg-blue-50', 'dark:!bg-blue-900/20');
        dropZone.classList.add('border-[var(--card-border)]', 'bg-[var(--input-bg)]');
    }
}

function displaySelectedFile(file) {
    const display = document.getElementById('selectedFileDisplay');
    const fileName = document.getElementById('selectedFileName');
    
    if (display && fileName) {
        fileName.textContent = file.name;
        display.classList.remove('hidden');
        
        // Hide the drop zone text and show a compact version
        const dropZone = document.getElementById('dropZone');
        dropZone.innerHTML = `
            <div class="flex items-center justify-center py-4">
                <i data-lucide="check-circle" class="w-6 h-6 mr-2" style="color: #0072ff;"></i>
                <span class="font-medium" style="color: #0072ff;">File selected</span>
            </div>
        `;
        
        // Reinitialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
}

function clearFileSelection() {
    const input = document.getElementById('documentFile');
    const display = document.getElementById('selectedFileDisplay');
    const dropZone = document.getElementById('dropZone');
    
    // Clear the file input
    if (input) {
        input.value = '';
    }
    
    // Hide the selected file display
    if (display) {
        display.classList.add('hidden');
    }
    
    // Reset the drop zone to original state
    if (dropZone) {
        dropZone.className = 'border-2 border-dashed border-[var(--card-border)] rounded-lg p-8 text-center bg-[var(--input-bg)] hover:bg-[var(--card-bg)] transition-colors cursor-pointer';
        
        dropZone.innerHTML = `
            <div class="flex flex-col items-center">
                <i data-lucide="cloud-upload" class="w-12 h-12 text-[var(--placeholder-color)] mb-4"></i>
                <p class="text-[var(--text-color)] font-medium mb-1">Drag your files here</p>
                <p class="text-sm text-[var(--placeholder-color)] mb-4">DOC, PDF, XLSX, and JPG formats, up to 50 MB</p>
                <button type="button" 
                        onclick="document.getElementById('documentFile').click()" 
                        class="px-4 py-2 text-white rounded-md text-sm font-medium transition-colors"
                        style="background: linear-gradient(to right, #0072ff, #00c6ff);">
                    Browse Files
                </button>
            </div>
        `;
        
        // Reinitialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
}

// --- Mobile Upload Modal Functions ---
function openMobileUploadModal() {
    const modal = document.getElementById('mobileUploadModal');
    const form = document.getElementById('mobileUploadForm');
    
    if (form) {
        form.reset();
        clearMobileFileSelection();
    }
    
    if (modal && window.openModal) {
        window.openModal(modal);
    }
}

function closeMobileUploadModal() {
    const modal = document.getElementById('mobileUploadModal');
    if (modal && window.closeModal) {
        window.closeModal(modal);
    }
}

function handleMobileFileSelect(input) {
    const file = input.files[0];
    if (file) {
        displayMobileSelectedFile(file);
    }
}

function displayMobileSelectedFile(file) {
    const display = document.getElementById('mobileSelectedFileDisplay');
    const fileName = document.getElementById('mobileSelectedFileName');
    const dropZone = document.getElementById('mobileDropZone');
    
    if (display && fileName) {
        fileName.textContent = file.name;
        display.classList.remove('hidden');
        
        // Update drop zone to show file selected
        if (dropZone) {
            dropZone.innerHTML = `
                <div class="flex items-center justify-center py-4">
                    <i data-lucide="check-circle" class="w-6 h-6 mr-2" style="color: #0072ff;"></i>
                    <span class="font-medium" style="color: #0072ff;">File selected</span>
                </div>
            `;
            
            // Reinitialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
    }
}

function clearMobileFileSelection() {
    const input = document.getElementById('mobileDocumentFile');
    const display = document.getElementById('mobileSelectedFileDisplay');
    const dropZone = document.getElementById('mobileDropZone');
    
    // Clear the file input
    if (input) {
        input.value = '';
    }
    
    // Hide the selected file display
    if (display) {
        display.classList.add('hidden');
    }
    
    // Reset the drop zone to original state
    if (dropZone) {
        dropZone.innerHTML = `
            <div class="flex flex-col items-center">
                <i data-lucide="cloud-upload" class="w-10 h-10 text-[var(--placeholder-color)] mb-3"></i>
                <p class="text-[var(--text-color)] font-medium mb-1">Tap to select file</p>
                <p class="text-xs text-[var(--placeholder-color)] mb-3">PDF, DOC, DOCX, JPG, PNG, TXT (max 5MB)</p>
                <button type="button" 
                        onclick="document.getElementById('mobileDocumentFile').click()" 
                        class="px-3 py-2 text-white rounded-md text-sm font-medium transition-colors"
                        style="background: linear-gradient(to right, #0072ff, #00c6ff);">
                  Browse Files
                </button>
            </div>
        `;
        
        // Reinitialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
}

/**
 * Initialize DTRS page functionality
 */
function initDTRS() {
    // Initialize drag and drop functionality for desktop
    const fileInput = document.getElementById('documentFile');
    const dropZone = document.getElementById('dropZone');
    
    if (fileInput && dropZone) {
        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });
        
        // Highlight drop zone when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });
        
        // Handle dropped files
        dropZone.addEventListener('drop', handleFileDrop, false);
        
        // Add change event listener to file input to handle manual file selection
        fileInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files.length > 0) {
                displaySelectedFile(e.target.files[0]);
            }
        });
    }
    
    // Initialize mobile upload button
    const mobileUploadBtn = document.getElementById('mobileUploadBtn');
    if (mobileUploadBtn) {
        mobileUploadBtn.addEventListener('click', openMobileUploadModal);
    }
    
    // Set up clear button event listeners
    document.addEventListener('click', function(e) {
        const clearButton = e.target.closest('button[data-action="clear-file"]');
        if (clearButton) {
            clearFileSelection();
        }
        
        const mobileClearButton = e.target.closest('button[data-action="clear-mobile-file"]');
        if (mobileClearButton) {
            clearMobileFileSelection();
        }
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    function highlight(e) {
        dropZone.classList.remove('border-[var(--card-border)]', 'bg-[var(--input-bg)]');
        dropZone.classList.add('!border-blue-400', '!bg-blue-50', 'dark:!bg-blue-900/20');
    }
    
    function unhighlight(e) {
        dropZone.classList.remove('!border-blue-400', '!bg-blue-50', 'dark:!bg-blue-900/20');
        dropZone.classList.add('border-[var(--card-border)]', 'bg-[var(--input-bg)]');
    }
}

// Make functions globally available
window.initDTRS = initDTRS;
window.openDocumentDetails = openDocumentDetails;
window.clearFileSelection = clearFileSelection;
window.openMobileUploadModal = openMobileUploadModal;
window.closeMobileUploadModal = closeMobileUploadModal;
window.handleMobileFileSelect = handleMobileFileSelect;
window.clearMobileFileSelection = clearMobileFileSelection;
window.handleFileSelect = handleFileSelect;
window.handleFileDrop = handleFileDrop;
window.handleDragOver = handleDragOver;
window.handleDragEnter = handleDragEnter;
window.handleDragLeave = handleDragLeave;

// Initialize Lucide Icons
function initLucideIcons() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// Make Lucide icon initialization globally available for this page
window.refreshLucideIcons = initLucideIcons;

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initDTRS();
    initLucideIcons();
}); 