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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'supplier_name' => $_POST['supplier_name'],
        'contact_person' => $_POST['contact_person'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address']
    ];
    if (updateSupplierProfile($supplier_id, $data)) { // New function
        // Optional: Add a success message
        header("Location: supplier_profile.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - SLATE Logistics</title>
    <link rel="icon" href="../assets/images/slate2.png" type="image/png">
    <link rel="stylesheet" href="../assets/css/supplier_portal.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include '../partials/supplier_sidebar.php'; ?>

    <div class="supplier-content">
        <?php include '../partials/supplier_header.php'; ?>

        <main class="mt-8">
            <div class="bg-white p-8 rounded-xl shadow-lg max-w-2xl mx-auto">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">My Profile</h2>
                <form method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Company Name</label>
                            <input type="text" name="supplier_name" value="<?php echo htmlspecialchars($supplier_details['supplier_name']); ?>" class="mt-1 w-full p-2 border rounded">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                            <input type="text" name="contact_person" value="<?php echo htmlspecialchars($supplier_details['contact_person']); ?>" class="mt-1 w-full p-2 border rounded">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($supplier_details['email']); ?>" class="mt-1 w-full p-2 border rounded">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($supplier_details['phone']); ?>" class="mt-1 w-full p-2 border rounded">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Address</label>
                            <textarea name="address" rows="3" class="mt-1 w-full p-2 border rounded"><?php echo htmlspecialchars($supplier_details['address']); ?></textarea>
                        </div>
                    </div>
                    <div class="mt-6 text-right">
                        <button type="submit" class="bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg hover:bg-blue-700">Save Changes</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script src="../assets/js/supplier_portal.js"></script>
</body>
</html>