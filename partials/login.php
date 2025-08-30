<?php
require_once '../includes/functions/auth.php';

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
}

$error_message = '';
$remembered_user = $_COOKIE['remember_user'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username    = $_POST['username'] ?? '';
    $password    = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    $authResult = authenticateUser($username, $password);

    if ($authResult['success']) {
        $_SESSION['username'] = $username;
        $_SESSION['role']     = $authResult['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();
        session_regenerate_id(true);

        if ($remember_me) {
            setcookie('remember_user', $username, time() + (86400 * 30), "/");
        } else {
            if (isset($_COOKIE['remember_user'])) {
                setcookie('remember_user', '', time() - 3600, "/");
            }
        }
        
        // --- Role-Based Redirection ---
        if ($authResult['role'] === 'supplier') {
            header("Location: ../pages/supplier_dashboard.php");
        } else {
            header("Location: ../pages/dashboard.php");
        }
        exit();

    } else {
        $error_message = $authResult['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login - SLATE System</title>
    <link rel="icon" href="../assets/images/slate2.png" type="image/png">
    <link rel="stylesheet" href="../assets/css/login.css" />
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body>
    <div class="main-container">
        <div class="login-container">
            <div class="welcome-panel">
                <img src="../assets/images/hero.png" alt="Freight Management System Logo" class="hero-image">
            </div>
            <div class="login-panel">
                <div class="login-box">
                    <img src="../assets/images/slate1.png" alt="Logo" />
                    <h2>Login</h2>
                    <form action="login.php" method="POST">
                        <?php if (!empty($error_message)): ?>
                            <p style="color: #f01111ff; margin-bottom: 20px;">
                              <?php echo htmlspecialchars($error_message); ?>
                            </p>
                        <?php endif; ?>
                        <input type="text" name="username" placeholder="Username" required value="<?php echo htmlspecialchars($remembered_user); ?>">
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" placeholder="Password" required>
                            <button type="button" class="toggle-password"><i data-lucide="eye"></i></button>
                        </div>
                        <div class="remember-me-container">
                            <input type="checkbox" id="remember_me" name="remember_me" <?php if(!empty($remembered_user)) echo 'checked'; ?>>
                            <label for="remember_me">Remember Me</label>
                        </div>
                        <button type="submit" class="login-button">Log In</button>
                        <p class="register-link" style="margin-top: 15px; font-size: 14px;">
                            Don't have an account? <a href="register.php" style="color: #00c6ff; text-decoration: none;">Register as a supplier</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Eye toggle script
        const toggleButton = document.querySelector('.toggle-password');
        const passwordInput = document.getElementById('password');
        toggleButton.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            const existingIcon = this.querySelector('svg');
            if (existingIcon) {
                existingIcon.remove();
            }
            
            if (type === 'text') {
                const eyeClosedIcon = lucide.createElement(lucide.icons['EyeClosed']);
                this.appendChild(eyeClosedIcon);
            } else {
                const eyeIcon = lucide.createElement(lucide.icons['Eye']);
                this.appendChild(eyeIcon);
            }
        });
    </script>
</body>
</html>