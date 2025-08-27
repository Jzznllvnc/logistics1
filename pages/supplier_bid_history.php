<?php
require_once '../includes/functions/auth.php';
require_once '../includes/functions/bids.php';
require_once '../includes/functions/notifications.php';
requireLogin();

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
}
if ($_SESSION['role'] !== 'supplier') {
    header("Location: dashboard.php");
    exit();
}

$supplier_id = getSupplierIdFromUsername($_SESSION['username']);
$bid_history = getBidsBySupplier($supplier_id); // New function
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bid History - SLATE Logistics</title>
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
                <h2 class="text-xl font-semibold text-gray-800 mb-4">My Bid History</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-3 text-sm font-semibold text-gray-600">PO Item</th>
                                <th class="p-3 text-sm font-semibold text-gray-600">My Bid Amount</th>
                                <th class="p-3 text-sm font-semibold text-gray-600">Date Submitted</th>
                                <th class="p-3 text-sm font-semibold text-gray-600">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bid_history)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-10 text-gray-500">You have not submitted any bids yet.</td>
                                </tr>
                            <?php else: foreach($bid_history as $bid): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-4 font-semibold"><?php echo htmlspecialchars($bid['item_name']); ?></td>
                                <td class="p-4">$<?php echo number_format($bid['bid_amount'], 2); ?></td>
                                <td class="p-4 text-gray-600"><?php echo date("F j, Y", strtotime($bid['bid_date'])); ?></td>
                                <td class="p-4">
                                    <span class="px-2 py-1 font-semibold leading-tight text-xs rounded-full
                                        <?php if ($bid['status'] === 'Awarded') echo 'bg-green-100 text-green-700';
                                              elseif ($bid['status'] === 'Rejected') echo 'bg-red-100 text-red-700';
                                              elseif ($bid['status'] === 'Pending') echo 'bg-yellow-100 text-yellow-700';
                                              else echo 'bg-gray-100 text-gray-700'; ?>">
                                        <?php echo htmlspecialchars($bid['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <script src="../assets/js/supplier_portal.js"></script>
</body>
</html>