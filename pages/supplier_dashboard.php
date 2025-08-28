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
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supplier Dashboard - SLATE Logistics</title>
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

            <main class="supplier-main">
                <div class="hero-section">
                    <h2 class="hero-title">Ready to Bid?</h2>
                    <p class="hero-description">
                        Here's a quick overview of your bidding activity. Find new opportunities and manage your ongoing proposals all in one place.
                    </p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <h3 class="stat-title">Open for Bidding</h3>
                            <div class="stat-icon-blue">
                                <i data-lucide="gavel"></i>
                            </div>
                        </div>
                        <p class="stat-number"><?php echo $open_bids_count; ?></p>
                        <a href="supplier_bidding.php" class="stat-link">View Opportunities →</a>
                    </div>

                    <div class="stat-card">
                         <div class="stat-header">
                            <h3 class="stat-title">Bids Awarded</h3>
                            <div class="stat-icon-green">
                                <i data-lucide="trophy"></i>
                            </div>
                        </div>
                        <p class="stat-number"><?php echo $awarded_bids_count; ?></p>
                         <a href="supplier_bid_history.php" class="stat-link text-green-600">View History →</a>
                    </div>

                    <div class="stat-card">
                         <div class="stat-header">
                            <h3 class="stat-title">Active Proposals</h3>
                            <div class="stat-icon-yellow">
                                <i data-lucide="file-text"></i>
                            </div>
                        </div>
                        <p class="stat-number"><?php echo $active_proposals_count; ?></p>
                        <a href="supplier_bid_history.php" class="stat-link text-yellow-600">Manage Bids →</a>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
    <?php include 'modals/supplier.php'; ?>
    
    <script src="../assets/js/custom-alerts.js"></script>
    <script src="../assets/js/supplier_portal.js"></script>
</body>
</html>