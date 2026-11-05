<?php
// public/otp-verify.php
session_start();
require_once '../src/config/database.php';

$verifyEmail = $_SESSION['verify_email'] ?? '';
$error = $_GET['error'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['otp_code'] ?? '');
    
    if (empty($verifyEmail) || empty($code)) {
        $error = "Pakilagay ang 6-digit verification code.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND otp_code = ? AND otp_expires_at > NOW()");
        $stmt->execute([$verifyEmail, $code]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Mark email as verified
            $upStmt = $pdo->prepare("UPDATE users SET email_verified = 1, otp_code = NULL, otp_expires_at = NULL WHERE id = ?");
            $upStmt->execute([$user['id']]);

            // Auto login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['barangay_id'] = $user['barangay_id'];

            unset($_SESSION['verify_email']);

            header("Location: citizen/dashboard.php?verified=1");
            exit();
        } else {
            $error = "Mali o expired na ang iyong verification code. Subukang muli.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#063151">
<title>6-Digit Email Verification &bull; e-Mabalacat</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/site.css">
</head>
<body data-route="/otp-verify">
<a class="skip-link" href="#main-content">Skip to main content</a>

<?php include 'partials/header.php'; ?>

<main id="main-content">
  <div class="wrap" style="padding-top: 3.5rem; padding-bottom: 5rem; max-width: 480px;">
    
    <div class="card" style="padding: 2.5rem; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.06);">
      
      <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">✉️</div>
      <span class="eyebrow" style="color: #059669;">BREVO API TWO-FACTOR VERIFICATION</span>
      <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--navy); margin: 0.25rem 0 0.5rem 0;">I-verify ang iyong Email</h1>
      <p class="text-muted" style="font-size: 0.85rem; margin: 0 0 1.5rem 0;">
        Nagpadala kami ng 6-digit OTP code sa <strong><?= htmlspecialchars($verifyEmail ?: 'iyong email address') ?></strong>. May bisa ito sa loob ng 15 minuto.
      </p>

      <?php if ($error): ?>
        <div class="callout callout-alert" style="margin-bottom: 1.5rem; background: #fef2f2; border-color: #ef4444; color: #991b1b; padding: 0.75rem 1rem; font-size: 0.85rem; text-align: left;">
          <strong>⚠ Puna:</strong> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group" style="margin-bottom: 1.5rem;">
          <label class="form-label" style="font-size: 0.85rem; font-weight: 700;">Ilagay ang 6-Digit Code</label>
          <input type="text" name="otp_code" maxlength="6" pattern="[0-9]{6}" class="form-control" placeholder="123456" required autofocus style="text-align: center; font-size: 1.8rem; letter-spacing: 0.4em; font-family: var(--font-mono); font-weight: 700;">
        </div>

        <button type="submit" class="btn btn-gold" style="width: 100%; font-weight: 800; padding: 0.8rem; font-size: 1rem;">
          Kumpirmahin ang OTP &rarr;
        </button>
      </form>

      <div style="margin-top: 1.5rem; font-size: 0.8rem; color: var(--muted);">
        Hindi natanggap ang code? <a href="register.php" style="color: #2563eb; text-decoration: underline;">Ulitin ang rehistro</a> o suriin ang Spam folder.
      </div>

    </div>

  </div>
</main>

<?php include 'partials/footer.php'; ?>
</body>
</html>
