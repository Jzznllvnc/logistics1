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
                createAsset($name, $type, $purchase_date, $status);
            } else {
                $id = $_POST['asset_id'] ?? 0;
                updateAsset($id, $name, $type, $purchase_date, $status);
            }
        } elseif ($action === 'delete_asset') {
            $id = $_POST['asset_id'] ?? 0;
            deleteAsset($id);
        }
    }
    
    // --- Actions for Maintenance Scheduling (All Roles on this page) ---
    if ($action === 'schedule_maintenance') {
        $asset_id = $_POST['asset_id_maint'] ?? 0;
        $description = $_POST['task_description'] ?? '';
        $scheduled_date = $_POST['scheduled_date'] ?? null;
        createMaintenanceSchedule($asset_id, $description, $scheduled_date);
    } elseif ($action === 'update_maintenance_status') {
        $schedule_id = $_POST['schedule_id'] ?? 0;
        $new_status = $_POST['new_status'] ?? '';
        updateMaintenanceStatus($schedule_id, $new_status);
    }
    
    header("Location: asset_lifecycle_maintenance.php");
    exit();
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
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="stylesheet" href="../assets/css/sidebar.css">
  <style>
    .alms-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
    .card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .card-title { font-size: 1.5rem; font-weight: 600; margin-bottom: 20px; }
    .table { width: 100%; border-collapse: collapse; }
    .table th, .table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--card-border); }
    .table th { background-color: rgba(128,128,128,0.05); text-transform: uppercase; font-size: 13px; }
    .actions a { margin-right: 15px; cursor: pointer; }
    .btn-add { background: var(--primary-btn-bg); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block; margin-bottom: 20px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: 500; margin-bottom: 5px; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid var(--input-border); border-radius: 6px; background: var(--input-bg); color: var(--input-text); }
    @media (max-width: 1024px) { .alms-grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <div class="sidebar" id="sidebar"> <?php include '../partials/sidebar.php'; ?> </div>
  <div class="main-content-wrapper" id="mainContentWrapper">
    <div class="content" id="mainContent">
      <?php include '../partials/header.php'; ?>
      <h1 class="page-title" style="font-family: 'Inter Tight', sans-serif; font-weight: 600; font-size: 2.5rem; margin-bottom: 2rem;">Asset Lifecycle & Maintenance (ALMS)</h1>
      
      <div class="alms-grid">
        <div class="card">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">Asset Registry</h2>
            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'alms'): ?>
            <button type="button" class="btn-add" onclick="openCreateAssetModal()"><i class="fas fa-plus"></i> Register Asset</button>
            <?php endif; ?>
          </div>
          <table class="table">
            <thead><tr><th>Name</th><th>Type</th><th>Status</th><?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'alms'): ?><th>Actions</th><?php endif; ?></tr></thead>
            <tbody>
              <?php foreach($assets as $asset): ?>
              <tr>
                <td><?php echo htmlspecialchars($asset['asset_name']); ?></td>
                <td><?php echo htmlspecialchars($asset['asset_type']); ?></td>
                <td><?php echo htmlspecialchars($asset['status']); ?></td>
                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'alms'): ?>
                <td class="actions">
                  <a class="edit" onclick='openEditAssetModal(<?php echo json_encode($asset); ?>)'><i class="fas fa-pencil-alt"></i></a>
                  <a class="delete" onclick="confirmDeleteAsset(<?php echo $asset['id']; ?>)"><i class="fas fa-trash-alt"></i></a>
                </td>
                <?php endif; ?>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="card">
          <h2 class="card-title">Schedule Maintenance</h2>
          <form action="asset_lifecycle_maintenance.php" method="POST">
            <input type="hidden" name="action" value="schedule_maintenance">
            <div class="form-group"><label>Asset</label><select name="asset_id_maint" required><option value="">-- Select Asset --</option><?php foreach($assets as $asset): ?><option value="<?php echo $asset['id']; ?>"><?php echo htmlspecialchars($asset['asset_name']); ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label>Task Description</label><textarea name="task_description" rows="3" required></textarea></div>
            <div class="form-group"><label>Scheduled Date</label><input type="date" name="scheduled_date" required></div>
            <button type="submit" class="btn-add" style="width: 100%;">Schedule Task</button>
          </form>
        </div>
      </div>

      <div class="card" style="margin-top: 30px;">
        <h2 class="card-title">Maintenance Schedule</h2>
        <table class="table">
          <thead><tr><th>Asset</th><th>Task</th><th>Scheduled Date</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
            <?php foreach($schedules as $schedule): ?>
            <tr>
              <td><?php echo htmlspecialchars($schedule['asset_name']); ?></td>
              <td><?php echo htmlspecialchars($schedule['task_description']); ?></td>
              <td><?php echo date('M d, Y', strtotime($schedule['scheduled_date'])); ?></td>
              <td><?php echo htmlspecialchars($schedule['status']); ?></td>
              <td>
                <?php if($schedule['status'] === 'Scheduled'): ?>
                <form action="asset_lifecycle_maintenance.php" method="POST" style="margin:0;">
                  <input type="hidden" name="action" value="update_maintenance_status">
                  <input type="hidden" name="schedule_id" value="<?php echo $schedule['id']; ?>">
                  <input type="hidden" name="new_status" value="Completed">
                  <button type="submit" class="btn-add" style="background: #10b981; padding: 5px 10px; font-size: 0.8em;">Mark as Complete</button>
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

  <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'alms'): ?>
  <div id="assetModal" class="modal" style="display:none;">
    <div class="modal-content bg-[var(--card-bg)] p-8 rounded-lg shadow-xl relative">
      <h2 id="modalTitle" class="text-2xl font-bold mb-4"></h2>
      <form id="assetForm" method="POST" action="asset_lifecycle_maintenance.php">
        <input type="hidden" name="action" id="formAction"><input type="hidden" name="asset_id" id="assetId">
        <div class="form-group"><label>Asset Name</label><input type="text" name="asset_name" id="asset_name" required></div>
        <div class="form-group"><label>Asset Type</label><input type="text" name="asset_type" id="asset_type" placeholder="e.g., Vehicle, Equipment"></div>
        <div class="form-group"><label>Purchase Date</label><input type="date" name="purchase_date" id="purchase_date"></div>
        <div class="form-group"><label>Status</label><select name="status" id="status"><option>Operational</option><option>Under Maintenance</option><option>Decommissioned</option></select></div>
        <div class="form-actions flex justify-end gap-4 mt-6">
          <button type="button" class="btn bg-gray-300" onclick="closeModal(document.getElementById('assetModal'))">Cancel</button>
          <button type="submit" class="btn btn-danger bg-green-500 text-white">Save Asset</button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <script src="../assets/js/sidebar.js"></script>
  <script src="../assets/js/script.js"></script>
  <script src="../assets/js/alms.js"></script>
</body>
</html>