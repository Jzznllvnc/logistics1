<!-- Mobile Upload Modal -->
<div id="mobileUploadModal" class="modal hidden lg:hidden">
  <div class="modal-content p-6 max-w-lg mx-4">
    <div class="flex justify-between items-center mb-4">
      <h2 class="modal-title flex items-center">
        <span>Upload Document</span>
      </h2>
      <button type="button" class="close-button" onclick="closeMobileUploadModal()">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    
    <form action="document_tracking_records.php" method="POST" enctype="multipart/form-data" id="mobileUploadForm">
      <div class="mb-4">
        <label for="mobileDocumentFile" class="block text-sm font-semibold mb-3 text-[var(--text-color)]">Document File</label>
        
        <!-- Mobile Drag and Drop Area -->
        <div class="relative">
          <input type="file" 
                 name="documentFile" 
                 id="mobileDocumentFile" 
                 required 
                 class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                 accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt"
                 onchange="handleMobileFileSelect(this)">
          
          <div id="mobileDropZone" class="border-2 border-dashed border-[var(--card-border)] rounded-lg p-6 text-center bg-[var(--input-bg)] hover:bg-[var(--card-bg)] transition-colors cursor-pointer">
            <div class="flex flex-col items-center">
              <i data-lucide="cloud-upload" class="w-10 h-10 text-[var(--placeholder-color)] mb-3"></i>
              <p class="text-[var(--text-color)] font-medium mb-1">Tap to select file</p>
              <p class="text-xs text-[var(--placeholder-color)] mb-3">PDF, DOC, DOCX, JPG, PNG, TXT (max 5MB)</p>
              <button type="button" 
                      onclick="document.getElementById('mobileDocumentFile').click()" 
                      class="px-3 py-2 text-white rounded-md text-sm font-medium transition-colors"
                      style="background: linear-gradient(to right, #0072ff, #00c6ff);">
                Browse Files
              </button>
            </div>
          </div>
        </div>
        
        <!-- Mobile Selected File Display -->
        <div id="mobileSelectedFileDisplay" class="hidden mt-3 p-3 bg-[var(--card-bg)] border rounded-lg" style="border-color: #0072ff;">
          <div class="flex items-center">
            <i data-lucide="file" class="w-5 h-5 mr-2" style="color: #0072ff;"></i>
            <span id="mobileSelectedFileName" class="text-sm text-[var(--text-color)] font-medium"></span>
            <button type="button" 
                    data-action="clear-mobile-file"
                    class="ml-auto hover:text-[var(--text-color)]" 
                    style="color: #0072ff;">
              <i data-lucide="x" class="w-4 h-4"></i>
            </button>
          </div>
        </div>
      </div>
      
      <div class="mb-4">
        <label for="mobileDocumentType" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Document Type</label>
        <input type="text" 
               name="document_type" 
               id="mobileDocumentType" 
               placeholder="e.g., Bill of Lading, Invoice" 
               required 
               class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
      </div>
      
      <div class="mb-4">
        <label for="mobileReferenceNumber" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Reference Number</label>
        <input type="text" 
               name="reference_number" 
               id="mobileReferenceNumber" 
               placeholder="e.g., INV-12345, BOL-ABCDE" 
               class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
      </div>
      
      <div class="mb-6">
        <label for="mobileExpiryDate" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Expiry Date (Optional)</label>
        <input type="date" 
               name="expiry_date" 
               id="mobileExpiryDate" 
               class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
      </div>
      
      <div class="flex gap-3">
        <button type="button" 
                onclick="closeMobileUploadModal()" 
                class="flex-1 px-4 py-2.5 rounded-md border border-gray-300 font-semibold transition-colors bg-gray-100 text-gray-700 hover:bg-gray-200">
          Cancel
        </button>
        <button type="submit" class="flex-1 btn-primary flex items-center justify-center">
          <i data-lucide="upload" class="w-4 h-4 mr-2"></i>
          Upload
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Document Details Modal -->
<div id="documentDetailsModal" class="modal hidden">
  <div class="modal-content p-8 max-w-2xl">
    <div class="flex justify-between items-center mb-2">
      <h2 class="modal-title flex items-center min-w-0 flex-1">
        <i data-lucide="file-text" class="w-6 h-6 mr-3 flex-shrink-0"></i>
        <span class="truncate">Document Details</span>
      </h2>
      <button type="button" class="close-button flex-shrink-0 ml-3" onclick="closeModal(document.getElementById('documentDetailsModal'))">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    <p class="modal-subtitle">Detailed information about the document.</p>
    <div class="border-b border-[var(--card-border)] mb-5"></div>
    
    <!-- Document Details Content -->
    <div id="documentDetailsContent" class="space-y-4">
      <!-- Content will be populated by JavaScript -->
      </div>
      
    <div class="flex justify-end gap-3 mt-6 pt-4">
      <button type="button" 
              id="downloadDocumentBtn"
              class="btn-primary flex items-center">
        <i data-lucide="download" class="w-4 h-4 mr-2"></i>
        Download File
        </button>
      <button type="button" class="px-5 py-2.5 rounded-md border border-gray-300 cursor-pointer font-semibold transition-all duration-300 bg-gray-100 text-gray-700 hover:bg-gray-200" onclick="closeModal(document.getElementById('documentDetailsModal'))">
        Close
        </button>
      </div>
  </div>
</div>
