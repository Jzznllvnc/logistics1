<?php
require_once '../includes/functions/auth.php';
requireLogin();

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
}

// Include additional functions for admin dashboard data
if ($_SESSION['role'] === 'admin') {
    require_once '../includes/functions/inventory.php';
    require_once '../includes/functions/asset.php';
    require_once '../includes/functions/project.php';
    require_once '../includes/functions/purchase_order.php';
    require_once '../includes/functions/supplier.php';
    
    // Fetch dashboard data
    $activeProjects = getActiveProjectsCount();
    $operationalAssets = getOperationalAssetsCount();
    $lowStockCount = getLowStockCount();
    $suppliersCount = getSuppliersCount();
    $deliveryTruck = getDeliveryTruckAsset();
    $allAssets = getAllAssets(); // Get all assets for pagination
    $lowStockItems = getLowStockItems(5);
    $biddingHistory = getRecentBiddingHistory(3); // Get recent bidding history
    
    // Fetch percentage changes
    $activeProjectsChange = getActiveProjectsChange();
    $operationalAssetsChange = getOperationalAssetsChange();
    $lowStockChange = getLowStockChange();
    $suppliersChange = getSuppliersChange();
    

    
    // Fetch additional inventory insights
    $inventoryStats = getInventoryStats();
    $topStockedItems = getTopStockedItems(3);
    $recentStockMovements = getRecentStockMovements(5);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <script>document.documentElement.classList.add('preload', 'loading');</script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logistics 1 - Dashboard</title>
  <link rel="icon" href="../assets/images/slate2.png" type="image/png">
  <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="stylesheet" href="../assets/css/sidebar.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha384-nRgPTkuX86pH8yjPJUAFuASXQSSl2/bBUiNV47vSYpKFxHJhbcrGnmlYpYJMeD7a" crossorigin="anonymous">
  <script src="https://cdn.tailwindcss.com"></script>
  <?php if ($_SESSION['role'] === 'admin'): ?>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
  <?php endif; ?>
</head>
<body>
  <div class="sidebar" id="sidebar">
    <?php include '../partials/sidebar.php'; ?>
  </div>

  <div class="main-content-wrapper" id="mainContentWrapper">
    <div class="content" id="mainContent">
      <script>
        // Apply persisted sidebar state immediately after elements exist
        (function() {
          try {
            const collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            var sidebar = document.getElementById('sidebar');
            var wrapper = document.getElementById('mainContentWrapper');
            if (collapsed && sidebar && wrapper) {
              sidebar.classList.add('initial-collapsed');
              wrapper.classList.add('initial-expanded');
              document.body.classList.remove('sidebar-active');
            } else {
              document.body.classList.add('sidebar-active');
            }
          } catch (e) {}
        })();
      </script>
      <?php include '../partials/header.php'; ?>

      <?php if ($_SESSION['role'] === 'admin'): ?>
        <!-- Admin Dashboard -->
        <h1 class="font-semibold mb-1.5 page-title">System Dashboard</h1>
        <p class="lg:text-lg text-base text-[var(--subtitle-color)] mb-6 page-subtitle">Your overview of system activities, operations, and freight workflows.</p>
        
        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
          <!-- Active Projects Card -->
          <div style="background: var(--card-bg); border: 1px solid var(--card-border);" class="rounded-2xl shadow-md p-6 hover:shadow-lg transition-shadow duration-300 h-40 relative flex flex-col justify-center">
            <div class="absolute top-4 right-4">
              <div style="background: var(--icon-container-bg);" class="p-3.5 rounded-xl">
                <i data-lucide="folder-open" class="w-7 h-7 text-[var(--description-color)]"></i>
              </div>
            </div>
            <div class="pr-20">
              <p style="color: var(--subtitle-color);" class="text-sm font-medium mb-3 whitespace-nowrap">Active Projects</p>
              <p style="color: var(--text-color);" class="text-4xl font-semibold mb-3"><?php echo $activeProjects; ?></p>
              <div class="flex items-end text-sm">
                <span style="color: var(--subtitle-color);" class="flex-grow mr-1 truncate min-w-24">Currently in progress</span>
                <span class="<?php echo $activeProjectsChange['is_positive'] ? 'text-green-600' : 'text-red-600'; ?> font-medium whitespace-nowrap flex-shrink-0">
                  <i data-lucide="<?php echo $activeProjectsChange['is_positive'] ? 'trending-up' : 'trending-down'; ?>" class="w-4 h-4 inline mr-1"></i>
                  <?php echo $activeProjectsChange['is_positive'] ? '+' : '-'; ?><?php echo $activeProjectsChange['percentage']; ?>%
                </span>
              </div>
            </div>
          </div>

          <!-- Operational Assets Card -->
          <div style="background: var(--card-bg); border: 1px solid var(--card-border);" class="rounded-2xl shadow-md p-6 hover:shadow-lg transition-shadow duration-300 h-40 relative flex flex-col justify-center">
            <div class="absolute top-4 right-4">
              <div style="background: var(--icon-container-bg);" class="p-3.5 rounded-xl">
                <i data-lucide="align-end-horizontal" class="w-7 h-7 text-[var(--description-color)]"></i>
              </div>
            </div>
            <div class="pr-20">
              <p style="color: var(--subtitle-color);" class="text-sm font-medium mb-3 whitespace-nowrap">Active Assets</p>
              <p style="color: var(--text-color);" class="text-4xl font-semibold mb-3"><?php echo $operationalAssets; ?></p>
              <div class="flex items-end text-sm">
                <span style="color: var(--subtitle-color);" class="flex-grow mr-1 truncate min-w-24">Operational and ready</span>
                <span class="<?php echo $operationalAssetsChange['is_positive'] ? 'text-green-600' : 'text-red-600'; ?> font-medium whitespace-nowrap flex-shrink-0">
                  <i data-lucide="<?php echo $operationalAssetsChange['is_positive'] ? 'trending-up' : 'trending-down'; ?>" class="w-4 h-4 inline mr-1"></i>
                  <?php echo $operationalAssetsChange['is_positive'] ? '+' : '-'; ?><?php echo $operationalAssetsChange['percentage']; ?>%
                </span>
              </div>
            </div>
          </div>

          <!-- Total Inventory Card -->
          <div style="background: var(--card-bg); border: 1px solid var(--card-border);" class="rounded-2xl shadow-md p-6 hover:shadow-lg transition-shadow duration-300 h-40 relative flex flex-col justify-center">
            <div class="absolute top-4 right-4">
              <div style="background: var(--icon-container-bg);" class="p-3.5 rounded-xl">
                <i data-lucide="package" class="w-7 h-7 text-[var(--description-color)]"></i>
              </div>
            </div>
            <div class="pr-20">
              <p style="color: var(--subtitle-color);" class="text-sm font-medium mb-3 whitespace-nowrap">Total Inventory</p>
              <p style="color: var(--text-color);" class="text-4xl font-semibold mb-3"><?php echo number_format($inventoryStats['total_quantity']); ?></p>
              <div class="flex items-end text-sm">
                <span style="color: var(--subtitle-color);" class="flex-grow mr-1 truncate min-w-24"><?php echo $inventoryStats['total_items']; ?> unique items</span>
                <span class="text-blue-600 font-medium whitespace-nowrap flex-shrink-0">
                  <i data-lucide="package-check" class="w-4 h-4 inline mr-1"></i>
                  <?php echo number_format($inventoryStats['avg_quantity'], 0); ?> avg
                </span>
              </div>
            </div>
          </div>

          <!-- Suppliers Card -->
          <div style="background: var(--card-bg); border: 1px solid var(--card-border);" class="rounded-2xl shadow-md p-6 hover:shadow-lg transition-shadow duration-300 h-40 relative flex flex-col justify-center">
            <div class="absolute top-4 right-4">
              <div style="background: var(--icon-container-bg);" class="p-3.5 rounded-xl">
                <i data-lucide="users" class="w-7 h-7 text-[var(--description-color)]"></i>
              </div>
            </div>
            <div class="pr-20">
              <p style="color: var(--subtitle-color);" class="text-sm font-medium mb-3 whitespace-nowrap">Suppliers</p>
              <p style="color: var(--text-color);" class="text-4xl font-semibold mb-3"><?php echo $suppliersCount; ?></p>
              <div class="flex items-end text-sm">
                <span style="color: var(--subtitle-color);" class="flex-grow mr-1 truncate min-w-24">Approved and active</span>
                <span class="<?php echo $suppliersChange['is_positive'] ? 'text-green-600' : 'text-red-600'; ?> font-medium whitespace-nowrap flex-shrink-0">
                  <i data-lucide="<?php echo $suppliersChange['is_positive'] ? 'trending-up' : 'trending-down'; ?>" class="w-4 h-4 inline mr-1"></i>
                  <?php echo $suppliersChange['is_positive'] ? '+' : '-'; ?><?php echo $suppliersChange['percentage']; ?>%
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Middle Section: Purchase Orders Chart and Asset Vehicles -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
          <!-- Purchase Orders Chart Card -->
          <div style="background: var(--card-bg); border: 1px solid var(--card-border);" class="lg:col-span-2 rounded-2xl shadow-md p-6">
            <div class="mb-4">
              <div class="flex items-start justify-between">
                <div>
                  <h3 style="color: var(--text-color);" class="text-lg font-semibold mb-1">Operations Overview</h3>
                  <p style="color: var(--subtitle-color);" class="text-sm">Track purchase orders and SWS inventory activity</p>
                </div>
                <div class="relative">
                  <button id="chartFilterButton" class="chart-filter-button inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg border transition-colors">
                    <i data-lucide="calendar" class="w-4 h-4 mr-2"></i>
                    <span id="currentFilterText">This Month</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i>
                  </button>
                  
                  <!-- Dropdown Menu -->
                  <div id="chartFilterDropdown" class="absolute right-0 top-full mt-2 w-40 py-2 rounded-lg shadow-lg border z-10 hidden" style="background: var(--card-bg); border-color: var(--card-border);">
                    <button class="chart-filter-option w-full text-left px-4 py-2 text-sm transition-colors" data-filter="This Week">
                      <i data-lucide="calendar-days" class="w-4 h-4 inline mr-2"></i>
                      This Week
                    </button>
                    <button class="chart-filter-option active w-full text-left px-4 py-2 text-sm transition-colors" data-filter="This Month">
                      <i data-lucide="calendar" class="w-4 h-4 inline mr-2"></i>
                      This Month
                    </button>
                    <button class="chart-filter-option w-full text-left px-4 py-2 text-sm transition-colors" data-filter="This Year">
                      <i data-lucide="calendar-range" class="w-4 h-4 inline mr-2"></i>
                      This Year
                    </button>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Chart Container -->
            <div class="h-[450px]">
              <div id="purchaseOrdersChart" class="w-full h-full"></div>
              
              <!-- Loading state -->
              <div id="chartLoading" class="w-full h-full flex items-center justify-center" style="color: var(--subtitle-color);">
                <div class="text-center">
                  <i data-lucide="loader-2" class="w-8 h-8 mx-auto mb-2 animate-spin"></i>
                  <p class="text-sm">Loading chart data...</p>
                </div>
              </div>
              
              <!-- No data state -->
              <div id="chartNoData" class="w-full h-full hidden items-center justify-center" style="color: var(--subtitle-color);">
                <div class="text-center">
                  <i data-lucide="bar-chart-3" class="w-12 h-12 mx-auto mb-3 text-gray-300"></i>
                  <p class="text-sm">No operations data found for this period</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Asset Vehicles Card (Right) -->
          <div style="background: var(--card-bg); border: 1px solid var(--card-border);" class="rounded-2xl shadow-md p-6 relative">
            <div class="mb-4 flex items-start justify-between">
              <div>
                <h3 style="color: var(--text-color);" class="text-lg font-semibold mb-1">Registered Assets</h3>
                <p style="color: var(--subtitle-color);" class="text-sm">Browse fleet vehicles and equipment inventory</p>
              </div>
              <a href="asset_lifecycle_maintenance.php#asset-registry" class="p-2 rounded-lg transition-all duration-200 hover:rounded-full" style="color: var(--subtitle-color);" title="View Asset Registry" onmouseover="this.style.backgroundColor='var(--close-btn-hover-bg)'" onmouseout="this.style.backgroundColor='transparent'">
                <i data-lucide="arrow-up-right" class="w-7 h-7"></i>
              </a>
            </div>
            
            <?php if (!empty($allAssets)): ?>
              <!-- Navigation Buttons -->
              <?php if (count($allAssets) > 1): ?>
                <button id="prevAsset" class="absolute left-2 top-1/2 transform -translate-y-1/2 z-10 p-2 rounded-full transition-all duration-200 ml-2" style="color: var(--subtitle-color);" onmouseover="this.style.backgroundColor='var(--close-btn-hover-bg)'" onmouseout="this.style.backgroundColor='transparent'">
                  <i data-lucide="chevron-left" class="w-5 h-5"></i>
                </button>
                <button id="nextAsset" class="absolute right-2 top-1/2 transform -translate-y-1/2 z-10 p-2 rounded-full transition-all duration-200 mr-2" style="color: var(--subtitle-color);" onmouseover="this.style.backgroundColor='var(--close-btn-hover-bg)'" onmouseout="this.style.backgroundColor='transparent'">
                  <i data-lucide="chevron-right" class="w-5 h-5"></i>
                </button>
              <?php endif; ?>
              
              <!-- Assets Container -->
              <div class="assets-carousel relative overflow-hidden">
                <div id="assetsContainer" class="flex transition-transform duration-300 ease-in-out" style="transform: translateX(0%);">
                  <?php foreach ($allAssets as $index => $asset): ?>
                    <div class="asset-slide flex-shrink-0 w-full text-center h-full flex flex-col">
                <div class="flex items-center justify-center pt-4">
                  <div class="mx-auto w-72 h-56 rounded-lg overflow-hidden">
                          <?php if ($asset['image_path']): ?>
                            <img src="../<?php echo htmlspecialchars($asset['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($asset['asset_name']); ?>" 
                           class="w-full h-full object-contain">
                    <?php else: ?>
                      <div class="flex items-center justify-center h-full">
                        <i data-lucide="truck" class="w-16 h-16 text-gray-400"></i>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
                
                <div class="mt-4 pb-8">
                        <h4 style="color: var(--text-color);" class="font-semibold mb-2"><?php echo htmlspecialchars($asset['asset_name']); ?></h4>
                  
                  <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                      <span style="color: var(--subtitle-color);">Status:</span>
                            <span class="<?php echo $asset['status'] === 'Operational' ? 'text-green-600 font-medium' : 'text-amber-600 font-medium'; ?>">
                              <?php echo htmlspecialchars($asset['status']); ?>
                      </span>
                    </div>
                    <div class="flex justify-between">
                      <span style="color: var(--subtitle-color);">Type:</span>
                            <span style="color: var(--text-color);" class="font-medium"><?php echo htmlspecialchars($asset['asset_type']); ?></span>
                    </div>
                    <div class="flex justify-between">
                      <span style="color: var(--subtitle-color);">Purchase Date:</span>
                            <span style="color: var(--text-color);" class="font-medium"><?php echo date('M Y', strtotime($asset['purchase_date'])); ?></span>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
              
              <!-- Dots Pagination (only show if more than 1 asset) -->
              <?php if (count($allAssets) > 1): ?>
                <div class="flex justify-center mt-4 space-x-2">
                  <?php for ($i = 0; $i < count($allAssets); $i++): ?>
                    <button class="asset-dot w-2 h-2 rounded-full transition-all duration-200 <?php echo $i === 0 ? 'bg-blue-500' : 'bg-gray-300'; ?>" data-index="<?php echo $i; ?>"></button>
                  <?php endfor; ?>
                </div>
              <?php endif; ?>
              
            <?php else: ?>
              <div style="color: var(--subtitle-color);" class="text-center">
                <i data-lucide="truck" class="w-16 h-16 mx-auto mb-4 text-gray-300"></i>
                <p>No assets found</p>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Bottom Section: Low Stock Alert and Bidding History -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-stretch">
          <!-- Inventory Management Card with Tabs (Left - Takes 2 columns) -->
          <div style="background: var(--card-bg); border: 1px solid var(--card-border);" class="lg:col-span-2 rounded-2xl shadow-md overflow-hidden flex flex-col">
            <!-- Header with Tabs -->
            <div style="border-bottom: 1px solid var(--card-border);" class="p-6">
              <div class="flex items-start justify-between mb-4">
                <div>
                  <h3 style="color: var(--text-color);" class="text-lg font-semibold mb-1">Inventory Management</h3>
                  <p style="color: var(--subtitle-color);" class="text-sm">Stock alerts, top items, and recent movements</p>
                </div>
                <div class="flex items-center">
                  <button id="exportInventoryData" class="inventory-export-button inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg border transition-colors">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 mr-2"></i>
                    Export CSV
                  </button>
                </div>
              </div>
              
              <!-- Tab Navigation -->
              <div class="inline-flex space-x-1 bg-gray-100 rounded-lg p-1" style="background: var(--icon-container-bg);">
                <button id="tab-alerts" class="inventory-tab active flex items-center px-4 py-2 text-sm font-medium rounded-md transition-all duration-200">
                  <i data-lucide="alert-triangle" class="w-4 h-4 mr-2"></i>
                  Stock Alerts
                </button>
                <button id="tab-top-items" class="inventory-tab flex items-center px-4 py-2 text-sm font-medium rounded-md transition-all duration-200">
                  <i data-lucide="trending-up" class="w-4 h-4 mr-2"></i>
                  Well-Stocked
                </button>
                <button id="tab-movements" class="inventory-tab flex items-center px-4 py-2 text-sm font-medium rounded-md transition-all duration-200">
                  <i data-lucide="activity" class="w-4 h-4 mr-2"></i>
                  Recent Movements
                </button>
              </div>
            </div>
            
            <!-- Tab Content -->
            <div class="flex-1">
              <!-- Stock Alerts Tab -->
              <div id="content-alerts" class="inventory-tab-content active">
                <div class="overflow-x-auto">
                  <table class="data-table">
                    <thead>
                      <tr>
                        <th class="py-4">Item Name</th>
                        <th class="py-4">Current Stock</th>
                        <th class="py-4">Status</th>
                        <th class="py-4">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($lowStockItems)): ?>
                        <tr>
                          <td colspan="4" class="table-empty py-12">
                            <i data-lucide="check-circle" class="w-12 h-12 mx-auto mb-2 text-green-500"></i>
                            <p>All items are well stocked!</p>
                          </td>
                        </tr>
                      <?php else: ?>
                        <?php foreach ($lowStockItems as $item): ?>
                          <tr>
                            <td class="py-4">
                              <div class="flex items-center">
                                <i data-lucide="package" class="w-5 h-5 text-gray-400 mr-3"></i>
                                <div class="text-sm font-medium" style="color: var(--text-color);"><?php echo htmlspecialchars($item['item_name']); ?></div>
                              </div>
                            </td>
                            <td class="py-4">
                              <div class="text-sm font-bold text-red-600"><?php echo $item['quantity']; ?></div>
                            </td>
                            <td class="py-4">
                              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i data-lucide="alert-circle" class="w-3 h-3 mr-1"></i>
                                Critical Low
                              </span>
                            </td>
                            <td class="py-4">
                              <a href="smart_warehousing.php" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200 transition-colors">
                                <i data-lucide="plus" class="w-3 h-3 mr-1"></i>
                                Restock
                              </a>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Well-Stocked Items Tab -->
              <div id="content-top-items" class="inventory-tab-content hidden">
                <?php if (empty($topStockedItems)): ?>
                  <div style="color: var(--subtitle-color);" class="text-center py-12">
                    <i data-lucide="package" class="w-12 h-12 mx-auto mb-3 text-gray-300"></i>
                    <p class="text-sm">No inventory data found</p>
                  </div>
                <?php else: ?>
                  <div class="p-6 space-y-4">
                    <?php foreach ($topStockedItems as $index => $item): ?>
                      <div class="flex items-center justify-between py-3 <?php echo $index < count($topStockedItems) - 1 ? 'border-b border-[var(--card-border)]' : ''; ?>">
                        <div class="flex items-center">
                          <div class="flex-shrink-0 mr-4">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-green-400 to-emerald-500 flex items-center justify-center">
                              <span class="text-white text-sm font-semibold"><?php echo $index + 1; ?></span>
                            </div>
                          </div>
                          <div>
                            <h4 style="color: var(--text-color);" class="text-sm font-medium">
                              <?php echo htmlspecialchars($item['item_name']); ?>
                            </h4>
                            <p style="color: var(--subtitle-color);" class="text-xs">
                              Updated <?php echo date('M d, Y', strtotime($item['last_updated'])); ?>
                            </p>
                          </div>
                        </div>
                        <div class="text-right">
                          <span style="color: var(--text-color);" class="text-lg font-semibold">
                            <?php echo number_format($item['quantity']); ?>
                          </span>
                          <p style="color: var(--subtitle-color);" class="text-xs">units</p>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>

              <!-- Recent Movements Tab -->
              <div id="content-movements" class="inventory-tab-content hidden">
                <?php if (empty($recentStockMovements)): ?>
                  <div style="color: var(--subtitle-color);" class="text-center py-12">
                    <i data-lucide="activity" class="w-12 h-12 mx-auto mb-3 text-gray-300"></i>
                    <p class="text-sm">No recent stock movements</p>
                  </div>
                <?php else: ?>
                  <div class="p-6 space-y-3">
                    <?php foreach ($recentStockMovements as $movement): ?>
                      <div class="flex items-center justify-between py-3 border-b border-[var(--card-border)]">
                        <div class="flex items-center">
                          <div class="flex-shrink-0 mr-3">
                            <?php if ($movement['change_amount'] > 0): ?>
                              <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                <i data-lucide="trending-up" class="w-4 h-4 text-green-600"></i>
                              </div>
                            <?php else: ?>
                              <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                                <i data-lucide="trending-down" class="w-4 h-4 text-red-600"></i>
                              </div>
                            <?php endif; ?>
                          </div>
                          <div>
                            <h4 style="color: var(--text-color);" class="text-sm font-medium">
                              <?php echo htmlspecialchars($movement['item_name']); ?>
                            </h4>
                            <p style="color: var(--subtitle-color);" class="text-xs">
                              <?php echo date('M d, g:i A', strtotime($movement['timestamp'])); ?>
                            </p>
                          </div>
                        </div>
                        <div class="text-right">
                          <span class="<?php echo $movement['change_amount'] > 0 ? 'text-green-600' : 'text-red-600'; ?> text-sm font-medium">
                            <?php echo $movement['change_amount'] > 0 ? '+' : ''; ?><?php echo number_format($movement['change_amount']); ?>
                          </span>
                          <p style="color: var(--subtitle-color);" class="text-xs">
                            to <?php echo number_format($movement['current_quantity']); ?>
                          </p>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Bidding History Card (Right) -->
          <div style="background: var(--card-bg); border: 1px solid var(--card-border);" class="rounded-2xl shadow-md p-6 flex flex-col">
            <div class="mb-4 flex items-start justify-between">
              <div>
                <h3 style="color: var(--text-color);" class="text-lg font-semibold mb-1">Orders Bidding History</h3>
                <p style="color: var(--subtitle-color);" class="text-sm">Showing 3 recent bids made by suppliers</p>
              </div>
              <a href="procurement_sourcing.php#purchase-orders" class="p-2 rounded-lg transition-all duration-200 hover:rounded-full" style="color: var(--subtitle-color);" title="View Purchase Orders" onmouseover="this.style.backgroundColor='var(--close-btn-hover-bg)'" onmouseout="this.style.backgroundColor='transparent'">
                <i data-lucide="arrow-up-right" class="w-7 h-7"></i>
              </a>
            </div>
            
            <div class="flex-1 flex flex-col justify-start">
              <?php if (empty($biddingHistory)): ?>
                <div style="color: var(--subtitle-color);" class="text-center py-8 flex-1 flex items-center justify-center">
                  <div>
                    <i data-lucide="file-text" class="w-12 h-12 mx-auto mb-3 text-gray-300"></i>
                    <p class="text-sm">No recent bids found</p>
                  </div>
                </div>
              <?php else: ?>
                <?php foreach ($biddingHistory as $index => $bid): ?>
                  <div class="flex items-start py-4 <?php echo $index < count($biddingHistory) - 1 ? 'border-b border-[var(--card-border)]' : ''; ?> flex-1">
                    <div class="flex-shrink-0 mr-3">
                      <i data-lucide="gavel" class="w-5 h-5 text-blue-500 mr-2"></i>
                    </div>
                    <div class="flex-1">
                      <div class="flex items-start justify-between">
                        <div class="flex-1">
                          <h4 style="color: var(--text-color);" class="text-sm font-medium mb-1">
                            <?php echo htmlspecialchars($bid['item_name']); ?>
                          </h4>
                          <p style="color: var(--subtitle-color);" class="text-xs mb-2">
                            by <?php echo htmlspecialchars($bid['supplier_name'] ?? 'Unknown Supplier'); ?>
                          </p>
                          <div class="flex items-center justify-between">
                            <span style="color: var(--text-color);" class="text-sm font-semibold">
                              ₱<?php echo number_format($bid['bid_amount'], 2); ?>
                            </span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                              <?php 
                                echo $bid['bid_status'] === 'Awarded' ? 'bg-green-100 text-green-800' : 
                                     ($bid['bid_status'] === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); 
                              ?>">
                              <?php echo htmlspecialchars($bid['bid_status']); ?>
                            </span>
                          </div>
                        </div>
                      </div>
                      <div style="color: var(--subtitle-color);" class="text-xs mt-2">
                        <?php echo date('M d, Y g:i A', strtotime($bid['bid_date'])); ?>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Pass PHP data to JavaScript -->
        <script>
          // Set dashboard data before loading the main dashboard script
          window.dashboardData = {
            totalAssets: <?php echo count($allAssets); ?>,
            stockData: <?php echo json_encode($lowStockItems); ?>,
            topStockedItems: <?php echo json_encode($topStockedItems); ?>,
            recentStockMovements: <?php echo json_encode($recentStockMovements); ?>
          };
        </script>
        
        <!-- Load Dashboard JavaScript -->
        <script src="../assets/js/dashboards.js"></script>
        
      <?php elseif ($_SESSION['role'] === 'smart_warehousing'): ?>
        <!-- Smart Warehousing Dashboard -->
        <h1 class="font-semibold mb-1.5 page-title">Dashboard</h1>
        <p class="lg:text-lg text-base text-[var(--subtitle-color)] mb-4 page-subtitle">SWS dashboard content coming soon...</p>
        
      <?php elseif ($_SESSION['role'] === 'procurement'): ?>
        <!-- Procurement Dashboard -->
        <h1 class="font-semibold mb-1.5 page-title">Dashboard</h1>
        <p class="lg:text-lg text-base text-[var(--subtitle-color)] mb-4 page-subtitle">PSM dashboard content coming soon...</p>
        
      <?php elseif ($_SESSION['role'] === 'plt'): ?>
        <!-- Project Logistics Tracker Dashboard -->
        <h1 class="font-semibold mb-1.5 page-title">Dashboard</h1>
        <p class="lg:text-lg text-base text-[var(--subtitle-color)] mb-4 page-subtitle">PLT dashboard content coming soon...</p>
        
      <?php elseif ($_SESSION['role'] === 'alms'): ?>
        <!-- Asset Lifecycle & Maintenance Dashboard -->
        <h1 class="font-semibold mb-1.5 page-title">Dashboard</h1>
        <p class="lg:text-lg text-base text-[var(--subtitle-color)] mb-4 page-subtitle">ALMS dashboard content coming soon...</p>
        
      <?php elseif ($_SESSION['role'] === 'dtrs'): ?>
        <!-- Document Tracking & Logistics Records Dashboard -->
        <h1 class="font-semibold mb-1.5 page-title">Dashboard</h1>
        <p class="lg:text-lg text-base text-[var(--subtitle-color)] mb-4 page-subtitle">DTRS dashboard content coming soon...</p>
        
      <?php else: ?>
        <!-- Default/Unknown Role -->
        <h1 class="font-semibold mb-1.5 page-title">Welcome to LOGISTICS 1</h1>
        <p class="lg:text-lg text-base text-[var(--subtitle-color)] mb-4 page-subtitle">Your dashboard content will appear here based on your assigned role.</p>
      <?php endif; ?>

    </div>
  </div>

  <script src="../assets/js/sidebar.js"></script>
  <script src="../assets/js/script.js"></script>
  <script>
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  </script>
</body>
</html>