// Logistic1/assets/js/alms.js

// --- Schedule Maintenance Modal Functions ---
function openScheduleMaintenanceModal() {
    const modal = document.getElementById('scheduleMaintenanceModal');
    const form = document.getElementById('scheduleMaintenanceForm');
    
    if (form) {
        form.reset();
    }
    
    if (modal && window.openModal) {
        window.openModal(modal);
    }
}

/**
 * Toggle asset action dropdown with smart positioning
 */
function toggleAssetDropdown(assetId) {
    const dropdown = document.getElementById(`asset-dropdown-${assetId}`);
    const allDropdowns = document.querySelectorAll('.action-dropdown');
    
    // Close all other dropdowns
    allDropdowns.forEach(d => {
        if (d.id !== `asset-dropdown-${assetId}`) {
            d.classList.add('hidden');
        }
    });
    
    // Toggle current dropdown
    if (dropdown) {
        if (dropdown.classList.contains('hidden')) {
            // Show dropdown with smart positioning
            positionDropdownSmart(dropdown, assetId);
            dropdown.classList.remove('hidden');
        } else {
            dropdown.classList.add('hidden');
        }
    }
}

/**
 * Position dropdown smartly based on available viewport space
 */
function positionDropdownSmart(dropdown, itemId) {
    const button = dropdown.previousElementSibling; // The ellipsis button
    const buttonRect = button.getBoundingClientRect();
    const dropdownHeight = 120; // More conservative estimate for dropdown height
    const viewportHeight = window.innerHeight;
    const buffer = 30; // Larger buffer to ensure dropdown isn't clipped
    
    // Reset classes and inline styles first
    dropdown.classList.remove('dropdown-above', 'dropdown-below');
    dropdown.style.top = '';
    dropdown.style.bottom = '';
    dropdown.style.left = '';
    dropdown.style.right = '';
    
    // Calculate position relative to viewport
    const spaceBelow = viewportHeight - buttonRect.bottom;
    const spaceAbove = buttonRect.top;
    
    // Position the dropdown with fixed positioning
    dropdown.style.right = (window.innerWidth - buttonRect.right) + 'px';
    dropdown.style.width = '128px'; // w-32 equivalent
    
    // Be very aggressive - if less than required space, position above
    if (spaceBelow < (dropdownHeight + buffer) && spaceAbove > (dropdownHeight + buffer)) {
        // Position above
        dropdown.style.bottom = (viewportHeight - buttonRect.top + 10) + 'px';
        dropdown.classList.add('dropdown-above');
    } else {
        // Position below (default)
        dropdown.style.top = (buttonRect.bottom + 10) + 'px';
        dropdown.classList.add('dropdown-below');
    }
}

/**
 * Close all dropdowns when clicking outside
 */
function closeAllAssetDropdowns() {
    const allDropdowns = document.querySelectorAll('.action-dropdown');
    allDropdowns.forEach(d => d.classList.add('hidden'));
}

/**
 * Initialize asset dropdown functionality
 */
function initAssetDropdowns() {
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.action-dropdown-btn') && !event.target.closest('.action-dropdown')) {
            closeAllAssetDropdowns();
        }
    });
    
    // Recalculate dropdown positions on window resize
    window.addEventListener('resize', function() {
        const openDropdowns = document.querySelectorAll('.action-dropdown:not(.hidden)');
        openDropdowns.forEach(dropdown => {
            const itemId = dropdown.id.replace('asset-dropdown-', '');
            positionDropdownSmart(dropdown, itemId);
        });
    });
    
    // Also recalculate on scroll
    window.addEventListener('scroll', function() {
        const openDropdowns = document.querySelectorAll('.action-dropdown:not(.hidden)');
        openDropdowns.forEach(dropdown => {
            const itemId = dropdown.id.replace('asset-dropdown-', '');
            positionDropdownSmart(dropdown, itemId);
        });
    });
}

/**
 * Initialize ALMS page functionality
 */
function initALMS() {
    const scheduleTaskBtn = document.getElementById('scheduleTaskBtn');
    
    if (scheduleTaskBtn) {
        // Remove existing listener to prevent duplicates
        scheduleTaskBtn.removeEventListener('click', openScheduleMaintenanceModal);
        scheduleTaskBtn.addEventListener('click', openScheduleMaintenanceModal);
    }
    
    // Initialize dropdown functionality
    initAssetDropdowns();
}

// Make the initializer globally available for PJAX
window.initALMS = initALMS;

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
    initALMS();
    initLucideIcons();
});

// --- Asset Modal Functions ---
function openCreateAssetModal() {
    document.getElementById('assetForm').reset();
    document.getElementById('modalTitle').innerText = 'Register New Asset';
    document.getElementById('formAction').value = 'create_asset';
    if (window.openModal) window.openModal(document.getElementById('assetModal'));
}

function openEditAssetModal(asset) {
    document.getElementById('assetForm').reset();
    document.getElementById('modalTitle').innerText = 'Edit Asset';
    document.getElementById('formAction').value = 'update_asset';
    document.getElementById('assetId').value = asset.id;
    document.getElementById('asset_name').value = asset.asset_name;
    document.getElementById('asset_type').value = asset.asset_type;
    document.getElementById('purchase_date').value = asset.purchase_date;
    document.getElementById('status').value = asset.status;
    if (window.openModal) window.openModal(document.getElementById('assetModal'));
}

function confirmDeleteAsset(assetId) {
    if (confirm('Are you sure you want to delete this asset? This will also remove all its maintenance history.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'asset_lifecycle_maintenance.php';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_asset">
            <input type="hidden" name="asset_id" value="${assetId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}