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
        <div style="background: var(--card-bg); border: 1px solid var(--card-border);" class="p-6 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold" style="color: var(--text-color);">Manage Purchase Orders</h2>
                <button class="btn-primary" onclick="window.openModal(document.getElementById('createPOModal'))">
                    <i data-lucide="shopping-cart" class="w-5 h-5 mr-2"></i>Create New PO
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
        <div style="background: var(--card-bg); border: 1px solid var(--card-border);" class="p-6 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold" style="color: var(--text-color);">Manage Suppliers</h2>
                <button class="btn-primary" onclick="openCreateSupplierModal()">
                   <i data-lucide="workflow" class="w-5 h-5 mr-2"></i>Add Supplier
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

  <script src="../assets/js/custom-alerts.js"></script>
  <script src="../assets/js/sidebar.js"></script>
  <script src="../assets/js/script.js"></script>
  <script src="../assets/js/procurement.js"></script>
  <script>
    lucide.createIcons();
    
    // Display flash message if present
    <?php if ($message): ?>
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
    <?php endif; ?>

    // Store current modal state for UI updates
    let currentPOId = null;
    let currentPOStatus = null;

    async function openViewBidsModal(po_id, bids = null, po_status = null) {
        const modal = document.getElementById('viewBidsModal');
        const container = document.getElementById('bidsContainer');
        const analysisContainer = document.getElementById('bidsAnalysisContainer') || document.createElement('div');
        analysisContainer.id = 'bidsAnalysisContainer';
        analysisContainer.className = 'bg-blue-50 border border-blue-200 text-blue-800 rounded-lg p-4 mb-4';
        analysisContainer.innerHTML = '<div class="flex items-center"><i class="fas fa-spinner fa-spin text-xl mr-3"></i><p>Analyzing supplier performance with AI...</p></div>';

        const modalContent = modal.querySelector('.modal-content');
        if (!document.getElementById('bidsAnalysisContainer')) {
            modalContent.insertBefore(analysisContainer, container);
        }
        
        // Remove the manual button if it exists
        const oldButton = document.getElementById('analyzeSuppliersBtn');
        if (oldButton) {
            oldButton.remove();
        }
        
        container.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i><p class="mt-2 text-gray-500">Loading bids...</p></div>';
        
        if (window.openModal) {
            window.openModal(modal);
        }

        // --- Automatically fetch the analysis ---
        getSupplierAnalysis(po_id); 
        
        try {
            const response = await fetch(`../includes/ajax/get_bids.php?po_id=${po_id}`);
            const result = await response.json();
            
            if (!result.success) throw new Error(result.message);
            
            const freshBids = result.bids;
            currentPOId = po_id;
            currentPOStatus = result.po_status;
            
            container.innerHTML = '';
            if (!freshBids || freshBids.length === 0) {
                container.innerHTML = '<p class="text-[var(--placeholder-color)] text-center py-8">No bids have been submitted for this item yet.</p>';
            } else {
                freshBids.forEach(bid => {
                    const isAwarded = bid.status === 'Awarded';
                    const isRejected = bid.status === 'Rejected';
                    const bidElement = document.createElement('div');
                    bidElement.className = `border rounded-lg p-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 border-[var(--card-border)] bg-[var(--card-bg)]`;

                    let actionButtons = '';
                    if (currentPOStatus === 'Open for Bidding' && bid.status === 'Pending') {
                        actionButtons = `
                            <div class="flex gap-3 justify-end sm:justify-start">
                                <button type="button" class="btn-primary" onclick="awardBid(${bid.id}, ${po_id}, ${bid.supplier_id}, this)">
                                    <span class="button-text">Award</span>
                                    <span class="button-spinner hidden"><i class="fas fa-spinner fa-spin"></i></span>
                                </button>
                                <button type="button" class="btn-primary-danger" onclick="rejectBid(${bid.id}, this)">
                                    <span class="button-text">Reject</span>
                                    <span class="button-spinner hidden"><i class="fas fa-spinner fa-spin"></i></span>
                                </button>
                            </div>`;
                    } else if (isAwarded) {
                        actionButtons = `<div class="flex justify-end sm:justify-start"><span class="px-2 py-1 font-semibold leading-tight text-xs rounded-full bg-green-100 text-green-700">AWARDED</span></div>`;
                    } else if (isRejected) {
                         actionButtons = `<div class="flex justify-end sm:justify-start"><span class="px-2 py-1 font-semibold leading-tight text-xs rounded-full bg-red-100 text-red-700">REJECTED</span></div>`;
                    }

                    bidElement.innerHTML = `
                        <div class="flex-1 space-y-2">
                            <p class="font-bold text-lg text-[var(--text-color)]">${bid.supplier_name}</p>
                            <p class="text-2xl font-light ${isAwarded ? 'text-green-700' : 'text-[var(--text-color)]'}">₱${parseFloat(bid.bid_amount).toFixed(2)}</p>
                            <p class="text-sm text-[var(--text-color)] mt-2"><em>${bid.notes || 'No notes provided.'}</em></p>
                        </div>
                        <div class="flex-shrink-0">${actionButtons}</div>`;
                    container.appendChild(bidElement);
                });
            }
        } catch (error) {
            console.error('Error loading bids:', error);
            container.innerHTML = `<div class="text-center py-8 text-red-500"><i class="fas fa-exclamation-triangle text-2xl mb-2"></i><p>Failed to load bids: ${error.message}</p></div>`;
        }
    }

    // AJAX function to award a bid
    async function awardBid(bidId, poId, supplierId, buttonElement) {
        const buttonText = buttonElement.querySelector('.button-text');
        const buttonSpinner = buttonElement.querySelector('.button-spinner');
        
        // Show loading state
        buttonText.classList.add('hidden');
        buttonSpinner.classList.remove('hidden');
        buttonElement.disabled = true;
        
        try {
            const response = await fetch('../includes/ajax/bid_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'award_bid',
                    bid_id: bidId,
                    po_id: poId,
                    supplier_id: supplierId
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Show success message
                if (window.showCustomAlert) {
                    showCustomAlert(result.message, 'success');
                }
                
                // Update the bid element to show "AWARDED" status
                updateBidStatus(bidId, 'Awarded');
                
                // Update other pending bids in the UI to show as rejected
                updateOtherBidsToRejected(bidId);
                
            } else {
                throw new Error(result.message || 'Failed to award bid');
            }
        } catch (error) {
            console.error('Error awarding bid:', error);
            if (window.showCustomAlert) {
                showCustomAlert(error.message || 'Failed to award bid. Please try again.', 'error');
            } else {
                alert('Failed to award bid. Please try again.');
            }
        } finally {
            // Reset button state
            buttonText.classList.remove('hidden');
            buttonSpinner.classList.add('hidden');
            buttonElement.disabled = false;
        }
    }

    // AJAX function to reject a bid
    async function rejectBid(bidId, buttonElement) {
        const buttonText = buttonElement.querySelector('.button-text');
        const buttonSpinner = buttonElement.querySelector('.button-spinner');
        
        // Show loading state
        buttonText.classList.add('hidden');
        buttonSpinner.classList.remove('hidden');
        buttonElement.disabled = true;
        
        try {
            const response = await fetch('../includes/ajax/bid_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'reject_bid',
                    bid_id: bidId
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Show success message
                if (window.showCustomAlert) {
                    showCustomAlert(result.message, 'success');
                }
                
                // Update the bid element to show "REJECTED" status
                updateBidStatus(bidId, 'Rejected');
                
            } else {
                throw new Error(result.message || 'Failed to reject bid');
            }
        } catch (error) {
            console.error('Error rejecting bid:', error);
            if (window.showCustomAlert) {
                showCustomAlert(error.message || 'Failed to reject bid. Please try again.', 'error');
            } else {
                alert('Failed to reject bid. Please try again.');
            }
        } finally {
            // Reset button state
            buttonText.classList.remove('hidden');
            buttonSpinner.classList.add('hidden');
            buttonElement.disabled = false;
        }
    }

    // Update bid status in the modal UI
    function updateBidStatus(bidId, newStatus) {
        const bidsContainer = document.getElementById('bidsContainer');
        const bidElements = bidsContainer.children;
        
        for (let i = 0; i < bidElements.length; i++) {
            const bidElement = bidElements[i];
            const actionDiv = bidElement.querySelector('.flex.gap-3');
            
            if (actionDiv) {
                const buttons = actionDiv.querySelectorAll('button');
                let isBidElement = false;
                
                // Check if this is the correct bid by looking at the onclick attributes
                buttons.forEach(button => {
                    const onclick = button.getAttribute('onclick');
                    if (onclick && onclick.includes(`(${bidId},`)) {
                        isBidElement = true;
                    }
                });
                
                if (isBidElement) {
                    // Replace action buttons with status badge
                    const isAwarded = newStatus === 'Awarded';
                    const statusClass = isAwarded ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
                    const statusText = isAwarded ? 'AWARDED' : 'REJECTED';
                    
                    actionDiv.innerHTML = `<span class="px-2 py-1 font-semibold leading-tight text-xs rounded-full ${statusClass} status-badge-transition">${statusText}</span>`;
                    
                    // Update price color if awarded
                    if (isAwarded) {
                        const priceElement = bidElement.querySelector('.text-2xl.font-light');
                        if (priceElement) {
                            priceElement.classList.add('text-green-700');
                        }
                    }
                    break;
                }
            }
        }
    }

    // When a bid is awarded, update other pending bids in the UI to show as rejected
    function updateOtherBidsToRejected(awardedBidId) {
        const bidsContainer = document.getElementById('bidsContainer');
        const bidElements = bidsContainer.children;
        
        for (let i = 0; i < bidElements.length; i++) {
            const bidElement = bidElements[i];
            const actionDiv = bidElement.querySelector('.flex.gap-3');
            
            if (actionDiv) {
                const buttons = actionDiv.querySelectorAll('button');
                let bidId = null;
                
                // Get the bid ID from button onclick attributes
                buttons.forEach(button => {
                    const onclick = button.getAttribute('onclick');
                    if (onclick && onclick.includes('(')) {
                        const match = onclick.match(/\((\d+),/);
                        if (match) {
                            bidId = parseInt(match[1]);
                        }
                    }
                });
                
                // If this is a different bid with pending status, mark as rejected
                if (bidId && bidId != awardedBidId && buttons.length > 0) {
                    updateBidStatus(bidId, 'Rejected');
                }
            }
        }
    }

    async function getSupplierAnalysis(po_id) {
        const analysisContainer = document.getElementById('bidsAnalysisContainer');
        
        try {
            const response = await fetch(`../includes/ajax/analyze_supplier.php?po_id=${po_id}`);
            const result = await response.json();

            if (result.success) {
                // Format the response for display
                analysisContainer.innerHTML = `<p class="font-bold">AI Analysis:</p><p>${result.analysis.replace(/\\n/g, '<br>')}</p>`;
            } else {
                analysisContainer.innerHTML = `<p class="text-red-500 font-bold">Error:</p><p>${result.error}</p>`;
            }
        } catch (error) {
            analysisContainer.innerHTML = '<p class="text-red-500 font-bold">An error occurred while fetching the analysis.</p>';
            console.error('Analysis error:', error);
        }
    }
    
  </script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</body>
</html>