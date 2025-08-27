<?php
// Logistic1/includes/functions/notifications.php
require_once __DIR__ . '/../config/db.php';

/**
 * Creates a notification for a specific supplier.
 * @param int $supplier_id The ID of the supplier to notify.
 * @param string $message The notification message.
 * @return bool True on success, false on failure.
 */
function createNotification($supplier_id, $message) {
    if (empty($supplier_id) || empty($message)) {
        return false;
    }
    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO notifications (supplier_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $supplier_id, $message);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

/**
 * Retrieves ALL notifications for a specific supplier, with unread ones first.
 * @param int $supplier_id The supplier's ID.
 * @return array An array of all notification records.
 */
function getAllNotificationsBySupplier($supplier_id) {
    if (!$supplier_id) return [];
    $conn = getDbConnection();
    // Order by is_read (unread first) and then by date (newest first)
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE supplier_id = ? ORDER BY is_read ASC, created_at DESC");
    $stmt->bind_param("i", $supplier_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    $conn->close();
    return $notifications;
}

/**
 * Counts the number of UNREAD notifications for a specific supplier.
 * @param int $supplier_id The supplier's ID.
 * @return int The count of unread notifications.
 */
function getUnreadNotificationCountBySupplier($supplier_id) {
    if (!$supplier_id) return 0;
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE supplier_id = ? AND is_read = 0");
    $stmt->bind_param("i", $supplier_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result ? (int)$result->fetch_assoc()['count'] : 0;
    $stmt->close();
    $conn->close();
    return $count;
}


/**
 * Marks all unread notifications for a supplier as read.
 * @param int $supplier_id The supplier's ID.
 * @return bool True on success, false on failure.
 */
function markAllNotificationsAsRead($supplier_id) {
    if (empty($supplier_id)) return false;
    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE supplier_id = ? AND is_read = 0");
    $stmt->bind_param("i", $supplier_id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}
?>