<?php
// Logistic1/includes/functions/supplier.php
require_once __DIR__ . '/../config/db.php';

/**
 * Retrieves all suppliers from the database.
 * @return array An array of supplier records.
 */
function getAllSuppliers() {
    $conn = getDbConnection();
    $result = $conn->query("SELECT * FROM suppliers ORDER BY supplier_name ASC");
    $suppliers = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $conn->close();
    return $suppliers;
}

/**
 * Creates a new supplier record (typically used by admins).
 * @param string $name
 * @param string $contact
 * @param string $email
 * @param string $phone
 * @param string $address
 * @return bool True on success, false on failure.
 */
function createSupplier($name, $contact, $email, $phone, $address) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO suppliers (supplier_name, contact_person, email, phone, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $contact, $email, $phone, $address);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

/**
 * Updates an existing supplier's information.
 * @param int $id
 * @param string $name
 * @param string $contact
 * @param string $email
 * @param string $phone
 * @param string $address
 * @return bool True on success, false on failure.
 */
function updateSupplier($id, $name, $contact, $email, $phone, $address) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE suppliers SET supplier_name = ?, contact_person = ?, email = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $name, $contact, $email, $phone, $address, $id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

/**
 * Deletes a supplier from the database.
 * @param int $id
 * @return bool True on success, false on failure.
 */
function deleteSupplier($id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("DELETE FROM suppliers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

/**
 * Handles the supplier registration process, including file upload and user creation.
 * @param array $data The supplier's information from the registration form.
 * @param array $file The uploaded verification document from $_FILES.
 * @return bool|string True on success, or an error message string on failure.
 */
function registerSupplier($data, $file) {
    // --- File Upload Handling ---
    $uploadDir = __DIR__ . '/../../uploads/verification/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $fileName = uniqid() . '-' . basename($file['name']);
    $targetFilePath = $uploadDir . $fileName;
    $dbPath = 'uploads/verification/' . $fileName; // Relative path for the database

    // Move the uploaded file
    if (!move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        return "Failed to upload verification document.";
    }

    $conn = getDbConnection();
    
    // --- Create the Supplier Record ---
    $stmt1 = $conn->prepare("INSERT INTO suppliers (supplier_name, contact_person, email, phone, address, verification_document_path, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt1->bind_param("ssssss", $data['supplier_name'], $data['contact_person'], $data['email'], $data['phone'], $data['address'], $dbPath);
    
    if (!$stmt1->execute()) {
        unlink($targetFilePath); // Clean up uploaded file if the database insert fails
        $stmt1->close();
        $conn->close();
        return "Failed to create supplier record. The email or company name may already be registered.";
    }
    $supplier_id = $stmt1->insert_id;
    $stmt1->close();

    // --- Create the User Record ---
    // **THE CHANGE IS HERE**: The password is now saved as plain text without hashing.
    $password = $data['password']; 
    
    $stmt2 = $conn->prepare("INSERT INTO users (username, password, role, supplier_id) VALUES (?, ?, 'supplier', ?)");
    $stmt2->bind_param("ssi", $data['username'], $password, $supplier_id);
    
    if (!$stmt2->execute()) {
        // If user creation fails, roll back the supplier creation to avoid orphaned data
        $conn->query("DELETE FROM suppliers WHERE id = $supplier_id");
        unlink($targetFilePath); // Also delete the uploaded file
        $stmt2->close();
        $conn->close();
        return "Failed to create user account. The username may already be taken.";
    }
    $stmt2->close();
    
    $conn->close();
    return true;
}

/**
 * Retrieves all suppliers with a 'Pending' status.
 * @return array An array of pending supplier records.
 */
function getPendingSuppliers() {
    $conn = getDbConnection();
    $result = $conn->query("SELECT * FROM suppliers WHERE status = 'Pending' ORDER BY created_at DESC");
    $suppliers = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $conn->close();
    return $suppliers;
}

/**
 * Updates the status of a supplier (e.g., 'Approved', 'Rejected').
 * @param int $supplier_id The ID of the supplier to update.
 * @param string $status The new status.
 * @return bool True on success, false on failure.
 */
function updateSupplierStatus($supplier_id, $status) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE suppliers SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $supplier_id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}
function getSupplierDetails($supplier_id) {
    if (!$supplier_id) return null;
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM suppliers WHERE id = ?");
    $stmt->bind_param("i", $supplier_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $details = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $details;
}

function updateSupplierProfile($supplier_id, $data) {
    if (!$supplier_id) return false;
    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE suppliers SET supplier_name = ?, contact_person = ?, email = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $data['supplier_name'], $data['contact_person'], $data['email'], $data['phone'], $data['address'], $supplier_id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}
?>
