<?php
// src/handlers/handle-register.php
session_start();
require_once '../config/database.php';
require_once '../includes/notifier.php';

die("ok");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['name'] ?? $_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $barangayId = $_POST['barangay'] ?? $_POST['barangay_id'] ?? null;
    $idPhotoBase64 = $_POST['id_photo_base64'] ?? '';

    if (empty($email) || empty($password) || empty($fullName) || empty($barangayId)) {
        die("Lahat ng required field ay kinakailangan.");
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        die("Ang email na ito ay ginagamit na.");
    }

    // Process Camera Snapshot (WebRTC / getUserMedia)
    $idImageName = null;
    if (!empty($idPhotoBase64)) {
        $uploadDir = '../../public/uploads/kyc/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (preg_match('/^data:image\/(\w+);base64,/', $idPhotoBase64, $type)) {
            $data = substr($idPhotoBase64, strpos($idPhotoBase64, ',') + 1);
            $decoded = base64_decode($data);
            if ($decoded !== false) {
                $ext = strtolower($type[1]) === 'jpeg' ? 'jpg' : strtolower($type[1]);
                $idImageName = 'KYC_' . time() . '_' . uniqid() . '.' . $ext;
                file_put_contents($uploadDir . $idImageName, $decoded);
            }
        }
    } elseif (isset($_FILES['kyc_id']) && $_FILES['kyc_id']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../public/uploads/kyc/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $ext = pathinfo($_FILES['kyc_id']['name'], PATHINFO_EXTENSION);
        $idImageName = 'KYC_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['kyc_id']['tmp_name'], $uploadDir . $idImageName);
    }

    $passHash = password_hash($password, PASSWORD_DEFAULT);
    $otpCode = str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $otpExpires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password_hash, role, barangay_id, kyc_id_image, otp_code, otp_expires_at) VALUES (?, ?, ?, ?, 'citizen', ?, ?, ?, ?)");
    $stmt->execute([$fullName, $email, $phone, $passHash, $barangayId, $idImageName, $otpCode, $otpExpires]);

    // Send email code
    $htmlContent = "<p>Hello <b>" . htmlspecialchars($fullName) . "</b>, ang iyong verification code ay: <b>$otpCode</b></p>";
    sendBrevoEmail($email, $fullName, "Verification Code", $htmlContent);

    $_SESSION['verify_email'] = $email;
    header("Location: ../../public/otp-verify.php");
    exit();
}
?>
