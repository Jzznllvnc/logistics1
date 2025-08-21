// Logistic1/assets/js/alms.js

function openCreateAssetModal() {
    document.getElementById('assetForm').reset();
    document.getElementById('modalTitle').innerText = 'Register New Asset';
    document.getElementById('formAction').value = 'create_asset';
    if (window.openModal) window.openModal(document.getElementById('assetModal'));
}

function openEditAssetModal(asset) {
    document.getElementById('assetForm').reset();
    document.getElementById('modalTitle').innerText = 'Edit Asset';
    document.getElementById('formAction').value = 'update_asset';
    document.getElementById('assetId').value = asset.id;
    document.getElementById('asset_name').value = asset.asset_name;
    document.getElementById('asset_type').value = asset.asset_type;
    document.getElementById('purchase_date').value = asset.purchase_date;
    document.getElementById('status').value = asset.status;
    if (window.openModal) window.openModal(document.getElementById('assetModal'));
}

function confirmDeleteAsset(assetId) {
    if (confirm('Are you sure you want to delete this asset? This will also remove all its maintenance history.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'asset_lifecycle_maintenance.php';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_asset">
            <input type="hidden" name="asset_id" value="${assetId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}