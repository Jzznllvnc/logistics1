// Logistic1/assets/js/dashboards.js
// Administrator Dashboard JavaScript functionality

// Global variables for dashboard state
window.dashboardCurrentAssetIndex = 0;
window.dashboardEventListeners = [];
window.dashboardStockAlertsListeners = [];
window.dashboardKeyboardHandler = null;

// Dashboard data will be set by the PHP page
window.dashboardData = window.dashboardData || {
  totalAssets: 0,
  stockData: []
};

// Stock Alerts Export Function - Make globally accessible
window.exportStockAlertsCSV = function exportStockAlertsCSV() {
  const stockData = window.dashboardData.stockData || [];
  
  let csvContent = '"Item Name","Current Stock","Status"\n';
  stockData.forEach(function(item) {
    const row = [
      `"${item.item_name}"`,
      `"${item.quantity}"`,
      `"Critical Low"`
    ].join(',');
    csvContent += row + '\n';
  });
  
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', `stock_alerts_${new Date().toISOString().split('T')[0]}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

// Stock Alerts Filter Function - Make globally accessible
window.toggleStockAlertsFilter = function toggleStockAlertsFilter() {
  // Remove existing filter if it exists
  const existingFilter = document.getElementById('stockAlertsFilter');
  if (existingFilter) {
    existingFilter.remove();
    return;
  }
  
  // Get filter button position
  const filterButton = document.getElementById('filterStockAlerts');
  
  // Create filter dropdown
  const filterContainer = document.createElement('div');
  filterContainer.id = 'stockAlertsFilter';
  filterContainer.className = 'absolute z-50 bg-white rounded-lg shadow-lg border py-2 min-w-48';
  filterContainer.style.background = 'var(--card-bg)';
  filterContainer.style.border = '1px solid var(--card-border)';
  filterContainer.style.top = '50px';
  filterContainer.style.right = '0px';
  
  filterContainer.innerHTML = `
    <div class="py-1">
              <button onclick="window.applyStockFilter('all')" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 transition-colors" style="color: var(--text-color);" onmouseover="this.style.backgroundColor='var(--close-btn-hover-bg)'" onmouseout="this.style.backgroundColor='transparent'">
          All Items
        </button>
        <button onclick="window.applyStockFilter('critical')" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 transition-colors" style="color: var(--text-color);" onmouseover="this.style.backgroundColor='var(--close-btn-hover-bg)'" onmouseout="this.style.backgroundColor='transparent'">
          Critical Low (0-25)
        </button>
        <button onclick="window.applyStockFilter('low')" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 transition-colors" style="color: var(--text-color);" onmouseover="this.style.backgroundColor='var(--close-btn-hover-bg)'" onmouseout="this.style.backgroundColor='transparent'">
          Low (26-50)
        </button>
        <button onclick="window.applyStockFilter('moderate')" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 transition-colors" style="color: var(--text-color);" onmouseover="this.style.backgroundColor='var(--close-btn-hover-bg)'" onmouseout="this.style.backgroundColor='transparent'">
          Moderate (51-100)
        </button>
    </div>
  `;
  
  // Position relative to the filter button
  filterButton.parentElement.style.position = 'relative';
  filterButton.parentElement.appendChild(filterContainer);
  
  // Close filter when clicking outside
  setTimeout(() => {
    document.addEventListener('click', function closeFilter(e) {
      if (!filterContainer.contains(e.target) && e.target.id !== 'filterStockAlerts') {
        filterContainer.remove();
        document.removeEventListener('click', closeFilter);
      }
    });
  }, 100);
}

// Apply Stock Filter - Make globally accessible for onclick handlers
window.applyStockFilter = function applyStockFilter(filterValue) {
  const tableRows = document.querySelectorAll('.data-table tbody tr');
  
  tableRows.forEach(row => {
    if (row.querySelector('.table-empty')) return; // Skip empty state row
    
    const stockCell = row.children[1];
    const stockValue = parseInt(stockCell.textContent.trim());
    
    let showRow = true;
    
    switch(filterValue) {
      case 'critical':
        showRow = stockValue <= 25;
        break;
      case 'low':
        showRow = stockValue >= 26 && stockValue <= 50;
        break;
      case 'moderate':
        showRow = stockValue >= 51 && stockValue <= 100;
        break;
      default:
        showRow = true;
    }
    
    row.style.display = showRow ? '' : 'none';
  });
  
  // Close the filter dropdown
  const filterDropdown = document.getElementById('stockAlertsFilter');
  if (filterDropdown) {
    filterDropdown.remove();
  }
}

// Initialize dashboard functionality
window.initDashboard = function() {
  // Asset Pagination Functionality
  const assetsContainer = document.getElementById('assetsContainer');
  const prevButton = document.getElementById('prevAsset');
  const nextButton = document.getElementById('nextAsset');
  const assetDots = document.querySelectorAll('.asset-dot');
  
  if (!assetsContainer) {
    return; // Exit if no assets container
  }
  
  const totalAssets = window.dashboardData.totalAssets || 0;
  
  // Initialize or reset the current asset index globally
  if (typeof window.dashboardCurrentAssetIndex === 'undefined') {
    window.dashboardCurrentAssetIndex = 0;
  }
  
  // Remove existing event listeners by storing references
  if (window.dashboardEventListeners) {
    window.dashboardEventListeners.forEach(({element, event, handler}) => {
      if (element) {
        element.removeEventListener(event, handler);
      }
    });
  }
  window.dashboardEventListeners = [];
  
  // Debounce function to prevent rapid clicks
  let isNavigating = false;
  
  function updateAssetDisplay() {
    // Ensure index is within bounds
    if (window.dashboardCurrentAssetIndex < 0) {
      window.dashboardCurrentAssetIndex = 0;
    }
    if (window.dashboardCurrentAssetIndex >= totalAssets) {
      window.dashboardCurrentAssetIndex = totalAssets - 1;
    }
    
    // Update container transform
    const translateX = -window.dashboardCurrentAssetIndex * 100;
    assetsContainer.style.transform = `translateX(${translateX}%)`;
    
    // Update dots
    document.querySelectorAll('.asset-dot').forEach((dot, index) => {
      if (index === window.dashboardCurrentAssetIndex) {
        dot.classList.remove('bg-gray-300');
        dot.classList.add('bg-blue-500');
      } else {
        dot.classList.remove('bg-blue-500');
        dot.classList.add('bg-gray-300');
      }
    });
  }
  
  function nextAsset() {
    if (isNavigating) return; // Prevent rapid clicks
    isNavigating = true;
    
    // Absolute next: increment by exactly 1, wrap around if needed
    window.dashboardCurrentAssetIndex = window.dashboardCurrentAssetIndex + 1;
    if (window.dashboardCurrentAssetIndex >= totalAssets) {
      window.dashboardCurrentAssetIndex = 0; // Wrap to first
    }
    
    updateAssetDisplay();
    
    setTimeout(() => { isNavigating = false; }, 300); // Reset debounce
  }
  
  function prevAsset() {
    if (isNavigating) return; // Prevent rapid clicks
    isNavigating = true;
    
    // Absolute previous: decrement by exactly 1, wrap around if needed
    window.dashboardCurrentAssetIndex = window.dashboardCurrentAssetIndex - 1;
    if (window.dashboardCurrentAssetIndex < 0) {
      window.dashboardCurrentAssetIndex = totalAssets - 1; // Wrap to last
    }
    
    updateAssetDisplay();
    
    setTimeout(() => { isNavigating = false; }, 300); // Reset debounce
  }
  
  function goToAsset(index) {
    if (isNavigating) return; // Prevent rapid clicks
    isNavigating = true;
    
    // Absolute positioning: set exact index
    if (index >= 0 && index < totalAssets) {
      window.dashboardCurrentAssetIndex = index;
      updateAssetDisplay();
    }
    
    setTimeout(() => { isNavigating = false; }, 300); // Reset debounce
  }
  
  // Store event listeners for cleanup
  function addEventListenerWithCleanup(element, event, handler) {
    if (element) {
      element.addEventListener(event, handler);
      window.dashboardEventListeners.push({element, event, handler});
    }
  }
  
  // Event listeners for buttons
  if (nextButton) {
    addEventListenerWithCleanup(nextButton, 'click', nextAsset);
  }
  
  if (prevButton) {
    addEventListenerWithCleanup(prevButton, 'click', prevAsset);
  }
  
  // Prevent arrow-up-right icons from triggering pagination
  const assetRegistryLink = document.querySelector('a[href*="asset_lifecycle_maintenance.php"]');
  if (assetRegistryLink) {
    addEventListenerWithCleanup(assetRegistryLink, 'click', function(e) {
      e.stopPropagation();
      // Let the default navigation happen, just stop event bubbling
    });
  }
  
  const procurementLink = document.querySelector('a[href*="procurement_sourcing.php"]');
  if (procurementLink) {
    addEventListenerWithCleanup(procurementLink, 'click', function(e) {
      e.stopPropagation();
      // Let the default navigation happen, just stop event bubbling
    });
  }
  
  // Stock Alerts Export and Filter functionality
  const exportButton = document.getElementById('exportStockAlerts');
  const filterButton = document.getElementById('filterStockAlerts');
  
  // Remove any existing event listeners first
  if (window.dashboardStockAlertsListeners) {
    window.dashboardStockAlertsListeners.forEach(({element, event, handler}) => {
      if (element) {
        element.removeEventListener(event, handler);
      }
    });
  }
  window.dashboardStockAlertsListeners = [];
  
  // Add new event listeners
  if (exportButton) {
    const exportHandler = function() {
      window.exportStockAlertsCSV();
    };
    exportButton.addEventListener('click', exportHandler);
    window.dashboardStockAlertsListeners.push({element: exportButton, event: 'click', handler: exportHandler});
  }
  
  if (filterButton) {
    const filterHandler = function() {
      window.toggleStockAlertsFilter();
    };
    filterButton.addEventListener('click', filterHandler);
    window.dashboardStockAlertsListeners.push({element: filterButton, event: 'click', handler: filterHandler});
  }
  
  // Dot navigation
  assetDots.forEach((dot, index) => {
    addEventListenerWithCleanup(dot, 'click', () => goToAsset(index));
  });
  
  // Keyboard navigation
  if (window.dashboardKeyboardHandler) {
    document.removeEventListener('keydown', window.dashboardKeyboardHandler);
  }
  
  window.dashboardKeyboardHandler = function(e) {
    if (e.target.closest('.assets-carousel') || e.target.closest('#assetsContainer')) {
      if (e.key === 'ArrowLeft') {
        e.preventDefault();
        prevAsset();
      } else if (e.key === 'ArrowRight') {
        e.preventDefault();
        nextAsset();
      }
    }
  };
  document.addEventListener('keydown', window.dashboardKeyboardHandler);
  
  // Touch/swipe support
  let startX = 0;
  let currentX = 0;
  let isDragging = false;
  
  const assetCard = assetsContainer.closest('.rounded-xl');
  if (assetCard) {
    // Touch events
    const touchStartHandler = function(e) {
      // Don't start dragging if clicking on links or buttons
      if (e.target.closest('a') || e.target.closest('button')) {
        return;
      }
      startX = e.touches[0].clientX;
      isDragging = true;
    };
    
    const touchMoveHandler = function(e) {
      if (!isDragging) return;
      currentX = e.touches[0].clientX;
    };
    
    const touchEndHandler = function(e) {
      if (!isDragging) return;
      isDragging = false;
      
      const diffX = startX - currentX;
      const threshold = 50;
      
      if (Math.abs(diffX) > threshold) {
        if (diffX > 0) {
          nextAsset();
        } else {
          prevAsset();
        }
      }
    };
    
    // Mouse events
    const mouseDownHandler = function(e) {
      // Don't start dragging if clicking on links or buttons
      if (e.target.closest('a') || e.target.closest('button')) {
        return;
      }
      startX = e.clientX;
      isDragging = true;
      assetCard.style.cursor = 'grabbing';
    };
    
    const mouseMoveHandler = function(e) {
      if (!isDragging) return;
      currentX = e.clientX;
    };
    
    const mouseUpHandler = function(e) {
      if (!isDragging) return;
      isDragging = false;
      assetCard.style.cursor = '';
      
      const diffX = startX - currentX;
      const threshold = 50;
      
      if (Math.abs(diffX) > threshold) {
        if (diffX > 0) {
          nextAsset();
        } else {
          prevAsset();
        }
      }
    };
    
    const mouseLeaveHandler = function() {
      isDragging = false;
      assetCard.style.cursor = '';
    };
    
    // Add all touch and mouse event listeners
    addEventListenerWithCleanup(assetCard, 'touchstart', touchStartHandler);
    addEventListenerWithCleanup(assetCard, 'touchmove', touchMoveHandler);
    addEventListenerWithCleanup(assetCard, 'touchend', touchEndHandler);
    addEventListenerWithCleanup(assetCard, 'mousedown', mouseDownHandler);
    addEventListenerWithCleanup(assetCard, 'mousemove', mouseMoveHandler);
    addEventListenerWithCleanup(assetCard, 'mouseup', mouseUpHandler);
    addEventListenerWithCleanup(assetCard, 'mouseleave', mouseLeaveHandler);
  }
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', window.initDashboard);

// Also initialize immediately if DOM is already ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', window.initDashboard);
} else {
  window.initDashboard();
}
