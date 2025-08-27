<?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'alms'): ?>
<div id="assetModal" class="modal hidden">
    <div class="modal-content p-8 max-w-xl">
        <div class="flex justify-between items-center mb-2">
            <h2 class="modal-title flex items-center" id="assetModalTitle">
                <i data-lucide="package" class="w-6 h-6 mr-3" id="assetModalIcon"></i>
                <span id="assetModalTitleText">Register New Asset</span>
            </h2>
            <button type="button" class="close-button" onclick="closeModal('assetModal')"><i data-lucide="x"></i></button>
        </div>
        <p class="modal-subtitle" id="assetModalSubtitle">Add a new logistics asset to the registry.</p>
        <div class="border-b border-[var(--card-border)] mb-5"></div>
        <form id="assetForm" method="POST" action="asset_lifecycle_maintenance.php" enctype="multipart/form-data">
            <input type="hidden" name="action" id="formAction">
            <input type="hidden" name="asset_id" id="assetId">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="asset_name" class="block text-sm font-semibold mb-2">Asset Name</label>
                    <input type="text" name="asset_name" id="asset_name" required class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" placeholder="e.g., Forklift Unit 01, Delivery Truck A1, Warehouse Scanner">
                </div>
                <div>
                    <label for="asset_type" class="block text-sm font-semibold mb-2">Asset Type</label>
                    <input type="text" name="asset_type" id="asset_type" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" placeholder="e.g., Vehicle, Equipment, Technology, Infrastructure">
                </div>
                <div>
                    <label for="purchase_date" class="block text-sm font-semibold mb-2">Purchase Date</label>
                    <input type="date" name="purchase_date" id="purchase_date" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
                </div>
                <div>
                    <label for="status" class="block text-sm font-semibold mb-2">Status</label>
                    <select name="status" id="status" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
                        <option>Operational</option>
                        <option>Under Maintenance</option>
                        <option>Decommissioned</option>
                    </select>
                </div>
            </div>
            <div class="mt-5">
                <label for="asset_image" class="block text-sm font-semibold mb-2">Asset Image</label>
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <input type="file" name="asset_image" id="asset_image" accept="image/*" class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]" onchange="previewAssetImage(this)">
                        <p class="text-xs text-gray-500 mt-1">Supported formats: JPG, PNG, GIF. Max size: 5MB</p>
                    </div>
                    <div id="imagePreviewContainer" class="hidden">
                        <img id="imagePreview" class="w-20 h-20 object-cover rounded-md border border-[var(--input-border)]" alt="Preview">
                        <button type="button" onclick="clearImagePreview()" class="text-xs text-red-500 mt-1 block">Remove</button>
                    </div>
                    <div id="currentImageContainer" class="hidden">
                        <img id="currentImage" class="w-20 h-20 object-cover rounded-md border border-[var(--input-border)]" alt="Current">
                        <p class="text-xs text-gray-600 mt-1">Current image</p>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-5">
                <button type="button" class="px-5 py-2.5 rounded-md border" onclick="closeModal(document.getElementById('assetModal'))">Cancel</button>
                <button type="submit" class="btn-primary">Save Asset</button>
            </div>
        </form>
    </div>
</div>

<div id="scheduleMaintenanceModal" class="modal hidden">
    <div class="modal-content p-8 max-w-lg">
        <div class="flex justify-between items-center mb-2">
            <h2 class="modal-title flex items-center">
                <i data-lucide="calendar-plus" class="w-6 h-6 mr-3"></i>
                <span>Schedule Maintenance Task</span>
            </h2>
            <button type="button" class="close-button" onclick="closeModal('scheduleMaintenanceModal')"><i data-lucide="x"></i></button>
        </div>
        <p class="modal-subtitle">Schedule a maintenance task for a logistics asset.</p>
        <div class="border-b border-[var(--card-border)] mb-5"></div>
        <form id="scheduleMaintenanceForm" method="POST" action="asset_lifecycle_maintenance.php">
            <input type="hidden" name="action" value="schedule_maintenance">
            <div class="mb-5">
                <label for="asset_id_maint" class="block text-sm font-semibold mb-2">Asset</label>
                <select name="asset_id_maint" id="asset_id_maint" required class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
                    <option value="">-- Select Asset --</option>
                    <?php foreach($assets as $asset): ?>
                        <option value="<?php echo $asset['id']; ?>"><?php echo htmlspecialchars($asset['asset_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-5">
                <label for="task_description" class="block text-sm font-semibold mb-2">Task Description</label>
                <textarea name="task_description" id="task_description" placeholder="e.g., Replace the battery, Check the brakes, Clean the scanner" rows="3" required class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]"></textarea>
            </div>
            <div class="mb-6">
                <label for="scheduled_date" class="block text-sm font-semibold mb-2">Scheduled Date</label>
                <input type="date" name="scheduled_date" id="scheduled_date" required class="w-full p-2.5 border border-[var(--input-border)] rounded-md bg-[var(--input-bg)] text-[var(--input-text)]">
            </div>
            <div class="flex justify-end gap-3 mt-5">
                <button type="button" class="px-5 py-2.5 rounded-md border" onclick="closeModal(document.getElementById('scheduleMaintenanceModal'))">Cancel</button>
                <button type="submit" class="btn-primary">Schedule Task</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
