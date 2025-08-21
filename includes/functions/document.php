<?php
// Logistic1/includes/functions/document.php
require_once __DIR__ . '/../config/db.php';

// --- Document Management Functions ---

function getAllDocuments() {
    $conn = getDbConnection();
    $result = $conn->query("SELECT * FROM documents ORDER BY upload_date DESC");
    $documents = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $conn->close();
    return $documents;
}

/**
 * Handles file upload and metadata storage.
 * @param array $file The $_FILES['documentFile'] array.
 * @param array $metadata The post data containing type, reference, and expiry.
 * @return string Success message or error message.
 */
function uploadDocument($file, $metadata) {
    $uploadDir = __DIR__ . '/../../uploads/'; // Relative path to the uploads folder
    $fileName = basename($file['name']);
    $targetFilePath = $uploadDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    // Basic validation
    if (empty($fileName)) return "Error: Please select a file to upload.";
    if ($file['error']) return "Error: There was an error with your upload - code " . $file['error'];
    if ($file['size'] > 5000000) return "Error: File is too large (max 5MB).";
    
    // Allow certain file formats
    $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];
    if (!in_array(strtolower($fileType), $allowedTypes)) {
        return "Error: Only PDF, DOC, XLS, and image files are allowed.";
    }

    // Check if file already exists to prevent overwriting
    if (file_exists($targetFilePath)) {
        return "Error: A file with this name already exists.";
    }

    // Move the file to the uploads directory
    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        // File uploaded successfully, now store metadata in the database
        $conn = getDbConnection();
        $stmt = $conn->prepare(
            "INSERT INTO documents (file_name, file_path, document_type, reference_number, expiry_date) VALUES (?, ?, ?, ?, ?)"
        );
        
        $filePathForDb = 'uploads/' . $fileName; // Path to store in DB
        $expiry = !empty($metadata['expiry_date']) ? $metadata['expiry_date'] : null;

        $stmt->bind_param("sssss", $fileName, $filePathForDb, $metadata['document_type'], $metadata['reference_number'], $expiry);
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            return "Success: The file ". htmlspecialchars($fileName). " has been uploaded.";
        } else {
            // If DB insert fails, delete the uploaded file
            unlink($targetFilePath);
            $stmt->close();
            $conn->close();
            return "Error: File uploaded but failed to save metadata to the database.";
        }
    } else {
        return "Error: There was an error moving the uploaded file.";
    }
}
?>