<?php
// Logistic1/includes/functions/asset.php
require_once __DIR__ . '/../config/db.php';

// --- Asset CRUD Functions ---
function getAllAssets() {
    $conn = getDbConnection();
    $result = $conn->query("SELECT * FROM assets ORDER BY asset_name ASC");
    $assets = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $conn->close();
    return $assets;
}

function createAsset($name, $type, $purchase_date, $status) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO assets (asset_name, asset_type, purchase_date, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $type, $purchase_date, $status);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

function updateAsset($id, $name, $type, $purchase_date, $status) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE assets SET asset_name = ?, asset_type = ?, purchase_date = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $name, $type, $purchase_date, $status, $id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

function deleteAsset($id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("DELETE FROM assets WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

// --- Maintenance Schedule Functions ---
function getMaintenanceSchedules() {
    $conn = getDbConnection();
    $sql = "SELECT ms.id, a.asset_name, ms.task_description, ms.scheduled_date, ms.status
            FROM maintenance_schedules ms
            JOIN assets a ON ms.asset_id = a.id
            ORDER BY ms.scheduled_date ASC";
    $result = $conn->query($sql);
    $schedules = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $conn->close();
    return $schedules;
}

function createMaintenanceSchedule($asset_id, $description, $scheduled_date) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO maintenance_schedules (asset_id, task_description, scheduled_date) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $asset_id, $description, $scheduled_date);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

function updateMaintenanceStatus($schedule_id, $status) {
    $conn = getDbConnection();
    $completed_date = ($status === 'Completed') ? date('Y-m-d') : null;
    $stmt = $conn->prepare("UPDATE maintenance_schedules SET status = ?, completed_date = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $completed_date, $schedule_id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}
?>