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
            window.location.href = '../partials/login.php?action=logout';
        });
        logoutButton.dataset.listenerAttached = 'true';
    }
}
document.addEventListener('click', function (event) {
    const openModal = document.querySelector('.modal.show-modal');
    
    // If a modal is open and the user clicks directly on the modal backdrop (not the content)
    if (openModal && event.target === openModal) {
        window.closeModal(openModal);
    }
});
window.initGlobalUI = initGlobalUI;
document.addEventListener('DOMContentLoaded', initGlobalUI);
