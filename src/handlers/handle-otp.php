<?php
// src/handlers/handle-otp.php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['verify_email'])) {
    $email = $_SESSION['verify_email'];
    $enteredOtp = trim($_POST['otp_code'] ?? '');

    $stmt = $pdo->prepare("SELECT id, otp_code, otp_expires_at FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && $user['otp_code'] === $enteredOtp && strtotime($user['otp_expires_at']) > time()) {
        $update = $pdo->prepare("UPDATE users SET email_verified = 1, otp_code = NULL, otp_expires_at = NULL WHERE id = ?");
        $update->execute([$user['id']]);

        unset($_SESSION['verify_email']);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = 'citizen';

        header("Location: ../../public/citizen/dashboard.php?verified=true");
        exit();
    } else {
        header("Location: ../../public/otp-verify.php?error=invalid");
        exit();
    }
}
?>
