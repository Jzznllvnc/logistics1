<?php
require_once '../includes/functions/auth.php';
requireLogin();

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
}

// Get current user's role
$userRole = $_SESSION['role'] ?? 'guest';
$username = $_SESSION['username'] ?? 'Unknown';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <script>document.documentElement.classList.add('preload', 'loading');</script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logistics 1 - Dashboard</title>
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

      <!-- Role-specific Dashboard Content -->
      <?php if ($userRole === 'admin'): ?>
        <h1 class="page-title" style="font-family: 'Inter Tight', sans-serif; font-weight: 600; font-size: 2.5rem; margin-bottom: 0.3rem;">Administrator Dashboard</h1>
        <p style="color: var(--text-color); opacity: 0.7; font-size: 1.1rem; margin-bottom: 2rem;">Manage all logistics operations and system administration</p>

        <!-- Admin Dashboard Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
          <!-- Metric Card 1 -->
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h3 style="color: var(--text-color); font-size: 14px; font-weight: 500; margin: 0;">Total Users</h3>
              <div style="width: 32px; height: 32px; background: #e3f2fd; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-users" style="color: #1976d2; font-size: 16px;"></i>
              </div>
            </div>
            <div style="color: var(--text-color); font-size: 28px; font-weight: 700; margin-bottom: 8px;">--</div>
            <div style="color: #4caf50; font-size: 12px; font-weight: 500;">-- %</div>
          </div>

          <!-- Metric Card 2 -->
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h3 style="color: var(--text-color); font-size: 14px; font-weight: 500; margin: 0;">Active Modules</h3>
              <div style="width: 32px; height: 32px; background: #f3e5f5; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-cube" style="color: #7b1fa2; font-size: 16px;"></i>
              </div>
            </div>
            <div style="color: var(--text-color); font-size: 28px; font-weight: 700; margin-bottom: 8px;">--</div>
            <div style="color: #4caf50; font-size: 12px; font-weight: 500;">-- %</div>
          </div>

          <!-- Metric Card 3 -->
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h3 style="color: var(--text-color); font-size: 14px; font-weight: 500; margin: 0;">System Health</h3>
              <div style="width: 32px; height: 32px; background: #e8f5e8; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chart-line" style="color: #388e3c; font-size: 16px;"></i>
              </div>
            </div>
            <div style="color: var(--text-color); font-size: 28px; font-weight: 700; margin-bottom: 8px;">--%</div>
            <div style="color: #4caf50; font-size: 12px; font-weight: 500;">-- %</div>
          </div>
        </div>

        <!-- Charts Section -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px;">
          <!-- Main Chart -->
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <h3 style="color: var(--text-color); font-size: 16px; font-weight: 600; margin: 0 0 20px 0;">System Activity</h3>
            <div style="height: 300px; background: var(--bg-color); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-color); opacity: 0.5;">
              Chart Placeholder
            </div>
          </div>

          <!-- Side Panel -->
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <h3 style="color: var(--text-color); font-size: 16px; font-weight: 600; margin: 0 0 20px 0;">Recent Activity</h3>
            <div style="height: 300px; display: flex; align-items: center; justify-content: center; color: var(--text-color); opacity: 0.5;">
              Activity List Placeholder
            </div>
          </div>
        </div>

      <?php elseif ($userRole === 'smart_warehousing'): ?>
        <h1 class="page-title" style="font-family: 'Inter Tight', sans-serif; font-weight: 600; font-size: 2.5rem; margin-bottom: 0.3rem;">Smart Warehousing Dashboard</h1>
        <p style="color: var(--text-color); opacity: 0.7; font-size: 1.1rem; margin-bottom: 2rem;">Monitor warehouse operations, inventory, and smart systems</p>

        <!-- Warehouse Dashboard Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
          <!-- Inventory Level -->
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h3 style="color: var(--text-color); font-size: 14px; font-weight: 500; margin: 0;">Inventory Level</h3>
              <div style="width: 32px; height: 32px; background: #e3f2fd; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-boxes" style="color: #1976d2; font-size: 16px;"></i>
              </div>
            </div>
            <div style="color: var(--text-color); font-size: 28px; font-weight: 700; margin-bottom: 8px;">--</div>
            <div style="color: #4caf50; font-size: 12px; font-weight: 500;">-- items</div>
          </div>

          <!-- Capacity Usage -->
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h3 style="color: var(--text-color); font-size: 14px; font-weight: 500; margin: 0;">Capacity Usage</h3>
              <div style="width: 32px; height: 32px; background: #fff3e0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-warehouse" style="color: #f57c00; font-size: 16px;"></i>
              </div>
            </div>
            <div style="color: var(--text-color); font-size: 28px; font-weight: 700; margin-bottom: 8px;">--%</div>
            <div style="color: #ff9800; font-size: 12px; font-weight: 500;">-- % utilized</div>
          </div>

          <!-- Active Orders -->
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h3 style="color: var(--text-color); font-size: 14px; font-weight: 500; margin: 0;">Active Orders</h3>
              <div style="width: 32px; height: 32px; background: #e8f5e8; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-clipboard-list" style="color: #388e3c; font-size: 16px;"></i>
              </div>
            </div>
            <div style="color: var(--text-color); font-size: 28px; font-weight: 700; margin-bottom: 8px;">--</div>
            <div style="color: #4caf50; font-size: 12px; font-weight: 500;">-- pending</div>
          </div>
        </div>

        <!-- Warehouse Charts -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px;">
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <h3 style="color: var(--text-color); font-size: 16px; font-weight: 600; margin: 0 0 20px 0;">Inventory Trends</h3>
            <div style="height: 300px; background: var(--bg-color); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-color); opacity: 0.5;">
              Inventory Chart Placeholder
            </div>
          </div>
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <h3 style="color: var(--text-color); font-size: 16px; font-weight: 600; margin: 0 0 20px 0;">Low Stock Alerts</h3>
            <div style="height: 300px; display: flex; align-items: center; justify-content: center; color: var(--text-color); opacity: 0.5;">
              Alerts List Placeholder
            </div>
          </div>
        </div>

      <?php elseif ($userRole === 'procurement'): ?>
        <h1 class="page-title" style="font-family: 'Inter Tight', sans-serif; font-weight: 600; font-size: 2.5rem; margin-bottom: 0.3rem;">Procurement Dashboard</h1>
        <p style="color: var(--text-color); opacity: 0.7; font-size: 1.1rem; margin-bottom: 2rem;">Manage procurement activities and supplier relationships</p>

        <!-- Procurement Dashboard Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h3 style="color: var(--text-color); font-size: 14px; font-weight: 500; margin: 0;">Purchase Orders</h3>
              <div style="width: 32px; height: 32px; background: #e8f5e8; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-file-invoice" style="color: #388e3c; font-size: 16px;"></i>
              </div>
            </div>
            <div style="color: var(--text-color); font-size: 28px; font-weight: 700; margin-bottom: 8px;">--</div>
            <div style="color: #4caf50; font-size: 12px; font-weight: 500;">-- active</div>
          </div>

          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h3 style="color: var(--text-color); font-size: 14px; font-weight: 500; margin: 0;">Total Spend</h3>
              <div style="width: 32px; height: 32px; background: #f3e5f5; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-dollar-sign" style="color: #7b1fa2; font-size: 16px;"></i>
              </div>
            </div>
            <div style="color: var(--text-color); font-size: 28px; font-weight: 700; margin-bottom: 8px;">$ --</div>
            <div style="color: #4caf50; font-size: 12px; font-weight: 500;">-- % vs target</div>
          </div>

          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h3 style="color: var(--text-color); font-size: 14px; font-weight: 500; margin: 0;">Suppliers</h3>
              <div style="width: 32px; height: 32px; background: #e3f2fd; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-truck" style="color: #1976d2; font-size: 16px;"></i>
              </div>
            </div>
            <div style="color: var(--text-color); font-size: 28px; font-weight: 700; margin-bottom: 8px;">--</div>
            <div style="color: #4caf50; font-size: 12px; font-weight: 500;">-- active</div>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px;">
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <h3 style="color: var(--text-color); font-size: 16px; font-weight: 600; margin: 0 0 20px 0;">Procurement Analytics</h3>
            <div style="height: 300px; background: var(--bg-color); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-color); opacity: 0.5;">
              Procurement Chart Placeholder
            </div>
          </div>
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <h3 style="color: var(--text-color); font-size: 16px; font-weight: 600; margin: 0 0 20px 0;">Top Suppliers</h3>
            <div style="height: 300px; display: flex; align-items: center; justify-content: center; color: var(--text-color); opacity: 0.5;">
              Suppliers List Placeholder
            </div>
          </div>
        </div>

      <?php elseif ($userRole === 'plt'): ?>
        <h1 class="page-title" style="font-family: 'Inter Tight', sans-serif; font-weight: 600; font-size: 2.5rem; margin-bottom: 0.3rem;">Project Logistics Dashboard</h1>
        <p style="color: var(--text-color); opacity: 0.7; font-size: 1.1rem; margin-bottom: 2rem;">Track and manage logistics for ongoing projects</p>

        <!-- PLT Dashboard Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h3 style="color: var(--text-color); font-size: 14px; font-weight: 500; margin: 0;">Active Projects</h3>
              <div style="width: 32px; height: 32px; background: #fff3e0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-project-diagram" style="color: #f57c00; font-size: 16px;"></i>
              </div>
            </div>
            <div style="color: var(--text-color); font-size: 28px; font-weight: 700; margin-bottom: 8px;">--</div>
            <div style="color: #ff9800; font-size: 12px; font-weight: 500;">-- in progress</div>
          </div>

          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h3 style="color: var(--text-color); font-size: 14px; font-weight: 500; margin: 0;">On-Time Delivery</h3>
              <div style="width: 32px; height: 32px; background: #e8f5e8; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-clock" style="color: #388e3c; font-size: 16px;"></i>
              </div>
            </div>
            <div style="color: var(--text-color); font-size: 28px; font-weight: 700; margin-bottom: 8px;">--%</div>
            <div style="color: #4caf50; font-size: 12px; font-weight: 500;">-- % rate</div>
          </div>

          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h3 style="color: var(--text-color); font-size: 14px; font-weight: 500; margin: 0;">Resource Utilization</h3>
              <div style="width: 32px; height: 32px; background: #e3f2fd; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chart-pie" style="color: #1976d2; font-size: 16px;"></i>
              </div>
            </div>
            <div style="color: var(--text-color); font-size: 28px; font-weight: 700; margin-bottom: 8px;">--%</div>
            <div style="color: #2196f3; font-size: 12px; font-weight: 500;">-- % utilized</div>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px;">
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <h3 style="color: var(--text-color); font-size: 16px; font-weight: 600; margin: 0 0 20px 0;">Project Timeline</h3>
            <div style="height: 300px; background: var(--bg-color); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-color); opacity: 0.5;">
              Timeline Chart Placeholder
            </div>
          </div>
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <h3 style="color: var(--text-color); font-size: 16px; font-weight: 600; margin: 0 0 20px 0;">Project Status</h3>
            <div style="height: 300px; display: flex; align-items: center; justify-content: center; color: var(--text-color); opacity: 0.5;">
              Status Overview Placeholder
            </div>
          </div>
        </div>

      <?php elseif ($userRole === 'alms'): ?>
        <h1 class="page-title" style="font-family: 'Inter Tight', sans-serif; font-weight: 600; font-size: 2.5rem; margin-bottom: 0.3rem;">Asset Lifecycle Dashboard</h1>
        <p style="color: var(--text-color); opacity: 0.7; font-size: 1.1rem; margin-bottom: 2rem;">Manage asset lifecycle and maintenance schedules</p>

        <!-- ALMS Dashboard Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h3 style="color: var(--text-color); font-size: 14px; font-weight: 500; margin: 0;">Total Assets</h3>
              <div style="width: 32px; height: 32px; background: #f3e5f5; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-cogs" style="color: #7b1fa2; font-size: 16px;"></i>
              </div>
            </div>
            <div style="color: var(--text-color); font-size: 28px; font-weight: 700; margin-bottom: 8px;">--</div>
            <div style="color: #9c27b0; font-size: 12px; font-weight: 500;">-- managed</div>
          </div>

          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h3 style="color: var(--text-color); font-size: 14px; font-weight: 500; margin: 0;">Maintenance Due</h3>
              <div style="width: 32px; height: 32px; background: #fff3e0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-tools" style="color: #f57c00; font-size: 16px;"></i>
              </div>
            </div>
            <div style="color: var(--text-color); font-size: 28px; font-weight: 700; margin-bottom: 8px;">--</div>
            <div style="color: #ff9800; font-size: 12px; font-weight: 500;">-- pending</div>
          </div>

          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h3 style="color: var(--text-color); font-size: 14px; font-weight: 500; margin: 0;">Asset Health</h3>
              <div style="width: 32px; height: 32px; background: #e8f5e8; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-heartbeat" style="color: #388e3c; font-size: 16px;"></i>
              </div>
            </div>
            <div style="color: var(--text-color); font-size: 28px; font-weight: 700; margin-bottom: 8px;">--%</div>
            <div style="color: #4caf50; font-size: 12px; font-weight: 500;">-- avg score</div>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px;">
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <h3 style="color: var(--text-color); font-size: 16px; font-weight: 600; margin: 0 0 20px 0;">Asset Performance</h3>
            <div style="height: 300px; background: var(--bg-color); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-color); opacity: 0.5;">
              Performance Chart Placeholder
            </div>
          </div>
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <h3 style="color: var(--text-color); font-size: 16px; font-weight: 600; margin: 0 0 20px 0;">Maintenance Schedule</h3>
            <div style="height: 300px; display: flex; align-items: center; justify-content: center; color: var(--text-color); opacity: 0.5;">
              Schedule List Placeholder
            </div>
          </div>
        </div>

      <?php elseif ($userRole === 'dtrs'): ?>
        <h1 class="page-title" style="font-family: 'Inter Tight', sans-serif; font-weight: 600; font-size: 2.5rem; margin-bottom: 0.3rem;">Document Tracking Dashboard</h1>
        <p style="color: var(--text-color); opacity: 0.7; font-size: 1.1rem; margin-bottom: 2rem;">Manage document tracking and logistics records</p>

        <!-- DTRS Dashboard Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h3 style="color: var(--text-color); font-size: 14px; font-weight: 500; margin: 0;">Total Documents</h3>
              <div style="width: 32px; height: 32px; background: #e1f5fe; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-file-alt" style="color: #0277bd; font-size: 16px;"></i>
              </div>
            </div>
            <div style="color: var(--text-color); font-size: 28px; font-weight: 700; margin-bottom: 8px;">--</div>
            <div style="color: #03a9f4; font-size: 12px; font-weight: 500;">-- tracked</div>
          </div>

          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h3 style="color: var(--text-color); font-size: 14px; font-weight: 500; margin: 0;">Pending Approval</h3>
              <div style="width: 32px; height: 32px; background: #fff3e0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-clock" style="color: #f57c00; font-size: 16px;"></i>
              </div>
            </div>
            <div style="color: var(--text-color); font-size: 28px; font-weight: 700; margin-bottom: 8px;">--</div>
            <div style="color: #ff9800; font-size: 12px; font-weight: 500;">-- awaiting</div>
          </div>

          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <h3 style="color: var(--text-color); font-size: 14px; font-weight: 500; margin: 0;">Compliance Rate</h3>
              <div style="width: 32px; height: 32px; background: #e8f5e8; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-check-circle" style="color: #388e3c; font-size: 16px;"></i>
              </div>
            </div>
            <div style="color: var(--text-color); font-size: 28px; font-weight: 700; margin-bottom: 8px;">--%</div>
            <div style="color: #4caf50; font-size: 12px; font-weight: 500;">-- % compliant</div>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px;">
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <h3 style="color: var(--text-color); font-size: 16px; font-weight: 600; margin: 0 0 20px 0;">Document Flow</h3>
            <div style="height: 300px; background: var(--bg-color); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-color); opacity: 0.5;">
              Document Flow Chart Placeholder
            </div>
          </div>
          <div style="background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid var(--card-border);">
            <h3 style="color: var(--text-color); font-size: 16px; font-weight: 600; margin: 0 0 20px 0;">Recent Documents</h3>
            <div style="height: 300px; display: flex; align-items: center; justify-content: center; color: var(--text-color); opacity: 0.5;">
              Document List Placeholder
            </div>
          </div>
        </div>

      <?php else: ?>
        <h1 class="page-title" style="font-family: 'Inter Tight', sans-serif; font-weight: 600; font-size: 2.5rem; margin-bottom: 0.3rem;">Dashboard</h1>
        <p style="color: var(--text-color); opacity: 0.7; font-size: 1.1rem; margin-bottom: 2rem;">Please contact your administrator for proper role assignment</p>
      <?php endif; ?>

    </div>
  </div>

  <script src="../assets/js/sidebar.js"></script>
  <script src="../assets/js/script.js"></script>
</body>
</html>