<?php
// src/includes/auth-guard.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireRole($allowedRoles) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header("Location: ../login.php");
        exit();
    }

    if (!in_array($_SESSION['role'], (array)$allowedRoles)) {
        header("Location: ../login.php?error=unauthorized");
        exit();
    }
}
?>
