<?php
require_once '../includes/functions/auth.php';
require_once '../includes/functions/inventory.php';
require_once '../includes/functions/supplier.php';
require_once '../includes/functions/purchase_order.php';
require_once '../includes/functions/bids.php'; // Needed for price history function
requireLogin();

// Role check
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'smart_warehousing') {
    header("Location: dashboard.php");
    exit();
}

// Handle AJAX pagination requests
if (isset($_GET['ajax']) && $_GET['ajax'] === 'pagination') {
    $itemsPerPage = 10;
    $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    $totalItems = getTotalInventoryCount();
    $totalPages = ceil($totalItems / $itemsPerPage);
    $inventory = getPaginatedInventory($offset, $itemsPerPage);
    
    // Get both stock and price forecasts
    $forecasts = getAutomaticForecasts($inventory);
    $price_forecasts = getAutomaticPriceForecasts($inventory);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'inventory' => $inventory,
        'forecasts' => $forecasts,
        'price_forecasts' => $price_forecasts, // Send price forecasts in JSON response
        'currentPage' => $currentPage,
        'totalPages' => $totalPages,
        'totalItems' => $totalItems,
        'itemsPerPage' => $itemsPerPage,
        'isAdmin' => $_SESSION['role'] === 'admin'
    ]);
    exit();
}

// Standard Page Load
$itemsPerPage = 10;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'stock-in') {
        $itemName = trim($_POST['item_name'] ?? '');
        $quantity = $_POST['quantity'] ?? 0;
        if (stockIn($itemName, $quantity)) {
            $_SESSION['flash_message'] = "Successfully stocked in $quantity of $itemName.";
            $_SESSION['flash_message_type'] = 'success';
        } else { 
            $_SESSION['flash_message'] = "Failed to stock in items. Check input."; 
            $_SESSION['flash_message_type'] = 'error'; 
        }
    } elseif ($action === 'stock-out') {
        $itemName = trim($_POST['item_name'] ?? '');
        $quantity = $_POST['quantity'] ?? 0;
        $result = stockOut($itemName, $quantity);
        if ($result === "Success") {
            $_SESSION['flash_message'] = "Successfully stocked out $quantity of $itemName.";
            $_SESSION['flash_message_type'] = 'success';
        } else { 
            $_SESSION['flash_message'] = $result; 
            $_SESSION['flash_message_type'] = 'error'; 
        }
    }

    if ($_SESSION['role'] === 'admin') {
        $itemId = $_POST['item_id'] ?? 0;
        if ($action === 'update_item') {
            $newItemName = trim($_POST['item_name_edit'] ?? '');
            if (updateInventoryItem($itemId, $newItemName)) {
                $_SESSION['flash_message'] = "Item successfully renamed.";
                $_SESSION['flash_message_type'] = 'success';
            } else { 
                $_SESSION['flash_message'] = "Failed to rename item."; 
                $_SESSION['flash_message_type'] = 'error'; 
            }
        } elseif ($action === 'delete_item') {
            if (deleteInventoryItem($itemId)) {
                $_SESSION['flash_message'] = "Item successfully deleted.";
                $_SESSION['flash_message_type'] = 'success';
            } else { 
                $_SESSION['flash_message'] = "Failed to delete item."; 
                $_SESSION['flash_message_type'] = 'error'; 
            }
        }
    }
    header("Location: smart_warehousing.php?page=" . $currentPage);
    exit();
}

// Check for flash messages
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $message_type = $_SESSION['flash_message_type'];
    unset($_SESSION['flash_message'], $_SESSION['flash_message_type']);
} else {
    $message = '';
}

