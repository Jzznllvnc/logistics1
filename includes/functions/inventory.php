<?php
// Logistic1/includes/functions/inventory.php

require_once __DIR__ . '/../config/db.php';

/**
 * Retrieves all items from the inventory.
 * @return array An array of inventory items.
 */
function getInventory() {
    $conn = getDbConnection();
    $result = $conn->query("SELECT id, item_name, quantity, last_updated FROM inventory ORDER BY item_name ASC");
    
    $items = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    
    $conn->close();
    return $items;
}

/**
 * Handles stocking in an item (adding or creating).
 * This version is SECURE and uses prepared statements.
 * @param string $itemName The name of the item.
 * @param int $quantity The quantity to add.
 * @return bool True on success, false on failure.
 */
function stockIn($itemName, $quantity) {
    if (empty($itemName) || !is_numeric($quantity) || $quantity <= 0) {
        return false;
    }
    
    $conn = getDbConnection();
    $quantity = (int)$quantity;

    // Use a prepared statement for security
    $stmt = $conn->prepare(
        "INSERT INTO inventory (item_name, quantity) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)"
    );
    $stmt->bind_param("si", $itemName, $quantity);
    
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    
    return $success;
}

/**
 * Handles stocking out an item (reducing quantity).
 * @param string $itemName The name of the item.
 * @param int $quantity The quantity to remove.
 * @return string "Success" on success, or an error message on failure.
 */
function stockOut($itemName, $quantity) {
    if (empty($itemName) || !is_numeric($quantity) || $quantity <= 0) {
        return "Invalid input.";
    }

    $conn = getDbConnection();
    $quantity = (int)$quantity;

    // Check current stock first to prevent negative inventory
    $stmt = $conn->prepare("SELECT quantity FROM inventory WHERE item_name = ?");
    $stmt->bind_param("s", $itemName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        return "Item not found in inventory.";
    }

    $currentStock = $result->fetch_assoc()['quantity'];
    if ($currentStock < $quantity) {
        $stmt->close();
        $conn->close();
        return "Stock-out failed. Only $currentStock items available.";
    }

    // Proceed with stock-out
    $updateStmt = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE item_name = ?");
    $updateStmt->bind_param("is", $quantity, $itemName);
    
    $message = $updateStmt->execute() ? "Success" : "An error occurred during stock-out.";

    $stmt->close();
    $updateStmt->close();
    $conn->close();
    
    return $message;
}

/**
 * Updates an inventory item's name. (Admin Only)
 * @param int $id The ID of the item to update.
 * @param string $itemName The new name for the item.
 * @return bool True on success, false on failure.
 */
function updateInventoryItem($id, $itemName) {
    if (empty($itemName) || !is_numeric($id)) {
        return false;
    }

    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE inventory SET item_name = ? WHERE id = ?");
    $stmt->bind_param("si", $itemName, $id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

/**
 * Deletes an inventory item. (Admin Only)
 * @param int $id The ID of the item to delete.
 * @return bool True on success, false on failure.
 */
function deleteInventoryItem($id) {
    if (!is_numeric($id)) {
        return false;
    }

    $conn = getDbConnection();
    $stmt = $conn->prepare("DELETE FROM inventory WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}
?>