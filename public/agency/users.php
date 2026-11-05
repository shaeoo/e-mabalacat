<?php
// public/agency/users.php
session_start();
require_once '../../src/config/database.php';
require_once '../../src/includes/auth-guard.php';
requireRole('agency_admin');

$agencyType = $_SESSION['agency_type'] ?? 'bfp';
$agencyName = strtoupper($agencyType);

$action = $_GET['action'] ?? '';
$error = '';
$success = '';

// Handle Delete (Only responders belonging to this agency)
if ($action === 'delete' && isset($_GET['id'])) {
    $delId = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND agency_type = ? AND role = 'responder'");
        $stmt->execute([$delId, $agencyType]);
        if ($stmt->rowCount() > 0) {
            $success = "Matagumpay na na-delete ang responder account (#$delId).";
        } else {
            $error = "Hindi ma-delete ang user. Maaaring hindi ito sakop ng iyong ahensya.";
        }
    } catch (PDOException $e) {
        $error = "Error sa pagbura: " . $e->getMessage();
    }
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $editId = (int)$_POST['user_id'];
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $isOnDuty = isset($_POST['is_on_duty']) ? 1 : 0;

    try {
        $stmt = $pdo->prepare("
            UPDATE users SET full_name = ?, email = ?, phone = ?, is_on_duty = ? 
            WHERE id = ? AND agency_type = ? AND role = 'responder'
        ");
        $stmt->execute([$fullName, $email, $phone, $isOnDuty, $editId, $agencyType]);

        if (!empty($_POST['new_password'])) {
            $pHash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $pStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $pStmt->execute([$pHash, $editId]);
        }

        $success = "Na-update ang tauhan: " . htmlspecialchars($fullName);
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Create Responder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, email, phone, password_hash, role, agency_type, kyc_status, email_verified, is_on_duty) 
            VALUES (?, ?, ?, ?, 'responder', ?, 'verified', 1, 1)
        ");
        $stmt->execute([$fullName, $email, $phone, $password, $agencyType]);
        $success = "Matagumpay na naidagdag ang responder para sa " . $agencyName . ": " . htmlspecialchars($fullName);
    } catch (PDOException $e) {
        $error = "Error (posibleng duplicate email): " . $e->getMessage();
    }
}

