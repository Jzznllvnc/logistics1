<?php
// This partial requires the functions from notifications.php and bids.php
require_once __DIR__ . '/../includes/functions/notifications.php';
require_once __DIR__ . '/../includes/functions/bids.php'; 

// Fetch notification data
$supplier_id_for_notif = getSupplierIdFromUsername($_SESSION['username'] ?? '');
$all_notifications = getAllNotificationsBySupplier($supplier_id_for_notif);
$notification_count = getUnreadNotificationCountBySupplier($supplier_id_for_notif);
?>
<header class="supplier-header">
  <div class="header-left">
    <img src="../assets/images/slate2.png" alt="SLATE Logo" class="header-logo">
    <h1 class="supplier-portal-title">Supplier Portal</h1>
  </div>
  
  <div class="header-right">
    <div class="notification-container" id="notification-button">
      <i data-lucide="bell" class="notification-bell"></i>
      <?php if ($notification_count > 0): ?>
        <span class="notification-count">
          <?php echo $notification_count; ?>
        </span>
      <?php endif; ?>
      <div id="notification-panel" class="notification-panel">
          <div class="notification-header">Notifications</div>
          <ul class="notification-list">
              <?php if (empty($all_notifications)): ?>
                  <li class="no-notifications">You have no notifications.</li>
              <?php else: ?>
                  <?php foreach ($all_notifications as $notif): ?>
                      <li class="notification-item <?php echo $notif['is_read'] ? '' : 'unread'; ?>" data-read="<?php echo $notif['is_read']; ?>">
                          <div class="notification-content">
                              <?php if (!$notif['is_read']): ?>
                                <span class="unread-dot"></span>
                              <?php endif; ?>
                              <div class="notification-text">
                                <p class="notification-message"><?php echo htmlspecialchars($notif['message']); ?></p>
                                <p class="notification-time"><?php echo date("F j, Y, g:i a", strtotime($notif['created_at'])); ?></p>
                              </div>
                          </div>
                      </li>
                  <?php endforeach; ?>
              <?php endif; ?>
          </ul>
      </div>
    </div>
    
    <div class="supplier-profile-dropdown">
        <div class="supplier-profile" id="supplierProfileToggle">
            <span class="supplier-name">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <i data-lucide="chevron-down" class="chevron-down"></i>
        </div>
        <div class="dropdown-menu" id="supplierDropdownMenu">
            <a href="supplier_profile.php" class="dropdown-link">
                <i data-lucide="user-circle" class="dropdown-icon"></i>
                My Profile
            </a>
            <a href="#" onclick="showLogoutModal()" class="dropdown-link">
                <i data-lucide="log-out" class="dropdown-icon"></i> 
                Logout
            </a>
        </div>
    </div>
  </div>
</header>



<script>
document.addEventListener('DOMContentLoaded', function() {
    const profileToggle = document.getElementById('supplierProfileToggle');
    const dropdownMenu = document.getElementById('supplierDropdownMenu');
    
    if (profileToggle && dropdownMenu) {
        profileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (dropdownMenu.style.display === 'block') {
                dropdownMenu.style.display = 'none';
            } else {
                dropdownMenu.style.display = 'block';
            }
        });
        
        document.addEventListener('click', function() {
            dropdownMenu.style.display = 'none';
        });
        
        dropdownMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

});
</script>