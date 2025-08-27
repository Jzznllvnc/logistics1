<?php
require_once '../includes/functions/auth.php';
require_once '../includes/functions/bids.php';
require_once '../includes/functions/notifications.php'; // Required for notifications
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

// Role check for suppliers
if ($_SESSION['role'] !== 'supplier') {
    header("Location: dashboard.php");
    exit();
}

// Fetch dynamic data for the dashboard
$supplier_id = getSupplierIdFromUsername($_SESSION['username']);
$open_bids_count = getOpenBiddingCount();
$awarded_bids_count = getAwardedBidsCountBySupplier($supplier_id);
$active_proposals_count = getActiveBidsCountBySupplier($supplier_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supplier Dashboard - SLATE Logistics</title>
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
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-8 rounded-xl shadow-lg mb-8">
                <h2 class="text-3xl font-bold mb-2">Ready to Bid?</h2>
                <p class="text-blue-100 max-w-2xl">
                    Here's a quick overview of your bidding activity. Find new opportunities and manage your ongoing proposals all in one place.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-500">Open for Bidding</h3>
                        <div class="bg-blue-100 text-blue-600 p-3 rounded-full">
                            <i class="fas fa-gavel fa-lg"></i>
                        </div>
                    </div>
                    <p class="text-4xl font-bold mt-4"><?php echo $open_bids_count; ?></p>
                    <a href="supplier_bidding.php" class="text-blue-600 font-semibold mt-2 inline-block">View Opportunities &rarr;</a>
                </div>

                <div class="stat-card">
                     <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-500">Bids Awarded</h3>
                        <div class="bg-green-100 text-green-600 p-3 rounded-full">
                            <i class="fas fa-trophy fa-lg"></i>
                        </div>
                    </div>
                    <p class="text-4xl font-bold mt-4"><?php echo $awarded_bids_count; ?></p>
                     <a href="#" class="text-green-600 font-semibold mt-2 inline-block">View History &rarr;</a>
                </div>

                <div class="stat-card">
                     <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-500">Active Proposals</h3>
                        <div class="bg-yellow-100 text-yellow-600 p-3 rounded-full">
                            <i class="fas fa-file-alt fa-lg"></i>
                        </div>
                    </div>
                    <p class="text-4xl font-bold mt-4"><?php echo $active_proposals_count; ?></p>
                    <a href="#" class="text-yellow-600 font-semibold mt-2 inline-block">Manage Bids &rarr;</a>
                </div>
            </div>
        </main>
    </div>
    <script src="../assets/js/supplier_portal.js"></script>
</body>
</html>