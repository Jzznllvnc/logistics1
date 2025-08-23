// Logistic1/assets/js/smart_warehousing.js

// Global variables for modal elements
let stockManagementModal, modalTitle, stockModalSubtitle, stockAction, confirmStockBtn;
let currentPaginationPage = 1; // Track current page

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
        // Remove existing listeners to prevent duplicates
        stockInBtn.removeEventListener('click', handleStockInClick);
        stockOutBtn.removeEventListener('click', handleStockOutClick);
        
        // Add event listeners
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
    
    // Close all other dropdowns
    allDropdowns.forEach(d => {
        if (d.id !== `dropdown-${itemId}`) {
            d.classList.add('hidden');
        }
    });
    
    // Toggle current dropdown
    if (dropdown) {
        if (dropdown.classList.contains('hidden')) {
            // Show dropdown with smart positioning
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
 * Load a specific page via AJAX
 */
async function loadPage(page) {
    try {
        // Show loading state
        showLoadingState();
        
        // Update current page tracker
        currentPaginationPage = page;
        
        const response = await fetch(`smart_warehousing.php?ajax=pagination&page=${page}`);
        const data = await response.json();
        
        if (data.success) {
            updateTableContent(data);
            updatePaginationControls(data);
            updatePaginationInfo(data);
            
            // Update URL without page reload
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('page', page);
            window.history.replaceState({}, '', newUrl);
        } else {
            console.error('Failed to load page data');
        }
    } catch (error) {
        console.error('Error loading page:', error);
    } finally {
        hideLoadingState();
    }
}

/**
 * Update table content with new data
 */
function updateTableContent(data) {
    const tableBody = document.getElementById('inventoryTableBody');
    if (!tableBody) return;
    
    // Clear current content
    tableBody.innerHTML = '';
    
    if (data.inventory.length === 0) {
        const colspan = data.isAdmin ? '4' : '3';
        tableBody.innerHTML = `<tr><td colspan="${colspan}" class="table-empty">No items in inventory.</td></tr>`;
        return;
    }
    
    // Add new rows
    data.inventory.forEach(item => {
        const row = document.createElement('tr');
        
        const quantityClass = item.quantity < 10 ? 'table-status-low' : 'table-status-normal';
        const lowStockText = item.quantity < 10 ? ' (Low Stock)' : '';
        
        const lastUpdated = new Date(item.last_updated).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
        
        let actionsColumn = '';
        if (data.isAdmin) {
            actionsColumn = `
                <td>
                    <div class="relative">
                        <button type="button" class="action-dropdown-btn p-2 rounded-full transition-colors" onclick="toggleActionDropdown(${item.id})">
                            <i data-lucide="more-horizontal" class="w-5 h-5"></i>
                        </button>
                        <div id="dropdown-${item.id}" class="action-dropdown bg-white border border-gray-200 rounded-md shadow-lg w-32 hidden">
                            <button type="button" onclick='openEditModal(${JSON.stringify(item)})' class="w-full text-left px-3 py-2 text-sm flex items-center">
                                <i data-lucide="edit-3" class="w-4 h-4 mr-2"></i>
                                Edit
                            </button>
                            <button type="button" onclick="confirmDeleteItem(${item.id})" class="w-full text-left px-3 py-2 text-sm flex items-center text-red-600">
                                <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i>
                                Delete
                            </button>
                        </div>
                    </div>
                </td>
            `;
        }
        
        row.innerHTML = `
            <td>${escapeHtml(item.item_name)}</td>
            <td class="${quantityClass}">
                ${item.quantity}${lowStockText}
            </td>
            <td>${lastUpdated}</td>
            ${actionsColumn}
        `;
        
        tableBody.appendChild(row);
    });
}

/**
 * Update pagination controls
 */
function updatePaginationControls(data) {
    const container = document.getElementById('paginationContainer');
    if (!container) return;
    
    const { currentPage, totalPages } = data;
    
    // Hide pagination if only one page
    if (totalPages <= 1) {
        container.parentElement.style.display = 'none';
        return;
    } else {
        container.parentElement.style.display = 'block';
    }
    
    container.innerHTML = '';
    
    // Previous button
    if (currentPage > 1) {
        const prevBtn = document.createElement('button');
        prevBtn.onclick = () => loadPage(currentPage - 1);
        prevBtn.className = 'pagination-btn';
        prevBtn.innerHTML = '<i data-lucide="chevron-left" class="w-4 h-4 mr-1"></i>Previous';
        container.appendChild(prevBtn);
    }
    
    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);
    
    // First page + ellipsis
    if (startPage > 1) {
        const firstBtn = document.createElement('button');
        firstBtn.onclick = () => loadPage(1);
        firstBtn.className = `pagination-btn ${currentPage === 1 ? 'active' : ''}`;
        firstBtn.textContent = '1';
        container.appendChild(firstBtn);
        
        if (startPage > 2) {
            const ellipsis = document.createElement('span');
            ellipsis.className = 'pagination-ellipsis';
            ellipsis.textContent = '...';
            container.appendChild(ellipsis);
        }
    }
    
    // Page number buttons
    for (let i = startPage; i <= endPage; i++) {
        const pageBtn = document.createElement('button');
        pageBtn.onclick = () => loadPage(i);
        pageBtn.className = `pagination-btn ${currentPage === i ? 'active' : ''}`;
        pageBtn.textContent = i;
        pageBtn.setAttribute('data-page', i);
        container.appendChild(pageBtn);
    }
    
    // Last page + ellipsis
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            const ellipsis = document.createElement('span');
            ellipsis.className = 'pagination-ellipsis';
            ellipsis.textContent = '...';
            container.appendChild(ellipsis);
        }
        
        const lastBtn = document.createElement('button');
        lastBtn.onclick = () => loadPage(totalPages);
        lastBtn.className = `pagination-btn ${currentPage === totalPages ? 'active' : ''}`;
        lastBtn.textContent = totalPages;
        container.appendChild(lastBtn);
    }
    
    // Next button
    if (currentPage < totalPages) {
        const nextBtn = document.createElement('button');
        nextBtn.onclick = () => loadPage(currentPage + 1);
        nextBtn.className = 'pagination-btn';
        nextBtn.innerHTML = 'Next<i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>';
        container.appendChild(nextBtn);
    }
    
    // Reinitialize Lucide icons for new content
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

