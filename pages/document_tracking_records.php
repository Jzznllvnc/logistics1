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
      
      <!-- Two Column Layout (Mobile: Single Column, Desktop: Two Columns) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
                <!-- Left Column - Document Records -->
        <div class="col-span-1 lg:col-span-2">
          <div class="bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl p-6 shadow-sm">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-semibold text-[var(--text-color)]">Document Records</h2>
              <!-- Mobile Upload Button -->
              <button type="button" 
                      id="mobileUploadBtn"
                      class="lg:hidden btn-primary flex items-center"
                      title="Upload Document">
                <i data-lucide="cloud-upload" class="w-5 h-5"></i>
              </button>
            </div>
            
            <!-- Document Cards -->
            <div class="space-y-3">
              <?php if (empty($documents)): ?>
                <div class="text-center py-8 text-[var(--placeholder-color)]">
                  <i data-lucide="file-x" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                  <p>No documents uploaded yet</p>
                </div>
              <?php else: ?>
                <?php foreach($documents as $doc): ?>
                  <?php 
                    $fileExtension = pathinfo($doc['file_name'], PATHINFO_EXTENSION);
                    $fileTypeDisplay = strtoupper($fileExtension);
                    
                    // Determine file type icon and color (more visible in light mode)
                    $iconClass = 'w-10 h-10';
                    $bgColor = 'bg-gray-600 dark:bg-gray-700';
                    $textColor = 'text-white font-bold dark:text-gray-300';
                    
                    switch(strtolower($fileExtension)) {
                      case 'pdf':
                        $bgColor = 'bg-red-600 dark:bg-red-900/30';
                        $textColor = 'text-white font-bold dark:text-red-400';
                        break;
                      case 'doc':
                      case 'docx':
                        $bgColor = 'bg-blue-600 dark:bg-blue-900/30';
                        $textColor = 'text-white font-bold dark:text-blue-400';
                        break;
                      case 'xls':
                      case 'xlsx':
                        $bgColor = 'bg-green-600 dark:bg-green-900/30';
                        $textColor = 'text-white font-bold dark:text-green-400';
                        break;
                      case 'jpg':
                      case 'jpeg':
                      case 'png':
                        $bgColor = 'bg-purple-600 dark:bg-purple-900/30';
                        $textColor = 'text-white font-bold dark:text-purple-400';
                        break;
                    }
                  ?>
                  <div class="flex items-center p-4 bg-[var(--card-bg)] border border-[var(--card-border)] rounded-lg hover:shadow-md transition-shadow group">
                    <!-- File Type Icon -->
                    <div class="flex-shrink-0 mr-4">
                      <div class="w-12 h-12 rounded-lg <?php echo $bgColor; ?> flex items-center justify-center">
                        <i data-lucide="file-text" class="w-6 h-6 <?php echo $textColor; ?>"></i>
                      </div>
                    </div>
                    
                    <!-- Document Info -->
                    <div class="flex-1 min-w-0">
                      <h3 class="text-lg font-semibold text-[var(--text-color)] truncate">
                        <?php echo htmlspecialchars($doc['document_type']); ?>
                      </h3>
                      <p class="text-sm text-[var(--placeholder-color)] mt-1">
                        <?php echo $fileTypeDisplay; ?> • <?php echo date('M d, Y', strtotime($doc['upload_date'])); ?>
                      </p>
                      <p class="text-xs text-[var(--placeholder-color)] mt-1 truncate">
                        <?php echo htmlspecialchars($doc['file_name']); ?>
                      </p>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex-shrink-0 ml-4 flex items-center space-x-2">
                      <a href="../<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" 
                         class="p-3 text-[var(--placeholder-color)] hover:text-[var(--text-color)] hover:bg-[var(--input-bg)] rounded-lg transition-colors"
                         title="Download file">
                        <i data-lucide="download" class="w-5 h-5"></i>
                      </a>
                      <button type="button" 
                              onclick="openDocumentDetails(<?php echo htmlspecialchars(json_encode($doc)); ?>)"
                              class="p-3 text-[var(--placeholder-color)] hover:text-[var(--text-color)] hover:bg-[var(--input-bg)] rounded-lg transition-colors"
                              title="View details">
                        <i data-lucide="eye" class="w-5 h-5"></i>
          </button>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
        
        <!-- Right Column - Upload Document Form (Hidden on Mobile) -->
        <div class="hidden lg:block lg:col-span-1">
          <div class="bg-[var(--card-bg)] border border-[var(--card-border)] rounded-xl p-6 shadow-sm sticky top-6">
            <h2 class="text-xl font-semibold text-[var(--text-color)] mb-6 flex items-center">
              Upload Document
            </h2>
            
            <form action="document_tracking_records.php" method="POST" enctype="multipart/form-data" id="uploadDocumentForm">
              <div class="mb-5">
                <label for="documentFile" class="block text-sm font-semibold mb-3 text-[var(--text-color)]">Document File</label>
                
                <!-- Drag and Drop Area -->
                <div class="relative">
                  <input type="file" 
                         name="documentFile" 
                         id="documentFile" 
                         required 
                         class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                         accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt"
                         onchange="handleFileSelect(this)"
                         ondrop="handleFileDrop(event)"
                         ondragover="handleDragOver(event)"
                         ondragenter="handleDragEnter(event)"
                         ondragleave="handleDragLeave(event)">
                  
                  <div id="dropZone" class="border-2 border-dashed border-[var(--card-border)] rounded-lg p-8 text-center bg-[var(--input-bg)] hover:bg-[var(--card-bg)] transition-colors cursor-pointer">
                    <div class="flex flex-col items-center">
                      <i data-lucide="cloud-upload" class="w-12 h-12 text-[var(--placeholder-color)] mb-4"></i>
                      <p class="text-[var(--text-color)] font-medium mb-1">Drag your files here</p>
                      <p class="text-sm text-[var(--placeholder-color)] mb-4">DOC, PDF, XLSX, and JPG formats, up to 50 MB</p>
                      <button type="button" 
                              onclick="document.getElementById('documentFile').click()" 
                              class="px-4 py-2 text-white rounded-md text-sm font-medium transition-colors"
                              style="background: linear-gradient(to right, #0072ff, #00c6ff);">
                        Browse Files
                      </button>
                    </div>
                  </div>
                </div>
                
                <!-- Selected File Display (OUTSIDE the file input area) -->
                <div id="selectedFileDisplay" class="hidden mt-3 p-3 bg-[var(--card-bg)] border rounded-lg" style="border-color: #0072ff;">
                  <div class="flex items-center">
                    <i data-lucide="file" class="w-5 h-5 mr-2" style="color: #0072ff;"></i>
                    <span id="selectedFileName" class="text-sm text-[var(--text-color)] font-medium"></span>
                    <button type="button" 
                            data-action="clear-file"
                            class="ml-auto hover:text-[var(--text-color)]" 
                            style="color: #0072ff;">
                      <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                  </div>
                </div>
              </div>
              
              <div class="mb-5">
                <label for="document_type" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Document Type</label>
                <input type="text" 
                       name="document_type" 
                       id="document_type" 
                       placeholder="e.g., Bill of Lading, Invoice" 
                       required 
                       class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
              </div>
              
              <div class="mb-5">
                <label for="reference_number" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Reference Number</label>
                <input type="text" 
                       name="reference_number" 
                       id="reference_number" 
                       placeholder="e.g., INV-12345, BOL-ABCDE" 
                       class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
              </div>
              
              <div class="mb-6">
                <label for="expiry_date" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Expiry Date (Optional)</label>
                <input type="date" 
                       name="expiry_date" 
                       id="expiry_date" 
                       class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
              </div>
              
              <button type="submit" class="w-full btn-primary flex items-center justify-center">
                <i data-lucide="upload" class="w-5 h-5 mr-2"></i>
                Upload Document
              </button>
            </form>
          </div>
        </div>
        
      </div>
    </div>
  </div>

  <?php include 'modals/dtrs.php'; ?>

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