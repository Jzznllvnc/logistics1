<?php
// Logistic1/includes/functions/purchase_order.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/notifications.php'; // Include notifications for awarding bids

/**
 * Creates a new purchase order with a 'Pending' status.
 * @param int|null $supplier_id Can be null initially.
 * @param string $item_name The name of the item being ordered.
 * @param int $quantity The quantity required.
 * @return bool True on success, false on failure.
 */
function createPurchaseOrder($supplier_id, $item_name, $quantity) {
    if (empty($item_name) || !is_numeric($quantity) || $quantity <= 0) {
        return false;
    }
    $conn = getDbConnection();
    
    // If no specific supplier is selected, create as 'Open for Bidding'
    // If a specific supplier is selected, create as 'Pending'
    $status = ($supplier_id === null) ? 'Open for Bidding' : 'Pending';
    
    $stmt = $conn->prepare("INSERT INTO purchase_orders (supplier_id, item_name, quantity, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $supplier_id, $item_name, $quantity, $status);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

/**
 * Retrieves a list of recent purchase orders, joined with supplier information if awarded.
 * @param int $limit The maximum number of orders to retrieve.
 * @return array An array of purchase order records.
 */
function getRecentPurchaseOrders($limit = 50) {
    $conn = getDbConnection();
    $sql = "SELECT po.*, s.supplier_name 
            FROM purchase_orders po
            LEFT JOIN suppliers s ON po.awarded_to_supplier_id = s.id
            ORDER BY po.order_date DESC
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    $conn->close();
    return $orders;
}

/**
 * Updates a purchase order's status to 'Open for Bidding'.
 * @param int $po_id The ID of the purchase order.
 * @return bool True on success, false on failure.
 */
function openPOForBidding($po_id) {
    if (empty($po_id)) return false;
    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE purchase_orders SET status = 'Open for Bidding' WHERE id = ?");
    $stmt->bind_param("i", $po_id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

/**
 * Awards a purchase order to a supplier, updates statuses, and sends notifications.
 * @param int $po_id The purchase order ID.
 * @param int $supplier_id The winning supplier's ID.
 * @param int $bid_id The winning bid's ID.
 * @return bool True on success, false on failure.
 */
function awardPOToSupplier($po_id, $supplier_id, $bid_id) {
    $conn = getDbConnection();
    $conn->begin_transaction();

    try {
        // Get PO details for notifications
        $po_stmt = $conn->prepare("SELECT item_name FROM purchase_orders WHERE id = ?");
        $po_stmt->bind_param("i", $po_id);
        $po_stmt->execute();
        $po_result = $po_stmt->get_result();
        $po = $po_result->fetch_assoc();
        $item_name = $po['item_name'];
        $po_stmt->close();

        // 1. Update the purchase order to 'Awarded'
        $stmt1 = $conn->prepare("UPDATE purchase_orders SET status = 'Awarded', awarded_to_supplier_id = ?, awarded_at = NOW() WHERE id = ?");
        $stmt1->bind_param("ii", $supplier_id, $po_id);
        $stmt1->execute();
        $stmt1->close();

        // 2. Update the winning bid to 'Awarded'
        $stmt2 = $conn->prepare("UPDATE bids SET status = 'Awarded' WHERE id = ?");
        $stmt2->bind_param("i", $bid_id);
        $stmt2->execute();
        $stmt2->close();

        // 3. Notify the winning supplier
        createNotification($supplier_id, "Congratulations! Your bid for '$item_name' (PO #$po_id) has been awarded.");

        // 4. Get all other bidders to reject their bids and notify them
        $reject_stmt = $conn->prepare("SELECT id, supplier_id FROM bids WHERE po_id = ? AND id != ?");
        $reject_stmt->bind_param("ii", $po_id, $bid_id);
        $reject_stmt->execute();
        $rejected_bids = $reject_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $reject_stmt->close();

        foreach ($rejected_bids as $bid) {
            $update_reject_stmt = $conn->prepare("UPDATE bids SET status = 'Rejected' WHERE id = ?");
            $update_reject_stmt->bind_param("i", $bid['id']);
            $update_reject_stmt->execute();
            $update_reject_stmt->close();
            
            // Notify the rejected supplier
            createNotification($bid['supplier_id'], "Your bid for '$item_name' (PO #$po_id) was not selected. Thank you for your submission.");
        }

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        // For debugging, you can log the error: error_log($e->getMessage());
        return false;
    }
}

/**
 * Gets count of pending purchase orders.
 * @return int The number of pending orders.
 */
function getPendingOrdersCount() {
    $conn = getDbConnection();
    $result = $conn->query("SELECT COUNT(*) as count FROM purchase_orders WHERE status = 'Pending'");
    $count = 0;
    
    if ($result) {
        $row = $result->fetch_assoc();
        $count = (int)$row['count'];
    }
    
    $conn->close();
    return $count;
}

/**
 * Gets recent bidding history for dashboard display.
 * @param int $limit The maximum number of bids to retrieve.
 * @return array An array of recent bid records with PO and supplier details.
 */
function getRecentBiddingHistory($limit = 5) {
    $conn = getDbConnection();
    $sql = "SELECT 
                b.id,
                b.bid_amount,
                b.bid_date,
                b.status as bid_status,
                po.item_name,
                po.quantity as po_quantity,
                po.status as po_status,
                s.supplier_name
            FROM bids b
            LEFT JOIN purchase_orders po ON b.po_id = po.id
            LEFT JOIN suppliers s ON b.supplier_id = s.id
            ORDER BY b.bid_date DESC
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $bids = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    $conn->close();
    return $bids;
}
?>