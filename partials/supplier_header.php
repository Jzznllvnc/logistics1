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
  <h1 class="text-2xl font-bold text-[var(--sp-heading)]">Supplier Portal</h1>
  <div class="flex items-center gap-4">
    <div class="relative" id="notification-button">
      <i class="fas fa-bell text-gray-500 text-xl cursor-pointer"></i>
      <?php if ($notification_count > 0): ?>
        <span class="notification-count absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
          <?php echo $notification_count; ?>
        </span>
      <?php endif; ?>
      <div id="notification-panel" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl overflow-hidden z-20 border">
          <div class="py-2 px-4 text-sm font-semibold text-gray-700 border-b">Notifications</div>
          <ul class="max-h-96 overflow-y-auto">
              <?php if (empty($all_notifications)): ?>
                  <li class="p-4 text-sm text-gray-500">You have no notifications.</li>
              <?php else: ?>
                  <?php foreach ($all_notifications as $notif): ?>
                      <li class="notification-item border-b hover:bg-gray-50 p-4 <?php echo $notif['is_read'] ? '' : 'bg-blue-50'; ?>" data-read="<?php echo $notif['is_read']; ?>">
                          <div class="flex items-start">
                              <?php if (!$notif['is_read']): ?>
                                <span class="unread-dot h-2 w-2 bg-blue-500 rounded-full mr-3 mt-1.5 flex-shrink-0"></span>
                              <?php endif; ?>
                              <div class="flex-grow">
                                <p class="text-sm text-gray-800"><?php echo htmlspecialchars($notif['message']); ?></p>
                                <p class="text-xs text-gray-400 mt-1"><?php echo date("F j, Y, g:i a", strtotime($notif['created_at'])); ?></p>
                              </div>
                          </div>
                      </li>
                  <?php endforeach; ?>
              <?php endif; ?>
          </ul>
      </div>
    </div>
    <div class="w-px h-6 bg-gray-200"></div>
    <span class="font-medium text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
  </div>
</header>