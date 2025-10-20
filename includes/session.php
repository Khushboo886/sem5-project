<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
}

function isEmployee() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Employee';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php?error=login_required');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ../unauthorized.php');
        exit();
    }
}

function requireEmployee() {
    requireLogin();
    if (!isEmployee()) {
        header('Location: ../unauthorized.php');
        exit();
    }
}
?>
