<?php $currentPage = basename($_SERVER['SCRIPT_NAME']); ?>
<aside class="supplier-sidebar">
    <div class="logo mb-10 text-center">
        <img src="../assets/images/slate1.png" alt="SLATE Logo" class="h-12 mx-auto">
    </div>
    <nav class="flex flex-col gap-2">
        <a href="supplier_dashboard.php" class="sidebar-link <?php echo ($currentPage === 'supplier_dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt fa-fw"></i>
            <span>Dashboard</span>
        </a>
        <a href="supplier_bidding.php" class="sidebar-link <?php echo ($currentPage === 'supplier_bidding.php') ? 'active' : ''; ?>">
            <i class="fas fa-gavel fa-fw"></i>
            <span>Open Bids</span>
        </a>
        <a href="supplier_bid_history.php" class="sidebar-link <?php echo ($currentPage === 'supplier_bid_history.php') ? 'active' : ''; ?>">
            <i class="fas fa-history fa-fw"></i>
            <span>Bid History</span>
        </a>
        <a href="supplier_profile.php" class="sidebar-link <?php echo ($currentPage === 'supplier_profile.php') ? 'active' : ''; ?>">
            <i class="fas fa-user-circle fa-fw"></i>
            <span>My Profile</span>
        </a>
    </nav>
    <div class="mt-auto">
        <a href="#" onclick="showLogoutModal()" class="sidebar-link text-red-500 hover:bg-red-50 hover:text-red-600">
            <i class="fas fa-sign-out-alt fa-fw"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>