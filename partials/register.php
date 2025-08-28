<?php
require_once '../includes/functions/supplier.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_data = [
        'supplier_name' => $_POST['supplier_name'],
        'contact_person' => $_POST['contact_person'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address'],
        'username' => $_POST['username'],
        'password' => $_POST['password'],
    ];

    $result = registerSupplier($supplier_data, $_FILES['verification_document']);

    if ($result === true) {
        $success_message = "Registration successful! Your application is pending review.";
    } else {
        $error_message = $result;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Supplier Registration - SLATE System</title>
  <link rel="icon" href="../assets/images/slate2.png" type="image/png">
  <link rel="stylesheet" href="../assets/css/register.css" />
</head>
<body>
  <div class="main-container">
    <div class="register-container">
      <div class="welcome-panel">
        <img src="../assets/images/hero.png" alt="Freight Management System Logo" class="hero-image">
      </div>
      <div class="register-panel">
        <div class="register-box">
          <img src="../assets/images/slate1.png" alt="Logo" />
          <h2>Supplier Registration</h2>
          <form action="register.php" method="POST" enctype="multipart/form-data">
            <?php if (!empty($error_message)): ?>
                <p style="color: #f01111ff; margin-bottom: 20px;"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <p style="color: #10b981; margin-bottom: 20px;"><?php echo htmlspecialchars($success_message); ?></p>
            <?php endif; ?>
            
            <div class="form-grid">
              <div class="form-column">
                <input type="text" name="supplier_name" placeholder="Company Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="username" placeholder="Username" required>
              </div>
              <div class="form-column">
                <input type="text" name="contact_person" placeholder="Contact Person" required>
                <input type="tel" name="phone" placeholder="Phone" required>
                <input type="password" name="password" placeholder="Password" required>
              </div>
            </div>
            
            <textarea name="address" placeholder="Address" rows="3" required></textarea>
            
            <div class="file-upload-section">
              <label for="verification_document">Verification Document (e.g., Business Permit)</label>
              <input type="file" name="verification_document" id="verification_document" accept=".pdf,.jpg,.jpeg,.png" required>
            </div>

            <button type="submit" class="register-button">Register</button>
          </form>
          <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
        </div>
      </div>
    </div>
  </div>
</body>
</html>