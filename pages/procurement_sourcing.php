<?php
require_once '../includes/functions/auth.php';
require_once '../includes/functions/supplier.php';
require_once '../includes/functions/purchase_order.php';
require_once '../includes/functions/inventory.php'; // For item list in modal
require_once '../includes/functions/bids.php';     // For handling bids
requireLogin();

// Role check for admin/procurement
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'procurement') {
    header("Location: dashboard.php");
    exit();
}

// Handle all form submissions for this page
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- Actions for both Admin & Procurement ---
    if ($action === 'create_po') {
        $itemName = $_POST['item_name_po'] ?? '';
        $quantity = $_POST['quantity_po'] ?? 0;
        if (createPurchaseOrder(null, $itemName, $quantity)) {
             $_SESSION['flash_message'] = "Purchase Order for <strong>" . htmlspecialchars($itemName) . "</strong> created and is pending approval.";
             $_SESSION['flash_message_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = "Failed to create Purchase Order.";
            $_SESSION['flash_message_type'] = 'error';
        }
    } elseif ($action === 'open_for_bidding') {
        $po_id = $_POST['po_id'] ?? 0;
        if (openPOForBidding($po_id)) {
            $_SESSION['flash_message'] = "Purchase Order #$po_id is now open for bidding.";
            $_SESSION['flash_message_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = "Failed to open PO for bidding.";
            $_SESSION['flash_message_type'] = 'error';
        }
    } elseif ($action === 'award_bid') {
        $po_id = $_POST['po_id'] ?? 0;
        $supplier_id = $_POST['supplier_id'] ?? 0;
        $bid_id = $_POST['bid_id'] ?? 0;
        if (awardPOToSupplier($po_id, $supplier_id, $bid_id)) {
            $_SESSION['flash_message'] = "Bid #$bid_id has been awarded for PO #$po_id.";
            $_SESSION['flash_message_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = "Failed to award the bid.";
            $_SESSION['flash_message_type'] = 'error';
        }
    } elseif ($action === 'reject_bid') {
        $bid_id = $_POST['bid_id'] ?? 0;
        if (rejectBid($bid_id)) {
            $_SESSION['flash_message'] = "Bid #$bid_id has been rejected.";
            $_SESSION['flash_message_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = "Failed to reject the bid.";
            $_SESSION['flash_message_type'] = 'error';
        }
    }

    // --- Admin-Only Actions for Supplier Management ---
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
                    $_SESSION['flash_message'] = "Failed to create supplier.";
                    $_SESSION['flash_message_type'] = 'error';
                }
            } else {
                $id = $_POST['supplier_id'] ?? 0;
                if (updateSupplier($id, $name, $contact, $email, $phone, $address)) {
                    $_SESSION['flash_message'] = "Supplier <strong>" . htmlspecialchars($name) . "</strong> updated successfully.";
                    $_SESSION['flash_message_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = "Failed to update supplier.";
                    $_SESSION['flash_message_type'] = 'error';
                }
            }
        } elseif ($action === 'delete_supplier') {
            $id = $_POST['supplier_id'] ?? 0;
            if (deleteSupplier($id)) {
                $_SESSION['flash_message'] = "Supplier deleted successfully.";
                $_SESSION['flash_message_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = "Failed to delete supplier.";
                $_SESSION['flash_message_type'] = 'error';
            }
        }
    }

    header("Location: procurement_sourcing.php");
    exit();
}

// Fetch data for the page
$suppliers = getAllSuppliers();
$inventoryItems = getInventory();
$purchaseOrders = getRecentPurchaseOrders(50);
$bids_by_po = [];
foreach ($purchaseOrders as $po) {
    if ($po['status'] === 'Open for Bidding' || $po['status'] === 'Awarded') {
        $bids_by_po[$po['id']] = getBidsForPO($po['id']);
    }
}
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
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body>
  <div class="sidebar" id="sidebar"> <?php include '../partials/sidebar.php'; ?> </div>
  <div class="main-content-wrapper" id="mainContentWrapper">
    <div class="content" id="mainContent">
      <?php include '../partials/header.php'; ?>
      <h1 class="font-semibold page-title">Procurement & Sourcing</h1>

       <div class="tabs-container mb-3">
        <div class="tabs-bar">
          <button class="tab-button active" data-tab="purchase-orders">
            <i data-lucide="shopping-cart" class="w-4 h-4 mr-2"></i>
            Purchase Orders
          </button>
           <?php if ($_SESSION['role'] === 'admin'): ?>
          <button class="tab-button" data-tab="suppliers">
            <i data-lucide="waypoints" class="w-4 h-4 mr-2"></i>
            Suppliers
          </button>
          <?php endif; ?>
        </div>
      </div>

      <div class="tab-content active" id="purchase-orders-tab">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-gray-800">Manage Purchase Orders</h2>
                <button class="btn-primary" onclick="window.openModal(document.getElementById('createPOModal'))">
                    <i data-lucide="plus" class="w-5 h-5 mr-2"></i>Create New PO
                </button>
            </div>

            <div class="table-container">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>PO ID</th>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Order Date</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($purchaseOrders)): ?>
                    <tr><td colspan="6" class="table-empty">No purchase orders found.</td></tr>
                  <?php else: foreach ($purchaseOrders as $po): ?>
                      <tr>
                        <td>#<?php echo $po['id']; ?></td>
                        <td><?php echo htmlspecialchars($po['item_name']); ?></td>
                        <td><?php echo $po['quantity']; ?></td>
                        <td>
                            <span class="px-2 py-1 font-semibold leading-tight text-xs rounded-full
                                <?php if ($po['status'] === 'Pending') echo 'bg-yellow-100 text-yellow-700';
                                      elseif ($po['status'] === 'Open for Bidding') echo 'bg-blue-100 text-blue-700';
                                      elseif ($po['status'] === 'Awarded') echo 'bg-green-100 text-green-700';
                                      else echo 'bg-gray-100 text-gray-700'; ?>">
                                <?php echo htmlspecialchars($po['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date("M j, Y", strtotime($po['order_date'])); ?></td>
                        <td>
                            <?php if ($po['status'] === 'Pending'): ?>
                                <form method="POST" class="form-no-margin">
                                    <input type="hidden" name="po_id" value="<?php echo $po['id']; ?>">
                                    <button type="submit" name="action" value="open_for_bidding" class="btn-primary btn-small">Open for Bidding</button>
                                </form>
                            <?php elseif ($po['status'] === 'Open for Bidding' || $po['status'] === 'Awarded'): ?>
                                <button class="btn-primary btn-small" onclick='openViewBidsModal(<?php echo $po["id"]; ?>, <?php echo json_encode($bids_by_po[$po["id"]] ?? []); ?>, "<?php echo $po["status"]; ?>")'>
                                    View Bids (<?php echo count($bids_by_po[$po['id']] ?? []); ?>)
                                </button>
                            <?php endif; ?>
                        </td>
                      </tr>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>
        </div>
      </div>

      <?php if ($_SESSION['role'] === 'admin'): ?>
      <div class="tab-content" id="suppliers-tab">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-gray-800">Manage Suppliers</h2>
                <button class="btn-primary" onclick="openCreateSupplierModal()">
                   <i data-lucide="plus" class="w-5 h-5 mr-2"></i>Add Supplier
                </button>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr><th>Supplier Name</th><th>Contact Person</th><th>Email</th><th>Phone</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($suppliers as $supplier): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($supplier['supplier_name']); ?></td>
                            <td><?php echo htmlspecialchars($supplier['contact_person']); ?></td>
                            <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                            <td><?php echo htmlspecialchars($supplier['phone']); ?></td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="text-blue-500 hover:text-blue-700" onclick='openEditSupplierModal(<?php echo json_encode($supplier); ?>)'><i data-lucide="edit-3" class="w-5 h-5"></i></button>
                                    <form method="POST" class="form-no-margin" onsubmit="return confirm('Are you sure you want to delete this supplier?');">
                                        <input type="hidden" name="action" value="delete_supplier">
                                        <input type="hidden" name="supplier_id" value="<?php echo $supplier['id']; ?>">
                                        <button type="submit" class="text-red-500 hover:text-red-700"><i data-lucide="trash-2" class="w-5 h-5"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>

  <?php include 'modals/psm.php'; ?>
  <div id="viewBidsModal" class="modal hidden">
    <div class="modal-content p-8 max-w-2xl">
        <div class="flex justify-between items-center mb-4">
            <h2 class="modal-title">Review Bids</h2>
            <button type="button" class="close-button" onclick="window.closeModal(document.getElementById('viewBidsModal'))"><i data-lucide="x"></i></button>
        </div>
        <div id="bidsContainer" class="space-y-4 max-h-96 overflow-y-auto"></div>
        <div class="flex justify-end mt-6">
            <button type="button" class="btn bg-gray-200" onclick="window.closeModal(document.getElementById('viewBidsModal'))">Close</button>
        </div>
    </div>
  </div>

  <script src="../assets/js/sidebar.js"></script>
  <script src="../assets/js/script.js"></script>
  <script src="../assets/js/procurement.js"></script>
  <script>
    lucide.createIcons();

    function openViewBidsModal(po_id, bids, po_status) {
        const modal = document.getElementById('viewBidsModal');
        const container = document.getElementById('bidsContainer');
        container.innerHTML = '';

        if (!bids || bids.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-center py-8">No bids have been submitted for this item yet.</p>';
        } else {
            bids.forEach(bid => {
                const isAwarded = bid.status === 'Awarded';
                const isRejected = bid.status === 'Rejected';
                const bidElement = document.createElement('div');
                let bgColor = 'border-gray-200';
                if (isAwarded) bgColor = 'bg-green-50 border-green-200';
                if (isRejected) bgColor = 'bg-red-50 border-red-200';

                bidElement.className = `border rounded-lg p-4 flex justify-between items-center ${bgColor}`;

                let actionButtons = '';
                if (po_status === 'Open for Bidding' && bid.status === 'Pending') {
                    actionButtons = `
                        <div class="flex gap-2">
                            <form method="POST" class="form-no-margin">
                                <input type="hidden" name="action" value="award_bid">
                                <input type="hidden" name="po_id" value="${po_id}">
                                <input type="hidden" name="supplier_id" value="${bid.supplier_id}">
                                <input type="hidden" name="bid_id" value="${bid.id}">
                                <button type="submit" class="btn-primary btn-small">Award</button>
                            </form>
                            <form method="POST" class="form-no-margin">
                                <input type="hidden" name="action" value="reject_bid">
                                <input type="hidden" name="bid_id" value="${bid.id}">
                                <button type="submit" class="btn btn-danger btn-small">Reject</button>
                            </form>
                        </div>
                    `;
                } else if (isAwarded) {
                    actionButtons = `<span class="font-bold text-green-600">AWARDED</span>`;
                } else if (isRejected) {
                     actionButtons = `<span class="font-bold text-red-600">REJECTED</span>`;
                }

                bidElement.innerHTML = `
                    <div>
                        <p class="font-bold text-lg">${bid.supplier_name}</p>
                        <p class="text-2xl font-light ${isAwarded ? 'text-green-600' : 'text-gray-800'}">$${parseFloat(bid.bid_amount).toFixed(2)}</p>
                        <p class="text-sm text-gray-600 mt-1"><em>${bid.notes || 'No notes provided.'}</em></p>
                    </div>
                    ${actionButtons}
                `;
                container.appendChild(bidElement);
            });
        }

        if (window.openModal) {
            window.openModal(modal);
        }
    }
  </script>
</body>
</html>