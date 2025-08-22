// Immediately add preload class to prevent FOUC
document.documentElement.classList.add('preload', 'loading');

// Enhanced FOUC prevention
document.addEventListener('DOMContentLoaded', () => {
    document.documentElement.classList.remove('loading');
});

window.addEventListener('load', () => {
    document.documentElement.classList.remove('preload');
    document.documentElement.classList.add('loaded');
});

// Expose an idempotent initializer so we can re-run after PJAX content swaps
function initGlobalUI() {
    const themeToggle = document.getElementById('themeToggle');
    const themeLabel = document.querySelector('.theme-label');
    const customAlert = document.getElementById('customAlert');
    const customAlertMessage = document.getElementById('customAlertMessage');
    const adminProfileToggle = document.getElementById('adminProfileToggle');
    const adminDropdownMenu = document.getElementById('adminDropdownMenu');
    const logoutButton = document.getElementById('logoutButton');
    const logoutConfirmModal = document.getElementById('logoutConfirmModal');
    const confirmLogoutBtn = document.getElementById('confirmLogoutBtn');

    // --- General UI Functions ---
    // Theme Toggle
    if (themeToggle && themeLabel && !themeToggle.dataset.listenerAttached) {
        const updateThemeLabel = (isDarkMode) => {
            themeLabel.textContent = isDarkMode ? 'Dark Mode' : 'Light Mode';
        };

        const isInitiallyDark = document.documentElement.classList.contains('dark-mode');
        themeToggle.checked = isInitiallyDark;
        updateThemeLabel(isInitiallyDark);

        themeToggle.addEventListener('change', () => {
            const isDarkMode = document.documentElement.classList.toggle('dark-mode');
            const currentTheme = isDarkMode ? 'dark' : 'light';
            localStorage.setItem('theme', currentTheme);
            updateThemeLabel(isDarkMode);
        });
        themeToggle.dataset.listenerAttached = 'true';
    }

    // eslint-disable-next-line no-undef
    window.showCustomAlert = function(message, type = 'success') {
        if (window.customAlert) {
            switch(type) {
                case 'success':
                    window.customAlert.success(message);
                    break;
                case 'error':
                    window.customAlert.error(message);
                    break;
                case 'warning':
                    window.customAlert.warning(message);
                    break;
                case 'info':
                    window.customAlert.info(message);
                    break;
                default:
                    window.customAlert.info(message);
            }
        } else {
            const customAlert = document.getElementById('customAlert');
            const customAlertMessage = document.getElementById('customAlertMessage');
            if (customAlert && customAlertMessage) {
                customAlertMessage.textContent = message;
                customAlert.className = `admin-alert show ${type}`;
                customAlert.style.display = 'block';

                setTimeout(() => {
                    customAlert.classList.remove('show');
                    customAlert.style.display = 'none';
                }, 3000);
            }
        }
    }

    // --- Modal Functions (GENERAL) ---
    document.querySelectorAll('.modal').forEach(modal => {
        if (modal.id !== 'customAlert') {
            modal.style.display = 'none';
            modal.classList.remove('show-modal');
            modal.setAttribute('aria-hidden', 'true');
        }
    });

    window.openModal = function(modalElement) {
        if (modalElement) {
            modalElement.style.display = 'flex';
            modalElement.classList.add('show-modal');
            modalElement.setAttribute('aria-hidden', 'false');
        }
    }

    window.closeModal = function(modalElement) {
        if (modalElement) {
            const focusedElement = modalElement.querySelector(':focus');
            if (focusedElement) {
                focusedElement.blur();
            }
            
            modalElement.classList.remove('show-modal');
            modalElement.style.display = 'none';
            modalElement.setAttribute('aria-hidden', 'true');
        }
    }

    document.querySelectorAll('.modal .close-button').forEach(button => {
        if (button.dataset.listenerAttached) return;
        button.dataset.listenerAttached = 'true';
        button.addEventListener('click', (e) => {
            const modal = e.target.closest('.modal');
            if (modal) {
                window.closeModal(modal);
            }
        });
    });

    // Header dropdown & logout (idempotent)
    if (adminProfileToggle && adminDropdownMenu && !adminProfileToggle.dataset.listenerAttached) {
        adminProfileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            adminDropdownMenu.classList.toggle('show-dropdown');
        });
        window.addEventListener('click', function(event) {
            if (!adminProfileToggle.contains(event.target) && !adminDropdownMenu.contains(event.target)) {
                adminDropdownMenu.classList.remove('show-dropdown');
            }
        });
        adminProfileToggle.dataset.listenerAttached = 'true';
    }

    if (logoutButton && logoutConfirmModal && confirmLogoutBtn && !logoutButton.dataset.listenerAttached) {
        logoutButton.addEventListener('click', function(e) {
            e.preventDefault();
            if (adminDropdownMenu && adminDropdownMenu.classList.contains('show-dropdown')) {
                adminDropdownMenu.classList.remove('show-dropdown');
            }
            window.openModal(logoutConfirmModal);
        });
        confirmLogoutBtn.addEventListener('click', function() {
            window.location.href = '../pages/dashboard.php?action=logout';
        });
        logoutButton.dataset.listenerAttached = 'true';
    }
    
    // Initialize Lucide Icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    /*
    LUCIDE ICONS USAGE GUIDE:
    
    To use Lucide icons in HTML:
    <i data-lucide="icon-name" class="w-4 h-4"></i>
    
    Common icon names:
    - plus, minus, x, check
    - user, users, settings, home
    - edit-3, trash-2, eye, eye-off
    - search, filter, calendar, clock
    - chevron-left, chevron-right, chevron-up, chevron-down
    - menu, more-horizontal, more-vertical
    - download, upload, file, folder
    - bell, mail, phone, map-pin
    
    After adding new Lucide icons via JavaScript, call:
    lucide.createIcons();
    
    Full icon list: https://lucide.dev/icons/
    */
}
document.addEventListener('mousedown', function (event) {
    const openModal = document.querySelector('.modal.show-modal');
    
    // Only proceed if a modal is open
    if (!openModal) return;
    
    // Check if the mousedown started on the backdrop (not content)
    const isBackdropClick = event.target === openModal;
    
    if (isBackdropClick) {
        // Store that we started on backdrop for mouseup check
        openModal._backdropMouseDown = true;
    }
});

