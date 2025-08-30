// Logistic1/assets/js/smart_warehousing.js

// Global variables for modal elements
var stockManagementModal, modalTitle, stockModalSubtitle, stockAction, confirmStockBtn;
var currentPaginationPage = 1; // Track current page

/**
 * AJAX Pagination functionality to prevent page flashing
 */
async function loadPage(page, updateHistory = true) {
    try {
        // Show loading state
        const tableBody = document.getElementById('inventoryTableBody');
        const paginationContainer = document.getElementById('paginationContainer');
        const paginationInfo = document.querySelector('.pagination-info');
        const tableContainer = document.querySelector('.table-container');
        
        if (tableBody) {
            tableBody.style.opacity = '0.6';
            tableBody.style.pointerEvents = 'none';
        }
        
        if (tableContainer) {
            tableContainer.classList.add('table-loading');
        }
        
        if (paginationContainer) {
            paginationContainer.classList.add('pagination-loading');
        }

        // Make AJAX request
        const response = await fetch(`smart_warehousing.php?ajax=pagination&page=${page}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            // Update table content
            updateTableContent(data.inventory, data.isAdmin, data.forecasts, data.price_forecasts);
            
            // Update pagination
            updatePaginationControls(data.currentPage, data.totalPages);
            
            // Update pagination info
            updatePaginationInfo(data.currentPage, data.itemsPerPage, data.totalItems);
            
            // Update current page tracking
            currentPaginationPage = data.currentPage;
            
            // Update URL without page reload
            if (updateHistory) {
                const newUrl = `smart_warehousing.php?page=${page}`;
                window.history.pushState({ page: page }, '', newUrl);
            }
        } else {
            throw new Error('Failed to load page data');
        }
        
    } catch (error) {
        console.error('Pagination error:', error);
        // Fallback to full page reload on error
        window.location.href = `smart_warehousing.php?page=${page}`;
    } finally {
        // Restore table state
        const tableBody = document.getElementById('inventoryTableBody');
        const paginationContainer = document.getElementById('paginationContainer');
        const tableContainer = document.querySelector('.table-container');
        
        if (tableBody) {
            tableBody.style.opacity = '1';
            tableBody.style.pointerEvents = 'auto';
        }
        
        if (tableContainer) {
            tableContainer.classList.remove('table-loading');
        }
        
        if (paginationContainer) {
            paginationContainer.classList.remove('pagination-loading');
        }
    }
}

/**
 * Update table content with new inventory data
 */
function updateTableContent(inventory, isAdmin, forecasts = {}, priceForecasts = {}) {
    const tableBody = document.getElementById('inventoryTableBody');
    if (!tableBody) return;
    
    if (inventory.length === 0) {
        const colspan = isAdmin ? '7' : '6';
        tableBody.innerHTML = `<tr><td colspan="${colspan}" class="table-empty">No items in inventory.</td></tr>`;
    } else {
        tableBody.innerHTML = inventory.map(item => {
            const stockClass = item.quantity < 10 ? 'table-status-low' : 'table-status-normal';
            const lowStockText = item.quantity < 10 ? ' (Low Stock)' : '';
            const lastUpdated = new Date(item.last_updated).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric', 
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            
            // Get forecast data for this item
            const itemForecast = forecasts[item.id] || {};
            const trendAnalysis = itemForecast.analysis || '<span class="text-gray-400">N/A</span>';
            const recommendedAction = itemForecast.action || '<span class="text-gray-400">N/A</span>';
            
            // Get price forecast for this item
            const priceForecast = priceForecasts[item.item_name] || '<span class="text-gray-400">N/A</span>';
            
            let actionsColumn = '';
            if (isAdmin) {
                actionsColumn = `
                    <td>
                        <div class="relative">
                            <button type="button" class="action-dropdown-btn p-2 rounded-full transition-colors" onclick="toggleActionDropdown(${item.id})">
                                <i data-lucide="more-horizontal" class="w-6 h-6"></i>
                            </button>
                            <div id="dropdown-${item.id}" class="action-dropdown hidden">
                                <button type="button" onclick='openEditModal(${JSON.stringify(item)})'>
                                    <i data-lucide="edit-3" class="w-5 h-5 mr-3"></i>
                                    Edit
                                </button>
                                <button type="button" onclick="confirmDeleteItem(${item.id})">
                                    <i data-lucide="trash-2" class="w-5 h-5 mr-3"></i>
                                    Delete
                                </button>
                                <button type="button" onclick="getPriceForecast('${item.item_name.replace(/'/g, "\\'")}')">
                                    <i data-lucide="trending-up" class="w-4 h-4 mr-3"></i>
                                    Forecast Price
                                </button>
                            </div>
                        </div>
                    </td>
                `;
            }
            
            return `
                <tr>
                    <td>${escapeHtml(item.item_name)}</td>
                    <td class="${stockClass}">
                        ${item.quantity}${lowStockText}
                    </td>
                    <td>
                        ${trendAnalysis}
                    </td>
                    <td>
                        ${recommendedAction}
                    </td>
                    <td>
                        ${priceForecast}
                    </td>
                    <td>${lastUpdated}</td>
                    ${actionsColumn}
                </tr>
            `;
        }).join('');
    }
    
    // Refresh Lucide icons for new content
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

/**
 * Update pagination controls
 */
function updatePaginationControls(currentPage, totalPages) {
    const paginationContainer = document.getElementById('paginationContainer');
    if (!paginationContainer || totalPages <= 1) {
        if (paginationContainer) paginationContainer.style.display = 'none';
        return;
    }
    
    paginationContainer.style.display = 'flex';
    
    let paginationHTML = '';
    
    // Previous button
    if (currentPage > 1) {
        paginationHTML += `
            <button onclick="loadPage(${currentPage - 1})" class="pagination-btn">
                <i data-lucide="chevron-left" class="w-4 h-4 mr-1"></i>
                Previous
            </button>
        `;
    }
    
    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);
    
    if (startPage > 1) {
        paginationHTML += `<button onclick="loadPage(1)" class="pagination-btn ${currentPage == 1 ? 'active' : ''}">1</button>`;
        if (startPage > 2) {
            paginationHTML += `<span class="pagination-ellipsis">...</span>`;
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        paginationHTML += `<button onclick="loadPage(${i})" class="pagination-btn ${currentPage == i ? 'active' : ''}" data-page="${i}">${i}</button>`;
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationHTML += `<span class="pagination-ellipsis">...</span>`;
        }
        paginationHTML += `<button onclick="loadPage(${totalPages})" class="pagination-btn ${currentPage == totalPages ? 'active' : ''}">${totalPages}</button>`;
    }
    
    // Next button
    if (currentPage < totalPages) {
        paginationHTML += `
            <button onclick="loadPage(${currentPage + 1})" class="pagination-btn">
                Next
                <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
            </button>
        `;
    }
    
    paginationContainer.innerHTML = paginationHTML;
    
    // Refresh Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

/**
 * Update pagination info
 */
function updatePaginationInfo(currentPage, itemsPerPage, totalItems) {
    const paginationInfo = document.querySelector('.pagination-info');
    if (!paginationInfo) return;
    
    const start = ((currentPage - 1) * itemsPerPage) + 1;
    const end = Math.min(currentPage * itemsPerPage, totalItems);
    
    paginationInfo.textContent = `Showing ${start} to ${end} of ${totalItems} items`;
}

/**
 * Utility function to escape HTML
 */
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/**
 * Initialize pagination controls on page load
 */
function initPaginationOnLoad() {
    // Get pagination info from the page to determine total pages
    const paginationInfo = document.querySelector('.pagination-info');
    if (!paginationInfo) return;
    
    // Extract total items from the pagination info text
    const infoText = paginationInfo.textContent;
    const totalItemsMatch = infoText.match(/of (\d+) items/);
    
    if (totalItemsMatch) {
        const totalItems = parseInt(totalItemsMatch[1]);
        const itemsPerPage = 10; // From PHP configuration
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        
        if (totalPages > 1) {
            updatePaginationControls(currentPaginationPage, totalPages);
        }
    }
}

/**
 * Initialize AJAX pagination
 */
function initAjaxPagination() {
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(event) {
        if (event.state && event.state.page) {
            loadPage(event.state.page, false);
        }
    });
    
    // Replace existing pagination button click handlers
    document.addEventListener('click', function(event) {
        const paginationBtn = event.target.closest('.pagination-btn');
        if (paginationBtn && paginationBtn.getAttribute('onclick')) {
            event.preventDefault();
            event.stopPropagation();
            
            // Extract page number from onclick attribute or data-page
            const onclickAttr = paginationBtn.getAttribute('onclick');
            const dataPage = paginationBtn.getAttribute('data-page');
            
            let page = currentPaginationPage;
            
            if (dataPage) {
                page = parseInt(dataPage);
            } else if (onclickAttr) {
                const match = onclickAttr.match(/loadPage\((\d+)\)/);
                if (match) {
                    page = parseInt(match[1]);
                }
            }
            
            if (page && page !== currentPaginationPage) {
                loadPage(page);
            }
        }
    });
}

/**
 * Opens the stock management modal and configures it for the specified action
 * @param {string} action - Either 'stock-in' or 'stock-out'
 */
function openStockModal(action) {
    // Always re-initialize elements to ensure they're fresh after PJAX navigation
    stockManagementModal = document.getElementById('stockManagementModal');
    modalTitle = document.getElementById('modalTitle');
    stockModalSubtitle = document.getElementById('stockModalSubtitle');
    stockAction = document.getElementById('stockAction');
    confirmStockBtn = document.getElementById('confirmStockBtn');

    if (!stockManagementModal || !modalTitle || !stockModalSubtitle || !stockAction || !confirmStockBtn) {
        console.warn('Stock management modal elements not found after PJAX navigation');
        return;
    }

    // Clear form fields
    const itemNameInput = document.getElementById('modal_item_name');
    const quantityInput = document.getElementById('modal_quantity');
    if (itemNameInput) itemNameInput.value = '';
    if (quantityInput) quantityInput.value = '';

    // Get icon and title text elements
    const stockModalIcon = document.getElementById('stockModalIcon');
    const stockModalTitleText = document.getElementById('stockModalTitleText');

    // Update modal content based on action
    if (action === 'stock-in') {
        if (stockModalIcon) stockModalIcon.setAttribute('data-lucide', 'package-plus');
        if (stockModalTitleText) stockModalTitleText.textContent = 'Stock In Items';
        stockModalSubtitle.textContent = 'Add items to inventory or increase existing quantities.';
        confirmStockBtn.textContent = 'Stock In';
        confirmStockBtn.className = 'btn-primary';
        stockAction.value = 'stock-in'; // Set action for stock in

    } else if (action === 'stock-out') {
        if (stockModalIcon) stockModalIcon.setAttribute('data-lucide', 'package-minus');
        if (stockModalTitleText) stockModalTitleText.textContent = 'Stock Out Items';
        stockModalSubtitle.textContent = 'Remove items or decrease existing inventory quantities.';
        confirmStockBtn.textContent = 'Stock Out';
        confirmStockBtn.className = 'btn-primary-danger';
        stockAction.value = 'stock-out'; // Set action for stock out
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

    // Remove existing event listeners first
    if (window.swsStockEventListeners) {
        window.swsStockEventListeners.forEach(({element, event, handler}) => {
            if (element) {
                element.removeEventListener(event, handler);
            }
        });
    }
    window.swsStockEventListeners = [];

    // Add new event listeners with tracking
    if (stockInBtn) {
        const stockInHandler = function() {
            openStockModal('stock-in');
        };
        stockInBtn.addEventListener('click', stockInHandler);
        window.swsStockEventListeners.push({element: stockInBtn, event: 'click', handler: stockInHandler});
    }

    if (stockOutBtn) {
        const stockOutHandler = function() {
            openStockModal('stock-out');
        };
        stockOutBtn.addEventListener('click', stockOutHandler);
        window.swsStockEventListeners.push({element: stockOutBtn, event: 'click', handler: stockOutHandler});
    }

    // Reset modal element references to ensure they're fresh
    stockManagementModal = null;
    modalTitle = null;
    stockModalSubtitle = null;
    stockAction = null;
    confirmStockBtn = null;
}

// Event handler functions removed - now handled inline for better PJAX compatibility

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
    const filterButton = document.getElementById('inventoryFilterBtn');
    
    // Remove existing search event listeners
    if (window.swsSearchEventListeners) {
        window.swsSearchEventListeners.forEach(({element, event, handler}) => {
            if (element) {
                element.removeEventListener(event, handler);
            }
        });
    }
    window.swsSearchEventListeners = [];
    
    // Add search input event listener with tracking
    if (searchInput) {
        const searchHandler = function() {
            applyFiltersAndSearch();
        };
        searchInput.addEventListener('keyup', searchHandler);
        window.swsSearchEventListeners.push({element: searchInput, event: 'keyup', handler: searchHandler});
    }
    
    // Remove existing filter event listeners
    if (window.swsFilterEventListeners) {
        window.swsFilterEventListeners.forEach(({element, event, handler}) => {
            if (element) {
                element.removeEventListener(event, handler);
            }
        });
    }
    window.swsFilterEventListeners = [];
    
    // Add new filter button event listener
    if (filterButton) {
        const filterHandler = function() {
            toggleInventoryFilter();
        };
        filterButton.addEventListener('click', filterHandler);
        window.swsFilterEventListeners.push({element: filterButton, event: 'click', handler: filterHandler});
    }
    
    // Remove existing global click listeners for dropdowns
    if (window.swsGlobalClickHandler) {
        document.removeEventListener('click', window.swsGlobalClickHandler);
    }
    
    // Add global click handler for closing dropdowns
    window.swsGlobalClickHandler = function(event) {
        if (!event.target.closest('.action-dropdown-btn') && !event.target.closest('.action-dropdown')) {
            document.querySelectorAll('.action-dropdown').forEach(d => d.classList.add('hidden'));
        }
    };
    document.addEventListener('click', window.swsGlobalClickHandler);
}

// Global variable to track current filter value
let currentInventoryFilter = 'all';

/**
 * Toggle inventory filter dropdown
 */
function toggleInventoryFilter() {
    // Remove existing filter if it exists
    const existingFilter = document.getElementById('inventoryFilterDropdown');
    if (existingFilter) {
        existingFilter.remove();
        return;
    }
    
    // Get filter button position
    const filterButton = document.getElementById('inventoryFilterBtn');
    
    // Create filter dropdown
    const filterContainer = document.createElement('div');
    filterContainer.id = 'inventoryFilterDropdown';
    filterContainer.className = 'absolute z-50 bg-white rounded-lg shadow-lg border py-2 min-w-48';
    filterContainer.style.background = 'var(--card-bg)';
    filterContainer.style.border = '1px solid var(--card-border)';
    filterContainer.style.top = '50px';
    filterContainer.style.left = '0px';
    
    filterContainer.innerHTML = `
        <div class="py-1">
            <button onclick="applyInventoryFilter('all')" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 transition-colors ${currentInventoryFilter === 'all' ? 'bg-blue-50 text-blue-700' : ''}" style="color: var(--text-color);" onmouseover="this.style.backgroundColor='var(--close-btn-hover-bg)'" onmouseout="this.style.backgroundColor='${currentInventoryFilter === 'all' ? 'rgb(239 246 255)' : 'transparent'}'">
                All Items
            </button>
            <button onclick="applyInventoryFilter('low-stock')" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 transition-colors ${currentInventoryFilter === 'low-stock' ? 'bg-blue-50 text-blue-700' : ''}" style="color: var(--text-color);" onmouseover="this.style.backgroundColor='var(--close-btn-hover-bg)'" onmouseout="this.style.backgroundColor='${currentInventoryFilter === 'low-stock' ? 'rgb(239 246 255)' : 'transparent'}'">
                Low Stock (&lt;10)
            </button>
            <button onclick="applyInventoryFilter('normal-stock')" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 transition-colors ${currentInventoryFilter === 'normal-stock' ? 'bg-blue-50 text-blue-700' : ''}" style="color: var(--text-color);" onmouseover="this.style.backgroundColor='var(--close-btn-hover-bg)'" onmouseout="this.style.backgroundColor='${currentInventoryFilter === 'normal-stock' ? 'rgb(239 246 255)' : 'transparent'}'">
                Normal Stock (10-100)
            </button>
            <button onclick="applyInventoryFilter('high-stock')" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 transition-colors ${currentInventoryFilter === 'high-stock' ? 'bg-blue-50 text-blue-700' : ''}" style="color: var(--text-color);" onmouseover="this.style.backgroundColor='var(--close-btn-hover-bg)'" onmouseout="this.style.backgroundColor='${currentInventoryFilter === 'high-stock' ? 'rgb(239 246 255)' : 'transparent'}'">
                High Stock (>100)
            </button>
        </div>
    `;
    
    // Position relative to the filter button
    filterButton.parentElement.style.position = 'relative';
    filterButton.parentElement.appendChild(filterContainer);
    
    // Close filter when clicking outside
    setTimeout(() => {
        document.addEventListener('click', function closeFilter(e) {
            if (!filterContainer.contains(e.target) && e.target.id !== 'inventoryFilterBtn') {
                filterContainer.remove();
                document.removeEventListener('click', closeFilter);
            }
        });
    }, 100);
}

/**
 * Apply inventory filter
 */
function applyInventoryFilter(filterValue) {
    currentInventoryFilter = filterValue;
    applyFiltersAndSearch();
    
    // Close the filter dropdown
    const filterDropdown = document.getElementById('inventoryFilterDropdown');
    if (filterDropdown) {
        filterDropdown.remove();
    }
}

/**
 * Apply both search and filter criteria to the inventory table
 */
function applyFiltersAndSearch() {
    const searchInput = document.getElementById('inventorySearchInput');
    const tableBody = document.getElementById('inventoryTableBody');
    
    if (!searchInput || !tableBody) return;
    
    const searchTerm = searchInput.value.toLowerCase();
    const filterValue = currentInventoryFilter;
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
    
    // Reset filter state on page initialization
    currentInventoryFilter = 'all';
    
    initStockManagement();
    initInventorySearch();
    initAjaxPagination(); // Initialize AJAX pagination
    initPaginationOnLoad(); // Initialize pagination controls on initial load
    
    // Make filter functions globally available for onclick handlers
    window.applyInventoryFilter = applyInventoryFilter;
    window.toggleInventoryFilter = toggleInventoryFilter;
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


// --- Price Forecasting Functions ---

async function getPriceForecast(itemName) {
    const modal = document.getElementById('priceForecastModal');
    const title = document.getElementById('forecastModalTitle');
    const container = document.getElementById('forecastResultContainer');

    if (!modal || !title || !container) {
        console.error('Price forecast modal elements not found.');
        return;
    }

    // Set title and show loading state
    title.textContent = `Price Forecast for "${itemName}"`;
    container.innerHTML = '<div class="flex items-center justify-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i><p class="ml-3 text-gray-500">Generating AI forecast...</p></div>';
    
    if (window.openModal) {
        window.openModal(modal);
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    try {
        const response = await fetch(`../includes/ajax/get_price_forecast.php?item_name=${encodeURIComponent(itemName)}`);
        const result = await response.json();

        if (result.success) {
            // Format the response with clear sections
            const formattedForecast = result.forecast
                .replace(/Recommendation:/g, '<strong class="block mt-4 text-blue-600">Recommendation:</strong>');

            container.innerHTML = `<div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">${formattedForecast.replace(/\n/g, '<br>')}</div>`;
        } else {
            container.innerHTML = `<div class="text-red-500 text-center py-8"><p class="font-bold">Error:</p><p>${result.error}</p></div>`;
        }
    } catch (error) {
        container.innerHTML = '<div class="text-red-500 text-center py-8"><p class="font-bold">An error occurred while fetching the forecast.</p></div>';
        console.error('Forecast error:', error);
    }
}