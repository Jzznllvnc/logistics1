<?php
// Logistic1/includes/functions/project.php
require_once __DIR__ . '/../config/db.php';

function getAllProjects() {
    $conn = getDbConnection();
    // This complex query fetches each project and aggregates its assigned supplier names into a single string
    $sql = "SELECT p.*, GROUP_CONCAT(s.supplier_name SEPARATOR ', ') as assigned_suppliers
            FROM projects p
            LEFT JOIN project_resources pr ON p.id = pr.project_id
            LEFT JOIN suppliers s ON pr.supplier_id = s.id
            GROUP BY p.id
            ORDER BY p.start_date DESC";
    $result = $conn->query($sql);
    $projects = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $conn->close();
    return $projects;
}

function createProject($name, $desc, $status, $start, $end, $suppliers) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO projects (project_name, description, status, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $desc, $status, $start, $end);
    $stmt->execute();
    $projectId = $stmt->insert_id; // Get the ID of the new project
    $stmt->close();
    
    updateProjectResources($projectId, $suppliers); // Assign suppliers
    
    $conn->close();
    return $projectId;
}

function updateProject($id, $name, $desc, $status, $start, $end, $suppliers) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE projects SET project_name = ?, description = ?, status = ?, start_date = ?, end_date = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $name, $desc, $status, $start, $end, $id);
    $stmt->execute();
    $stmt->close();

    updateProjectResources($id, $suppliers); // Re-sync suppliers
    
    $conn->close();
    return true;
}

function deleteProject($id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

// Helper function to manage supplier assignments
function updateProjectResources($projectId, $supplierIds) {
    $conn = getDbConnection();
    // First, remove all existing resources for this project
    $stmt = $conn->prepare("DELETE FROM project_resources WHERE project_id = ?");
    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $stmt->close();

    // Now, insert the new ones
    if (!empty($supplierIds)) {
        $stmt = $conn->prepare("INSERT INTO project_resources (project_id, supplier_id) VALUES (?, ?)");
        foreach ($supplierIds as $supplierId) {
            $stmt->bind_param("ii", $projectId, $supplierId);
            $stmt->execute();
        }
        $stmt->close();
    }
    $conn->close();
}
?>