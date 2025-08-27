<?php if ($_SESSION['role'] === 'admin'): ?>
<div id="projectModal" class="modal hidden">
  <div class="modal-content p-8 max-w-2xl">
    <div class="flex justify-between items-center mb-2">
      <h2 class="modal-title flex items-center min-w-0 flex-1" id="projectModalTitle">
        <i data-lucide="folder-plus" class="w-6 h-6 mr-3 flex-shrink-0" id="projectModalIcon"></i>
        <span id="projectModalTitleText" class="truncate">Create New Project</span>
      </h2>
      <button type="button" class="close-button flex-shrink-0 ml-3" onclick="closeModal('projectModal')">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    <p class="modal-subtitle" id="projectModalSubtitle">Create a new logistics project for tracking.</p>
    <div class="border-b border-[var(--card-border)] mb-5"></div>
    
    <form id="projectForm" method="POST" action="project_logistics_tracker.php">
      <input type="hidden" name="action" id="formAction">
      <input type="hidden" name="project_id" id="projectId">
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="space-y-5">
          <div>
            <label for="project_name" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Project Name</label>
            <input type="text" name="project_name" id="project_name" required class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" placeholder="Enter project name">
          </div>
          
          <div>
            <label for="description" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Description</label>
            <textarea name="description" id="description" rows="4" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" placeholder="Enter project description"></textarea>
          </div>
          
          <div>
            <label for="status" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Status</label>
            <select name="status" id="status" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
              <option value="Not Started">Not Started</option>
              <option value="In Progress">In Progress</option>
              <option value="Completed">Completed</option>
            </select>
          </div>
        </div>
        
        <div class="space-y-5">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="start_date" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Start Date</label>
              <input type="date" name="start_date" id="start_date" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
            </div>
            <div>
              <label for="end_date" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">End Date</label>
              <input type="date" name="end_date" id="end_date" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Assign Resources (Suppliers)</label>
            <div class="w-full max-h-32 overflow-y-auto p-3 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)]">
              <?php foreach($allSuppliers as $supplier): ?>
                <div class="flex items-center mb-2 last:mb-0">
                  <input 
                    type="checkbox" 
                    name="assigned_suppliers[]" 
                    value="<?php echo $supplier['id']; ?>" 
                    id="supplier_<?php echo $supplier['id']; ?>"
                    class="w-4 h-4 text-blue-600 bg-[var(--input-bg)] border-[var(--input-border)] rounded focus:ring-blue-500 focus:ring-2"
                  >
                  <label for="supplier_<?php echo $supplier['id']; ?>" class="ml-2 text-sm text-[var(--text-color)] cursor-pointer">
                    <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
            <p class="text-sm text-[var(--placeholder-color)] mt-1">Select multiple suppliers as needed</p>
          </div>
        </div>
      </div>
      
      <div class="flex justify-end gap-3 mt-6">
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
