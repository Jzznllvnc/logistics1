// Logistic1/assets/js/dashboards.js
// Administrator Dashboard JavaScript functionality

// Global variables for dashboard state
window.dashboardCurrentAssetIndex = 0;
window.dashboardEventListeners = [];
window.dashboardStockAlertsListeners = [];
window.dashboardKeyboardHandler = null;
window.purchaseOrdersChart = null;
// Chart now shows This Month only

// Dashboard data will be set by the PHP page
window.dashboardData = window.dashboardData || {
  totalAssets: 0,
  stockData: [],
  topStockedItems: [],
  recentStockMovements: []
};

// Comprehensive Inventory Export Function - Make globally accessible
window.exportInventoryDataCSV = function exportInventoryDataCSV() {
  const stockData = window.dashboardData.stockData || [];
  const topStockedItems = window.dashboardData.topStockedItems || [];
  const recentMovements = window.dashboardData.recentStockMovements || [];
  
  let csvContent = '';
  
  // Stock Alerts Section
  csvContent += 'STOCK ALERTS\n';
  csvContent += '"Item Name","Current Stock","Status","Notes"\n';
  
  if (stockData.length === 0) {
    csvContent += '"No low stock items","","All items well stocked",""\n';
  } else {
    stockData.forEach(function(item) {
      const row = [
        `"${item.item_name}"`,
        `"${item.quantity}"`,
        `"Critical Low"`,
        `"Requires restocking"`
      ].join(',');
      csvContent += row + '\n';
    });
  }
  
  csvContent += '\n'; // Empty line separator
  
  // Well-Stocked Items Section
  csvContent += 'WELL-STOCKED ITEMS\n';
  csvContent += '"Rank","Item Name","Current Stock","Last Updated"\n';
  
  if (topStockedItems.length === 0) {
    csvContent += '"","No data available","",""\n';
  } else {
    topStockedItems.forEach(function(item, index) {
      const lastUpdated = new Date(item.last_updated).toLocaleDateString();
      const row = [
        `"${index + 1}"`,
        `"${item.item_name}"`,
        `"${item.quantity}"`,
        `"${lastUpdated}"`
      ].join(',');
      csvContent += row + '\n';
    });
  }
  
  csvContent += '\n'; // Empty line separator
  
  // Recent Stock Movements Section
  csvContent += 'RECENT STOCK MOVEMENTS (Last 7 Days)\n';
  csvContent += '"Item Name","Change Amount","Current Stock","Movement Date","Movement Type"\n';
  
  if (recentMovements.length === 0) {
    csvContent += '"No recent movements","","","",""\n';
  } else {
    recentMovements.forEach(function(movement) {
      const movementDate = new Date(movement.timestamp).toLocaleDateString();
      const movementType = movement.change_amount > 0 ? 'Stock In' : 'Stock Out';
      const changeAmount = movement.change_amount > 0 ? `+${movement.change_amount}` : movement.change_amount;
      
      const row = [
        `"${movement.item_name}"`,
        `"${changeAmount}"`,
        `"${movement.current_quantity}"`,
        `"${movementDate}"`,
        `"${movementType}"`
      ].join(',');
      csvContent += row + '\n';
    });
  }
  
  // Export summary at the end
  csvContent += '\n';
  csvContent += 'EXPORT SUMMARY\n';
  csvContent += `"Generated on","${new Date().toLocaleDateString()}","${new Date().toLocaleTimeString()}",""\n`;
  csvContent += `"Stock Alerts Count","${stockData.length}","",""\n`;
  csvContent += `"Well-Stocked Items Count","${topStockedItems.length}","",""\n`;
  csvContent += `"Recent Movements Count","${recentMovements.length}","",""\n`;
  
  // Create and download the file
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', `inventory_management_report_${new Date().toISOString().split('T')[0]}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

// Initialize Inventory Tabs - Make globally accessible
window.initInventoryTabs = function() {
  const tabs = document.querySelectorAll('.inventory-tab');
  const contents = document.querySelectorAll('.inventory-tab-content');
  
  if (tabs.length === 0 || contents.length === 0) {
    return; // Exit if no tabs found
  }
  
  // Remove existing event listeners
  tabs.forEach(tab => {
    const newTab = tab.cloneNode(true);
    tab.parentNode.replaceChild(newTab, tab);
  });
  
  // Add click handlers
  document.querySelectorAll('.inventory-tab').forEach(tab => {
    tab.addEventListener('click', function() {
      const tabId = this.id.replace('tab-', '');
      
      // Remove active classes from all tabs and contents
      document.querySelectorAll('.inventory-tab').forEach(t => {
        t.classList.remove('active');
      });
      
      document.querySelectorAll('.inventory-tab-content').forEach(c => {
        c.classList.remove('active');
        c.classList.add('hidden');
      });
      
      // Add active class to clicked tab and corresponding content
      this.classList.add('active');
      
      const targetContent = document.getElementById(`content-${tabId}`);
      if (targetContent) {
        targetContent.classList.add('active');
        targetContent.classList.remove('hidden');
      }
    });
  });
}

// Purchase Orders Chart Functions
window.initPurchaseOrdersChart = function() {
  const chartContainer = document.getElementById('purchaseOrdersChart');
  if (!chartContainer) return;
  
  // Initialize filter functionality with a small delay to ensure DOM is ready
  setTimeout(() => {
    window.initChartFilter();
  }, 50);
  
  // Load chart with This Month data only
  window.loadPurchaseOrdersData('This Month');
}

// Initialize Chart Filter Functionality
window.initChartFilter = function() {
  const filterButton = document.getElementById('chartFilterButton');
  const dropdown = document.getElementById('chartFilterDropdown');
  const filterOptions = document.querySelectorAll('.chart-filter-option');
  const currentFilterText = document.getElementById('currentFilterText');
  
  // Add debugging to help identify PJAX issues
  console.log('Initializing chart filter. Elements found:', {
    filterButton: !!filterButton,
    dropdown: !!dropdown,
    filterOptions: filterOptions.length,
    currentFilterText: !!currentFilterText
  });
  
  if (!filterButton || !dropdown) {
    console.log('Chart filter elements not found, retrying in 100ms...');
    setTimeout(() => {
      window.initChartFilter();
    }, 100);
    return;
  }
  
  // Clean up existing filter event listeners first
  if (window.chartFilterListeners) {
    window.chartFilterListeners.forEach(({element, event, handler}) => {
      if (element) {
        element.removeEventListener(event, handler);
      }
    });
  }
  window.chartFilterListeners = [];
  
  // Set default active filter (This Month)
  function setDefaultActiveFilter() {
    filterOptions.forEach(option => {
      const optionFilter = option.getAttribute('data-filter');
      if (optionFilter === 'This Month') {
        option.classList.add('active');
      } else {
        option.classList.remove('active');
      }
    });
  }
  
  // Toggle dropdown visibility
  function toggleDropdown(event) {
    event.stopPropagation();
    dropdown.classList.toggle('hidden');
  }
  
  // Close dropdown when clicking outside
  function closeDropdown(event) {
    if (!filterButton.contains(event.target) && !dropdown.contains(event.target)) {
      dropdown.classList.add('hidden');
    }
  }
  
  // Handle filter selection
  function selectFilter(event) {
    event.stopPropagation();
    const selectedFilter = event.target.closest('.chart-filter-option').getAttribute('data-filter');
    
    if (selectedFilter) {
      // Update button text
      if (currentFilterText) {
        currentFilterText.textContent = selectedFilter;
      }
      
      // Update active state in dropdown
      filterOptions.forEach(option => {
        const optionFilter = option.getAttribute('data-filter');
        if (optionFilter === selectedFilter) {
          option.classList.add('active');
        } else {
          option.classList.remove('active');
        }
      });
      
      // Load new chart data
      window.loadPurchaseOrdersData(selectedFilter);
      
      // Close dropdown
      dropdown.classList.add('hidden');
    }
  }
  
  // Add event listeners with cleanup tracking
  function addFilterEventListener(element, event, handler) {
    if (element) {
      element.addEventListener(event, handler);
      window.chartFilterListeners.push({element, event, handler});
    }
  }
  
  // Add event listeners
  addFilterEventListener(filterButton, 'click', toggleDropdown);
  addFilterEventListener(document, 'click', closeDropdown);
  
  // Add listeners to filter options
  filterOptions.forEach(option => {
    addFilterEventListener(option, 'click', selectFilter);
  });
  
  // Set the default active state
  setDefaultActiveFilter();
}

// Function to update chart theme
window.updateChartTheme = function() {
  if (window.purchaseOrdersChart) {
    const isDarkMode = document.documentElement.classList.contains('dark-mode');
    
    // Update tooltip theme with custom styling
    window.purchaseOrdersChart.updateOptions({
      tooltip: {
        theme: isDarkMode ? 'dark' : 'light',
        style: {
          fontSize: '12px',
          fontFamily: 'Inter, ui-sans-serif, system-ui'
        },
        custom: function({series, seriesIndex, dataPointIndex, w}) {
          const isDark = document.documentElement.classList.contains('dark-mode');
          const bgColor = isDark ? '#2d2d2d' : '#ffffff';
          const textColor = isDark ? '#f5f5f5' : '#333333';
          const borderColor = isDark ? '#404040' : '#e0e0e0';
          
          const purchaseOrders = series[0][dataPointIndex];
          const inventoryMovements = series[1][dataPointIndex];
          

          
          return `
            <div style="
              background: ${bgColor}; 
              color: ${textColor}; 
              padding: 8px 12px; 
              border-radius: 6px; 
              border: 1px solid ${borderColor}; 
              box-shadow: 0 4px 12px rgba(0, 0, 0, ${isDark ? '0.4' : '0.15'});
              font-family: Inter, ui-sans-serif, system-ui;
              font-size: 12px;
              position: relative;
            ">
              <div style="margin-bottom: 4px;">
                <span style="color: #3B82F6; font-weight: 500;">●</span> Purchase Orders: <strong>${purchaseOrders} purchase orders</strong>
              </div>
              <div>
                <span style="color: #10B981; font-weight: 500;">●</span> SWS Inventory Items: <strong>${inventoryMovements} inventory movements</strong>
              </div>
            </div>
          `;
        }
      }
    });
  }
};

window.loadPurchaseOrdersData = function(period) {
  const chartContainer = document.getElementById('purchaseOrdersChart');
  const loadingDiv = document.getElementById('chartLoading');
  const noDataDiv = document.getElementById('chartNoData');
  
  if (!chartContainer) return;
  
  // Show loading state
  if (loadingDiv) loadingDiv.classList.remove('hidden');
  if (noDataDiv) noDataDiv.classList.add('hidden');
  chartContainer.innerHTML = '';
  
  // Update period in global variable
  // Period is now always 'This Month'
  
  // Fetch data from server
  fetch(`../includes/ajax/get_purchase_orders_chart.php?filter=${encodeURIComponent(period)}`)
    .then(response => response.json())
    .then(data => {
      // Hide loading state
      if (loadingDiv) loadingDiv.classList.add('hidden');
      
      if (data.success && data.data && data.data.length > 0) {
        // Show chart container
        if (noDataDiv) noDataDiv.classList.add('hidden');
        
        // Prepare chart data
        const categories = data.data.map((item, index) => {
          if (period === 'This Year') {
            // For "This Year", the date field already contains month abbreviations (Jan, Feb, etc.)
            return item.date;
          } else {
            // For Week/Month, process dates normally
            const date = new Date(item.date);
            
            // For "This Month", show only every 3rd day to reduce crowding
            if (period === 'This Month' && data.data.length > 10) {
              if (index % 3 !== 0 && index !== data.data.length - 1) {
                return ''; // Return empty string for days we don't want to show
              }
            }
            
            return date.toLocaleDateString('en-US', { 
              month: 'short', 
              day: 'numeric'
            });
          }
        });
        
        const purchaseOrdersData = data.data.map(item => item.purchase_orders);
        const inventoryMovementsData = data.data.map(item => item.inventory_movements);
        
        // Create chart options
        const options = {
          chart: {
            height: 420,
            type: 'area',
            fontFamily: 'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"',
            toolbar: {
              show: false
            },
            zoom: {
              enabled: false
            }
          },
          series: [
            {
              name: 'Purchase Orders',
              data: purchaseOrdersData,
              color: '#3B82F6'
            },
            {
              name: 'SWS Inventory Items',
              data: inventoryMovementsData,
              color: '#10B981'
            }
          ],
          xaxis: {
            categories: categories,
            labels: {
              style: {
                colors: 'var(--subtitle-color)',
                fontSize: '12px'
              },
              hideOverlappingLabels: true,
              rotate: 0,
              rotateAlways: false,
              offsetY: 12
            },
            axisBorder: {
              show: false
            },
            axisTicks: {
              show: false
            }
          },
          yaxis: {
            labels: {
              style: {
                colors: 'var(--subtitle-color)',
                fontSize: '12px'
              },
              formatter: function (value) {
                return Math.floor(value);
              }
            }
          },
          stroke: {
            curve: 'smooth',
            width: 3
          },
          fill: {
            type: 'gradient',
            gradient: {
              shadeIntensity: 1,
              opacityFrom: 0.6,
              opacityTo: 0.15,
              stops: [0, 85, 100]
            }
          },
          dataLabels: {
            enabled: false
          },
          grid: {
            borderColor: 'var(--card-border)',
            strokeDashArray: 0,
            xaxis: {
              lines: {
                show: false
              }
            },
            yaxis: {
              lines: {
                show: false
              }
            },
            padding: {
              top: 20,
              right: 30,
              bottom: 40,
              left: 20
            }
          },
          legend: {
            position: 'top',
            horizontalAlign: 'center',
            fontWeight: 500,
            fontSize: '12px',
            labels: {
              colors: 'var(--subtitle-color)',
              useSeriesColors: false
            },
            markers: {
              width: 8,
              height: 8,
              radius: 2,
              offsetX: -2,
              offsetY: 0
            },
            itemMargin: {
              horizontal: 12,
              vertical: 5
            },
            offsetY: 0
          },
          tooltip: {
            theme: document.documentElement.classList.contains('dark-mode') ? 'dark' : 'light',
            style: {
              fontSize: '12px',
              fontFamily: 'Inter, ui-sans-serif, system-ui'
            },
            custom: function({series, seriesIndex, dataPointIndex, w}) {
              const isDark = document.documentElement.classList.contains('dark-mode');
              const bgColor = isDark ? '#2d2d2d' : '#ffffff';
              const textColor = isDark ? '#f5f5f5' : '#333333';
              const borderColor = isDark ? '#404040' : '#e0e0e0';
              
              const purchaseOrders = series[0][dataPointIndex];
              const inventoryMovements = series[1][dataPointIndex];
              

              
              return `
                <div style="
                  background: ${bgColor}; 
                  color: ${textColor}; 
                  padding: 8px 12px; 
                  border-radius: 6px; 
                  border: 1px solid ${borderColor}; 
                  box-shadow: 0 4px 12px rgba(0, 0, 0, ${isDark ? '0.4' : '0.15'});
                  font-family: Inter, ui-sans-serif, system-ui;
                  font-size: 12px;
                  position: relative;
                ">
                  <div style="margin-bottom: 4px;">
                    <span style="color: #3B82F6; font-weight: 500;">●</span> Purchase Orders: <strong>${purchaseOrders} purchase orders</strong>
                  </div>
                  <div>
                    <span style="color: #10B981; font-weight: 500;">●</span> SWS Inventory Items: <strong>${inventoryMovements} inventory movements</strong>
                  </div>
                </div>
              `;
            }
          }
        };
        
        // Destroy existing chart if it exists
        if (window.purchaseOrdersChart) {
          window.purchaseOrdersChart.destroy();
        }
        
        // Create new chart
        window.purchaseOrdersChart = new ApexCharts(chartContainer, options);
        window.purchaseOrdersChart.render();
        
      } else {
        // Show no data state
        if (noDataDiv) noDataDiv.classList.remove('hidden');
      }
    })
    .catch(error => {
      console.error('Error loading purchase orders data:', error);
      
      // Hide loading state and show no data
      if (loadingDiv) loadingDiv.classList.add('hidden');
      if (noDataDiv) noDataDiv.classList.remove('hidden');
    });
}

// Chart loads This Month data automatically

// Initialize dashboard functionality
window.initDashboard = function() {
  
  // Initialize inventory tabs
  window.initInventoryTabs();
  
  // Initialize purchase orders chart
  window.initPurchaseOrdersChart();
  
  // Add theme change listener for chart updates
  if (!window.chartThemeListenerAdded) {
    const observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
          // Theme changed, update chart
          setTimeout(() => {
            window.updateChartTheme();
          }, 100); // Small delay to ensure theme change is complete
        }
      });
    });
    
    observer.observe(document.documentElement, {
      attributes: true,
      attributeFilter: ['class']
    });
    
    window.chartThemeListenerAdded = true;
  }
  
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
  
  // Inventory Export functionality
  const exportButton = document.getElementById('exportInventoryData');
  
  // Remove any existing event listeners first
  if (window.dashboardStockAlertsListeners) {
    window.dashboardStockAlertsListeners.forEach(({element, event, handler}) => {
      if (element) {
        element.removeEventListener(event, handler);
      }
    });
  }
  window.dashboardStockAlertsListeners = [];
  
  // Add new event listener for export
  if (exportButton) {
    const exportHandler = function() {
      window.exportInventoryDataCSV();
    };
    exportButton.addEventListener('click', exportHandler);
    window.dashboardStockAlertsListeners.push({element: exportButton, event: 'click', handler: exportHandler});
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

// Initialize dashboard functionality with proper PJAX support
function initDashboardWithCleanup() {
  // Clean up any existing chart instances first
  if (window.currentChart) {
    window.currentChart.destroy();
    window.currentChart = null;
  }
  
  if (window.purchaseOrdersChart) {
    window.purchaseOrdersChart.destroy();
    window.purchaseOrdersChart = null;
  }
  
  // Clean up chart filter event listeners
  if (window.chartFilterListeners) {
    window.chartFilterListeners.forEach(({element, event, handler}) => {
      if (element) {
        element.removeEventListener(event, handler);
      }
    });
    window.chartFilterListeners = [];
  }
  
  // Clean up any legacy dropdown elements
  const existingPeriodDropdown = document.getElementById('periodFilterDropdown');
  if (existingPeriodDropdown) {
    existingPeriodDropdown.remove();
  }
  
  // Initialize dashboard
  window.initDashboard();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initDashboardWithCleanup);

// Also initialize immediately if DOM is already ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initDashboardWithCleanup);
} else {
  initDashboardWithCleanup();
}

// Re-initialize on PJAX navigation (when content is replaced)
// This ensures dashboard works after navigating back via PJAX
if (typeof window.MutationObserver !== 'undefined') {
  const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      if (mutation.type === 'childList') {
        // Check if dashboard elements were added
        const addedNodes = Array.from(mutation.addedNodes);
        const hasChartContainer = false; // No chart containers to watch for
        
        if (hasChartContainer) {
          // Dashboard content was added, reinitialize
          setTimeout(initDashboardWithCleanup, 100);
        }
      }
    });
  });
  
  observer.observe(document.body, {
    childList: true,
    subtree: true
  });
}
