<?php
require_once '../includes/functions/auth.php';
require_once '../includes/functions/document.php';
requireLogin();

// Role check
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'dtrs') {
    header("Location: dashboard.php");
    exit();
}

$message = '';
$message_type = '';

// Handle file upload submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['documentFile'])) {
    $metadata = [
        'document_type'    => $_POST['document_type'] ?? '',
        'reference_number' => $_POST['reference_number'] ?? '',
        'expiry_date'      => $_POST['expiry_date'] ?? ''
    ];

    $result = uploadDocument($_FILES['documentFile'], $metadata);

    if (strpos($result, 'Success') === 0) {
        $message_type = 'success';
    } else {
        $message_type = 'error';
    }
    $message = $result;
}

$documents = getAllDocuments();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <script>document.documentElement.classList.add('preload', 'loading');</script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logistics 1 - DTRS</title>
  <link rel="icon" href="../assets/images/slate2.png" type="image/png">
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="stylesheet" href="../assets/css/sidebar.css">
  <style>
    .dtrs-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }
    .card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .card-title { font-size: 1.5rem; font-weight: 600; margin-bottom: 20px; }
    .table { width: 100%; border-collapse: collapse; }
    .table th, .table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--card-border); }
    .table th { background-color: rgba(128,128,128,0.05); text-transform: uppercase; font-size: 13px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: 500; margin-bottom: 5px; }
    .form-group input { width: 100%; padding: 8px; border: 1px solid var(--input-border); border-radius: 6px; background: var(--input-bg); color: var(--input-text); }
    .btn-upload { background: var(--primary-btn-bg); color: white; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
    @media (max-width: 1024px) { .dtrs-grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <div class="sidebar" id="sidebar"> <?php include '../partials/sidebar.php'; ?> </div>
  <div class="main-content-wrapper" id="mainContentWrapper">
    <div class="content" id="mainContent">
      <script>
        <?php if ($message): ?>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.showCustomAlert) {
                showCustomAlert("<?php echo htmlspecialchars($message); ?>", "<?php echo htmlspecialchars($message_type); ?>");
            }
        });
        <?php endif; ?>
      </script>
      <?php include '../partials/header.php'; ?>
      <h1 class="page-title" style="font-family: 'Inter Tight', sans-serif; font-weight: 600; font-size: 2.5rem; margin-bottom: 2rem;">Document Tracking & Records (DTRS)</h1>
      
      <div class="dtrs-grid">
        <div class="card">
          <h2 class="card-title">Upload New Document</h2>
          <form action="document_tracking_records.php" method="POST" enctype="multipart/form-data">
            <div class="form-group"><label>Document File</label><input type="file" name="documentFile" required></div>
            <div class="form-group"><label>Document Type</label><input type="text" name="document_type" placeholder="e.g., Bill of Lading, Invoice" required></div>
            <div class="form-group"><label>Reference #</label><input type="text" name="reference_number" placeholder="e.g., INV-12345, BOL-ABCDE"></div>
            <div class="form-group"><label>Expiry Date (Optional)</label><input type="date" name="expiry_date"></div>
            <button type="submit" class="btn-upload" style="width: 100%;">Upload Document</button>
          </form>
        </div>

        <div class="card">
          <h2 class="card-title">Document Records</h2>
          <div style="overflow-x: auto;">
            <table class="table">
              <thead><tr><th>File Name</th><th>Type</th><th>Reference #</th><th>Expiry</th><th>Uploaded</th></tr></thead>
              <tbody>
                <?php foreach($documents as $doc): ?>
                <tr>
                  <td><a href="../<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" style="color: var(--primary-color); text-decoration: underline;"><?php echo htmlspecialchars($doc['file_name']); ?></a></td>
                  <td><?php echo htmlspecialchars($doc['document_type']); ?></td>
                  <td><?php echo htmlspecialchars($doc['reference_number']); ?></td>
                  <td><?php echo $doc['expiry_date'] ? date('M d, Y', strtotime($doc['expiry_date'])) : 'N/A'; ?></td>
                  <td><?php echo date('M d, Y', strtotime($doc['upload_date'])); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="../assets/js/sidebar.js"></script>
  <script src="../assets/js/script.js"></script>
</body>
</html>