<?php
require_once '../includes/functions/auth.php';

$error_message = '';
$remembered_user = $_COOKIE['remember_user'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username    = $_POST['username'] ?? '';
    $password    = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    // authenticate user via auth.php
    if (authenticateUser($username, $password)) {
        $_SESSION['username'] = $username;
        $_SESSION['role']     = getUserRole($username); // helper function in auth.php
        $_SESSION['logged_in'] = true;
        session_regenerate_id(true); // prevent session fixation

        // remember me
        if ($remember_me) {
            setcookie('remember_user', $username, time() + (86400 * 30), "/"); // 30 days
        } else {
            if (isset($_COOKIE['remember_user'])) {
                setcookie('remember_user', '', time() - 3600, "/"); // clear cookie
            }
        }

        // role-based redirect - now all users go to dashboard with role-specific content
        header("Location: ../pages/dashboard.php");
        exit();
    } else {
        $error_message = "❌ Invalid username or password.";
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
                <p style="color: red; margin-bottom: 10px;">
                  <?php echo htmlspecialchars($error_message); ?>
                </p>
            <?php endif; ?>
            <input type="text" name="username" id="username" placeholder="Username" required value="<?php echo htmlspecialchars($remembered_user); ?>">
            <div class="password-wrapper">
              <input type="password" name="password" id="password" placeholder="Password" required autocomplete="current-password">
              <button type="button" class="toggle-password" aria-label="Show password">
                <i class="fa-solid fa-eye" aria-hidden="true"></i>
              </button>
            </div>
            <div class="remember-me-container">
              <input type="checkbox" id="remember_me" name="remember_me" <?php if(!empty($remembered_user)) echo 'checked'; ?>>
              <label for="remember_me">Remember Me</label>
            </div>
            <button type="submit" class="login-button">Log In</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <footer>
    &copy; 2025 SLATE Freight Management System. All rights reserved.
  </footer>

  <script>
    (function() {
      const passwordInput = document.getElementById('password');
      const toggleButton = document.querySelector('.toggle-password');
      if (!passwordInput || !toggleButton) return;

      toggleButton.addEventListener('click', function () {
        const isPassword = passwordInput.getAttribute('type') === 'password';
        passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
        const icon = this.querySelector('i');
        if (icon) {
          icon.classList.toggle('fa-eye');
          icon.classList.toggle('fa-eye-slash');
        }
        this.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
      });
    })();
  </script>
</body>
</html>
