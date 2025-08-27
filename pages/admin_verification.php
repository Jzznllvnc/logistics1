<?php
require_once '../includes/functions/auth.php';
require_once '../includes/functions/supplier.php';
requireAdmin(); // Ensure only admins and procurement can access

// Handle form submissions for approving or rejecting suppliers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $supplier_id = $_POST['supplier_id'] ?? 0;

    // Logic for approving or rejecting suppliers
    if ($action === 'approve_supplier') {
        if (updateSupplierStatus($supplier_id, 'Approved')) {
            $_SESSION['flash_message'] = "Supplier has been approved and can now log in.";
            $_SESSION['flash_message_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = "Failed to approve supplier.";
            $_SESSION['flash_message_type'] = 'error';
        }
    } elseif ($action === 'reject_supplier') {
        if (updateSupplierStatus($supplier_id, 'Rejected')) {
            $_SESSION['flash_message'] = "Supplier has been rejected.";
            $_SESSION['flash_message_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = "Failed to reject supplier.";
            $_SESSION['flash_message_type'] = 'error';
        }
    }
    header("Location: admin_verification.php");
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

$pending_suppliers = getPendingSuppliers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <script>document.documentElement.classList.add('preload', 'loading');</script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logistics 1 - Supplier Verification</title>
  <link rel="icon" href="../assets/images/slate2.png" type="image/png">
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
      <h1 class="font-semibold page-title">Supplier Verification</h1>
      
      <div class="bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl p-6 shadow-sm">
        <h2 class="text-2xl font-semibold text-[var(--text-color)] mb-5">Pending Applications</h2>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Company Name</th>
                <th>Contact Person</th>
                <th>Email</th>
                <th>Document</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($pending_suppliers)): ?>
                <tr><td colspan="5" class="table-empty">No pending supplier applications.</td></tr>
              <?php else: foreach ($pending_suppliers as $supplier): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($supplier['supplier_name']); ?></td>
                    <td><?php echo htmlspecialchars($supplier['contact_person']); ?></td>
                    <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                    <td>
                      <a href="../<?php echo htmlspecialchars($supplier['verification_document_path']); ?>" target="_blank" class="file-link">
                        View Document
                      </a>
                    </td>
                    <td class="flex gap-2">
                      <form action="admin_verification.php" method="POST" class="form-no-margin">
                        <input type="hidden" name="action" value="approve_supplier">
                        <input type="hidden" name="supplier_id" value="<?php echo $supplier['id']; ?>">
                        <button type="submit" class="btn-primary btn-small btn-success">Approve</button>
                      </form>
                      <form action="admin_verification.php" method="POST" class="form-no-margin">
                        <input type="hidden" name="action" value="reject_supplier">
                        <input type="hidden" name="supplier_id" value="<?php echo $supplier['id']; ?>">
                        <button type="submit" class="btn-primary-danger btn-small">Reject</button>
                      </form>
                    </td>
                  </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script src="../assets/js/sidebar.js"></script>
  <script src="../assets/js/script.js"></script>
  <script src="../assets/js/custom-alerts.js"></script>
  <script>
    lucide.createIcons();
    
    // Display flash message if present
    <?php if ($message): ?>
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
    <?php endif; ?>
  </script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</body>
</html>