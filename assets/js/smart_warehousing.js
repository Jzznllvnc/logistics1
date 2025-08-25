// Logistic1/assets/js/smart_warehousing.js

// Global variables for modal elements
var stockManagementModal, modalTitle, stockModalSubtitle, stockAction, confirmStockBtn;
var currentPaginationPage = 1; // Track current page

/**
 * Opens the stock management modal and configures it for the specified action
 * @param {string} action - Either 'stock-in' or 'stock-out'
 */
function openStockModal(action) {
    // Initialize elements if not already done
    if (!stockManagementModal) {
        stockManagementModal = document.getElementById('stockManagementModal');
        modalTitle = document.getElementById('modalTitle');
        stockModalSubtitle = document.getElementById('stockModalSubtitle');
        stockAction = document.getElementById('stockAction');
        confirmStockBtn = document.getElementById('confirmStockBtn');
    }

    if (!stockManagementModal || !modalTitle || !stockModalSubtitle || !stockAction || !confirmStockBtn) {
        console.warn('Stock management modal elements not found');
        return;
    }

    // Clear form fields
    const itemNameInput = document.getElementById('modal_item_name');
    const quantityInput = document.getElementById('modal_quantity');
    if (itemNameInput) itemNameInput.value = '';
    if (quantityInput) quantityInput.value = '';

    // Set action
    stockAction.value = action;

    // Get icon and title text elements
    const stockModalIcon = document.getElementById('stockModalIcon');
    const stockModalTitleText = document.getElementById('stockModalTitleText');

    // Update modal content based on action
    if (action === 'stock-in') {
        if (stockModalIcon) stockModalIcon.setAttribute('data-lucide', 'package-plus');
        if (stockModalTitleText) stockModalTitleText.textContent = 'Stock In Items';
        stockModalSubtitle.textContent = 'Add new items or increase existing inventory quantities.';
        confirmStockBtn.textContent = 'Stock In';
        confirmStockBtn.className = 'btn-primary';
    } else if (action === 'stock-out') {
        if (stockModalIcon) stockModalIcon.setAttribute('data-lucide', 'package-minus');
        if (stockModalTitleText) stockModalTitleText.textContent = 'Stock Out Items';
        stockModalSubtitle.textContent = 'Remove items or decrease existing inventory quantities.';
        confirmStockBtn.textContent = 'Stock Out';
        confirmStockBtn.className = 'btn-primary-danger';
    }

    // Open the modal using the global function
    if (window.openModal) {
        window.openModal(stockManagementModal);
        // Refresh icons after modal opens
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
}

/**
 * Initialize stock management functionality
 * This function can be called multiple times safely
 */
function initStockManagement() {
    const stockInBtn = document.getElementById('stockInBtn');
    const stockOutBtn = document.getElementById('stockOutBtn');

    if (stockInBtn && stockOutBtn) {
        stockInBtn.removeEventListener('click', handleStockInClick);
        stockOutBtn.removeEventListener('click', handleStockOutClick);
        
        stockInBtn.addEventListener('click', handleStockInClick);
        stockOutBtn.addEventListener('click', handleStockOutClick);
    }
}

// Event handler functions
function handleStockInClick() {
    openStockModal('stock-in');
}

function handleStockOutClick() {
    openStockModal('stock-out');
}

/**
 * Toggle action dropdown for table rows with smart positioning
 */
function toggleActionDropdown(itemId) {
    const dropdown = document.getElementById(`dropdown-${itemId}`);
    const allDropdowns = document.querySelectorAll('.action-dropdown');
    
    allDropdowns.forEach(d => {
        if (d.id !== `dropdown-${itemId}`) {
            d.classList.add('hidden');
        }
    });
    
    if (dropdown) {
        if (dropdown.classList.contains('hidden')) {
            positionDropdownSmart(dropdown, itemId);
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
    const button = dropdown.previousElementSibling; 
    const buttonRect = button.getBoundingClientRect();
    const dropdownHeight = 120;
    const viewportHeight = window.innerHeight;
    const buffer = 30;
    
    dropdown.classList.remove('dropdown-above', 'dropdown-below');
    dropdown.style.top = '';
    dropdown.style.bottom = '';
    dropdown.style.left = '';
    dropdown.style.right = '';
    
    const spaceBelow = viewportHeight - buttonRect.bottom;
    const spaceAbove = buttonRect.top;
    
    dropdown.style.right = (window.innerWidth - buttonRect.right) + 'px';
    dropdown.style.width = '128px';
    
    if (spaceBelow < (dropdownHeight + buffer) && spaceAbove > (dropdownHeight + buffer)) {
        dropdown.style.bottom = (viewportHeight - buttonRect.top + 10) + 'px';
        dropdown.classList.add('dropdown-above');
    } else {
        dropdown.style.top = (buttonRect.bottom + 10) + 'px';
        dropdown.classList.add('dropdown-below');
    }
}

/**
 * Initialize inventory search functionality
 */
function initInventorySearch() {
    const searchInput = document.getElementById('inventorySearchInput');
    const filterSelect = document.getElementById('inventoryFilter');
    
    if (searchInput) {
        searchInput.removeEventListener('keyup', applyFiltersAndSearch);
        searchInput.addEventListener('keyup', applyFiltersAndSearch);
    }
    
    if (filterSelect) {
        filterSelect.removeEventListener('change', applyFiltersAndSearch);
        filterSelect.addEventListener('change', applyFiltersAndSearch);
    }
    
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.action-dropdown-btn') && !event.target.closest('.action-dropdown')) {
            document.querySelectorAll('.action-dropdown').forEach(d => d.classList.add('hidden'));
        }
    });
}

