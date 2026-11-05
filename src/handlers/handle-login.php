<?php
// src/handlers/handle-login.php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    var_dump($_POST);
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        header("Location: ../../public/login.php?error=invalid");
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && !empty($user['password_hash']) && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['barangay_id'] = $user['barangay_id'];
        $_SESSION['agency_type'] = $user['agency_type'];

        $role = $user['role'];
        if ($role === 'super_admin') {
            header("Location: ../../public/superadmin/dashboard.php");
        } elseif ($role === 'agency_admin') {
            header("Location: ../../public/agency/dashboard.php");
        } elseif ($role === 'mini_admin') {
            header("Location: ../../public/barangay/dashboard.php");
        } elseif ($role === 'responder') {
            header("Location: ../../public/responder/dashboard.php");
        } else {
            header("Location: ../../public/citizen/dashboard.php");
        }
        exit();
    } else {
        header("Location: ../../public/login.php?error=invalid");
        exit();
    }
}
?>
