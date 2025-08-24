<?php
require_once '../includes/functions/auth.php';
require_once '../includes/functions/asset.php';
requireLogin();

// Role check
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'alms') {
    header("Location: dashboard.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // --- Asset CRUD Actions (Admin & ALMS roles) ---
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'alms') {
        if ($action === 'create_asset' || $action === 'update_asset') {
            $name = $_POST['asset_name'] ?? '';
            $type = $_POST['asset_type'] ?? '';
            $purchase_date = $_POST['purchase_date'] ?? null;
            $status = $_POST['status'] ?? '';
            if ($action === 'create_asset') {
                if (createAsset($name, $type, $purchase_date, $status)) {
                    $_SESSION['flash_message'] = "Asset <strong>" . htmlspecialchars($name) . "</strong> registered successfully.";
                    $_SESSION['flash_message_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = "Failed to register asset. Please try again.";
                    $_SESSION['flash_message_type'] = 'error';
                }
            } else {
                $id = $_POST['asset_id'] ?? 0;
                if (updateAsset($id, $name, $type, $purchase_date, $status)) {
                    $_SESSION['flash_message'] = "Asset <strong>" . htmlspecialchars($name) . "</strong> updated successfully.";
                    $_SESSION['flash_message_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = "Failed to update asset. Please try again.";
                    $_SESSION['flash_message_type'] = 'error';
                }
            }
        } elseif ($action === 'delete_asset') {
            $id = $_POST['asset_id'] ?? 0;
            if (deleteAsset($id)) {
                $_SESSION['flash_message'] = "Asset deleted successfully.";
                $_SESSION['flash_message_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = "Failed to delete asset. Please try again.";
                $_SESSION['flash_message_type'] = 'error';
            }
        }
    }
    
    // --- Actions for Maintenance Scheduling (All Roles on this page) ---
    if ($action === 'schedule_maintenance') {
        $asset_id = $_POST['asset_id_maint'] ?? 0;
        $description = $_POST['task_description'] ?? '';
        $scheduled_date = $_POST['scheduled_date'] ?? null;
        if (createMaintenanceSchedule($asset_id, $description, $scheduled_date)) {
            $_SESSION['flash_message'] = "Maintenance task scheduled successfully.";
            $_SESSION['flash_message_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = "Failed to schedule maintenance task. Please try again.";
            $_SESSION['flash_message_type'] = 'error';
        }
    } elseif ($action === 'update_maintenance_status') {
        $schedule_id = $_POST['schedule_id'] ?? 0;
        $new_status = $_POST['new_status'] ?? '';
        if (updateMaintenanceStatus($schedule_id, $new_status)) {
            $_SESSION['flash_message'] = "Maintenance status updated to <strong>" . htmlspecialchars($new_status) . "</strong>.";
            $_SESSION['flash_message_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = "Failed to update maintenance status. Please try again.";
            $_SESSION['flash_message_type'] = 'error';
        }
    }
    
    header("Location: asset_lifecycle_maintenance.php");
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

$assets = getAllAssets();
$schedules = getMaintenanceSchedules();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <script>document.documentElement.classList.add('preload', 'loading');</script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logistics 1 - ALMS</title>
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
      <h1 class="font-semibold page-title">Asset Lifecycle & Maintenance</h1>
      
      <!-- Tabs Navigator -->
      <div class="tabs-container mb-3">
        <div class="tabs-bar">
          <button class="tab-button active" data-tab="asset-registry">
            <i data-lucide="package" class="w-4 h-4 mr-2"></i>
            Asset Registry
          </button>
          <button class="tab-button" data-tab="maintenance-schedule">
            <i data-lucide="calendar-check" class="w-4 h-4 mr-2"></i>
            Maintenance Schedule
          </button>
        </div>
      </div>
      
      <!-- Tab Content -->
      <div class="tab-content active" id="asset-registry-tab">
        <div class="grid grid-cols-1 lg:grid-cols-1 gap-8">
          <div class="bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl p-6 shadow-sm">
            <div class="flex justify-between items-center mb-5">
              <h2 class="text-2xl font-semibold text-[var(--text-color)]">Asset Registry</h2>
              <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'alms'): ?>
              <button type="button" class="btn-primary" onclick="openCreateAssetModal()">
                <i data-lucide="file-box" class="w-5 h-5 lg:mr-2 sm:mr-0"></i><span class="hidden sm:inline">Register Asset</span>
              </button>
              <?php endif; ?>
            </div>
            <div class="table-container">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Status</th>
                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'alms'): ?><th>Action</th><?php endif; ?>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($assets as $asset): ?>
                  <tr>
                    <td class="py-3 px-4 border-b border-[var(--card-border)]"><?php echo htmlspecialchars($asset['asset_name']); ?></td>
                    <td class="py-3 px-4 border-b border-[var(--card-border)]"><?php echo htmlspecialchars($asset['asset_type']); ?></td>
                    <td class="py-3 px-4 border-b border-[var(--card-border)]"><?php echo htmlspecialchars($asset['status']); ?></td>
                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'alms'): ?>
                    <td class="py-3 px-4 border-b border-[var(--card-border)]">
                      <div class="relative">
                        <button type="button" class="action-dropdown-btn p-2 rounded-full transition-colors" onclick="toggleAssetDropdown(<?php echo $asset['id']; ?>)">
                          <i data-lucide="more-horizontal" class="w-6 h-6"></i>
                        </button>
                        <div id="asset-dropdown-<?php echo $asset['id']; ?>" class="action-dropdown hidden">
                          <button type="button" onclick='openEditAssetModal(<?php echo json_encode($asset); ?>)'>
                            <i data-lucide="edit-3" class="w-4 h-4 mr-3"></i>
                            Edit
                          </button>
                          <button type="button" onclick="confirmDeleteAsset(<?php echo $asset['id']; ?>)">
                            <i data-lucide="trash-2" class="w-4 h-4 mr-3"></i>
                            Delete
                          </button>
                        </div>
                      </div>
                    </td>
                    <?php endif; ?>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="tab-content" id="maintenance-schedule-tab">
        <div class="mt-8 bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl p-6 shadow-sm">
          <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-semibold text-[var(--text-color)]">Maintenance Schedule</h2>
            <button type="button" id="scheduleTaskBtn" class="btn-primary">
              <i data-lucide="calendar-plus" class="w-5 h-5 lg:mr-2 sm:mr-0"></i><span class="hidden sm:inline">Schedule Task</span>
            </button>
          </div>
          <div class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th class="w-1/4">Asset</th>
                  <th class="w-1/5">Task</th>
                  <th class="w-1/5">Scheduled Date</th>
                  <th class="w-1/5">Status</th>
                  <th class="w-32">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($schedules as $schedule): ?>
                <tr>
                  <td class="py-3 px-4 border-b border-[var(--card-border)]"><?php echo htmlspecialchars($schedule['asset_name']); ?></td>
                  <td class="py-3 px-4 border-b border-[var(--card-border)]"><?php echo htmlspecialchars($schedule['task_description']); ?></td>
                  <td class="py-3 px-4 border-b border-[var(--card-border)]"><?php echo date('M d, Y', strtotime($schedule['scheduled_date'])); ?></td>
                  <td class="py-3 px-4 border-b border-[var(--card-border)]"><?php echo htmlspecialchars($schedule['status']); ?></td>
                  <td class="py-3 px-4 border-b border-[var(--card-border)]">
                    <?php if($schedule['status'] === 'Scheduled'): ?>
                    <form action="asset_lifecycle_maintenance.php" method="POST" class="m-0">
                      <input type="hidden" name="action" value="update_maintenance_status">
                      <input type="hidden" name="schedule_id" value="<?php echo $schedule['id']; ?>">
                      <input type="hidden" name="new_status" value="Completed">
                      <button type="submit" class="bg-emerald-500 text-white py-1 px-2.5 text-xs rounded-md hover:bg-emerald-600 transition-colors">Mark as Complete</button>
                    </form>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'alms'): ?>
  <div id="assetModal" class="modal hidden">
    <div class="modal-content p-8 max-w-xl">
      <div class="flex justify-between items-center mb-2">
        <h2 class="modal-title flex items-center min-w-0 flex-1" id="assetModalTitle">
          <i data-lucide="package" class="w-6 h-6 mr-3 flex-shrink-0" id="assetModalIcon"></i>
          <span id="assetModalTitleText" class="truncate">Register New Asset</span>
        </h2>
        <button type="button" class="close-button flex-shrink-0 ml-3" onclick="closeModal('assetModal')">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      <p class="modal-subtitle" id="assetModalSubtitle">Add a new logistics asset to the registry.</p>
      <div class="border-b border-[var(--card-border)] mb-5"></div>
      
      <form id="assetForm" method="POST" action="asset_lifecycle_maintenance.php">
        <input type="hidden" name="action" id="formAction">
        <input type="hidden" name="asset_id" id="assetId">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div class="mb-5">
            <label for="asset_name" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Asset Name</label>
            <input type="text" name="asset_name" id="asset_name" required class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" placeholder="Enter asset name">
          </div>
          
          <div class="mb-5">
            <label for="asset_type" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Asset Type</label>
            <input type="text" name="asset_type" id="asset_type" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" placeholder="e.g., Vehicle, Equipment">
          </div>
          
          <div class="mb-5">
            <label for="purchase_date" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Purchase Date</label>
            <input type="date" name="purchase_date" id="purchase_date" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
          </div>
          
          <div class="mb-6">
            <label for="status" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Status</label>
            <select name="status" id="status" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
              <option value="Operational">Operational</option>
              <option value="Under Maintenance">Under Maintenance</option>
              <option value="Decommissioned">Decommissioned</option>
            </select>
          </div>
        </div>
        
        <div class="flex justify-end gap-3 mt-5">
          <button type="button" class="px-5 py-2.5 rounded-md border border-gray-300 cursor-pointer font-semibold transition-all duration-300 bg-gray-100 text-gray-700 hover:bg-gray-200" onclick="closeModal(document.getElementById('assetModal'))">
            Cancel
          </button>
          <button type="submit" class="btn-primary">
            Save Asset
          </button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- Schedule Maintenance Modal -->
  <div id="scheduleMaintenanceModal" class="modal hidden">
    <div class="modal-content p-8 max-w-lg">
      <div class="flex justify-between items-center mb-2">
        <h2 class="modal-title flex items-center min-w-0 flex-1">
          <i data-lucide="calendar-plus" class="w-6 h-6 mr-3 flex-shrink-0"></i>
          <span class="truncate">Schedule Maintenance Task</span>
        </h2>
        <button type="button" class="close-button flex-shrink-0 ml-3" onclick="closeModal('scheduleMaintenanceModal')">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      <p class="modal-subtitle" id="maintenanceModalSubtitle">Schedule a maintenance task for a logistics asset.</p>
      <div class="border-b border-[var(--card-border)] mb-5"></div>

      <form action="asset_lifecycle_maintenance.php" method="POST" id="scheduleMaintenanceForm">
        <input type="hidden" name="action" value="schedule_maintenance">
        
        <div class="mb-5">
          <label for="asset_id_maint" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Asset</label>
          <select name="asset_id_maint" id="asset_id_maint" required class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
            <option value="">-- Select Asset --</option>
            <?php foreach($assets as $asset): ?>
              <option value="<?php echo $asset['id']; ?>"><?php echo htmlspecialchars($asset['asset_name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="mb-5">
          <label for="task_description" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Task Description</label>
          <textarea name="task_description" id="task_description" rows="3" required class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" placeholder="Describe the maintenance task"></textarea>
        </div>
        
        <div class="mb-6">
          <label for="scheduled_date" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Scheduled Date</label>
          <input type="date" name="scheduled_date" id="scheduled_date" required class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
        </div>
        
        <div class="flex justify-end gap-3 mt-5">
          <button type="button" class="px-5 py-2.5 rounded-md border border-gray-300 cursor-pointer font-semibold transition-all duration-300 bg-gray-100 text-gray-700 hover:bg-gray-200" onclick="closeModal(document.getElementById('scheduleMaintenanceModal'))">
            Cancel
          </button>
          <button type="submit" class="btn-primary">
            Schedule Task
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="../assets/js/sidebar.js"></script>
  <script src="../assets/js/script.js"></script>
  <script src="../assets/js/alms.js"></script>
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