<?php
require_once '../includes/functions/auth.php';
require_once '../includes/functions/project.php';
require_once '../includes/functions/supplier.php'; // Needed for the resource list
requireLogin();

// Role check
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'plt') {
    header("Location: dashboard.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $suppliers = $_POST['assigned_suppliers'] ?? [];

    if ($action === 'create_project' || $action === 'update_project') {
        $name = $_POST['project_name'] ?? '';
        $desc = $_POST['description'] ?? '';
        $status = $_POST['status'] ?? 'Not Started';
        $start = $_POST['start_date'] ?? null;
        $end = $_POST['end_date'] ?? null;

        if ($action === 'create_project') {
            createProject($name, $desc, $status, $start, $end, $suppliers);
        } else {
            $id = $_POST['project_id'] ?? 0;
            updateProject($id, $name, $desc, $status, $start, $end, $suppliers);
        }
    } elseif ($action === 'delete_project') {
        $id = $_POST['project_id'] ?? 0;
        deleteProject($id);
    }
    header("Location: project_logistics_tracker.php");
    exit();
}

$projects = getAllProjects();
$allSuppliers = getAllSuppliers(); // For the modal dropdown
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <script>document.documentElement.classList.add('preload', 'loading');</script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logistics 1 - PLT</title>
  <link rel="icon" href="../assets/images/slate2.png" type="image/png">
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="stylesheet" href="../assets/css/sidebar.css">
  <style>
    .plt-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    .btn-add { background: var(--primary-btn-bg); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; }
    .project-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; }
    .project-card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); display: flex; flex-direction: column; }
    .project-card h3 { font-size: 1.2rem; font-weight: 600; margin-bottom: 10px; }
    .project-card p { color: #888; margin-bottom: 15px; flex-grow: 1; }
    .project-meta { display: flex; justify-content: space-between; align-items: center; font-size: 0.9em; }
    .status-pill { padding: 4px 10px; border-radius: 15px; font-weight: 500; }
    .status-in-progress { background: #3b82f6; color: white; }
    .status-completed { background: #10b981; color: white; }
    .status-not-started { background: #6b7280; color: white; }
    .project-actions { margin-top: 15px; border-top: 1px solid var(--card-border); padding-top: 15px; text-align: right; }
    .actions a { margin-left: 15px; cursor: pointer; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: 500; margin-bottom: 5px; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid var(--input-border); border-radius: 6px; background: var(--input-bg); color: var(--input-text); }
  </style>
</head>
<body>
  <div class="sidebar" id="sidebar"> <?php include '../partials/sidebar.php'; ?> </div>
  <div class="main-content-wrapper" id="mainContentWrapper">
    <div class="content" id="mainContent">
      <?php include '../partials/header.php'; ?>
      <div class="plt-header">
        <h1 class="page-title" style="margin: 0;">Project Logistics Tracker (PLT)</h1>
        <button type="button" class="btn-add" onclick="openCreateProjectModal()"><i class="fas fa-plus"></i> New Project</button>
      </div>
      
      <div class="project-grid">
        <?php foreach($projects as $project): ?>
        <div class="project-card">
          <h3><?php echo htmlspecialchars($project['project_name']); ?></h3>
          <p><?php echo htmlspecialchars($project['description']); ?></p>
          <div class="project-meta">
            <div><strong>Timeline:</strong> <?php echo date('M d', strtotime($project['start_date'])); ?> - <?php echo date('M d, Y', strtotime($project['end_date'])); ?></div>
            <span class="status-pill status-<?php echo strtolower(str_replace(' ', '-', $project['status'])); ?>"><?php echo htmlspecialchars($project['status']); ?></span>
          </div>
          <div style="font-size: 0.9em; margin-top: 10px;"><strong>Resources:</strong> <?php echo htmlspecialchars($project['assigned_suppliers'] ?? 'None'); ?></div>
          <div class="project-actions actions">
            <a class="edit" onclick='openEditProjectModal(<?php echo json_encode($project); ?>, <?php echo json_encode($allSuppliers); ?>)'><i class="fas fa-pencil-alt"></i> Edit</a>
            <a class="delete" onclick="confirmDeleteProject(<?php echo $project['id']; ?>)"><i class="fas fa-trash-alt"></i> Delete</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div id="projectModal" class="modal" style="display:none;">
    <div class="modal-content bg-[var(--card-bg)] p-8 rounded-lg shadow-xl relative">
      <h2 id="modalTitle" class="text-2xl font-bold mb-4"></h2>
      <form id="projectForm" method="POST" action="project_logistics_tracker.php">
        <input type="hidden" name="action" id="formAction">
        <input type="hidden" name="project_id" id="projectId">
        <div class="form-group"><label>Project Name</label><input type="text" name="project_name" id="project_name" required></div>
        <div class="form-group"><label>Description</label><textarea name="description" id="description" rows="4"></textarea></div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
          <div class="form-group"><label>Start Date</label><input type="date" name="start_date" id="start_date"></div>
          <div class="form-group"><label>End Date</label><input type="date" name="end_date" id="end_date"></div>
        </div>
        <div class="form-group"><label>Status</label>
          <select name="status" id="status">
            <option>Not Started</option><option>In Progress</option><option>Completed</option>
          </select>
        </div>
        <div class="form-group"><label>Assign Resources (Suppliers)</label>
          <select name="assigned_suppliers[]" id="assigned_suppliers" multiple size="5">
            <?php foreach($allSuppliers as $supplier): ?>
            <option value="<?php echo $supplier['id']; ?>"><?php echo htmlspecialchars($supplier['supplier_name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-actions flex justify-end gap-4 mt-6">
          <button type="button" class="btn bg-gray-300" onclick="closeModal(document.getElementById('projectModal'))">Cancel</button>
          <button type="submit" class="btn btn-danger bg-green-500 text-white">Save Project</button>
        </div>
      </form>
    </div>
  </div>

  <script src="../assets/js/sidebar.js"></script>
  <script src="../assets/js/script.js"></script>
  <script src="../assets/js/plt.js"></script>
</body>
</html>