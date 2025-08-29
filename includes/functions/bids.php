<?php
// Logistic1/includes/functions/bids.php
require_once __DIR__ . '/../config/db.php';

/**
 * Retrieves all purchase orders with the status 'Open for Bidding'.
 * @return array An array of purchase order records.
 */
function getOpenForBiddingPOs() {
    $conn = getDbConnection();
    $result = $conn->query("SELECT * FROM purchase_orders WHERE status = 'Open for Bidding' ORDER BY order_date DESC");
    $pos = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $conn->close();
    return $pos;
}

/**
 * Creates a new bid from a supplier for a specific purchase order.
 * @param int $po_id
 * @param int $supplier_id
 * @param float $bid_amount
 * @param string $notes
 * @return bool True on success, false on failure.
 */
function createBid($po_id, $supplier_id, $bid_amount, $notes) {
    if (empty($po_id) || empty($supplier_id) || !is_numeric($bid_amount) || $bid_amount <= 0) {
        return false;
    }
    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO bids (po_id, supplier_id, bid_amount, notes) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iids", $po_id, $supplier_id, $bid_amount, $notes);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

/**
 * Retrieves all bids submitted for a specific purchase order.
 * @param int $po_id
 * @return array An array of bid records, joined with supplier names.
 */
function getBidsForPO($po_id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare(
        "SELECT b.*, s.supplier_name 
         FROM bids b 
         JOIN suppliers s ON b.supplier_id = s.id 
         WHERE b.po_id = ? 
         ORDER BY b.bid_amount ASC"
    );
    $stmt->bind_param("i", $po_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $bids = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    $conn->close();
    return $bids;
}

/**
 * Finds the supplier ID associated with a given username.
 * @param string $username
 * @return int|null The supplier's ID or null if not found.
 */
function getSupplierIdFromUsername($username) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT supplier_id FROM users WHERE username = ? AND role = 'supplier'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $user ? (int)$user['supplier_id'] : null;
}

/**
 * Rejects a specific bid and notifies the supplier.
 * @param int $bid_id The ID of the bid to reject.
 * @return bool True on success, false on failure.
 */
function rejectBid($bid_id) {
    if (empty($bid_id)) {
        return false;
    }
    $conn = getDbConnection();
    
    // --- FIX STARTS HERE ---
    // First, get the bid details needed for the notification
    $bid_stmt = $conn->prepare(
        "SELECT b.supplier_id, po.item_name, po.id as po_id 
         FROM bids b 
         JOIN purchase_orders po ON b.po_id = po.id 
         WHERE b.id = ?"
    );
    $bid_stmt->bind_param("i", $bid_id);
    $bid_stmt->execute();
    $bid_result = $bid_stmt->get_result();
    $bid_details = $bid_result->fetch_assoc();
    $bid_stmt->close();

    if (!$bid_details) {
        $conn->close();
        return false; // Bid not found
    }

    // Now, update the bid status to 'Rejected'
    $stmt = $conn->prepare("UPDATE bids SET status = 'Rejected' WHERE id = ?");
    $stmt->bind_param("i", $bid_id);
    $success = $stmt->execute();
    $stmt->close();

    // If the update was successful, create the notification
    if ($success) {
        $supplier_id = $bid_details['supplier_id'];
        $item_name = $bid_details['item_name'];
        $po_id = $bid_details['po_id'];
        createNotification($supplier_id, "Your bid for '$item_name' (PO #$po_id) was not selected. Thank you for your submission.");
    }
    // --- FIX ENDS HERE ---
    
    $conn->close();
    return $success;
}


/**
 * Counts the total number of purchase orders currently open for bidding.
 * @return int The total count.
 */
function getOpenBiddingCount() {
    $conn = getDbConnection();
    $result = $conn->query("SELECT COUNT(*) as count FROM purchase_orders WHERE status = 'Open for Bidding'");
    $count = $result ? $result->fetch_assoc()['count'] : 0;
    $conn->close();
    return $count;
}

/**
 * Counts the number of bids awarded to a specific supplier.
 * @param int $supplier_id
 * @return int The count of awarded bids.
 */
function getAwardedBidsCountBySupplier($supplier_id) {
    if (!$supplier_id) return 0;
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM bids WHERE supplier_id = ? AND status = 'Awarded'");
    $stmt->bind_param("i", $supplier_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result ? $result->fetch_assoc()['count'] : 0;
    $stmt->close();
    $conn->close();
    return $count;
}

/**
 * Counts the number of active (pending) bids for a specific supplier.
 * @param int $supplier_id
 * @return int The count of pending bids.
 */
function getActiveBidsCountBySupplier($supplier_id) {
    if (!$supplier_id) return 0;
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM bids WHERE supplier_id = ? AND status = 'Pending'");
    $stmt->bind_param("i", $supplier_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result ? $result->fetch_assoc()['count'] : 0;
    $stmt->close();
    $conn->close();
    return $count;
}
function getBidsBySupplier($supplier_id) {
    if (!$supplier_id) return [];
    $conn = getDbConnection();
    $stmt = $conn->prepare(
        "SELECT b.*, po.item_name 
         FROM bids b 
         JOIN purchase_orders po ON b.po_id = po.id 
         WHERE b.supplier_id = ? 
         ORDER BY b.bid_date DESC"
    );
    $stmt->bind_param("i", $supplier_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $bids = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    $conn->close();
    return $bids;
}
/**
 * Retrieves the full bidding history for a specific supplier.
 * @param int $supplier_id The supplier's ID.
 * @return array An array of the supplier's bids with item names.
 */
function getBiddingHistoryBySupplier($supplier_id) {
    if (!$supplier_id) return [];
    $conn = getDbConnection();
    $stmt = $conn->prepare(
        "SELECT b.status, b.bid_amount, po.item_name
         FROM bids b
         JOIN purchase_orders po ON b.po_id = po.id
         WHERE b.supplier_id = ?"
    );
    $stmt->bind_param("i", $supplier_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $history = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    $conn->close();
    return $history;
}
/**
 * Retrieves the price history for a specific item from past bids.
 * @param string $item_name The name of the item.
 * @return array An array of the item's price history.
 */
function getItemPriceHistory($item_name) {
    if (empty($item_name)) return [];
    $conn = getDbConnection();
    $stmt = $conn->prepare(
        "SELECT b.bid_amount, b.bid_date
         FROM bids b
         JOIN purchase_orders po ON b.po_id = po.id
         WHERE po.item_name = ?
         ORDER BY b.bid_date ASC"
    );
    $stmt->bind_param("s", $item_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $history = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    $conn->close();
    return $history;
}
?>