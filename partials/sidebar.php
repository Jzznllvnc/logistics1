<?php require_once '../includes/functions/auth.php'; ?>
<div class="sidebar" id="sidebar">
  <div class="logo">
    <img src="../assets/images/slate1.png" alt="SLATE Logo">
  </div>
  <div class="system-name">LOGISTICS 1</div>
  <br><hr class="border-t text-[0.2px] border-gray-400"><br>

  <!-- Dashboard: visible to all logged-in users -->
  <a href="../pages/dashboard.php" class="sidebar-sub-item">
    <i class="fas fa-home sidebar-icon"></i> Dashboard
  </a>

  <!-- Smart Warehousing -->
  <?php if ($_SESSION['role'] === 'smart_warehousing' || $_SESSION['role'] === 'admin'): ?>
    <a href="../pages/smart_warehousing.php" class="sidebar-sub-item">
      <i class="fas fa-warehouse sidebar-icon"></i> Smart Warehousing System (SWS)
    </a>
  <?php endif; ?>

  <!-- Procurement -->
  <?php if ($_SESSION['role'] === 'procurement' || $_SESSION['role'] === 'admin'): ?>
    <a href="../pages/procurement_sourcing.php" class="sidebar-sub-item">
      <i class="fas fa-truck-loading sidebar-icon"></i> Procurement & Sourcing Management (PSM)
    </a>
  <?php endif; ?>

  <!-- Project Logistics Tracker -->
  <?php if ($_SESSION['role'] === 'plt' || $_SESSION['role'] === 'admin'): ?>
    <a href="../pages/project_logistics_tracker.php" class="sidebar-sub-item">
      <i class="fas fa-project-diagram sidebar-icon"></i> Project Logistics Tracker (PLT)
    </a>
  <?php endif; ?>

  <!-- Asset Lifecycle & Maintenance -->
  <?php if ($_SESSION['role'] === 'alms' || $_SESSION['role'] === 'admin'): ?>
    <a href="../pages/asset_lifecycle_maintenance.php" class="sidebar-sub-item">
      <i class="fas fa-tools sidebar-icon"></i> Asset Lifecycle & Maintenance (ALMS)
    </a>
  <?php endif; ?>

  <!-- Document Tracking & Logistics Records -->
  <?php if ($_SESSION['role'] === 'dtrs' || $_SESSION['role'] === 'admin'): ?>
    <a href="../pages/document_tracking_records.php" class="sidebar-sub-item">
      <i class="fas fa-file-alt sidebar-icon"></i> Document Tracking & Logistics Records (DTRS)
    </a>
  <?php endif; ?>
</div>
