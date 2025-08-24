<?php
require_once '../includes/functions/auth.php';
require_once '../includes/functions/supplier.php';
require_once '../includes/functions/purchase_order.php';
require_once '../includes/functions/inventory.php'; // For item list
requireLogin();

// Role check for admin/procurement
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'procurement') {
    header("Location: dashboard.php");
    exit();
}

// Handle all form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // --- Admin-Only Actions ---
    if ($_SESSION['role'] === 'admin') {
        if ($action === 'create_supplier' || $action === 'update_supplier') {
            $name = $_POST['supplier_name'] ?? '';
            $contact = $_POST['contact_person'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $address = $_POST['address'] ?? '';
            if ($action === 'create_supplier') {
                if (createSupplier($name, $contact, $email, $phone, $address)) {
                    $_SESSION['flash_message'] = "Supplier <strong>" . htmlspecialchars($name) . "</strong> created successfully.";
                    $_SESSION['flash_message_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = "Failed to create supplier. Please try again.";
                    $_SESSION['flash_message_type'] = 'error';
                }
            } else {
                $id = $_POST['supplier_id'] ?? 0;
                if (updateSupplier($id, $name, $contact, $email, $phone, $address)) {
                    $_SESSION['flash_message'] = "Supplier <strong>" . htmlspecialchars($name) . "</strong> updated successfully.";
                    $_SESSION['flash_message_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = "Failed to update supplier. Please try again.";
                    $_SESSION['flash_message_type'] = 'error';
                }
            }
        } elseif ($action === 'delete_supplier') {
            $id = $_POST['supplier_id'] ?? 0;
            if (deleteSupplier($id)) {
                $_SESSION['flash_message'] = "Supplier deleted successfully.";
                $_SESSION['flash_message_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = "Failed to delete supplier. Please try again.";
                $_SESSION['flash_message_type'] = 'error';
            }
        }
    }

    // --- Actions for All Roles on this Page ---
    if ($action === 'create_po') {
        $supplier_id = $_POST['supplier_id_po'] ?? 0;
        $item_name = $_POST['item_name_po'] ?? '';
        $quantity = $_POST['quantity_po'] ?? 0;
        if (createPurchaseOrder($supplier_id, $item_name, $quantity)) {
            $_SESSION['flash_message'] = "Purchase order created successfully for <strong>" . htmlspecialchars($quantity) . "</strong> x <strong>" . htmlspecialchars($item_name) . "</strong>.";
            $_SESSION['flash_message_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = "Failed to create purchase order. Please try again.";
            $_SESSION['flash_message_type'] = 'error';
        }
    }
    
    header("Location: procurement_sourcing.php");
    exit();
}

// Check for flash messages
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $message_type = $_SESSION['flash_message_type'] ?? 'info';
    unset($_SESSION['flash_message'], $_SESSION['flash_message_type']);
} else {
    $message = '';
    $message_type = '';
}

// Fetch data for the page
$suppliers = getAllSuppliers();
$inventoryItems = getInventory();
$purchaseOrders = getRecentPurchaseOrders();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <script>document.documentElement.classList.add('preload', 'loading');</script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logistics 1 - PSM</title>
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
      <?php include '../partials/header.php'; ?>
      <h1 class="font-semibold page-title">Procurement & Sourcing</h1>
      
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        <div class="xl:col-span-3 bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl p-6 shadow-sm">
          <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-semibold text-[var(--text-color)]">Supplier Management</h2>
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <button type="button" class="btn-primary" onclick="openCreateSupplierModal()">
              <i data-lucide="user-plus" class="w-5 h-5 lg:mr-2 sm:mr-0"></i><span class="hidden sm:inline">Add Supplier</span>
            </button>
            <?php endif; ?>
          </div>
          <div class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Supplier Name</th>
                  <th>Contact Person</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <?php if ($_SESSION['role'] === 'admin'): ?><th>Action</th><?php endif; ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach($suppliers as $supplier): ?>
                <tr>
                  <td class="py-3 px-4 border-b border-[var(--card-border)]"><?php echo htmlspecialchars($supplier['supplier_name']); ?></td>
                  <td class="py-3 px-4 border-b border-[var(--card-border)]"><?php echo htmlspecialchars($supplier['contact_person']); ?></td>
                  <td class="py-3 px-4 border-b border-[var(--card-border)]"><?php echo htmlspecialchars($supplier['email']); ?></td>
                  <td class="py-3 px-4 border-b border-[var(--card-border)]"><?php echo htmlspecialchars($supplier['phone']); ?></td>
                  <?php if ($_SESSION['role'] === 'admin'): ?>
                  <td class="py-3 px-4 border-b border-[var(--card-border)]">
                    <div class="relative">
                      <button type="button" class="action-dropdown-btn p-2 rounded-full transition-colors" onclick="toggleSupplierDropdown(<?php echo $supplier['id']; ?>)">
                        <i data-lucide="more-horizontal" class="w-6 h-6"></i>
                      </button>
                      <div id="supplier-dropdown-<?php echo $supplier['id']; ?>" class="action-dropdown hidden">
                        <button type="button" onclick='openEditSupplierModal(<?php echo json_encode($supplier); ?>)'>
                          <i data-lucide="edit-3" class="w-5 h-5 mr-3"></i>
                          Edit
                        </button>
                        <button type="button" onclick="confirmDeleteSupplier(<?php echo $supplier['id']; ?>)" class="text-red-600">
                          <i data-lucide="trash-2" class="w-5 h-5 mr-3"></i>
                          Delete
                        </button>
                      </div>
                    </div>
                  </td>
                  <?php endif; ?>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        
        <div class="xl:col-span-3 bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl p-6 shadow-sm">
          <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-semibold text-[var(--text-color)]">Recent Purchase Orders</h2>
            <button type="button" id="createPOBtn" class="btn-primary">
              <i data-lucide="shopping-cart" class="w-5 h-5 lg:mr-2 sm:mr-0"></i><span class="hidden sm:inline">Create PO</span>
            </button>
          </div>
          <div class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Supplier</th>
                  <th>Item</th>
                  <th>Qty</th>
                  <th>Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($purchaseOrders as $po): ?>
                <tr>
                  <td class="py-3 px-4 border-b border-[var(--card-border)]"><?php echo htmlspecialchars($po['supplier_name']); ?></td>
                  <td class="py-3 px-4 border-b border-[var(--card-border)]"><?php echo htmlspecialchars($po['item_name']); ?></td>
                  <td class="py-3 px-4 border-b border-[var(--card-border)]"><?php echo htmlspecialchars($po['quantity']); ?></td>
                  <td class="py-3 px-4 border-b border-[var(--card-border)]"><span class="bg-amber-400 text-gray-800 py-1 px-2 rounded-xl text-xs font-medium"><?php echo htmlspecialchars($po['status']); ?></span></td>
                  <td class="py-3 px-4 border-b border-[var(--card-border)]"><?php echo date('M d, Y', strtotime($po['order_date'])); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php if ($_SESSION['role'] === 'admin'): ?>
  <div id="supplierModal" class="modal hidden">
    <div class="modal-content p-8 max-w-2xl">
      <div class="flex justify-between items-center mb-2">
        <h2 id="modalTitle" class="modal-title flex items-center min-w-0 flex-1">
          <i data-lucide="building" class="w-6 h-6 mr-3 flex-shrink-0" id="supplierModalIcon"></i>
          <span id="supplierModalTitleText" class="truncate">Add New Supplier</span>
        </h2>
        <button type="button" class="close-button flex-shrink-0 ml-3" onclick="closeModal('supplierModal')">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      <p class="modal-subtitle" id="supplierModalSubtitle">Register a new supplier to your network.</p>
      <div class="border-b border-[var(--card-border)] mb-5"></div>
      
      <form id="supplierForm" method="POST" action="procurement_sourcing.php">
        <input type="hidden" name="action" id="formAction">
        <input type="hidden" name="supplier_id" id="supplierId">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div class="mb-5">
            <label for="supplier_name" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Supplier Name</label>
            <input type="text" name="supplier_name" id="supplier_name" required class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" placeholder="Enter legal company name">
          </div>
          
          <div class="mb-5">
            <label for="contact_person" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Contact Person</label>
            <input type="text" name="contact_person" id="contact_person" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" placeholder="Enter contact person name">
          </div>
          
          <div class="mb-5">
            <label for="email" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Email</label>
            <input type="email" name="email" id="email" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" placeholder="contact@example">
          </div>
          
          <div class="mb-5">
            <label for="phone" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Phone</label>
            <input type="tel" name="phone" id="phone" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" placeholder="Enter phone number">
          </div>
        </div>
        
        <div class="mb-6">
          <label for="address" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Address</label>
          <textarea name="address" id="address" rows="3" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" placeholder="Street Address, City, Postal Code"></textarea>
        </div>
        
        <div class="flex justify-end gap-3">
          <button type="button" class="px-5 py-2.5 rounded-md border border-gray-300 cursor-pointer font-semibold transition-all duration-300 bg-gray-100 text-gray-700 hover:bg-gray-200" onclick="closeModal(document.getElementById('supplierModal'))">
            Cancel
          </button>
          <button type="submit" class="btn-primary">
            Save Supplier
          </button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- Create Purchase Order Modal -->
  <div id="createPOModal" class="modal hidden">
    <div class="modal-content p-8 max-w-lg">
      <div class="flex justify-between items-center mb-2">
        <h2 class="modal-title flex items-center min-w-0 flex-1">
          <i data-lucide="shopping-cart" class="w-6 h-6 mr-3 flex-shrink-0"></i>
          <span class="truncate">Create Purchase Order</span>
        </h2>
        <button type="button" class="close-button flex-shrink-0 ml-3" onclick="closeModal('createPOModal')">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      <p class="modal-subtitle">Create a purchase order.</p>
      <div class="border-b border-[var(--card-border)] mb-5"></div>
      
      <form action="procurement_sourcing.php" method="POST" id="createPOForm">
        <input type="hidden" name="action" value="create_po">
        
        <div class="mb-5">
          <label for="supplier_id_po" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Supplier</label>
          <select name="supplier_id_po" id="supplier_id_po" required class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
            <option value="">-- Select Supplier --</option>
            <?php foreach($suppliers as $supplier): ?>
              <option value="<?php echo $supplier['id']; ?>"><?php echo htmlspecialchars($supplier['supplier_name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="mb-5">
          <label for="item_name_po" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Item</label>
          <select name="item_name_po" id="item_name_po" required class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
            <option value="">-- Select Item --</option>
            <?php foreach($inventoryItems as $item): ?>
              <option value="<?php echo htmlspecialchars($item['item_name']); ?>"><?php echo htmlspecialchars($item['item_name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="mb-6">
          <label for="quantity_po" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Quantity</label>
          <input type="number" name="quantity_po" id="quantity_po" min="1" required class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" placeholder="Enter quantity to order">
        </div>
        
        <div class="flex justify-end gap-3">
          <button type="button" class="px-5 py-2.5 rounded-md border border-gray-300 cursor-pointer font-semibold transition-all duration-300 bg-gray-100 text-gray-700 hover:bg-gray-200" onclick="closeModal(document.getElementById('createPOModal'))">
            Cancel
          </button>
          <button type="submit" class="btn-primary">
            Create PO
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="../assets/js/sidebar.js"></script>
  <script src="../assets/js/script.js"></script>
  <script src="../assets/js/procurement.js"></script>
  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
  
  <?php if ($message && !empty(trim($message))): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.showCustomAlert) {
            showCustomAlert(<?php echo json_encode($message); ?>, <?php echo json_encode($message_type); ?>);
        } else {
            // Fallback - strip HTML for plain alert
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = <?php echo json_encode($message); ?>;
            alert(tempDiv.textContent || tempDiv.innerText || '');
        }
    });
  </script>
  <?php endif; ?>
</body>
</html>