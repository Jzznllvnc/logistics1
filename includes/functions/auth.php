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

// --- Database-driven User Functions ---
function authenticateUser($username, $password) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if ($password === $user['password']) {
            $stmt->close(); $conn->close(); return true;
        }
    }
    $stmt->close(); $conn->close(); return false;
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
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
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