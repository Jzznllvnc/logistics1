<?php
// auth.php
session_start();

// Hardcoded users (username => [password, role])
$users = [
    'admin'      => ['password' => 'admin123', 'role' => 'admin'],
    'warehouse'  => ['password' => 'wh123',    'role' => 'smart_warehousing'],
    'procure'    => ['password' => 'pr123',    'role' => 'procurement'],
    'pltuser'    => ['password' => 'plt123',   'role' => 'plt'],
    'almsuser'   => ['password' => 'alms123',  'role' => 'alms'],
    'dtrsuser'   => ['password' => 'dtrs123',  'role' => 'dtrs'],
];

/**
 * Authenticate a user by username and password
 */
function authenticateUser($username, $password) {
    global $users;
    if (isset($users[$username]) && $users[$username]['password'] === $password) {
        return true;
    }
    return false;
}

/**
 * Get the role of a user
 */
function getUserRole($username) {
    global $users;
    return $users[$username]['role'] ?? null;
}

/**
 * Require login (protect pages)
 */
function requireLogin() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: ../partials/login.php");
        exit();
    }
}

/**
 * Logout the current user
 */
function logout() {
    session_unset();
    session_destroy();
    header("Location: ../partials/login.php");
    exit();
}
