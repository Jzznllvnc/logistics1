// Logistic1/assets/js/smart_warehousing.js

document.addEventListener('DOMContentLoaded', function() {
    // --- Live Search for Inventory Table ---
    const searchInput = document.getElementById('inventorySearchInput');
    const tableBody = document.getElementById('inventoryTableBody');
    if (searchInput && tableBody) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = tableBody.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const itemNameCell = rows[i].getElementsByTagName('td')[0];
                if (itemNameCell) {
                    const itemName = itemNameCell.textContent || itemNameCell.innerText;
                    if (itemName.toLowerCase().indexOf(searchTerm) > -1) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            }
        });
    }
});

// --- Modal Functions (Admin Only) ---

/**
 * Opens the modal to edit an inventory item's name.
 * @param {object} item - The inventory item object (id, item_name).
 */
function openEditModal(item) {
    const modal = document.getElementById('editItemModal');
    if (modal) {
        document.getElementById('edit_item_id').value = item.id;
        document.getElementById('item_name_edit').value = item.item_name;
        // Assuming you have a global openModal function from script.js
        if(window.openModal) {
            window.openModal(modal);
        }
    }
}

/**
 * Confirms and submits a request to delete an inventory item.
 * @param {number} itemId - The ID of the item to delete.
 */
function confirmDeleteItem(itemId) {
    if (confirm('Are you sure you want to permanently delete this item? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'smart_warehousing.php'; // Submits back to the same page
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_item">
            <input type="hidden" name="item_id" value="${itemId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}