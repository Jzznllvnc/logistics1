<?php
require_once '../includes/functions/auth.php';
require_once '../includes/functions/inventory.php';
requireLogin();

// Role check
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'smart_warehousing') {
    header("Location: dashboard.php");
    exit();
}

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
    header("Location: smart_warehousing.php");
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

$inventory = getInventory();
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
  <style>
    /* ... (All the CSS styles remain the same) ... */
    .sws-container { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }
    .sws-form-card, .sws-inventory-card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: var(--text-color); }
    .form-group input { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--input-border); background-color: var(--input-bg); color: var(--input-text); }
    .form-actions { display: flex; gap: 10px; }
    .btn-stock-in { background: #10b981; color: white; }
    .btn-stock-out { background: #ef4444; color: white; }
    .inventory-table { width: 100%; border-collapse: collapse; }
    .inventory-table th, .inventory-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--card-border); }
    .inventory-table th { background-color: rgba(128,128,128,0.05); font-size: 13px; text-transform: uppercase; }
    .low-stock { color: #f59e0b; font-weight: bold; }
    .item-actions a { margin-right: 15px; cursor: pointer; }
    .item-actions a.edit:hover { color: #3b82f6; }
    .item-actions a.delete:hover { color: #ef4444; }
    .modal-content { max-width: 500px; }
    .inventory-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    #inventorySearchInput { padding: 8px 12px; width: 250px; border-radius: 6px; border: 1px solid var(--input-border); background-color: var(--input-bg); color: var(--input-text); }
    @media (max-width: 900px) { .sws-container { grid-template-columns: 1fr; } }
    @media (max-width: 600px) { .inventory-header { flex-direction: column; align-items: flex-start; gap: 15px; } #inventorySearchInput { width: 100%; } }
  </style>
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

      <h1 class="page-title" style="font-family: 'Inter Tight', sans-serif; font-weight: 600; font-size: 2.5rem; margin-bottom: 2rem;">Smart Warehousing System (SWS)</h1>
      
      <div class="sws-container">
        <div class="sws-form-card">
          <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 20px;">Manage Stock Levels</h2>
          <p style="font-size: 0.9rem; color: #888; margin-top: -15px; margin-bottom: 20px;">Use this form to add new items or update quantities.</p>
          <form action="smart_warehousing.php" method="POST">
            <div class="form-group">
              <label for="item_name">Item Name</label>
              <input type="text" id="item_name" name="item_name" list="inventory_items" placeholder="Type to see stock levels..." required>
              <datalist id="inventory_items">
                <?php foreach ($inventory as $item): ?>
                    <option value="<?php echo htmlspecialchars($item['item_name']); ?>">
                        <?php echo htmlspecialchars($item['item_name']) . ' (' . htmlspecialchars($item['quantity']) . ' in stock)'; ?>
                    </option>
                <?php endforeach; ?>
              </datalist>
            </div>
            <div class="form-group">
              <label for="quantity">Quantity</label>
              <input type="number" id="quantity" name="quantity" min="1" placeholder="e.g., 50" required>
            </div>
            <div class="form-actions">
              <button type="submit" name="action" value="stock-in" class="btn btn-stock-in">Stock In</button>
              <button type="submit" name="action" value="stock-out" class="btn btn-stock-out">Stock Out</button>
            </div>
          </form>
        </div>
        <div class="sws-inventory-card">
          <div class="inventory-header">
            <h2 style="font-size: 1.5rem; font-weight: 600; margin: 0;">Current Inventory</h2>
            <input type="text" id="inventorySearchInput" placeholder="🔎 Search by item name...">
          </div>
          <table class="inventory-table">
            <thead>
              <tr>
                <th>Item Name</th><th>Quantity</th><th>Last Updated</th>
                <?php if ($_SESSION['role'] === 'admin'): ?><th>Actions</th><?php endif; ?>
              </tr>
            </thead>
            <tbody id="inventoryTableBody">
              <?php if (empty($inventory)): ?>
                <tr><td colspan="<?php echo ($_SESSION['role'] === 'admin') ? '4' : '3'; ?>" style="text-align: center; padding: 20px; color: #888;">No items in inventory.</td></tr>
              <?php else: foreach ($inventory as $item): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td class="<?php echo ($item['quantity'] < 10) ? 'low-stock' : ''; ?>">
                      <?php echo htmlspecialchars($item['quantity']); ?>
                      <?php if ($item['quantity'] < 10): ?> (Low Stock)<?php endif; ?>
                    </td>
                    <td><?php echo date('M d, Y g:i A', strtotime($item['last_updated'])); ?></td>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                      <td class="item-actions">
                        <a class="edit" onclick='openEditModal(<?php echo json_encode($item); ?>)'><i class="fas fa-pencil-alt"></i></a>
                        <a class="delete" onclick="confirmDeleteItem(<?php echo $item['id']; ?>)"><i class="fas fa-trash-alt"></i></a>
                      </td>
                    <?php endif; ?>
                  </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <?php if ($_SESSION['role'] === 'admin'): ?>
  <div id="editItemModal" class="modal" style="display:none;">
    <div class="modal-content bg-[var(--card-bg)] p-8 rounded-lg shadow-xl relative">
      <h2 class="text-2xl font-bold mb-4">Edit Item Name</h2>
      <form id="editItemForm" method="POST" action="smart_warehousing.php">
        <input type="hidden" name="action" value="update_item">
        <input type="hidden" name="item_id" id="edit_item_id">
        <div class="form-group">
          <label for="item_name_edit">Item Name</label>
          <input type="text" name="item_name_edit" id="item_name_edit" required>
        </div>
        <div class="form-actions flex justify-end gap-4 mt-6">
          <button type="button" class="btn bg-gray-300" onclick="closeModal(document.getElementById('editItemModal'))">Cancel</button>
          <button type="submit" class="btn btn-danger bg-green-500 text-white">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <script src="../assets/js/sidebar.js"></script>
  <script src="../assets/js/script.js"></script>
  <script src="../assets/js/smart_warehousing.js"></script>
</body>
</html>