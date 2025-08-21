// Logistic1/assets/js/procurement.js

// --- Supplier Modal Functions ---
function openCreateSupplierModal() {
    document.getElementById('supplierForm').reset();
    document.getElementById('modalTitle').innerText = 'Add New Supplier';
    document.getElementById('formAction').value = 'create_supplier';
    if(window.openModal) window.openModal(document.getElementById('supplierModal'));
}

function openEditSupplierModal(supplier) {
    document.getElementById('supplierForm').reset();
    document.getElementById('modalTitle').innerText = 'Edit Supplier';
    document.getElementById('formAction').value = 'update_supplier';
    document.getElementById('supplierId').value = supplier.id;
    document.getElementById('supplier_name').value = supplier.supplier_name;
    document.getElementById('contact_person').value = supplier.contact_person;
    document.getElementById('email').value = supplier.email;
    document.getElementById('phone').value = supplier.phone;
    document.getElementById('address').value = supplier.address;
    if(window.openModal) window.openModal(document.getElementById('supplierModal'));
}

function confirmDeleteSupplier(supplierId) {
    if (confirm('Are you sure you want to delete this supplier? This will also delete all associated purchase orders.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'procurement_sourcing.php';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_supplier">
            <input type="hidden" name="supplier_id" value="${supplierId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}