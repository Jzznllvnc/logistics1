<?php
// Logistic1/includes/functions/auth.php
session_start();

// --- Headers to prevent browser caching ---
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// --- Session Timeout Logic ---
$timeout_duration = 900; // 15 minutes
if (isset($_SESSION['logged_in']) && isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout_duration)) {
    session_unset();
    session_destroy();
    header("Location: ../partials/login.php?session_expired=true");
    exit();
}
$_SESSION['last_activity'] = time();

// --- Include Database Connection ---
require_once __DIR__ . '/../config/db.php';

/**
 * Authenticates a user using plain-text passwords and returns their role and status.
 * @param string $username The user's username.
 * @param string $password The user's plain-text password.
 * @return array An array containing 'success' (bool), 'role' (string|null), and 'message' (string).
 */
function authenticateUser($username, $password) {
    $conn = getDbConnection();
    $stmt = $conn->prepare(
        "SELECT u.password, u.role, s.status 
         FROM users u 
         LEFT JOIN suppliers s ON u.supplier_id = s.id 
         WHERE u.username = ?"
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // **PLAIN TEXT PASSWORD CHECK**
        if ($password === $user['password']) {
            
            // If the user is a supplier, check if their account is approved.
            if ($user['role'] === 'supplier') {
                if ($user['status'] === 'Approved') {
                    $stmt->close(); $conn->close();
                    return ['success' => true, 'role' => $user['role'], 'message' => 'Login successful.'];
                } elseif ($user['status'] === 'Pending') {
                    $stmt->close(); $conn->close();
                    return ['success' => false, 'role' => null, 'message' => 'Your supplier account is pending approval.'];
                } else { // Handles 'Rejected' or any other status
                    $stmt->close(); $conn->close();
                    return ['success' => false, 'role' => null, 'message' => 'Your supplier account has been rejected or is inactive.'];
                }
            }
            
            // For all other non-supplier roles, login is successful.
            $stmt->close(); $conn->close();
            return ['success' => true, 'role' => $user['role'], 'message' => 'Login successful.'];
        }
    }
    
    // This message is returned if the username is not found or the password does not match.
    $stmt->close(); $conn->close();
    return ['success' => false, 'role' => null, 'message' => 'Invalid username or password.'];
}


function getUserRole($username) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $role = $result->fetch_assoc()['role'];
        $stmt->close(); $conn->close(); return $role;
    }
    $stmt->close(); $conn->close(); return null;
}

// --- Page Security Functions ---
function requireLogin() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: ../partials/login.php");
        exit();
    }
}

function requireAdmin() {
    if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'procurement')) {
        header("Location: dashboard.php");
        exit();
    }
}

function logout() {
    session_unset();
    session_destroy();
    header("Location: ../partials/login.php");
    exit();
}