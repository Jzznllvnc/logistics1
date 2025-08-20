<?php
// includes/functions/auth.php
session_start();

function authenticateUser($username, $password) {
    $valid_username = 'admin';
    $valid_password = 'password123';

    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        return true;
    } else {
        return false;
    }
}

function logout() {
    session_unset();
    session_destroy();
    header("Location: ../../partials/login.php");
    exit();
}

function requireLogin() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: ../../partials/login.php");
        exit();
    }
}
?> 