document.addEventListener('mouseup', function (event) {
    const openModal = document.querySelector('.modal.show-modal');
    
    // Only proceed if a modal is open and mousedown started on backdrop
    if (!openModal || !openModal._backdropMouseDown) return;
    
    // Only close if mouseup is also on the backdrop (complete click on backdrop)
    const isBackdropRelease = event.target === openModal;
    
    if (isBackdropRelease) {
        window.closeModal(openModal);
    }
    
    // Clean up the flag
    delete openModal._backdropMouseDown;
});

// Also clean up on mouse leave to handle edge cases
document.addEventListener('mouseleave', function () {
    const openModal = document.querySelector('.modal.show-modal');
    if (openModal) {
        delete openModal._backdropMouseDown;
    }
});

// Custom Confirmation Modal Functions
window.showCustomConfirm = function(options = {}) {
    const {
        title = 'Confirm Action',
        message = 'Are you sure you want to continue?',
        confirmText = 'Confirm',
        cancelText = 'Cancel',
        icon = 'alert-triangle',
        iconColor = 'text-yellow-500',
        confirmButtonClass = 'btn-primary-danger',
        onConfirm = () => {},
        onCancel = () => {}
    } = options;

    const modal = document.getElementById('customConfirmModal');
    const titleElement = document.getElementById('confirmModalTitle');
    const messageElement = document.getElementById('confirmModalMessage');
    const iconElement = document.getElementById('confirmModalIcon');
    const confirmButton = document.getElementById('confirmModalConfirm');
    const cancelButton = document.getElementById('confirmModalCancel');

    if (!modal || !titleElement || !messageElement || !iconElement || !confirmButton || !cancelButton) {
        console.warn('Custom confirmation modal elements not found, falling back to browser confirm');
        return Promise.resolve(confirm(message));
    }

    // Set content
    titleElement.textContent = title;
    messageElement.textContent = message;
    confirmButton.textContent = confirmText;
    cancelButton.textContent = cancelText;
    
    // Auto-detect delete operations and force custom SVG
    const isDeleteOperation = title.toLowerCase().includes('delete') || 
                             message.toLowerCase().includes('delete') || 
                             confirmText.toLowerCase().includes('delete') ||
                             icon === 'trash-2' || 
                             icon.startsWith('custom-');
    
    // Set icon
    if (isDeleteOperation) {
        // Force custom SVG for all delete operations
        iconElement.className = `w-36 h-36 mb-4 flex items-center justify-center`;
        iconElement.innerHTML = `<img src="../assets/icons/trash.svg" alt="Delete Icon" class="custom-svg-red w-36 h-36">`;
    } else if (icon.startsWith('custom-')) {
        // Use other custom SVG files
        const svgFileName = icon.replace('custom-', '') + '.svg';
        iconElement.className = `w-36 h-36 mb-4 flex items-center justify-center`;
        iconElement.innerHTML = `<img src="../assets/icons/${svgFileName}" alt="Icon" class="w-36 h-36">`;
    } else {
        // Use Lucide icon
        iconElement.className = `w-36 h-36 mb-4 ${iconColor} flex items-center justify-center`;
        iconElement.innerHTML = `<i data-lucide="${icon}" class="w-36 h-36"></i>`;
    }
    
    // Set confirm button style
    confirmButton.className = confirmButtonClass;

    // Return a promise that resolves with the user's choice
    return new Promise((resolve) => {
        const handleConfirm = () => {
            cleanup();
            onConfirm();
            resolve(true);
        };

        const handleCancel = () => {
            cleanup();
            onCancel();
            resolve(false);
        };

        const cleanup = () => {
            confirmButton.removeEventListener('click', handleConfirm);
            cancelButton.removeEventListener('click', handleCancel);
            window.closeModal(modal);
        };

        // Add event listeners
        confirmButton.addEventListener('click', handleConfirm);
        cancelButton.addEventListener('click', handleCancel);

        // Show modal
        window.openModal(modal);
        
        // Re-initialize Lucide icons for the new icon
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
};

// Convenience function for delete confirmations
window.confirmDelete = function(itemName = 'this item') {
    return window.showCustomConfirm({
        title: 'Delete Confirmation',
        message: `Are you sure you want to permanently delete ${itemName}? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        icon: 'custom-trash',
        iconColor: 'text-red-500',
        confirmButtonClass: 'btn-primary-danger'
    });
};

window.initGlobalUI = initGlobalUI;
document.addEventListener('DOMContentLoaded', initGlobalUI);