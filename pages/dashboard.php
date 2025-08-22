<?php
require_once '../includes/functions/auth.php';
requireLogin();

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <script>document.documentElement.classList.add('preload', 'loading');</script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logistics 1 - Dashboard</title>
  <link rel="icon" href="../assets/images/slate2.png" type="image/png">
  <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="stylesheet" href="../assets/css/sidebar.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha384-nRgPTkuX86pH8yjPJUAFuASXQSSl2/bBUiNV47vSYpKFxHJhbcrGnmlYpYJMeD7a" crossorigin="anonymous">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
  <div class="sidebar" id="sidebar">
    <?php include '../partials/sidebar.php'; ?>
  </div>

  <div class="main-content-wrapper" id="mainContentWrapper">
    <div class="content" id="mainContent">
      <script>
        // Apply persisted sidebar state immediately after elements exist
        (function() {
          try {
            const collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            var sidebar = document.getElementById('sidebar');
            var wrapper = document.getElementById('mainContentWrapper');
            if (collapsed && sidebar && wrapper) {
              sidebar.classList.add('initial-collapsed');
              wrapper.classList.add('initial-expanded');
              document.body.classList.remove('sidebar-active');
            } else {
              document.body.classList.add('sidebar-active');
            }
          } catch (e) {}
        })();
      </script>
      <?php include '../partials/header.php'; ?>

      <?php if ($_SESSION['role'] === 'admin'): ?>
        <!-- Admin Dashboard -->
        <h1 class="font-semibold mb-3 page-title">Administrator Dashboard</h1>
        <p class="text-lg mb-8 page-subtitle">Your overview of system activities, operations, and freight workflows.</p>
        
      <?php elseif ($_SESSION['role'] === 'smart_warehousing'): ?>
        <!-- Smart Warehousing Dashboard -->
        <h1 class="font-semibold mb-3 page-title">Smart Warehousing Dashboard</h1>
        <p class="text-lg mb-8 page-subtitle">SWS dashboard content coming soon...</p>
        
      <?php elseif ($_SESSION['role'] === 'procurement'): ?>
        <!-- Procurement Dashboard -->
        <h1 class="font-semibold mb-3 page-title">Procurement & Sourcing Dashboard</h1>
        <p class="text-lg mb-8 page-subtitle">PSM dashboard content coming soon...</p>
        
      <?php elseif ($_SESSION['role'] === 'plt'): ?>
        <!-- Project Logistics Tracker Dashboard -->
        <h1 class="font-semibold mb-3 page-title">Project Logistics Tracker Dashboard</h1>
        <p class="text-lg mb-8 page-subtitle">PLT dashboard content coming soon...</p>
        
      <?php elseif ($_SESSION['role'] === 'alms'): ?>
        <!-- Asset Lifecycle & Maintenance Dashboard -->
        <h1 class="font-semibold mb-3 page-title">Asset Lifecycle & Maintenance Dashboard</h1>
        <p class="text-lg mb-8 page-subtitle">ALMS dashboard content coming soon...</p>
        
      <?php elseif ($_SESSION['role'] === 'dtrs'): ?>
        <!-- Document Tracking & Logistics Records Dashboard -->
        <h1 class="font-semibold mb-3 page-title">Document Tracking & Logistics Records Dashboard</h1>
        <p class="text-lg mb-8 page-subtitle">DTRS dashboard content coming soon...</p>
        
      <?php else: ?>
        <!-- Default/Unknown Role -->
        <h1 class="font-semibold mb-3 page-title">Welcome to LOGISTICS 1</h1>
        <p class="text-lg mb-8 page-subtitle">Your dashboard content will appear here based on your assigned role.</p>
      <?php endif; ?>

    </div>
  </div>

  <script src="../assets/js/sidebar.js"></script>
  <script src="../assets/js/script.js"></script>
  <script>
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  </script>
</body>
</html>