/**
 * Update pagination info text
 */
function updatePaginationInfo(data) {
    const infoElement = document.getElementById('paginationInfo');
    if (!infoElement) return;
    
    const { currentPage, totalPages, totalItems, itemsPerPage } = data;
    const startItem = ((currentPage - 1) * itemsPerPage) + 1;
    const endItem = Math.min(currentPage * itemsPerPage, totalItems);
    
    infoElement.className = 'pagination-info';
    infoElement.textContent = `Showing ${startItem} to ${endItem} of ${totalItems} items`;
}

/**
 * Show loading state
 */
function showLoadingState() {
    const tableBody = document.getElementById('inventoryTableBody');
    if (tableBody) {
        tableBody.style.opacity = '0.5';
        tableBody.style.pointerEvents = 'none';
    }
    
    const pagination = document.getElementById('paginationContainer');
    if (pagination) {
        pagination.style.opacity = '0.5';
        pagination.style.pointerEvents = 'none';
    }
}

/**
 * Hide loading state
 */
function hideLoadingState() {
    const tableBody = document.getElementById('inventoryTableBody');
    if (tableBody) {
        tableBody.style.opacity = '1';
        tableBody.style.pointerEvents = 'auto';
    }
    
    const pagination = document.getElementById('paginationContainer');
    if (pagination) {
        pagination.style.opacity = '1';
        pagination.style.pointerEvents = 'auto';
    }
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
 * Close all dropdowns when clicking outside
 */
function closeAllDropdowns() {
    const allDropdowns = document.querySelectorAll('.action-dropdown');
    allDropdowns.forEach(d => d.classList.add('hidden'));
}

/**
 * Initialize form submissions to use AJAX
 */
function initAjaxForms() {
    // Handle edit form submission
    const editForm = document.getElementById('editItemForm');
    if (editForm) {
        editForm.removeEventListener('submit', handleEditFormSubmit);
        editForm.addEventListener('submit', handleEditFormSubmit);
    }
    
    // Handle stock management form submission
    const stockForm = document.getElementById('stockManagementForm');
    if (stockForm) {
        stockForm.removeEventListener('submit', handleStockFormSubmit);
        stockForm.addEventListener('submit', handleStockFormSubmit);
    }
}

/**
 * Handle edit form submission via AJAX
 */
function handleEditFormSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    fetch('smart_warehousing.php', {
        method: 'POST',
        body: formData
    })
    .then(() => {
        // Refresh current page data
        loadPage(currentPaginationPage);
        
        // Close modal
        const modal = document.getElementById('editItemModal');
        if (modal && window.closeModal) {
            window.closeModal(modal);
        }
        
        // Show success message
        if (window.showCustomAlert) {
            window.showCustomAlert('Item successfully updated.', 'success');
        }
    })
    .catch(error => {
        console.error('Error updating item:', error);
        if (window.showCustomAlert) {
            window.showCustomAlert('Failed to update item.', 'error');
        }
    });
}

