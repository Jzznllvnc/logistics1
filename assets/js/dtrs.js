// Logistic1/assets/js/dtrs.js

// --- Upload Document Modal Functions ---
function openUploadDocumentModal() {
    const modal = document.getElementById('uploadDocumentModal');
    const form = document.getElementById('uploadDocumentForm');
    
    if (form) {
        form.reset();
    }
    
    if (modal && window.openModal) {
        window.openModal(modal);
    }
}

/**
 * Initialize DTRS page functionality
 */
function initDTRS() {
    const uploadDocumentBtn = document.getElementById('uploadDocumentBtn');
    
    if (uploadDocumentBtn) {
        // Remove existing listener to prevent duplicates
        uploadDocumentBtn.removeEventListener('click', openUploadDocumentModal);
        uploadDocumentBtn.addEventListener('click', openUploadDocumentModal);
    }
}

// Make the initializer globally available for PJAX
window.initDTRS = initDTRS;

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