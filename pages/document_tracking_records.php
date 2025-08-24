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
      <script>
        <?php if ($message && !empty(trim($message))): ?>
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
      <?php include '../partials/header.php'; ?>
      <h1 class="font-semibold page-title">Document Tracking & Records</h1>
      
      <!-- Document Records - Now Full Width -->
      <div class="bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl p-6 shadow-sm">
        <div class="flex justify-between items-center mb-5">
          <h2 class="text-2xl font-semibold text-[var(--text-color)]">Document Records</h2>
          <button type="button" id="uploadDocumentBtn" class="btn-primary">
            <i data-lucide="cloud-upload" class="w-6 h-6 lg:mr-2 sm:mr-0"></i><span class="hidden sm:inline">Upload Document</span>
          </button>
        </div>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>File Name</th>
                <th>Type</th>
                <th>Reference #</th>
                <th>Expiry</th>
                <th>Uploaded</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($documents as $doc): ?>
              <tr>
                <td class="py-3 px-4 border-b border-[var(--card-border)]"><a href="../<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="text-[var(--primary-color)] underline hover:text-blue-600 transition-colors"><?php echo htmlspecialchars($doc['file_name']); ?></a></td>
                <td class="py-3 px-4 border-b border-[var(--card-border)]"><?php echo htmlspecialchars($doc['document_type']); ?></td>
                <td class="py-3 px-4 border-b border-[var(--card-border)]"><?php echo htmlspecialchars($doc['reference_number']); ?></td>
                <td class="py-3 px-4 border-b border-[var(--card-border)]"><?php echo $doc['expiry_date'] ? date('M d, Y', strtotime($doc['expiry_date'])) : 'N/A'; ?></td>
                <td class="py-3 px-4 border-b border-[var(--card-border)]"><?php echo date('M d, Y', strtotime($doc['upload_date'])); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Upload Document Modal -->
  <div id="uploadDocumentModal" class="modal hidden">
    <div class="modal-content p-8 max-w-lg">
      <div class="flex justify-between items-center mb-2">
        <h2 class="modal-title flex items-center min-w-0 flex-1">
          <i data-lucide="file-plus-2" class="w-6 h-6 mr-3 flex-shrink-0"></i>
          <span class="truncate">Upload Document</span>
        </h2>
        <button type="button" class="close-button flex-shrink-0 ml-3" onclick="closeModal('uploadModal')">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      <p class="modal-subtitle">Upload a document to the system.</p>
      <div class="border-b border-[var(--card-border)] mb-5"></div>
      
      <form action="document_tracking_records.php" method="POST" enctype="multipart/form-data" id="uploadDocumentForm">
        <div class="mb-5">
          <label for="documentFile" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Document File</label>
          <input type="file" name="documentFile" id="documentFile" required class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt">
          <p class="text-sm text-[var(--placeholder-color)] mt-1">Supported formats: PDF, DOC, DOCX, JPG, PNG, TXT</p>
        </div>
        
        <div class="mb-5">
          <label for="document_type" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Document Type</label>
          <input type="text" name="document_type" id="document_type" placeholder="e.g., Bill of Lading, Invoice" required class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
        </div>
        
        <div class="mb-5">
          <label for="reference_number" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Reference Number</label>
          <input type="text" name="reference_number" id="reference_number" placeholder="e.g., INV-12345, BOL-ABCDE" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
        </div>
        
        <div class="mb-6">
          <label for="expiry_date" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Expiry Date (Optional)</label>
          <input type="date" name="expiry_date" id="expiry_date" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
        </div>
        
        <div class="flex justify-end gap-3">
          <button type="button" class="px-5 py-2.5 rounded-md border border-gray-300 cursor-pointer font-semibold transition-all duration-300 bg-gray-100 text-gray-700 hover:bg-gray-200" onclick="closeModal(document.getElementById('uploadDocumentModal'))">
            Cancel
          </button>
          <button type="submit" class="btn-primary">
            Upload Document
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="../assets/js/sidebar.js"></script>
  <script src="../assets/js/script.js"></script>
  <script src="../assets/js/dtrs.js"></script>
  <script>
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  </script>
</body>
</html>