// Query responders for this agency
$stmt = $pdo->prepare("
    SELECT * FROM users 
    WHERE agency_type = ? AND role = 'responder' 
    ORDER BY created_at DESC
");
$stmt->execute([$agencyType]);
$personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if editing
$editUser = null;
if (isset($_GET['edit_id'])) {
    $eStmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND agency_type = ?");
    $eStmt->execute([(int)$_GET['edit_id'], $agencyType]);
    $editUser = $eStmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#063151">
<title><?= $agencyName ?> Personnel CRUD &bull; e-Mabalacat</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/site.css">
</head>
<body>

<div class="app-shell">
  <?php include '../partials/sidebar.php'; ?>

  <div class="app-main">
    
    <!-- TOPBAR -->
    <header class="app-topbar">
      <div class="app-topbar-title">
        <span>🚒</span>
        <span><?= $agencyName ?> Agency Admin &bull; Field Responders Management</span>
      </div>
      <div class="app-topbar-actions">
        <span class="badge badge-gold"><?= count($personnel) ?> Active Units</span>
        <a href="dashboard.php" class="btn btn-outline-light btn-xs">&larr; Back to Dashboard</a>
      </div>
    </header>

    <!-- CONTENT -->
    <main class="app-content">

      <?php if ($success): ?>
        <div class="callout callout-info" style="margin-bottom: 1.5rem; background: #ecfdf5; border-color: #10b981; color: #065f46;">
          <strong>✓ Tagumpay:</strong> <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="callout callout-alert" style="margin-bottom: 1.5rem; background: #fef2f2; border-color: #ef4444; color: #991b1b;">
          <strong>⚠ Puna:</strong> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <!-- EDIT MODAL CARD IF ACTIVE -->
      <?php if ($editUser): ?>
        <div class="bento-card span-12" style="border: 2px solid var(--gold); margin-bottom: 24px;">
          <div class="bento-head">
            <h2 class="bento-title">I-edit ang Responder Profile (#<?= $editUser['id'] ?>)</h2>
            <a href="users.php" class="btn btn-outline-light btn-xs">&times; Kanselahin</a>
          </div>
          <form method="POST">
            <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 14px;">
              <div class="form-group">
                <label class="form-label">Buong Pangalan</label>
                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($editUser['full_name']) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($editUser['email']) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Telepono</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($editUser['phone']) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Reset Password (Optional)</label>
                <input type="password" name="new_password" class="form-control" placeholder="Iwanang bakante kung di papalitan">
              </div>
              <div class="form-group" style="display:flex; align-items:center; gap:8px; padding-top:24px;">
                <input type="checkbox" name="is_on_duty" id="duty" value="1" <?= $editUser['is_on_duty'] ? 'checked' : '' ?> style="width:18px;height:18px;">
                <label for="duty" style="font-weight:700; cursor:pointer;">On Active Duty</label>
              </div>
            </div>
            <button type="submit" name="update_user" class="btn btn-gold btn-sm">I-save ang Pagbabago</button>
          </form>
        </div>
      <?php endif; ?>

      <!-- BENTO GRID -->
      <div class="bento-grid">
        
        <!-- BENTO 1: HERO METRIC -->
        <div class="bento-card span-4 hero-bento">
          <div class="bento-head">
            <span class="bento-title" style="color:#fff;">Station Personnel</span>
            <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;"><?= $agencyName ?></span>
          </div>
          <div class="bento-kpi-value"><?= count($personnel) ?></div>
          <p class="bento-sub" style="color:#cbd5e1;">Lahat ng nakatalagang field responders at desk personnel para sa lungsod ng Mabalacat.</p>
        </div>

        <!-- BENTO 2: QUICK ADD FORM -->
        <div class="bento-card span-8">
          <div class="bento-head">
            <h2 class="bento-title">+ Magdagdag ng Bagong Field Responder</h2>
            <span class="badge badge-outline">Grab-Style First-to-Accept Units</span>
          </div>
          <form method="POST">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 12px;">
              <div class="form-group">
                <label class="form-label">Pangalan ng Responder</label>
                <input type="text" name="full_name" class="form-control" placeholder="hal. FO1 Juan Dela Cruz" required>
              </div>
              <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="officer@mabalacat.gov.ph" required>
              </div>
              <div class="form-group">
                <label class="form-label">Contact / Radio Phone</label>
                <input type="text" name="phone" class="form-control" placeholder="0917-000-0000" required>
              </div>
              <div class="form-group">
                <label class="form-label">Initial Password</label>
                <input type="password" name="password" class="form-control" value="Mabalacat2026!" required>
              </div>
            </div>
            <button type="submit" name="create_user" class="btn btn-gold btn-sm">+ Lumikha ng Responder Account</button>
          </form>
        </div>

        <!-- BENTO 3: PERSONNEL TABLE -->
        <div class="bento-card span-12" style="padding: 0;">
          <div style="padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid var(--line); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size: 1rem; font-weight: 700; color: var(--navy); margin: 0;">Roster ng mga Rehistradong Responders (CRUD)</h3>
            <span class="badge badge-gold"><?= count($personnel) ?> Tauhan</span>
          </div>
          <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
              <thead>
                <tr style="background: #f1f5f9; text-align: left; border-bottom: 1px solid var(--line);">
                  <th style="padding: 10px 14px;">ID</th>
                  <th style="padding: 10px 14px;">Pangalan ng Responder</th>
                  <th style="padding: 10px 14px;">Contact &amp; Radio</th>
                  <th style="padding: 10px 14px;">Duty Status</th>
                  <th style="padding: 10px 14px;">Petsa ng Pagkakalikha</th>
                  <th style="padding: 10px 14px; text-align: right;">Aksyon</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($personnel)): ?>
                  <tr>
                    <td colspan="6" style="padding: 2.5rem; text-align: center; color: var(--muted);">
                      Walang rehistradong field responder para sa ahensyang ito. Gamitin ang form sa itaas upang magdagdag.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($personnel as $p): ?>
                    <tr style="border-bottom: 1px solid var(--line);">
                      <td style="padding: 10px 14px; font-family: var(--font-mono); color: var(--muted);">#<?= $p['id'] ?></td>
                      <td style="padding: 10px 14px; font-weight: 700; color: var(--navy);"><?= htmlspecialchars($p['full_name']) ?></td>
                      <td style="padding: 10px 14px;">
                        <div><?= htmlspecialchars($p['email']) ?></div>
                        <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($p['phone']) ?></div>
                      </td>
                      <td style="padding: 10px 14px;">
                        <?php if ($p['is_on_duty']): ?>
                          <span class="badge badge-emerald" style="font-size:0.7rem;">● On Active Duty</span>
                        <?php else: ?>
                          <span class="badge badge-outline" style="font-size:0.7rem;">○ Off-Duty</span>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 10px 14px; color: var(--muted); font-size: 0.75rem;">
                        <?= date('M d, Y', strtotime($p['created_at'])) ?>
                      </td>
                      <td style="padding: 10px 14px; text-align: right; white-space: nowrap;">
                        <a href="users.php?edit_id=<?= $p['id'] ?>" class="btn btn-outline-light btn-xs">Edit</a>
                        <a href="users.php?action=delete&id=<?= $p['id'] ?>" class="btn btn-outline-light btn-xs" style="color: #ef4444; border-color: #fca5a5;" onclick="return confirm('Sigurado ka bang nais mong burahin ang responder na ito?');">Delete</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>

    </main>
  </div>
</div>

</body>
</html>

