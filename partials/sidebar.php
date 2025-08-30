<?php require_once '../includes/functions/auth.php'; ?>
<div class="sidebar" id="sidebar">
  <div class="logo">
    <img src="../assets/images/slate1.png" alt="SLATE Logo">
  </div>
  <div class="system-name">LOGISTICS 1</div>
  <br><hr class="border-t text-[0.2px] border-gray-400"><br>

  <?php if ($_SESSION['role'] === 'supplier'): ?>
    <a href="../pages/supplier_dashboard.php" class="sidebar-sub-item">
      <i class="fas fa-tachometer-alt sidebar-icon"></i> My Dashboard
    </a>
    <a href="../pages/supplier_dashboard.php" class="sidebar-sub-item">
      <i class="fas fa-gavel sidebar-icon"></i> Bidding Portal
    </a>
  <?php else: ?>
    <div class="sidebar-category">Core Operations</div>
    
    <a href="../pages/dashboard.php" class="sidebar-sub-item">
      <i class="fas fa-home sidebar-icon"></i> Dashboard
    </a>
    
    <?php if ($_SESSION['role'] === 'smart_warehousing' || $_SESSION['role'] === 'admin'): ?>
      <a href="../pages/smart_warehousing.php" class="sidebar-sub-item">
        <i class="fas fa-warehouse sidebar-icon"></i> Smart Warehousing System
      </a>
    <?php endif; ?>

    <?php if ($_SESSION['role'] === 'procurement' || $_SESSION['role'] === 'admin'): ?>
      <a href="../pages/procurement_sourcing.php" class="sidebar-sub-item">
        <i class="fas fa-truck-loading sidebar-icon"></i> Procurement & Sourcing Management
      </a>
    <?php endif; ?>

    <?php if ($_SESSION['role'] === 'plt' || $_SESSION['role'] === 'admin'): ?>
      <a href="../pages/project_logistics_tracker.php" class="sidebar-sub-item">
        <i class="fas fa-project-diagram sidebar-icon"></i> Project Logistics Tracker
      </a>
    <?php endif; ?>

    <?php if ($_SESSION['role'] === 'alms' || $_SESSION['role'] === 'admin'): ?>
      <a href="../pages/asset_lifecycle_maintenance.php" class="sidebar-sub-item">
        <i class="fas fa-tools sidebar-icon"></i> Asset Lifecycle & Maintenance
      </a>
    <?php endif; ?>

    <?php if ($_SESSION['role'] === 'dtrs' || $_SESSION['role'] === 'admin'): ?>
      <a href="../pages/document_tracking_records.php" class="sidebar-sub-item">
        <i class="fas fa-file-alt sidebar-icon"></i> Document Tracking & Logistics Records
      </a>
    <?php endif; ?>

    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'procurement'): ?>
      <div class="sidebar-category">Network</div>
      
      <a href="../pages/admin_verification.php" class="sidebar-sub-item">
        <i class="fas fa-user-check sidebar-icon"></i> Supplier Verification
      </a>
    <?php endif; ?>
  <?php endif; ?>
</div>