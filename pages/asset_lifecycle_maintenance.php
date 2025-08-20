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
  <title>Logistics 1 - ALMS</title>
  <link rel="icon" href="../assets/images/slate2.png" type="image/png">
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="stylesheet" href="../assets/css/sidebar.css">
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

      <h2 class="page-title">Asset Lifecycle & Maintenance (ALMS)</h2>
      <div style="text-align: center; padding: 50px; color: #666;">
        <h3>Empty page, work in progress</h3>
        <p>This module is under development.</p>
      </div>

    </div>
  </div>

  <script src="../assets/js/sidebar.js"></script>
  <script src="../assets/js/script.js"></script>
</body>
</html> 