<?php
require_once '../includes/functions/auth.php';
require_once '../includes/functions/inventory.php';
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
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'inventory' => $inventory,
        'currentPage' => $currentPage,
        'totalPages' => $totalPages,
        'totalItems' => $totalItems,
        'itemsPerPage' => $itemsPerPage,
        'isAdmin' => $_SESSION['role'] === 'admin'
    ]);
    exit();
}

// Pagination settings
$itemsPerPage = 10;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// ... (All the PHP logic for handling POST requests remains the same) ...
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'stock-in' || $action === 'stock-out') {
        $itemName = trim($_POST['item_name'] ?? '');
        $quantity = $_POST['quantity'] ?? 0;
        if ($action === 'stock-in') {
            if (stockIn($itemName, $quantity)) {
                $_SESSION['flash_message'] = "Successfully stocked in $quantity of $itemName.";
                $_SESSION['flash_message_type'] = 'success';
            } else { $_SESSION['flash_message'] = "Failed to stock in. Check input."; $_SESSION['flash_message_type'] = 'error'; }
        } else {
            $result = stockOut($itemName, $quantity);
            if ($result === "Success") {
                $_SESSION['flash_message'] = "Successfully stocked out $quantity of $itemName.";
                $_SESSION['flash_message_type'] = 'success';
            } else { $_SESSION['flash_message'] = $result; $_SESSION['flash_message_type'] = 'error'; }
        }
    }
    if ($_SESSION['role'] === 'admin') {
        $itemId = $_POST['item_id'] ?? 0;
        if ($action === 'update_item') {
            $newItemName = trim($_POST['item_name_edit'] ?? '');
            if (updateInventoryItem($itemId, $newItemName)) {
                $_SESSION['flash_message'] = "Item successfully renamed.";
                $_SESSION['flash_message_type'] = 'success';
            } else { $_SESSION['flash_message'] = "Failed to rename item."; $_SESSION['flash_message_type'] = 'error'; }
        } elseif ($action === 'delete_item') {
            if (deleteInventoryItem($itemId)) {
                $_SESSION['flash_message'] = "Item successfully deleted.";
                $_SESSION['flash_message_type'] = 'success';
            } else { $_SESSION['flash_message'] = "Failed to delete item."; $_SESSION['flash_message_type'] = 'error'; }
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

// Get total count and paginated inventory
$totalItems = getTotalInventoryCount();
$totalPages = ceil($totalItems / $itemsPerPage);
$inventory = getPaginatedInventory($offset, $itemsPerPage);
$allInventory = getInventory(); // For the modal datalist
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <script>document.documentElement.classList.add('preload', 'loading');</script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logistics 1 - SWS</title>
  <link rel="icon" href="../assets/images/slate2.png" type="image/png">
  <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
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
                showCustomAlert("<?php echo htmlspecialchars($message); ?>", "<?php echo htmlspecialchars($message_type); ?>");
            }
        });
        <?php endif; ?>
      </script>
      <?php include '../partials/header.php'; ?>

      <div class="flex justify-between items-center">
        <h1 class="font-semibold page-title">Smart Warehousing System</h1>
      </div>
      
      <!-- Current Inventory Section - Now Full Width -->
      <div class="bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl p-6 shadow-sm">
        <div class="flex justify-between items-center mb-5 flex-col lg:flex-row gap-4 lg:gap-0 lg:justify-between justify-center">
          <h2 class="text-2xl font-semibold text-[var(--text-color)]">Current Inventory</h2>
          <div class="flex gap-2 lg:gap-3 w-full lg:w-auto items-center flex-wrap sm:flex-nowrap justify-center lg:justify-end">
            <div class="relative w-32 sm:w-36 md:w-40 lg:w-48">
              <i data-lucide="search" class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
              <input type="text" id="inventorySearchInput" placeholder="Search..." class="py-2 pl-10 pr-3 w-full rounded-full border border-[var(--input-border)] bg-[var(--input-bg)] text-[var(--input-text)]">
            </div>
            <div class="relative inline-block">
              <select id="inventoryFilter" class="py-2 pl-10 pr-3 rounded-md border border-[var(--input-border)] bg-[var(--input-bg)] text-[var(--input-text)] appearance-none cursor-pointer opacity-0 absolute inset-0 w-full h-full">
                <option value="all">All Items</option>
                <option value="low-stock">Low Stock (&lt;10)</option>
                <option value="normal-stock">Normal Stock (10-100)</option>
                <option value="high-stock">High Stock (&gt;100)</option>
              </select>
              <div class="flex items-center py-2 pl-4 pr-4 rounded-md border border-[var(--input-border)] bg-[var(--input-bg)] cursor-pointer">
                <i data-lucide="list-filter" class="w-5 h-5 mr-2 text-[var(--input-text)]"></i>
                <span class="text-[var(--input-text)] text-[1rem] whitespace-nowrap">Filter</span>
              </div>
            </div>
            <div class="h-8 w-px bg-gray-300 dark:bg-gray-600 mx-2"></div>
            <button id="stockInBtn" type="button" class="btn-primary text-sm sm:text-base whitespace-nowrap">
              <i data-lucide="package-plus" class="w-6 h-6 lg:mr-2 sm:mr-0"></i><span class="hidden sm:inline">Stock In</span>
            </button>
            <button id="stockOutBtn" type="button" class="btn-secondary-danger text-sm sm:text-base whitespace-nowrap">
              <i data-lucide="package-minus" class="w-6 h-6 lg:mr-2 sm:mr-0"></i><span class="hidden sm:inline">Stock Out</span>
            </button>
          </div>
        </div>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Last Updated</th>
                <?php if ($_SESSION['role'] === 'admin'): ?><th>Action</th><?php endif; ?>
              </tr>
            </thead>
            <tbody id="inventoryTableBody">
              <?php if (empty($inventory)): ?>
                <tr><td colspan="<?php echo ($_SESSION['role'] === 'admin') ? '4' : '3'; ?>" class="table-empty">No items in inventory.</td></tr>
              <?php else: foreach ($inventory as $item): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td class="<?php echo ($item['quantity'] < 10) ? 'table-status-low' : 'table-status-normal'; ?>">
                      <?php echo htmlspecialchars($item['quantity']); ?>
                      <?php if ($item['quantity'] < 10): ?> (Low Stock)<?php endif; ?>
                    </td>
                    <td><?php echo date('M d, Y g:i A', strtotime($item['last_updated'])); ?></td>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                      <td>
                        <div class="relative">
                          <button type="button" class="action-dropdown-btn p-2 rounded-full transition-colors" onclick="toggleActionDropdown(<?php echo $item['id']; ?>)">
                            <i data-lucide="more-horizontal" class="w-6 h-6"></i>
                          </button>
                          <div id="dropdown-<?php echo $item['id']; ?>" class="action-dropdown hidden">
                            <button type="button" onclick='openEditModal(<?php echo json_encode($item); ?>)'>
                              <i data-lucide="edit-3" class="w-5 h-5 mr-3"></i>
                              Edit
                            </button>
                            <button type="button" onclick="confirmDeleteItem(<?php echo $item['id']; ?>)" class="text-red-600">
                              <i data-lucide="trash-2" class="w-5 h-5 mr-3"></i>
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
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="flex justify-center items-center mt-6 gap-2" id="paginationContainer">
          <?php if ($currentPage > 1): ?>
            <button onclick="loadPage(<?php echo $currentPage - 1; ?>)" class="pagination-btn">
              <i data-lucide="chevron-left" class="w-4 h-4 mr-1"></i>
              Previous
            </button>
          <?php endif; ?>
          
          <?php
          $startPage = max(1, $currentPage - 2);
          $endPage = min($totalPages, $currentPage + 2);
          
          if ($startPage > 1): ?>
            <button onclick="loadPage(1)" class="pagination-btn <?php echo ($currentPage == 1) ? 'active' : ''; ?>">1</button>
            <?php if ($startPage > 2): ?>
              <span class="pagination-ellipsis">...</span>
            <?php endif; ?>
          <?php endif; ?>
          
          <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <button onclick="loadPage(<?php echo $i; ?>)" class="pagination-btn <?php echo ($currentPage == $i) ? 'active' : ''; ?>" data-page="<?php echo $i; ?>"><?php echo $i; ?></button>
          <?php endfor; ?>
          
          <?php if ($endPage < $totalPages): ?>
            <?php if ($endPage < $totalPages - 1): ?>
              <span class="pagination-ellipsis">...</span>
            <?php endif; ?>
            <button onclick="loadPage(<?php echo $totalPages; ?>)" class="pagination-btn <?php echo ($currentPage == $totalPages) ? 'active' : ''; ?>"><?php echo $totalPages; ?></button>
          <?php endif; ?>
          
          <?php if ($currentPage < $totalPages): ?>
            <button onclick="loadPage(<?php echo $currentPage + 1; ?>)" class="pagination-btn">
              Next
              <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
            </button>
          <?php endif; ?>
        </div>
        
        <div class="pagination-info" id="paginationInfo">
          Showing <?php echo (($currentPage - 1) * $itemsPerPage) + 1; ?> to <?php echo min($currentPage * $itemsPerPage, $totalItems); ?> of <?php echo $totalItems; ?> items
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if ($_SESSION['role'] === 'admin'): ?>
  <div id="editItemModal" class="modal hidden">
    <div class="modal-content p-8">
      <div class="flex justify-between items-center mb-2">
        <h2 class="modal-title flex items-center min-w-0 flex-1">
          <i data-lucide="square-pen" class="w-6 h-6 mr-3 flex-shrink-0"></i>
          <span class="truncate">Edit Item Name</span>
        </h2>
        <button type="button" class="close-button flex-shrink-0 ml-3">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      <p class="modal-subtitle">Update item name.</p>
      <div class="border-b border-[var(--card-border)] mb-5"></div>

      <form id="editItemForm" method="POST" action="smart_warehousing.php">
        <input type="hidden" name="action" value="update_item">
        <input type="hidden" name="item_id" id="edit_item_id">
        <div class="form-group mb-2">
          <label for="item_name_edit" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Item Name</label>
          <input type="text" name="item_name_edit" id="item_name_edit" required class="w-full p-2.5 rounded-md border border-[var(--input-border)] bg-[var(--input-bg)] text-[var(--input-text)]">
        </div>
        <div class="form-actions flex justify-end gap-4 mt-6">
          <button type="button" class="px-5 py-2.5 rounded-md border border-gray-300 cursor-pointer font-semibold transition-all duration-300 bg-gray-100 text-gray-700 hover:bg-gray-200" onclick="closeModal(document.getElementById('editItemModal'))">Cancel</button>
          <button type="submit" class="btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- Stock Management Modal -->
  <div id="stockManagementModal" class="modal hidden">
    <div class="modal-content p-8 max-w-lg">
      <div class="flex justify-between items-center mb-2">
        <h2 class="modal-title flex items-center min-w-0 flex-1" id="modalTitle">
          <i data-lucide="package" class="w-7 h-7 mr-3 flex-shrink-0" id="stockModalIcon"></i>
          <span id="stockModalTitleText" class="truncate">Manage Stock Levels</span>
        </h2>
        <button type="button" class="close-button flex-shrink-0 ml-3">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      <p class="modal-subtitle" id="stockModalSubtitle">Add/Remove new items or update quantities.</p>
      <div class="border-b border-[var(--card-border)] mb-5"></div>
      
      <form action="smart_warehousing.php" method="POST" id="stockManagementForm">
        <input type="hidden" name="action" id="stockAction" value="">
        
        <div class="mb-5">
          <label for="modal_item_name" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Item Name</label>
          <input type="text" id="modal_item_name" name="item_name" list="inventory_items" placeholder="Type to see stock levels..." required class="w-full p-2.5 rounded-md border border-[var(--input-border)] bg-[var(--input-bg)] text-[var(--input-text)]">
          <datalist id="inventory_items">
            <?php foreach ($allInventory as $item): ?>
                <option value="<?php echo htmlspecialchars($item['item_name']); ?>">
                    <?php echo htmlspecialchars($item['item_name']) . ' (' . htmlspecialchars($item['quantity']) . ' in stock)'; ?>
                </option>
            <?php endforeach; ?>
          </datalist>
        </div>
        
        <div class="mb-6">
          <label for="modal_quantity" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Quantity</label>
          <input type="number" id="modal_quantity" name="quantity" min="1" placeholder="e.g., 50" required class="w-full p-2.5 rounded-md border border-[var(--input-border)] bg-[var(--input-bg)] text-[var(--input-text)]">
        </div>
        
        <div class="flex justify-end gap-3 pt-4">
          <button type="button" class="px-5 py-2.5 rounded-md border border-gray-300 cursor-pointer font-semibold transition-all duration-300 bg-gray-100 text-gray-700 hover:bg-gray-200" onclick="closeModal(document.getElementById('stockManagementModal'))">
            Cancel
          </button>
          <button type="submit" id="confirmStockBtn" class="btn-primary">
            Confirm
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="../assets/js/sidebar.js"></script>
  <script src="../assets/js/script.js"></script>
  <script src="../assets/js/smart_warehousing.js"></script>
</body>
</html>