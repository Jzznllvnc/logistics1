<?php
// Logistic1/includes/functions/purchase_order.php
require_once __DIR__ . '/../config/db.php';

function createPurchaseOrder($supplier_id, $item_name, $quantity) {
    if (empty($supplier_id) || empty($item_name) || !is_numeric($quantity) || $quantity <= 0) {
        return false;
    }
    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO purchase_orders (supplier_id, item_name, quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $supplier_id, $item_name, $quantity);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

function getRecentPurchaseOrders($limit = 10) {
    $conn = getDbConnection();
    // Join with suppliers table to get the supplier name
    $sql = "SELECT po.id, s.supplier_name, po.item_name, po.quantity, po.status, po.order_date 
            FROM purchase_orders po
            JOIN suppliers s ON po.supplier_id = s.id
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
?>