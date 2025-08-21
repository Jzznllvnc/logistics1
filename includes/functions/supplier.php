<?php
// Logistic1/includes/functions/supplier.php
require_once __DIR__ . '/../config/db.php';

function getAllSuppliers() {
    $conn = getDbConnection();
    $result = $conn->query("SELECT * FROM suppliers ORDER BY supplier_name ASC");
    $suppliers = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $conn->close();
    return $suppliers;
}

function createSupplier($name, $contact, $email, $phone, $address) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO suppliers (supplier_name, contact_person, email, phone, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $contact, $email, $phone, $address);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

function updateSupplier($id, $name, $contact, $email, $phone, $address) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE suppliers SET supplier_name = ?, contact_person = ?, email = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $name, $contact, $email, $phone, $address, $id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

function deleteSupplier($id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("DELETE FROM suppliers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}
?>