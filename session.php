<?php
session_start();

// Require user to be logged in
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Require a specific role
function requireRole($role) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        // Clear session for safety
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }
}

// Optional: allow multiple roles
function requireRoles(array $roles) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], $roles)) {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }
}
?>








