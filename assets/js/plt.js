// Logistic1/assets/js/plt.js

function openCreateProjectModal() {
    document.getElementById('projectForm').reset();
    document.getElementById('modalTitle').innerText = 'Create New Project';
    document.getElementById('formAction').value = 'create_project';
    // Clear supplier selections
    Array.from(document.querySelectorAll('#assigned_suppliers option')).forEach(opt => opt.selected = false);
    if (window.openModal) window.openModal(document.getElementById('projectModal'));
}

function openEditProjectModal(project, allSuppliers) {
    document.getElementById('projectForm').reset();
    document.getElementById('modalTitle').innerText = 'Edit Project';
    document.getElementById('formAction').value = 'update_project';
    document.getElementById('projectId').value = project.id;
    document.getElementById('project_name').value = project.project_name;
    document.getElementById('description').value = project.description;
    document.getElementById('status').value = project.status;
    document.getElementById('start_date').value = project.start_date;
    document.getElementById('end_date').value = project.end_date;
    
    // Pre-select the assigned suppliers
    const assigned = project.assigned_suppliers ? project.assigned_suppliers.split(', ') : [];
    Array.from(document.querySelectorAll('#assigned_suppliers option')).forEach(opt => {
        const supplier = allSuppliers.find(s => s.id == opt.value);
        if (supplier && assigned.includes(supplier.supplier_name)) {
            opt.selected = true;
        } else {
            opt.selected = false;
        }
    });

    if (window.openModal) window.openModal(document.getElementById('projectModal'));
}

function confirmDeleteProject(projectId) {
    if (confirm('Are you sure you want to delete this project? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'project_logistics_tracker.php';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_project">
            <input type="hidden" name="project_id" value="${projectId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Initialize Lucide Icons
function initLucideIcons() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// Make Lucide icon initialization globally available for this page
window.refreshLucideIcons = initLucideIcons;

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initLucideIcons();
});