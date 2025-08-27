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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_bid') {
    $po_id = $_POST['po_id'] ?? 0;
    $bid_amount = $_POST['bid_amount'] ?? 0;
    $notes = $_POST['notes'] ?? '';
    
    // Get the logged-in supplier's ID
    $supplier_id = getSupplierIdFromUsername($_SESSION['username']);

    if ($supplier_id && createBid($po_id, $supplier_id, $bid_amount, $notes)) {
        // Success message can be handled via session flash messages if needed
    } else {
        // Error message can be handled here
    }
    // Redirect to prevent form resubmission
    header("Location: supplier_bidding.php");
    exit();
}

// Fetch Data for the Page
$open_purchase_orders = getOpenForBiddingPOs();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bidding Portal - SLATE Logistics</title>
    <link rel="icon" href="../assets/images/slate2.png" type="image/png">
    <link rel="stylesheet" href="../assets/css/supplier_portal.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include '../partials/supplier_sidebar.php'; ?>

    <div class="supplier-content">
        <?php include '../partials/supplier_header.php'; ?>

        <main class="mt-8">
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Open for Bidding</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-3 text-sm font-semibold text-gray-600">Item Name</th>
                                <th class="p-3 text-sm font-semibold text-gray-600">Quantity</th>
                                <th class="p-3 text-sm font-semibold text-gray-600">Date Posted</th>
                                <th class="p-3 text-sm font-semibold text-gray-600"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($open_purchase_orders)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-10 text-gray-500">
                                        There are no purchase orders currently open for bidding.
                                    </td>
                                </tr>
                            <?php else: foreach($open_purchase_orders as $po): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-4 font-semibold"><?php echo htmlspecialchars($po['item_name']); ?></td>
                                <td class="p-4"><?php echo $po['quantity']; ?></td>
                                <td class="p-4 text-gray-600"><?php echo date("F j, Y", strtotime($po['order_date'])); ?></td>
                                <td class="p-4 text-right">
                                    <button onclick="openBidModal(<?php echo $po['id']; ?>, '<?php echo htmlspecialchars(addslashes($po['item_name'])); ?>')" class="bg-blue-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                                        Place Bid
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="bidModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center hidden z-50">
        <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md">
            <h2 class="text-2xl font-bold mb-4 text-gray-800" id="bidModalTitle">Place Your Bid</h2>
            <form method="POST">
                <input type="hidden" name="action" value="place_bid">
                <input type="hidden" name="po_id" id="po_id_input">
                
                <div class="mb-4">
                    <label for="bid_amount" class="block text-sm font-medium text-gray-700 mb-1">Bid Amount ($)</label>
                    <input type="number" name="bid_amount" id="bid_amount" step="0.01" placeholder="Enter your bid amount" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div class="mb-6">
                     <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                    <textarea name="notes" id="notes" placeholder="Include any notes for the procurement team..." rows="3" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex justify-end gap-4">
                    <button type="button" onclick="closeBidModal()" class="px-5 py-2 rounded-lg bg-gray-200 text-gray-800 font-semibold hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700">Submit Bid</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/supplier_portal.js"></script>
    <script>
        function openBidModal(po_id, item_name) {
            document.getElementById('bidModalTitle').innerText = `Place Your Bid for "${item_name}"`;
            document.getElementById('po_id_input').value = po_id;
            document.getElementById('bidModal').classList.remove('hidden');
        }
        function closeBidModal() {
            document.getElementById('bidModal').classList.add('hidden');
        }
    </script>
</body>
</html>