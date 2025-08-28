<?php
require_once '../includes/functions/auth.php';
require_once '../includes/functions/bids.php';
require_once '../includes/functions/supplier.php';
require_once '../includes/functions/notifications.php';
requireLogin();

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
}
if ($_SESSION['role'] !== 'supplier') {
    header("Location: dashboard.php");
    exit();
}

$supplier_id = getSupplierIdFromUsername($_SESSION['username']);
$supplier_details = getSupplierDetails($supplier_id); // New function

$message = '';
$message_type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'supplier_name' => $_POST['supplier_name'],
        'contact_person' => $_POST['contact_person'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address']
    ];
    if (updateSupplierProfile($supplier_id, $data)) { // New function
        $_SESSION['message'] = 'Profile updated successfully! Your changes have been saved.';
        $_SESSION['message_type'] = 'success';
        // Refresh supplier details
        $supplier_details = getSupplierDetails($supplier_id);
    } else {
        $_SESSION['message'] = 'Failed to update profile. Please try again or contact support.';
        $_SESSION['message_type'] = 'error';
    }
    // Redirect to prevent form resubmission
    header("Location: supplier_profile.php");
    exit();
}

// Check for flash messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - SLATE Logistics</title>
    <link rel="icon" href="../assets/images/slate2.png" type="image/png">
    <link rel="stylesheet" href="../assets/css/supplier_portal.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body>
    <div class="supplier-content-full">
        <?php include '../partials/supplier_header.php'; ?>

        <div class="supplier-content-area">
            <div class="supplier-content-container">
                <div class="content-card">
                    <div class="profile-header">
                        <div class="profile-header-left">
                            <div class="profile-icon">
                                <i data-lucide="user-circle" class="profile-icon-svg"></i>
                            </div>
                            <h2 class="content-title">My Profile</h2>
                        </div>
                        <a href="supplier_dashboard.php" class="back-button">
                            <i data-lucide="arrow-left" class="back-icon"></i>
                            Back to Dashboard
                        </a>
                    </div>
                    

                    
                    <form method="POST">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="form-label">
                                    <i data-lucide="building" class="w-4 h-4 inline mr-2"></i>
                                    Company Name
                                </label>
                                <input type="text" name="supplier_name" value="<?php echo htmlspecialchars($supplier_details['supplier_name']); ?>" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">
                                    <i data-lucide="user" class="w-4 h-4 inline mr-2"></i>
                                    Contact Person
                                </label>
                                <input type="text" name="contact_person" value="<?php echo htmlspecialchars($supplier_details['contact_person']); ?>" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">
                                    <i data-lucide="mail" class="w-4 h-4 inline mr-2"></i>
                                    Email Address
                                </label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($supplier_details['email']); ?>" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">
                                    <i data-lucide="phone" class="w-4 h-4 inline mr-2"></i>
                                    Phone Number
                                </label>
                                <input type="text" name="phone" value="<?php echo htmlspecialchars($supplier_details['phone']); ?>" class="form-input" required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="form-label">
                                    <i data-lucide="map-pin" class="w-4 h-4 inline mr-2"></i>
                                    Address
                                </label>
                                <textarea name="address" rows="3" class="form-input" required><?php echo htmlspecialchars($supplier_details['address']); ?></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end mt-8 gap-4">
                            <button type="button" class="btn-secondary" onclick="resetForm()">
                                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                Reset
                            </button>
                            <button type="submit" class="btn-primary">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

        <?php include 'modals/supplier.php'; ?>
    
    <script src="../assets/js/custom-alerts.js"></script>
      
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        function resetForm() {
            showResetModal();
        }
    </script>
    
    <?php if ($message && !empty(trim($message))): ?>
    <script>
        // Wait for both DOM and custom alerts to be ready
        function tryShowCustomAlert() {
            if (document.body && window.customAlert && typeof window.customAlert.show === 'function') {
                window.customAlert.show(
                    <?php echo json_encode($message); ?>, 
                    <?php echo json_encode($message_type); ?>, 
                    5000
                );
            } else if (document.body && window.showCustomAlert && typeof window.showCustomAlert === 'function') {
                window.showCustomAlert(
                    <?php echo json_encode($message); ?>, 
                    <?php echo json_encode($message_type); ?>
                );
            } else {
                // Retry after a short delay if not ready
                setTimeout(tryShowCustomAlert, 50);
            }
        }

        // Start when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', tryShowCustomAlert);
        } else {
            tryShowCustomAlert();
        }
    </script>
    <?php endif; ?>
    
    <script src="../assets/js/supplier_portal.js"></script>
</body>
</html>