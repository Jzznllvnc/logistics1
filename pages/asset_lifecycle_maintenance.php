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
    
    // Asset CRUD Actions
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'alms') {
        if ($action === 'create_asset' || $action === 'update_asset') {
            $name = $_POST['asset_name'] ?? '';
            $type = $_POST['asset_type'] ?? '';
            $purchase_date = $_POST['purchase_date'] ?? null;
            $status = $_POST['status'] ?? '';
            if ($action === 'create_asset') {
                if (createAsset($name, $type, $purchase_date, $status)) {
                    $_SESSION['flash_message'] = "Asset <strong>" . htmlspecialchars($name) . "</strong> created successfully.";
                    $_SESSION['flash_message_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = "Failed to create asset. Please try again.";
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
    
    // Maintenance Scheduling Actions
    if ($action === 'schedule_maintenance') {
        $asset_id = $_POST['asset_id_maint'] ?? 0;
        $task_description = $_POST['task_description'] ?? '';
        $scheduled_date = $_POST['scheduled_date'] ?? null;
        if (createMaintenanceSchedule($asset_id, $task_description, $scheduled_date, 'Manual Entry')) {
            $_SESSION['flash_message'] = "Maintenance task <strong>" . htmlspecialchars($task_description) . "</strong> scheduled successfully.";
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

// --- Data Fetching and Automation ---
automateMaintenanceSchedules(); // Run the AI automation logic

$assets = getAllAssets();
$schedules = getMaintenanceSchedules(); // Re-fetch schedules after automation
$forecasts = getPredictiveMaintenanceForecasts($assets);
$usageLogsByAsset = getAllUsageLogsGroupedByAsset();
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
          <button class="tab-button" data-tab="usage-logs">
            <i data-lucide="line-chart" class="w-4 h-4 mr-2"></i>
            Usage Logs
          </button>
        </div>
      </div>
      
      <div class="tab-content active" id="asset-registry-tab">
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
                  <th>Failure Risk 
                      <span class="inline-flex items-center gap-1 ml-2 px-2 py-0.3 text-[0.8rem] font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-full align-top">
                        <i data-lucide="bot" class="w-4 h-4"></i>
                        AI
                      </span>
                    </th>
                  <th>Predicted Next Service</th>
                  <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'alms'): ?><th>Action</th><?php endif; ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach($assets as $asset): ?>
                <tr>
                  <td><?php echo htmlspecialchars($asset['asset_name']); ?></td>
                  <td><?php echo htmlspecialchars($asset['asset_type']); ?></td>
                  <td>
                    <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full font-medium text-sm <?php 
                      $status_class = 'bg-gray-50 text-gray-700 border border-gray-200';
                      if ($asset['status'] === 'Operational') $status_class = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                      if ($asset['status'] === 'Under Maintenance') $status_class = 'bg-amber-50 text-amber-700 border border-amber-200';
                      if ($asset['status'] === 'Decommissioned') $status_class = 'bg-red-50 text-red-700 border border-red-200';
                      echo $status_class;
                    ?>">
                      <?php echo htmlspecialchars($asset['status']); ?>
                    </span>
                  </td>
                  <td>
                    <?php 
                      $risk = $forecasts[$asset['id']]['risk'] ?? 'No Data';
                      $risk_class = 'text-gray-400';
                      if ($risk === 'High') $risk_class = 'text-red-500 font-bold';
                      if ($risk === 'Medium') $risk_class = 'text-yellow-500 font-bold';
                      if ($risk === 'Low') $risk_class = 'text-green-500 font-bold';
                    ?>
                    <span class="<?php echo $risk_class; ?>"><?php echo $risk; ?></span>
                  </td>
                  <td><?php echo $forecasts[$asset['id']]['next_maintenance'] ?? 'N/A'; ?></td>
                  <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'alms'): ?>
                  <td>
                    <div class="relative">
                      <button type="button" class="action-dropdown-btn p-2 rounded-full" onclick="toggleAssetDropdown(<?php echo $asset['id']; ?>)">
                        <i data-lucide="more-horizontal" class="w-6 h-6"></i>
                      </button>
                      <div id="asset-dropdown-<?php echo $asset['id']; ?>" class="action-dropdown hidden">
                        <button type="button" onclick='openEditAssetModal(<?php echo json_encode($asset); ?>)'><i data-lucide="edit-3" class="w-4 h-4 mr-3"></i>Edit</button>
                        <button type="button" onclick="confirmDeleteAsset(<?php echo $asset['id']; ?>)"><i data-lucide="trash-2" class="w-4 h-4 mr-3"></i>Delete</button>
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
      
      <div class="tab-content" id="maintenance-schedule-tab">
         <div class="bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl p-6 shadow-sm">
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
                          <th>Asset 
                            <span class="inline-flex items-center gap-1 ml-2 px-2 py-0.3 text-[0.8rem] font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-full align-top">
                              <i data-lucide="bot" class="w-4 h-4"></i>
                              AI
                            </span>
                          </th>
                          <th>Task
                            <span class="inline-flex items-center gap-1 ml-2 px-2 py-0.3 text-[0.8rem] font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-full align-top">
                              <i data-lucide="bot" class="w-4 h-4"></i>
                              AI
                            </span>
                          </th>
                          <th>Scheduled Date</th>
                          <th>Status</th>
                          <th>Action</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php foreach($schedules as $schedule): ?>
                      <tr>
                          <td>
                              <?php echo htmlspecialchars($schedule['asset_name']); ?>
                              <?php if (strpos($schedule['notes'], 'Automated') !== false): ?>
                                <span class="ml-2 text-xs text-sky-700 bg-sky-50 border border-sky-200 rounded-full px-2 py-1">AI-Scheduled</span>
                              <?php endif; ?>
                          </td>
                          <td><?php echo htmlspecialchars($schedule['task_description']); ?></td>
                          <td><?php echo date('M d, Y', strtotime($schedule['scheduled_date'])); ?></td>
                          <td>
                              <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full font-medium text-sm <?php 
                                $status_class = 'bg-gray-50 text-gray-700 border border-gray-200';
                                if ($schedule['status'] === 'Scheduled') $status_class = 'bg-blue-50 text-blue-700 border border-blue-200';
                                if ($schedule['status'] === 'Completed') $status_class = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                                echo $status_class;
                              ?>">
                                <?php echo htmlspecialchars($schedule['status']); ?>
                              </span>
                          </td>
                          <td>
                              <?php if($schedule['status'] === 'Scheduled'): ?>
                              <form action="asset_lifecycle_maintenance.php" method="POST" class="m-0">
                                  <input type="hidden" name="action" value="update_maintenance_status">
                                  <input type="hidden" name="schedule_id" value="<?php echo $schedule['id']; ?>">
                                  <input type="hidden" name="new_status" value="Completed">
                                  <button type="submit" class="text-xs bg-emerald-500 text-white py-1 px-2.5 rounded-md">Mark as Complete</button>
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

      <div class="tab-content" id="usage-logs-tab">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
          <?php foreach($usageLogsByAsset as $assetId => $data): ?>
          <div class="bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl p-5 shadow-sm">
            <h3 class="text-xl font-semibold mb-3 text-[var(--text-color)]"><?php echo htmlspecialchars($data['asset_name']); ?></h3>
            <div class="table-container max-h-60 overflow-y-auto">
              <table class="data-table">
                <thead class="sticky top-0 bg-[var(--card-bg)]">
                  <tr>
                    <th>Date</th>
                    <th>Metric</th>
                    <th>Value</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($data['logs'] as $log): ?>
                  <tr>
                    <td><?php echo date('M d, Y', strtotime($log['log_date'])); ?></td>
                    <td><?php echo htmlspecialchars($log['metric_name']); ?></td>
                    <td><?php echo number_format($log['metric_value'], 2); ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <?php include 'modals/alms.php'; ?>

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