/**
 * Handle stock form submission via AJAX
 */
function handleStockFormSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    fetch('smart_warehousing.php', {
        method: 'POST',
        body: formData
    })
    .then(() => {
        // Refresh current page data
        loadPage(currentPaginationPage);
        
        // Close modal
        const modal = document.getElementById('stockManagementModal');
        if (modal && window.closeModal) {
            window.closeModal(modal);
        }
        
        // Show success message
        const action = formData.get('action');
        const itemName = formData.get('item_name');
        const quantity = formData.get('quantity');
        
        if (window.showCustomAlert) {
            if (action === 'stock-in') {
                window.showCustomAlert(`Successfully stocked in ${quantity} of ${itemName}.`, 'success');
            } else if (action === 'stock-out') {
                window.showCustomAlert(`Successfully stocked out ${quantity} of ${itemName}.`, 'success');
            }
        }
    })
    .catch(error => {
        console.error('Error processing stock operation:', error);
        if (window.showCustomAlert) {
            window.showCustomAlert('Failed to process stock operation.', 'error');
        }
    });
}

/**
 * Initialize inventory search functionality
 */
function initInventorySearch() {
    const searchInput = document.getElementById('inventorySearchInput');
    const filterSelect = document.getElementById('inventoryFilter');
    const tableBody = document.getElementById('inventoryTableBody');
    
    if (searchInput && tableBody) {
        // Remove existing listener to prevent duplicates
        searchInput.removeEventListener('keyup', handleInventorySearch);
        searchInput.addEventListener('keyup', handleInventorySearch);
    }
    
    if (filterSelect && tableBody) {
        // Remove existing listener to prevent duplicates
        filterSelect.removeEventListener('change', handleInventoryFilter);
        filterSelect.addEventListener('change', handleInventoryFilter);
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.action-dropdown-btn') && !event.target.closest('.action-dropdown')) {
            closeAllDropdowns();
        }
    });
    
    // Recalculate dropdown positions on window resize
    window.addEventListener('resize', function() {
        const openDropdowns = document.querySelectorAll('.action-dropdown:not(.hidden)');
        openDropdowns.forEach(dropdown => {
            const itemId = dropdown.id.replace('dropdown-', '');
            positionDropdownSmart(dropdown, itemId);
        });
    });
    
    // Also recalculate on scroll
    window.addEventListener('scroll', function() {
        const openDropdowns = document.querySelectorAll('.action-dropdown:not(.hidden)');
        openDropdowns.forEach(dropdown => {
            const itemId = dropdown.id.replace('dropdown-', '');
            positionDropdownSmart(dropdown, itemId);
        });
    });
}

function handleInventorySearch() {
    applyFiltersAndSearch();
}

