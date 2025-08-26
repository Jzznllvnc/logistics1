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
      
      <!-- Tabs Navigator -->
      <div class="tabs-container mb-3">
        <div class="tabs-bar">
          <button class="tab-button active" data-tab="suppliers">
            <i data-lucide="waypoints" class="w-4 h-4 mr-2"></i>
            Suppliers
          </button>
          <button class="tab-button" data-tab="purchase-orders">
            <i data-lucide="shopping-cart" class="w-4 h-4 mr-2"></i>
            Purchase Orders
          </button>
        </div>
      </div>
      
      <!-- Tab Content -->
      <div class="tab-content active" id="suppliers-tab">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
          <div class="xl:col-span-3 bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl p-6 shadow-sm">
            <div class="flex justify-between items-center mb-5">
              <h2 class="text-2xl font-semibold text-[var(--text-color)]">Supplier Management</h2>
              <?php if ($_SESSION['role'] === 'admin'): ?>
              <button type="button" class="btn-primary" onclick="openCreateSupplierModal()">
                <i data-lucide="workflow" class="w-5 h-5 lg:mr-2 sm:mr-0"></i><span class="hidden sm:inline">Add Supplier</span>
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
                            <i data-lucide="edit-3" class="w-4 h-4 mr-3"></i>
                            Edit
                          </button>
                          <button type="button" onclick="confirmDeleteSupplier(<?php echo $supplier['id']; ?>)">
                            <i data-lucide="trash-2" class="w-4 h-4 mr-3"></i>
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
        </div>
      </div>
      
      <div class="tab-content" id="purchase-orders-tab">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
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
                    <td class="py-3 px-4 border-b border-[var(--card-border)]">
                      <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full font-medium text-sm <?php 
                        $status_class = '';
                        $status_icon = '';
                        switch(strtolower(str_replace(' ', '-', $po['status']))) {
                          case 'pending': 
                            $status_class = 'bg-amber-50 text-amber-700 border border-amber-200'; 
                            $status_icon = 'clock';
                            break;
                          case 'approved': 
                            $status_class = 'bg-blue-50 text-blue-700 border border-blue-200'; 
                            $status_icon = 'check-circle';
                            break;
                          case 'shipped': 
                            $status_class = 'bg-purple-50 text-purple-700 border border-purple-200'; 
                            $status_icon = 'truck';
                            break;
                          case 'delivered': 
                            $status_class = 'bg-emerald-50 text-emerald-700 border border-emerald-200'; 
                            $status_icon = 'package-check';
                            break;
                          case 'cancelled': 
                            $status_class = 'bg-red-50 text-red-700 border border-red-200'; 
                            $status_icon = 'x-circle';
                            break;
                          case 'processing': 
                            $status_class = 'bg-blue-50 text-blue-700 border border-blue-200'; 
                            $status_icon = 'settings';
                            break;
                          default: 
                            $status_class = 'bg-gray-50 text-gray-700 border border-gray-200';
                            $status_icon = 'help-circle';
                        }
                        echo $status_class;
                      ?>">
                        <i data-lucide="<?php echo $status_icon; ?>" class="w-3.5 h-3.5"></i>
                        <?php echo htmlspecialchars($po['status']); ?>
                      </span>
                    </td>
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
  </div>

  <?php include 'modals/psm.php'; ?>

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