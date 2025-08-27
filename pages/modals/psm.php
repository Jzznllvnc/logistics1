<?php if ($_SESSION['role'] === 'admin'): ?>
<div id="supplierModal" class="modal hidden">
  <div class="modal-content p-8 max-w-2xl">
    <div class="flex justify-between items-center mb-2">
      <h2 id="modalTitle" class="modal-title flex items-center min-w-0 flex-1">
        <i data-lucide="building" class="w-6 h-6 mr-3 flex-shrink-0" id="supplierModalIcon"></i>
        <span id="supplierModalTitleText" class="truncate">Add New Supplier</span>
      </h2>
      <button type="button" class="close-button flex-shrink-0 ml-3" onclick="closeModal('supplierModal')">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    <p class="modal-subtitle" id="supplierModalSubtitle">Register a new supplier to your network.</p>
    <div class="border-b border-[var(--card-border)] mb-5"></div>
    
    <form id="supplierForm" method="POST" action="procurement_sourcing.php">
      <input type="hidden" name="action" id="formAction">
      <input type="hidden" name="supplier_id" id="supplierId">
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="mb-5">
          <label for="supplier_name" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Supplier Name</label>
          <input type="text" name="supplier_name" id="supplier_name" required class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" placeholder="Enter legal company name">
        </div>
        
        <div class="mb-5">
          <label for="contact_person" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Contact Person</label>
          <input type="text" name="contact_person" id="contact_person" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" placeholder="Enter contact person name">
        </div>
        
        <div class="mb-5">
          <label for="email" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Email</label>
          <input type="email" name="email" id="email" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" placeholder="contact@example">
        </div>
        
        <div class="mb-5">
          <label for="phone" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Phone</label>
          <input type="tel" name="phone" id="phone" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" placeholder="Enter phone number">
        </div>
      </div>
      
      <div class="mb-6">
        <label for="address" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Address</label>
        <textarea name="address" id="address" rows="3" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" placeholder="Street Address, City, Postal Code"></textarea>
      </div>
      
      <div class="flex justify-end gap-3">
        <button type="button" class="px-5 py-2.5 rounded-md border border-gray-300 cursor-pointer font-semibold transition-all duration-300 bg-gray-100 text-gray-700 hover:bg-gray-200" onclick="closeModal(document.getElementById('supplierModal'))">
          Cancel
        </button>
        <button type="submit" class="btn-primary">
          Save Supplier
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- Create Purchase Order Modal -->
<div id="createPOModal" class="modal hidden">
  <div class="modal-content p-8 max-w-lg">
    <div class="flex justify-between items-center mb-2">
      <h2 class="modal-title flex items-center min-w-0 flex-1">
        <i data-lucide="shopping-cart" class="w-6 h-6 mr-3 flex-shrink-0"></i>
        <span class="truncate">Create Purchase Order</span>
      </h2>
      <button type="button" class="close-button flex-shrink-0 ml-3" onclick="closeModal('createPOModal')">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    <p class="modal-subtitle">Create a purchase order.</p>
    <div class="border-b border-[var(--card-border)] mb-5"></div>
    
    <form action="procurement_sourcing.php" method="POST" id="createPOForm">
      <input type="hidden" name="action" value="create_po">
      
      <div class="mb-5">
        
        </select>
      </div>
      
      <div class="mb-5">
        <label for="item_name_po" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Item</label>
        <select name="item_name_po" id="item_name_po" required class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
          <option value="">-- Select Item --</option>
          <?php foreach($inventoryItems as $item): ?>
            <option value="<?php echo htmlspecialchars($item['item_name']); ?>"><?php echo htmlspecialchars($item['item_name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div class="mb-6">
        <label for="quantity_po" class="block text-sm font-semibold mb-2 text-[var(--text-color)]">Quantity</label>
        <input type="number" name="quantity_po" id="quantity_po" min="1" required class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" placeholder="Enter quantity to order">
      </div>
      
      <div class="flex justify-end gap-3">
        <button type="button" class="px-5 py-2.5 rounded-md border border-gray-300 cursor-pointer font-semibold transition-all duration-300 bg-gray-100 text-gray-700 hover:bg-gray-200" onclick="closeModal(document.getElementById('createPOModal'))">
          Cancel
        </button>
        <button type="submit" class="btn-primary">
          Create PO
        </button>
      </div>
    </form>
  </div>
</div>
