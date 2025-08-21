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
    
    // Supplier Actions
    if ($action === 'create_supplier' || $action === 'update_supplier') {
        $name = $_POST['supplier_name'] ?? '';
        $contact = $_POST['contact_person'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';
        if ($action === 'create_supplier') {
            createSupplier($name, $contact, $email, $phone, $address);
        } else {
            $id = $_POST['supplier_id'] ?? 0;
            updateSupplier($id, $name, $contact, $email, $phone, $address);
        }
    } elseif ($action === 'delete_supplier') {
        $id = $_POST['supplier_id'] ?? 0;
        deleteSupplier($id);
    }

    // Purchase Order Action
    if ($action === 'create_po') {
        $supplier_id = $_POST['supplier_id_po'] ?? 0;
        $item_name = $_POST['item_name_po'] ?? '';
        $quantity = $_POST['quantity_po'] ?? 0;
        createPurchaseOrder($supplier_id, $item_name, $quantity);
    }
    
    header("Location: procurement_sourcing.php");
    exit();
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
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="stylesheet" href="../assets/css/sidebar.css">
  <style>
    .psm-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; }
    .card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .card-title { font-size: 1.5rem; font-weight: 600; margin-bottom: 20px; }
    .full-width { grid-column: 1 / -1; } /* Span all 3 columns */
    .table { width: 100%; border-collapse: collapse; }
    .table th, .table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--card-border); }
    .table th { background-color: rgba(128,128,128,0.05); text-transform: uppercase; font-size: 13px; }
    .actions a { margin-right: 15px; cursor: pointer; }
    .actions a.edit:hover { color: #3b82f6; }
    .actions a.delete:hover { color: #ef4444; }
    .btn-add { background: var(--primary-btn-bg); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block; margin-bottom: 20px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: 500; margin-bottom: 5px; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid var(--input-border); border-radius: 6px; background: var(--input-bg); color: var(--input-text); }
    @media (max-width: 1200px) { .psm-grid { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 768px) { .psm-grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <div class="sidebar" id="sidebar"> <?php include '../partials/sidebar.php'; ?> </div>
  <div class="main-content-wrapper" id="mainContentWrapper">
    <div class="content" id="mainContent">
      <?php include '../partials/header.php'; ?>
      <h1 class="page-title" style="font-family: 'Inter Tight', sans-serif; font-weight: 600; font-size: 2.5rem; margin-bottom: 2rem;">Procurement & Sourcing (PSM)</h1>
      
      <div class="psm-grid">
        <div class="card full-width">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">Supplier Management</h2>
          <button type="button" class="btn-add" onclick="openCreateSupplierModal()"><i class="fas fa-plus"></i> Add Supplier</button>
          </div>
          <table class="table">
            <thead><tr><th>Supplier Name</th><th>Contact Person</th><th>Email</th><th>Phone</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach($suppliers as $supplier): ?>
              <tr>
                <td><?php echo htmlspecialchars($supplier['supplier_name']); ?></td>
                <td><?php echo htmlspecialchars($supplier['contact_person']); ?></td>
                <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                <td><?php echo htmlspecialchars($supplier['phone']); ?></td>
                <td class="actions">
                  <a class="edit" onclick='openEditSupplierModal(<?php echo json_encode($supplier); ?>)'><i class="fas fa-pencil-alt"></i></a>
                  <a class="delete" onclick="confirmDeleteSupplier(<?php echo $supplier['id']; ?>)"><i class="fas fa-trash-alt"></i></a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="card">
          <h2 class="card-title">Create Purchase Order</h2>
          <form action="procurement_sourcing.php" method="POST">
            <input type="hidden" name="action" value="create_po">
            <div class="form-group">
              <label for="supplier_id_po">Supplier</label>
              <select name="supplier_id_po" required>
                <option value="">-- Select Supplier --</option>
                <?php foreach($suppliers as $supplier): ?>
                <option value="<?php echo $supplier['id']; ?>"><?php echo htmlspecialchars($supplier['supplier_name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label for="item_name_po">Item</label>
              <select name="item_name_po" required>
                <option value="">-- Select Item --</option>
                <?php foreach($inventoryItems as $item): ?>
                <option value="<?php echo htmlspecialchars($item['item_name']); ?>"><?php echo htmlspecialchars($item['item_name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label for="quantity_po">Quantity</label>
              <input type="number" name="quantity_po" min="1" required>
            </div>
            <button type="submit" class="btn btn-add" style="width: 100%;">Create PO</button>
          </form>
        </div>
        
        <div class="card" style="grid-column: 2 / -1;">
          <h2 class="card-title">Recent Purchase Orders</h2>
          <table class="table">
            <thead><tr><th>Supplier</th><th>Item</th><th>Qty</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
              <?php foreach($purchaseOrders as $po): ?>
              <tr>
                <td><?php echo htmlspecialchars($po['supplier_name']); ?></td>
                <td><?php echo htmlspecialchars($po['item_name']); ?></td>
                <td><?php echo htmlspecialchars($po['quantity']); ?></td>
                <td><span style="background: #ffc107; color: #333; padding: 3px 8px; border-radius: 12px; font-size: 0.8em;"><?php echo htmlspecialchars($po['status']); ?></span></td>
                <td><?php echo date('M d, Y', strtotime($po['order_date'])); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div id="supplierModal" class="modal" style="display:none;">
    <div class="modal-content bg-[var(--card-bg)] p-8 rounded-lg shadow-xl relative">
      <h2 id="modalTitle" class="text-2xl font-bold mb-4"></h2>
      <form id="supplierForm" method="POST" action="procurement_sourcing.php">
        <input type="hidden" name="action" id="formAction">
        <input type="hidden" name="supplier_id" id="supplierId">
        <div class="form-group"><label>Supplier Name</label><input type="text" name="supplier_name" id="supplier_name" required></div>
        <div class="form-group"><label>Contact Person</label><input type="text" name="contact_person" id="contact_person"></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" id="email"></div>
        <div class="form-group"><label>Phone</label><input type="tel" name="phone" id="phone"></div>
        <div class="form-group"><label>Address</label><textarea name="address" id="address" rows="3"></textarea></div>
        <div class="form-actions flex justify-end gap-4 mt-6">
          <button type="button" class="btn bg-gray-300" onclick="closeModal(document.getElementById('supplierModal'))">Cancel</button>
          <button type="submit" class="btn btn-danger bg-green-500 text-white">Save Supplier</button>
        </div>
      </form>
    </div>
  </div>

  <script src="../assets/js/sidebar.js"></script>
  <script src="../assets/js/script.js"></script>
  <script src="../assets/js/procurement.js"></script>
</body>
</html>