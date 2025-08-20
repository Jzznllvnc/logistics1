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
  <title>Logistics 1 - PLT</title>
  <link rel="icon" href="../assets/images/slate2.png" type="image/png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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

      <h1 class="page-title" style="font-family: 'Inter Tight', sans-serif; font-weight: 600; font-size: 2.5rem; margin-bottom: 0.3rem;">Project Logistics Tracker (PLT)</h1>

    </div>
  </div>

  <script src="../assets/js/sidebar.js"></script>
  <script src="../assets/js/script.js"></script>
</body>
</html> 