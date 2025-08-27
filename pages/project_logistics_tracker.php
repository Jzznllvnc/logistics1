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
      <div class="flex justify-between items-center">
        <h1 class="font-semibold page-title">Project Logistics Tracker</h1>
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <button type="button" class="btn-primary" onclick="openCreateProjectModal()">
          <i data-lucide="folder-plus" class="w-5 h-5 lg:mr-2 sm:mr-0"></i><span class="hidden sm:inline">New Project</span>
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
            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full font-semibold leading-tight text-xs <?php 
              $status_class = '';
              $status_icon = '';
              switch(strtolower(str_replace(' ', '-', $project['status']))) {
                case 'in-progress': 
                  $status_class = 'bg-blue-100 text-blue-700'; 
                  $status_icon = 'clock';
                  break;
                case 'completed': 
                  $status_class = 'bg-green-100 text-green-700'; 
                  $status_icon = 'check-circle';
                  break;
                case 'not-started': 
                  $status_class = 'bg-gray-100 text-gray-700'; 
                  $status_icon = 'circle';
                  break;
                default: 
                  $status_class = 'bg-gray-100 text-gray-700';
                  $status_icon = 'circle';
              }
              echo $status_class;
            ?>">
              <i data-lucide="<?php echo $status_icon; ?>" class="w-3 h-3"></i>
              <?php echo htmlspecialchars($project['status']); ?>
            </span>
          </div>
          <div class="text-sm mb-2.5"><strong>Resources:</strong> <?php echo htmlspecialchars($project['assigned_suppliers'] ?? 'None'); ?></div>
          <?php if ($_SESSION['role'] === 'admin'): ?>
          <div class="mt-4 border-t border-[var(--card-border)] pt-4 text-right">
            <a class="ml-4 cursor-pointer hover:text-blue-500 transition-colors inline-flex items-center" onclick='openEditProjectModal(<?php echo json_encode($project); ?>, <?php echo json_encode($allSuppliers); ?>)'><i data-lucide="edit-3" class="w-4 h-4 mr-2"></i> Edit</a>
            <a class="ml-4 cursor-pointer hover:text-red-500 transition-colors inline-flex items-center" onclick="confirmDeleteProject(<?php echo $project['id']; ?>)"><i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Delete</a>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <?php include 'modals/plt.php'; ?>

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