// Get data for initial page load
$totalItems = getTotalInventoryCount();
$totalPages = ceil($totalItems / $itemsPerPage);
$inventory = getPaginatedInventory($offset, $itemsPerPage);
$allInventory = getInventory();
$allSuppliers = getAllSuppliers();
$forecasts = getAutomaticForecasts($inventory);
$price_forecasts = getAutomaticPriceForecasts($inventory); // Get price forecasts for initial load
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <script>document.documentElement.classList.add('preload', 'loading');</script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logistics 1 - SWS</title>
  <link rel="icon" href="../assets/images/slate2.png" type="image/png">
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="stylesheet" href="../assets/css/sidebar.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha384-nRgPTkuX86pH8yjPJUAFuASXQSSl2/bBUiNV47vSYpKFxHJhbcrGnmlYpYJMeD7a" crossorigin="anonymous">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
  <div class="sidebar" id="sidebar"> <?php include '../partials/sidebar.php'; ?> </div>

  <div class="main-content-wrapper" id="mainContentWrapper">
    <div class="content" id="mainContent">
      <script>
        <?php if ($message): ?>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.showCustomAlert) {
                showCustomAlert(<?php echo json_encode($message); ?>, <?php echo json_encode($message_type); ?>);
            }
        });
        <?php endif; ?>
      </script>
      <?php include '../partials/header.php'; ?>

      <div class="flex justify-between items-center">
        <h1 class="font-semibold page-title">Smart Warehousing System</h1>
      </div>
      
      <div class="bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl p-6 shadow-sm">
        <div class="flex justify-between items-center mb-5 flex-col lg:flex-row gap-4 lg:gap-0 lg:justify-between justify-center">
          <h2 class="text-2xl font-semibold text-[var(--text-color)]">Current Inventory</h2>
          <div class="flex gap-2 lg:gap-3 w-full lg:w-auto items-center flex-wrap sm:flex-nowrap justify-center lg:justify-end">
            <div class="relative w-32 sm:w-36 md:w-40 lg:w-48">
              <i data-lucide="search" class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
              <input type="text" id="inventorySearchInput" placeholder="Search..." class="py-2 pl-10 pr-3 w-full rounded-full border border-[var(--input-border)] bg-[var(--input-bg)] text-[var(--input-text)]">
            </div>
            <div class="relative inline-block">
              <button id="inventoryFilterBtn" class="flex items-center py-2 pl-4 pr-4 rounded-md border border-[var(--input-border)] bg-[var(--input-bg)] cursor-pointer transition-colors" style="color: var(--input-text);" onmouseover="this.style.backgroundColor='var(--close-btn-hover-bg)'" onmouseout="this.style.backgroundColor='var(--input-bg)'">
                <i data-lucide="list-filter" class="w-5 h-5 mr-3"></i>
                <span class="text-[1rem] whitespace-nowrap">Filter</span>
              </button>
            </div>
            <div class="h-8 w-px bg-gray-300 dark:bg-gray-600 mx-2"></div>
            <button id="stockInBtn" type="button" class="btn-primary text-sm sm:text-base whitespace-nowrap">
              <i data-lucide="package-plus" class="w-6 h-6 lg:mr-2 sm:mr-0"></i><span class="hidden sm:inline">Stock In</span>
            </button>
            <button id="stockOutBtn" type="button" class="inline-flex items-center gap-1 px-5 py-2.5 font-semibold text-[#495057] bg-[#e0e0e0] border border-[#ced4da] rounded-lg shadow-[0_2px_4px_rgba(0,0,0,0.1)] transition-all duration-300 ease-in-out hover:bg-[#c8c8c8] hover:border-[#c8c8c8] hover:translate-y-[-1px] hover:shadow-[0_4px_8px_rgba(0,0,0,0.15)] active:translate-y-0 active:shadow-[0_2px_4px_rgba(0,0,0,0.1)] cursor-pointer text-sm sm:text-base whitespace-nowrap">
              <i data-lucide="package-minus" class="w-6 h-6 lg:mr-2 sm:mr-0"></i>
              <span class="hidden sm:inline">Stock Out</span>
            </button>
          </div>
        </div>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Item Name</th>
                <th>Current Quantity</th>
                <th>
                    Stock Trend Analysis 
                    <span class="inline-flex items-center gap-1 ml-2 px-2 py-0.3 text-[0.8rem] font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-full align-top">
                      <i data-lucide="bot" class="w-4 h-4"></i>
                      AI
                    </span>
                </th>
                <th>
                    Recommended Action 
                    <span class="inline-flex items-center gap-1 ml-2 px-2 py-0.3 text-[0.8rem] font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-full align-top">
                      <i data-lucide="bot" class="w-4 h-4"></i>
                      AI
                    </span>
                </th>
                <th>
                    Price Forecast
                    <span class="inline-flex items-center gap-1 ml-2 px-2 py-0.3 text-[0.8rem] font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-full align-top">
                      <i data-lucide="bot" class="w-4 h-4"></i>
                      AI
                    </span>
                </th>
                <th>Last Updated</th>
                <?php if ($_SESSION['role'] === 'admin'): ?><th>Action</th><?php endif; ?>
              </tr>
            </thead>
            <tbody id="inventoryTableBody">
              <?php if (empty($inventory)): ?>
                <tr><td colspan="<?php echo ($_SESSION['role'] === 'admin') ? '7' : '6'; ?>" class="table-empty">No items in inventory.</td></tr>
              <?php else: foreach ($inventory as $item): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td class="<?php echo ($item['quantity'] < 10) ? 'table-status-low' : 'table-status-normal'; ?>">
                      <?php echo htmlspecialchars($item['quantity']); ?>
                      <?php if ($item['quantity'] < 10): ?> (Low Stock)<?php endif; ?>
                    </td>
                    <td><?php echo $forecasts[$item['id']]['analysis'] ?? '<span class="text-gray-400">N/A</span>'; ?></td>
                    <td><?php echo $forecasts[$item['id']]['action'] ?? '<span class="text-gray-400">N/A</span>'; ?></td>
                    <td><?php echo $price_forecasts[$item['item_name']] ?? '<span class="text-gray-400">N/A</span>'; ?></td>
                    <td><?php echo date('M d, Y g:i A', strtotime($item['last_updated'])); ?></td>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                      <td>
                        <div class="relative">
                          <button type="button" class="action-dropdown-btn p-2 rounded-full transition-colors" onclick="toggleActionDropdown(<?php echo $item['id']; ?>)">
                            <i data-lucide="more-horizontal" class="w-6 h-6"></i>
                          </button>
                          <div id="dropdown-<?php echo $item['id']; ?>" class="action-dropdown hidden">
                            <button type="button" onclick='openEditModal(<?php echo json_encode($item); ?>)'>
                              <i data-lucide="edit-3" class="w-4 h-4 mr-3"></i>
                              Edit
                            </button>
                            <button type="button" onclick="confirmDeleteItem(<?php echo $item['id']; ?>)">
                              <i data-lucide="trash-2" class="w-4 h-4 mr-3"></i>
                              Delete
                            </button>
                          </div>
                        </div>
                      </td>
                    <?php endif; ?>
                  </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <div class="flex justify-center items-center mt-6 gap-2" id="paginationContainer">
          </div>
        <div class="pagination-info" id="paginationInfo">
          Showing <?php echo (($currentPage - 1) * $itemsPerPage) + 1; ?> to <?php echo min($currentPage * $itemsPerPage, $totalItems); ?> of <?php echo $totalItems; ?> items
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php include 'modals/sws.php'; ?>

  <script src="../assets/js/sidebar.js"></script>
  <script src="../assets/js/script.js"></script>
  <script src="../assets/js/smart_warehousing.js"></script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
  <script>
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  </script>
</body>
</html>