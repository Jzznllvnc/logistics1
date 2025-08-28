<?php
require_once '../includes/functions/auth.php';
require_once '../includes/functions/bids.php';
require_once '../includes/functions/notifications.php'; // Required for header
requireLogin();

// Handle AJAX request to mark notifications as read
if (isset($_GET['mark_notifications_as_read']) && $_GET['mark_notifications_as_read'] === 'true') {
    header('Content-Type: application/json');
    $supplier_id = getSupplierIdFromUsername($_SESSION['username']);
    if (markAllNotificationsAsRead($supplier_id)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

// Handle logout action
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
}

// Security: Ensure only suppliers can access this page
if ($_SESSION['role'] !== 'supplier') {
    header("Location: dashboard.php");
    exit();
}

// Handle Form Submission for Placing a Bid
$message = '';
$message_type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_bid') {
    $po_id = $_POST['po_id'] ?? 0;
    $bid_amount = $_POST['bid_amount'] ?? 0;
    $notes = $_POST['notes'] ?? '';
    
    // Get the logged-in supplier's ID
    $supplier_id = getSupplierIdFromUsername($_SESSION['username']);

    if ($supplier_id && createBid($po_id, $supplier_id, $bid_amount, $notes)) {
        $_SESSION['message'] = 'Your bid has been submitted successfully! We\'ll notify you once the procurement team reviews it.';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Failed to submit your bid. Please try again or contact support if the issue persists.';
        $_SESSION['message_type'] = 'error';
    }
    // Redirect to prevent form resubmission
    header("Location: supplier_bidding.php");
    exit();
}

// Check for flash messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}

// Fetch Data for the Page
$open_purchase_orders = getOpenForBiddingPOs();
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bidding Portal - SLATE Logistics</title>
    <link rel="icon" href="../assets/images/slate2.png" type="image/png">
    <link rel="stylesheet" href="../assets/css/supplier_portal.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body>
    <div class="supplier-content-full">
        <?php include '../partials/supplier_header.php'; ?>

        <div class="supplier-content-area">
            <!-- Navigation Tabs -->
            <div class="supplier-tabs-container">
                <div class="supplier-tabs-bar">
                    <a href="supplier_dashboard.php" class="supplier-tab-button <?php echo ($currentPage === 'supplier_dashboard.php') ? 'active' : ''; ?>">
                        <i data-lucide="layout-dashboard" class="tab-icon"></i>
                        Dashboard
                    </a>
                    <a href="supplier_bidding.php" class="supplier-tab-button <?php echo ($currentPage === 'supplier_bidding.php') ? 'active' : ''; ?>">
                        <i data-lucide="gavel" class="tab-icon"></i>
                        Open Bids
                    </a>
                    <a href="supplier_bid_history.php" class="supplier-tab-button <?php echo ($currentPage === 'supplier_bid_history.php') ? 'active' : ''; ?>">
                        <i data-lucide="history" class="tab-icon"></i>
                        Bid History
                    </a>
                </div>
            </div>

            <div class="supplier-content-container">
                <div class="content-card">
                    <h2 class="content-title">Open for Bidding</h2>
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Quantity</th>
                                    <th>Date Posted</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($open_purchase_orders)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-10 text-gray-500">
                                            <div class="flex flex-col items-center gap-3">
                                                <i data-lucide="gavel" class="w-12 h-12 text-gray-300"></i>
                                                <p>There are no purchase orders currently open for bidding.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: foreach($open_purchase_orders as $po): ?>
                                <tr>
                                    <td class="font-semibold"><?php echo htmlspecialchars($po['item_name']); ?></td>
                                    <td><?php echo $po['quantity']; ?></td>
                                    <td class="text-gray-600"><?php echo date("F j, Y", strtotime($po['order_date'])); ?></td>
                                    <td class="text-right">
                                        <button onclick="openBidModal(<?php echo $po['id']; ?>, '<?php echo htmlspecialchars(addslashes($po['item_name'])); ?>')" class="btn-primary">
                                            <i data-lucide="gavel" class="w-4 h-4"></i>
                                            Place Bid
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
    <?php include 'modals/supplier.php'; ?>
    
    <script src="../assets/js/custom-alerts.js"></script>
    
    <?php if ($message && !empty(trim($message))): ?>
    <script>
        // Wait for both DOM and custom alerts to be ready
        function tryShowCustomAlert() {
            if (document.body && window.customAlert && typeof window.customAlert.show === 'function') {
                window.customAlert.show(
                    <?php echo json_encode($message); ?>, 
                    <?php echo json_encode($message_type); ?>, 
                    5000
                );
            } else if (document.body && window.showCustomAlert && typeof window.showCustomAlert === 'function') {
                window.showCustomAlert(
                    <?php echo json_encode($message); ?>, 
                    <?php echo json_encode($message_type); ?>
                );
            } else {
                // Retry after a short delay if not ready
                setTimeout(tryShowCustomAlert, 50);
            }
        }

        // Start when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', tryShowCustomAlert);
        } else {
            tryShowCustomAlert();
        }
    </script>
    <?php endif; ?>
    
    <script src="../assets/js/supplier_portal.js"></script>
</body>
</html>