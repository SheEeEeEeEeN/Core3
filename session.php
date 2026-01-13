<?php
session_start();


// if (!isset($_SESSION['username'])) {
//     header("Location: login.php");
//     exit;
// }

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
        header("Location: login.php");
        exit();
    }
}

// Optional: allow multiple roles
function requireRoles(array $roles) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], $roles)) {
        header("Location: login.php");
        exit();
    }
}
?>
