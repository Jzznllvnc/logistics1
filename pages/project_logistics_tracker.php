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

// Handle form submissions (Admin Only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'admin') {
    $action = $_POST['action'] ?? '';
    $suppliers = $_POST['assigned_suppliers'] ?? [];

    if ($action === 'create_project' || $action === 'update_project') {
        $name = $_POST['project_name'] ?? '';
        $desc = $_POST['description'] ?? '';
        $status = $_POST['status'] ?? 'Not Started';
        $start = $_POST['start_date'] ?? null;
        $end = $_POST['end_date'] ?? null;

        if ($action === 'create_project') {
            if (createProject($name, $desc, $status, $start, $end, $suppliers)) {
                $_SESSION['flash_message'] = "Project <strong>" . htmlspecialchars($name) . "</strong> created successfully.";
                $_SESSION['flash_message_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = "Failed to create project. Please try again.";
                $_SESSION['flash_message_type'] = 'error';
            }
        } else {
            $id = $_POST['project_id'] ?? 0;
            if (updateProject($id, $name, $desc, $status, $start, $end, $suppliers)) {
                $_SESSION['flash_message'] = "Project <strong>" . htmlspecialchars($name) . "</strong> updated successfully.";
                $_SESSION['flash_message_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = "Failed to update project. Please try again.";
                $_SESSION['flash_message_type'] = 'error';
            }
        }
    } elseif ($action === 'delete_project') {
        $id = $_POST['project_id'] ?? 0;
        if (deleteProject($id)) {
            $_SESSION['flash_message'] = "Project deleted successfully.";
            $_SESSION['flash_message_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = "Failed to delete project. Please try again.";
            $_SESSION['flash_message_type'] = 'error';
        }
    }
    header("Location: project_logistics_tracker.php");
    exit();
}

// Check for flash messages
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $message_type = $_SESSION['flash_message_type'] ?? 'info';
    unset($_SESSION['flash_message'], $_SESSION['flash_message_type']);
} else {
    $message = '';
    $message_type = '';
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
  <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="stylesheet" href="../assets/css/sidebar.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha384-nRgPTkuX86pH8yjPJUAFuASXQSSl2/bBUiNV47vSYpKFxHJhbcrGnmlYpYJMeD7a" crossorigin="anonymous">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
  <div class="sidebar" id="sidebar"> <?php include '../partials/sidebar.php'; ?> </div>
  <div class="main-content-wrapper" id="mainContentWrapper">
    <div class="content" id="mainContent">
      <?php include '../partials/header.php'; ?>
      <div class="flex justify-between items-center mb-6">
        <h1 class="font-semibold page-title">Project Logistics Tracker (PLT)</h1>
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <button type="button" class="btn-primary" onclick="openCreateProjectModal()">
          <i data-lucide="plus" class="w-5 h-5 lg:mr-2 sm:mr-0"></i><span class="hidden sm:inline">New Project</span>
        </button>
        <?php endif; ?>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        <?php foreach($projects as $project): ?>
        <div class="bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl p-5 shadow-sm flex flex-col h-full">
          <h3 class="text-xl font-semibold mb-2.5 text-[var(--text-color)]"><?php echo htmlspecialchars($project['project_name']); ?></h3>
          <p class="description-text flex-grow"><?php echo htmlspecialchars($project['description']); ?></p>
          <div class="flex justify-between items-center text-sm mb-2.5">
            <div><strong>Timeline:</strong> <?php echo date('M d', strtotime($project['start_date'])); ?> - <?php echo date('M d, Y', strtotime($project['end_date'])); ?></div>
            <span class="py-1 px-2.5 rounded-2xl font-medium text-white <?php 
              $status_class = '';
              switch(strtolower(str_replace(' ', '-', $project['status']))) {
                case 'in-progress': $status_class = 'bg-blue-500'; break;
                case 'completed': $status_class = 'bg-emerald-500'; break;
                case 'not-started': $status_class = 'bg-gray-500'; break;
                default: $status_class = 'bg-gray-500';
              }
              echo $status_class;
            ?>"><?php echo htmlspecialchars($project['status']); ?></span>
          </div>
          <div class="text-sm mb-2.5"><strong>Resources:</strong> <?php echo htmlspecialchars($project['assigned_suppliers'] ?? 'None'); ?></div>
          <?php if ($_SESSION['role'] === 'admin'): ?>
          <div class="mt-4 border-t border-[var(--card-border)] pt-4 text-right">
            <a class="ml-4 cursor-pointer hover:text-blue-500 transition-colors inline-flex items-center" onclick='openEditProjectModal(<?php echo json_encode($project); ?>, <?php echo json_encode($allSuppliers); ?>)'><i data-lucide="edit-3" class="w-4 h-4 mr-1"></i> Edit</a>
            <a class="ml-4 cursor-pointer hover:text-red-500 transition-colors inline-flex items-center" onclick="confirmDeleteProject(<?php echo $project['id']; ?>)"><i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Delete</a>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <?php if ($_SESSION['role'] === 'admin'): ?>
  <div id="projectModal" class="modal hidden">
    <div class="modal-content p-8">
      <div class="flex justify-between items-center mb-2">
        <h2 class="modal-title" id="projectModalTitle">Create New Project</h2>
        <button type="button" class="close-button" onclick="closeModal('projectModal')">
          <i data-lucide="x" class="w-5 h-5 text-[var(--text-color)]"></i>
        </button>
      </div>
      
      <form id="projectForm" method="POST" action="project_logistics_tracker.php">
        <input type="hidden" name="action" id="formAction">
        <input type="hidden" name="project_id" id="projectId">
        
        <div class="mb-5">
          <label for="project_name" class="block font-semibold mb-2 text-[var(--text-color)]">Project Name</label>
          <input type="text" name="project_name" id="project_name" required class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" placeholder="Enter project name">
        </div>
        
        <div class="mb-5">
          <label for="description" class="block font-semibold mb-2 text-[var(--text-color)]">Description</label>
          <textarea name="description" id="description" rows="4" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" placeholder="Enter project description"></textarea>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
          <div>
            <label for="start_date" class="block font-semibold mb-2 text-[var(--text-color)]">Start Date</label>
            <input type="date" name="start_date" id="start_date" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
          </div>
          <div>
            <label for="end_date" class="block font-semibold mb-2 text-[var(--text-color)]">End Date</label>
            <input type="date" name="end_date" id="end_date" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
          </div>
        </div>
        
        <div class="mb-5">
          <label for="status" class="block font-semibold mb-2 text-[var(--text-color)]">Status</label>
          <select name="status" id="status" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
            <option value="Not Started">Not Started</option>
            <option value="In Progress">In Progress</option>
            <option value="Completed">Completed</option>
          </select>
        </div>
        
        <div class="mb-6">
          <label for="assigned_suppliers" class="block font-semibold mb-2 text-[var(--text-color)]">Assign Resources (Suppliers)</label>
          <select name="assigned_suppliers[]" id="assigned_suppliers" multiple size="5" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
            <?php foreach($allSuppliers as $supplier): ?>
              <option value="<?php echo $supplier['id']; ?>"><?php echo htmlspecialchars($supplier['supplier_name']); ?></option>
            <?php endforeach; ?>
          </select>
          <p class="text-sm text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple suppliers</p>
        </div>
        
        <div class="flex justify-end gap-3">
          <button type="button" class="px-5 py-2.5 rounded-md border border-gray-300 cursor-pointer font-semibold transition-all duration-300 bg-gray-100 text-gray-700 hover:bg-gray-200" onclick="closeModal(document.getElementById('projectModal'))">
            Cancel
          </button>
          <button type="submit" class="btn-primary">
            Save Project
          </button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <script src="../assets/js/sidebar.js"></script>
  <script src="../assets/js/script.js"></script>
  <script src="../assets/js/plt.js"></script>
  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
  
  <?php if ($message && !empty(trim($message))): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.showCustomAlert) {
            showCustomAlert(<?php echo json_encode($message); ?>, <?php echo json_encode($message_type); ?>);
        } else {
            // Fallback - strip HTML for plain alert
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = <?php echo json_encode($message); ?>;
            alert(tempDiv.textContent || tempDiv.innerText || '');
        }
    });
  </script>
  <?php endif; ?>
</body>
</html>