/**
 * Apply both search and filter criteria to the inventory table
 */
function applyFiltersAndSearch() {
    const searchInput = document.getElementById('inventorySearchInput');
    const filterSelect = document.getElementById('inventoryFilter');
    const tableBody = document.getElementById('inventoryTableBody');
    
    if (!searchInput || !filterSelect || !tableBody) return;
    
    const searchTerm = searchInput.value.toLowerCase();
    const filterValue = filterSelect.value;
    const rows = tableBody.getElementsByTagName('tr');

    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const itemNameCell = row.getElementsByTagName('td')[0];
        const quantityCell = row.getElementsByTagName('td')[1];
        
        if (!itemNameCell || !quantityCell) continue;
        
        const itemName = itemNameCell.textContent || itemNameCell.innerText;
        const quantityText = quantityCell.textContent || quantityCell.innerText;
        const quantity = parseInt(quantityText.replace(/[^0-9]/g, '')) || 0;
        
        const matchesSearch = itemName.toLowerCase().includes(searchTerm);
        
        let matchesFilter = true;
        if (filterValue === 'low-stock') {
            matchesFilter = quantity < 10;
        } else if (filterValue === 'normal-stock') {
            matchesFilter = quantity >= 10 && quantity <= 100;
        } else if (filterValue === 'high-stock') {
            matchesFilter = quantity > 100;
        }
        
        row.style.display = (matchesSearch && matchesFilter) ? '' : 'none';
    }
}

/**
 * Main initialization function for Smart Warehousing page
 */
function initSmartWarehousing() {
    const urlParams = new URLSearchParams(window.location.search);
    currentPaginationPage = parseInt(urlParams.get('page')) || 1;
    
    initStockManagement();
    initInventorySearch();
}

// Make the initializer globally available for PJAX
window.initSmartWarehousing = initSmartWarehousing;

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', initSmartWarehousing);


// --- Modal Functions (Admin Only) ---

/**
 * Opens the modal to edit an inventory item's name.
 * @param {object} item - The inventory item object (id, item_name).
 */
function openEditModal(item) {
    const modal = document.getElementById('editItemModal');
    if (modal) {
        document.getElementById('edit_item_id').value = item.id;
        document.getElementById('item_name_edit').value = item.item_name;
        if(window.openModal) {
            window.openModal(modal);
        }
    }
}

/**
 * Confirms and submits a request to delete an inventory item.
 * @param {number} itemId - The ID of the item to delete.
 */
async function confirmDeleteItem(itemId) {
    const confirmed = await window.confirmDelete('this item');
    
    if (confirmed) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `smart_warehousing.php?page=${currentPaginationPage}`; 
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_item">
            <input type="hidden" name="item_id" value="${itemId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}