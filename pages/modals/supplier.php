<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 opacity-0 invisible transition-all duration-200">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 transform scale-95 transition-transform duration-200">
        <div class="px-10 py-8 text-center">
            <div class="w-56 h-56 md:w-68 md:h-68 mx-auto mb-2 flex items-center justify-center">
                <img src="../assets/images/logout.png" alt="Logout" class="w-full h-full object-contain">
            </div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Sign Out?</h2>
            <p class="text-sm text-gray-600 leading-relaxed">Are you sure you want to sign out of your account? You&apos;ll need to log in again to access your supplier dashboard.</p>
        </div>
        <div class="px-6 pb-6 flex gap-3 justify-center mt-4">
            <button type="button" class="px-4 py-2.5 bg-gray-50 text-gray-700 border border-gray-200 rounded-md font-medium text-sm hover:bg-gray-100 hover:text-gray-900 transition-colors duration-200 flex-1 max-w-[120px]" onclick="closeLogoutModal()">
                Cancel
            </button>
            <button type="button" class="px-4 py-2.5 bg-red-600 text-white rounded-md font-medium text-sm hover:bg-red-700 transition-colors duration-200 flex-1 max-w-[120px]" onclick="confirmLogout()">
                Sign Out
            </button>
        </div>
    </div>
</div>

<!-- Reset Confirmation Modal -->
<div id="resetModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 opacity-0 invisible transition-all duration-200">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 transform scale-95 transition-transform duration-200">
        <div class="px-6 py-6 text-center">
            <div class="w-12 h-12 mx-auto mb-6 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center">
                <i data-lucide="rotate-ccw" class="w-6 h-6"></i>
            </div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Reset All Changes?</h2>
            <p class="text-sm text-gray-600 leading-relaxed">Are you sure you want to reset all changes? This will reload the form with the original values and any unsaved changes will be lost.</p>
        </div>
        <div class="px-6 pb-6 flex gap-3 justify-center mt-4">
            <button type="button" class="px-4 py-2.5 bg-gray-50 text-gray-700 border border-gray-200 rounded-md font-medium text-sm hover:bg-gray-100 hover:text-gray-900 transition-colors duration-200 flex-1 max-w-[120px]" onclick="closeResetModal()">
                Cancel
            </button>
            <button type="button" class="px-4 py-2.5 bg-red-600 text-white rounded-md font-medium text-sm hover:bg-red-700 transition-colors whitespace-nowrap duration-200 flex-1 max-w-[140px]" onclick="confirmReset()">
                Reset Changes
            </button>
        </div>
    </div>
</div>

<!-- Bid Modal -->
<div id="bidModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center hidden z-50">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md mx-4">
        <h2 class="text-2xl font-bold mb-4 text-gray-800" id="bidModalTitle">Place Your Bid</h2>
        <form method="POST">
            <input type="hidden" name="action" value="place_bid">
            <input type="hidden" name="po_id" id="po_id_input">
            
            <div class="mb-4">
                <label for="bid_amount" class="form-label">Bid Amount (₱)</label>
                <input type="number" name="bid_amount" id="bid_amount" step="0.01" placeholder="Enter your bid amount" class="form-input" required>
            </div>

            <div class="mb-6">
                 <label for="notes" class="form-label">Notes (Optional)</label>
                <textarea name="notes" id="notes" placeholder="Include any notes for the procurement team..." rows="3" class="form-input"></textarea>
            </div>

            <div class="flex justify-end gap-4">
                <button type="button" onclick="closeBidModal()" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Submit Bid
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Global modal functions for supplier portal
window.showLogoutModal = function() {
    const modal = document.getElementById('logoutModal');
    const content = modal.querySelector('.transform');
    
    modal.classList.remove('opacity-0', 'invisible');
    modal.classList.add('opacity-100', 'visible');
    
    setTimeout(() => {
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }, 10);
    
    // Close any open dropdown menus
    const dropdownMenu = document.getElementById('supplierDropdownMenu');
    if (dropdownMenu) {
        dropdownMenu.style.display = 'none';
    }
};

window.closeLogoutModal = function() {
    const modal = document.getElementById('logoutModal');
    const content = modal.querySelector('.transform');
    
    content.classList.remove('scale-100');
    content.classList.add('scale-95');
    
    setTimeout(() => {
        modal.classList.remove('opacity-100', 'visible');
        modal.classList.add('opacity-0', 'invisible');
    }, 200);
};

window.confirmLogout = function() {
    closeLogoutModal();
    window.location.href = '?action=logout';
};

window.showResetModal = function() {
    const modal = document.getElementById('resetModal');
    const content = modal.querySelector('.transform');
    
    modal.classList.remove('opacity-0', 'invisible');
    modal.classList.add('opacity-100', 'visible');
    
    setTimeout(() => {
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }, 10);
    
    // Re-initialize icons in the modal
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
};

window.closeResetModal = function() {
    const modal = document.getElementById('resetModal');
    const content = modal.querySelector('.transform');
    
    content.classList.remove('scale-100');
    content.classList.add('scale-95');
    
    setTimeout(() => {
        modal.classList.remove('opacity-100', 'visible');
        modal.classList.add('opacity-0', 'invisible');
    }, 200);
};

window.confirmReset = function() {
    closeResetModal();
    location.reload();
};

// Bid Modal Functions
window.openBidModal = function(po_id, item_name) {
    document.getElementById('bidModalTitle').innerText = `Place Your Bid for "${item_name}"`;
    document.getElementById('po_id_input').value = po_id;
    document.getElementById('bidModal').classList.remove('hidden');
    // Reinitialize Lucide icons after modal is shown
    lucide.createIcons();
};

window.closeBidModal = function() {
    document.getElementById('bidModal').classList.add('hidden');
};

// Modal event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Close logout modal when clicking outside
    const logoutModal = document.getElementById('logoutModal');
    if (logoutModal) {
        logoutModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeLogoutModal();
            }
        });
    }
    
    // Close reset modal when clicking outside  
    const resetModal = document.getElementById('resetModal');
    if (resetModal) {
        resetModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeResetModal();
            }
        });
    }
    
    // Close bid modal when clicking outside
    const bidModal = document.getElementById('bidModal');
    if (bidModal) {
        bidModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeBidModal();
            }
        });
    }
    
    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (logoutModal && !logoutModal.classList.contains('invisible')) {
                closeLogoutModal();
            }
            if (resetModal && !resetModal.classList.contains('invisible')) {
                closeResetModal();
            }
            if (bidModal && !bidModal.classList.contains('hidden')) {
                closeBidModal();
            }
        }
    });
});
</script>