function handleInventoryFilter() {
    applyFiltersAndSearch();
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
        
        // Check search criteria
        const matchesSearch = itemName.toLowerCase().indexOf(searchTerm) > -1;
        
        // Check filter criteria
        let matchesFilter = true;
        switch (filterValue) {
            case 'low-stock':
                matchesFilter = quantity < 10;
                break;
            case 'normal-stock':
                matchesFilter = quantity >= 10 && quantity <= 100;
                break;
            case 'high-stock':
                matchesFilter = quantity > 100;
                break;
            case 'all':
            default:
                matchesFilter = true;
                break;
        }
        
        // Show/hide row based on both criteria
        if (matchesSearch && matchesFilter) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
    
    // Update filter results count
    updateFilterResultsCount();
}

/**
 * Update the display to show how many items are currently visible
 */
function updateFilterResultsCount() {
    const tableBody = document.getElementById('inventoryTableBody');
    if (!tableBody) return;
    
    const rows = tableBody.getElementsByTagName('tr');
    let visibleCount = 0;
    let totalCount = 0;
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        // Skip the "No items" row if it exists
        if (row.textContent.includes('No items in inventory')) continue;
        
        totalCount++;
        if (row.style.display !== 'none') {
            visibleCount++;
        }
    }
    
    // Add or update results info
    let resultsInfo = document.getElementById('filter-results-info');
    if (!resultsInfo) {
        resultsInfo = document.createElement('div');
        resultsInfo.id = 'filter-results-info';
        resultsInfo.className = 'text-sm text-gray-500 mt-2 mb-2';
        
        const tableContainer = tableBody.closest('.overflow-x-auto');
        if (tableContainer) {
            tableContainer.insertBefore(resultsInfo, tableContainer.querySelector('table'));
        }
    }
    
    if (visibleCount === totalCount) {
        resultsInfo.textContent = `Showing all ${totalCount} items`;
    } else {
        resultsInfo.textContent = `Showing ${visibleCount} of ${totalCount} items`;
    }
    
    // Hide the info if there are no items or if showing all
    const searchInput = document.getElementById('inventorySearchInput');
    const filterSelect = document.getElementById('inventoryFilter');
    const hasActiveFilters = (searchInput && searchInput.value) || (filterSelect && filterSelect.value !== 'all');
    
    if (!hasActiveFilters || totalCount === 0) {
        resultsInfo.style.display = 'none';
                    } else {
        resultsInfo.style.display = 'block';
    }
}

/**
 * Main initialization function for Smart Warehousing page
 * This is called by the PJAX system
 */
function initSmartWarehousing() {
    // Reset modal references to ensure fresh initialization
    stockManagementModal = null;
    modalTitle = null;
    stockModalSubtitle = null;
    stockAction = null;
    confirmStockBtn = null;
    
    // Get current page from URL
    const urlParams = new URLSearchParams(window.location.search);
    currentPaginationPage = parseInt(urlParams.get('page')) || 1;
    
    // Initialize all functionality
    initStockManagement();
    initInventorySearch();
    initAjaxForms(); // Initialize AJAX forms
}

// Make the initializer globally available for PJAX
window.initSmartWarehousing = initSmartWarehousing;

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initSmartWarehousing();
});

// Also initialize when the page becomes visible (for navigation scenarios)
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        initSmartWarehousing();
    }
});

// Fallback: Initialize after a short delay to handle edge cases
setTimeout(function() {
    initSmartWarehousing();
}, 100);

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
        // Assuming you have a global openModal function from script.js
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
        form.action = 'smart_warehousing.php'; // Submits back to the same page
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_item">
            <input type="hidden" name="item_id" value="${itemId}">
        `;
        document.body.appendChild(form);
        
        // Handle form submission via fetch to avoid page reload
        const formData = new FormData(form);
        
        fetch('smart_warehousing.php', {
            method: 'POST',
            body: formData
        })
        .then(() => {
            // Refresh current page data
            loadPage(currentPaginationPage);
            
            // Show success message if available
            if (window.showCustomAlert) {
                window.showCustomAlert('Item successfully deleted.', 'success');
            }
        })
        .catch(error => {
            console.error('Error deleting item:', error);
            if (window.showCustomAlert) {
                window.showCustomAlert('Failed to delete item.', 'error');
            }
        })
        .finally(() => {
            document.body.removeChild(form);
        });
    }
}