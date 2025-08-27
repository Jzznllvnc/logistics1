<?php if ($_SESSION['role'] === 'admin'): ?>
<div id="editItemModal" class="modal hidden">
  <div class="modal-content p-8">
    <div class="flex justify-between items-center mb-2">
      <h2 class="modal-title flex items-center min-w-0 flex-1">
        <i data-lucide="square-pen" class="w-6 h-6 mr-3 flex-shrink-0"></i>
        <span class="truncate">Edit Item Name</span>
      </h2>
      <button type="button" class="close-button flex-shrink-0 ml-3" onclick="closeModal(this.closest('.modal'))">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    <p class="modal-subtitle">Update item name.</p>
    <div class="border-b border-[var(--card-border)] mb-5"></div>

    <form id="editItemForm" method="POST" action="smart_warehousing.php">
      <input type="hidden" name="action" value="update_item">
      <input type="hidden" name="item_id" id="edit_item_id">
      <div class="form-group mb-2">
        <label for="item_name_edit" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Item Name</label>
        <input type="text" name="item_name_edit" id="item_name_edit" placeholder="Enter new item name" required class="w-full p-2.5 rounded-md border border-[var(--input-border)] bg-[var(--input-bg)] text-[var(--input-text)]">
      </div>
      <div class="form-actions flex justify-end gap-4 mt-6">
        <button type="button" class="px-5 py-2.5 rounded-md border border-gray-300 cursor-pointer font-semibold transition-all duration-300 bg-gray-100 text-gray-700 hover:bg-gray-200" onclick="closeModal(this.closest('.modal'))">Cancel</button>
        <button type="submit" class="btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<div id="stockManagementModal" class="modal hidden">
  <div class="modal-content p-8 max-w-lg">
      <div class="flex justify-between items-center mb-2">
          <h2 class="modal-title flex items-center min-w-0 flex-1" id="modalTitle">
              <i data-lucide="package" class="w-7 h-7 mr-3 flex-shrink-0" id="stockModalIcon"></i>
              <span id="stockModalTitleText" class="truncate">Manage Stock Levels</span>
          </h2>
          <button type="button" class="close-button flex-shrink-0 ml-3" onclick="closeModal(this.closest('.modal'))">
              <i data-lucide="x" class="w-5 h-5"></i>
          </button>
      </div>
      <p class="modal-subtitle" id="stockModalSubtitle">Add/Remove new items or update quantities.</p>
      <div class="border-b border-[var(--card-border)] mb-5"></div>
    
      <form action="smart_warehousing.php" method="POST" id="stockManagementForm">
          <input type="hidden" name="action" id="stockAction" value="">
          
          <div id="supplier_selection_div" class="hidden mb-5">
              </select>
          </div>
          
          <div class="mb-5">
              <label for="modal_item_name" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Item Name</label>
              <input type="text" id="modal_item_name" name="item_name" list="inventory_items" placeholder="Type to see stock levels..." required class="w-full p-2.5 rounded-md border border-[var(--input-border)] bg-[var(--input-bg)] text-[var(--input-text)]">
              <datalist id="inventory_items">
                  <?php foreach ($allInventory as $item): ?>
                      <option value="<?php echo htmlspecialchars($item['item_name']); ?>">
                          <?php echo htmlspecialchars($item['item_name']) . ' (' . htmlspecialchars($item['quantity']) . ' in stock)'; ?>
                      </option>
                  <?php endforeach; ?>
              </datalist>
          </div>
          
          <div class="mb-6">
              <label for="modal_quantity" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Quantity</label>
              <input type="number" id="modal_quantity" name="quantity" min="1" placeholder="e.g., 50" required class="w-full p-2.5 rounded-md border border-[var(--input-border)] bg-[var(--input-bg)] text-[var(--input-text)]">
          </div>
          
          <div class="flex justify-end gap-3 pt-4">
              <button type="button" class="px-5 py-2.5 rounded-md border border-gray-300 cursor-pointer font-semibold transition-all duration-300 bg-gray-100 text-gray-700 hover:bg-gray-200" onclick="closeModal(this.closest('.modal'))">
                  Cancel
              </button>
              <button type="submit" id="confirmStockBtn" class="btn-primary">
                  Confirm
              </button>
          </div>
      </form>
  </div>
</div>
