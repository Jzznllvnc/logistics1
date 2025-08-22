// Logistic1/assets/js/procurement.js

// --- Purchase Order Modal Functions ---
function openCreatePOModal() {
    const modal = document.getElementById('createPOModal');
    const form = document.getElementById('createPOForm');
    
    if (form) {
        form.reset();
    }
    
    if (modal && window.openModal) {
        window.openModal(modal);
    }
}

/**
 * Toggle supplier action dropdown with smart positioning
 */
function toggleSupplierDropdown(supplierId) {
    const dropdown = document.getElementById(`supplier-dropdown-${supplierId}`);
    const allDropdowns = document.querySelectorAll('.action-dropdown');
    
    // Close all other dropdowns
    allDropdowns.forEach(d => {
        if (d.id !== `supplier-dropdown-${supplierId}`) {
            d.classList.add('hidden');
        }
    });
    
    // Toggle current dropdown
    if (dropdown) {
        if (dropdown.classList.contains('hidden')) {
            // Show dropdown with smart positioning
            positionDropdownSmart(dropdown, supplierId);
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
function closeAllSupplierDropdowns() {
    const allDropdowns = document.querySelectorAll('.action-dropdown');
    allDropdowns.forEach(d => d.classList.add('hidden'));
}

/**
 * Initialize supplier dropdown functionality
 */
function initSupplierDropdowns() {
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.action-dropdown-btn') && !event.target.closest('.action-dropdown')) {
            closeAllSupplierDropdowns();
        }
    });
    
    // Recalculate dropdown positions on window resize
    window.addEventListener('resize', function() {
        const openDropdowns = document.querySelectorAll('.action-dropdown:not(.hidden)');
        openDropdowns.forEach(dropdown => {
            const itemId = dropdown.id.replace('supplier-dropdown-', '');
            positionDropdownSmart(dropdown, itemId);
        });
    });
    
    // Also recalculate on scroll
    window.addEventListener('scroll', function() {
        const openDropdowns = document.querySelectorAll('.action-dropdown:not(.hidden)');
        openDropdowns.forEach(dropdown => {
            const itemId = dropdown.id.replace('supplier-dropdown-', '');
            positionDropdownSmart(dropdown, itemId);
        });
    });
}

/**
 * Initialize procurement page functionality
 */
function initProcurement() {
    const createPOBtn = document.getElementById('createPOBtn');
    
    if (createPOBtn) {
        // Remove existing listener to prevent duplicates
        createPOBtn.removeEventListener('click', openCreatePOModal);
        createPOBtn.addEventListener('click', openCreatePOModal);
    }
    
    // Initialize dropdown functionality
    initSupplierDropdowns();
}

// Make the initializer globally available for PJAX
window.initProcurement = initProcurement;

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
    initProcurement();
    initLucideIcons();
});

// --- Supplier Modal Functions ---
function openCreateSupplierModal() {
    document.getElementById('supplierForm').reset();
    document.getElementById('modalTitle').innerText = 'Add New Supplier';
    document.getElementById('formAction').value = 'create_supplier';
    if(window.openModal) window.openModal(document.getElementById('supplierModal'));
}

function openEditSupplierModal(supplier) {
    document.getElementById('supplierForm').reset();
    document.getElementById('modalTitle').innerText = 'Edit Supplier';
    document.getElementById('formAction').value = 'update_supplier';
    document.getElementById('supplierId').value = supplier.id;
    document.getElementById('supplier_name').value = supplier.supplier_name;
    document.getElementById('contact_person').value = supplier.contact_person;
    document.getElementById('email').value = supplier.email;
    document.getElementById('phone').value = supplier.phone;
    document.getElementById('address').value = supplier.address;
    if(window.openModal) window.openModal(document.getElementById('supplierModal'));
}

function confirmDeleteSupplier(supplierId) {
    if (confirm('Are you sure you want to delete this supplier? This will also delete all associated purchase orders.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'procurement_sourcing.php';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_supplier">
            <input type="hidden" name="supplier_id" value="${supplierId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}