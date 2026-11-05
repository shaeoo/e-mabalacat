<?php
// public/barangay/users.php
session_start();
require_once '../../src/config/database.php';
require_once '../../src/includes/auth-guard.php';
requireRole('mini_admin');

$barangayId = (int)$_SESSION['barangay_id'];

// Get Barangay Name
$bStmt = $pdo->prepare("SELECT name FROM barangays WHERE id = ?");
$bStmt->execute([$barangayId]);
$barangay = $bStmt->fetch(PDO::FETCH_ASSOC);

$action = $_GET['action'] ?? '';
$error = '';
$success = '';

// Handle Delete (Only citizens or responders of this barangay)
if ($action === 'delete' && isset($_GET['id'])) {
    $delId = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND barangay_id = ? AND role IN ('citizen', 'responder')");
        $stmt->execute([$delId, $barangayId]);
        if ($stmt->rowCount() > 0) {
            $success = "Matagumpay na nabura ang user (#$delId).";
        } else {
            $error = "Hindi nabura ang user. Maaaring hindi ito sakop ng iyong barangay.";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $editId = (int)$_POST['user_id'];
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $kycStatus = $_POST['kyc_status'];
    $strikeCount = (int)$_POST['strike_count'];

    try {
        $stmt = $pdo->prepare("
            UPDATE users SET full_name = ?, email = ?, phone = ?, kyc_status = ?, strike_count = ? 
            WHERE id = ? AND barangay_id = ? AND role IN ('citizen', 'responder')
        ");
        $stmt->execute([$fullName, $email, $phone, $kycStatus, $strikeCount, $editId, $barangayId]);

        if (!empty($_POST['new_password'])) {
            $pHash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $pStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $pStmt->execute([$pHash, $editId]);
        }

        $success = "Na-update ang record para kay: " . htmlspecialchars($fullName);
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Add Local Tanod Responder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_responder'])) {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, email, phone, password_hash, role, barangay_id, kyc_status, email_verified, is_on_duty) 
            VALUES (?, ?, ?, ?, 'responder', ?, 'verified', 1, 1)
        ");
        $stmt->execute([$fullName, $email, $phone, $password, $barangayId]);
        $success = "Nalikha ang bagong Barangay Tanod account para kay: " . htmlspecialchars($fullName);
    } catch (PDOException $e) {
        $error = "Error (posibleng duplicate email): " . $e->getMessage();
    }
}

// Filter users in this barangay
$roleFilter = $_GET['role'] ?? '';
$params = [$barangayId];
$sql = "SELECT * FROM users WHERE barangay_id = ? AND role IN ('citizen', 'responder')";
if ($roleFilter) {
    $sql .= " AND role = ?";
    $params[] = $roleFilter;
}
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$localUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Counts
$citizenCount = 0;
$tanodCount = 0;
$pendingKycCount = 0;
foreach ($localUsers as $lu) {
    if ($lu['role'] === 'citizen') {
        $citizenCount++;
        if ($lu['kyc_status'] === 'pending') $pendingKycCount++;
    } elseif ($lu['role'] === 'responder') {
        $tanodCount++;
    }
}

// Check edit
$editUser = null;
if (isset($_GET['edit_id'])) {
    $eStmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND barangay_id = ?");
    $eStmt->execute([(int)$_GET['edit_id'], $barangayId]);
    $editUser = $eStmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#1d4ed8">
<title>Brgy. <?= htmlspecialchars($barangay['name'] ?? 'Local') ?> &bull; User &amp; Tanod CRUD | e-Mabalacat</title>
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
        <span>🛡️</span>
        <span>Brgy. <?= htmlspecialchars($barangay['name'] ?? '') ?> Mini-Admin &bull; Citizen &amp; Tanod Management</span>
      </div>
      <div class="app-topbar-actions">
        <a href="dashboard.php" class="btn btn-outline-light btn-xs">&larr; Back to Triage Desk</a>
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

      <!-- EDIT USER MODAL/BOX -->
      <?php if ($editUser): ?>
        <div class="bento-card span-12" style="border: 2px solid var(--gold); margin-bottom: 24px;">
          <div class="bento-head">
            <h2 class="bento-title">I-edit ang User Profile (#<?= $editUser['id'] ?> &mdash; <?= htmlspecialchars($editUser['full_name']) ?>)</h2>
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
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($editUser['email']) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Telepono</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($editUser['phone']) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">KYC Verification Status</label>
                <select name="kyc_status" class="form-control">
                  <option value="verified" <?= $editUser['kyc_status'] === 'verified' ? 'selected' : '' ?>>Verified</option>
                  <option value="pending" <?= $editUser['kyc_status'] === 'pending' ? 'selected' : '' ?>>Pending Review</option>
                  <option value="rejected" <?= $editUser['kyc_status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">False Alarm Strikes</label>
                <input type="number" name="strike_count" class="form-control" value="<?= (int)$editUser['strike_count'] ?>" min="0" max="10">
              </div>
              <div class="form-group">
                <label class="form-label">Reset Password</label>
                <input type="password" name="new_password" class="form-control" placeholder="Iwanang bakante kung di papalitan">
              </div>
            </div>
            <button type="submit" name="update_user" class="btn btn-gold btn-sm">I-save ang mga Pagbabago</button>
          </form>
        </div>
      <?php endif; ?>

      <!-- BENTO GRID -->
      <div class="bento-grid">
        
        <!-- BENTO 1: HERO METRICS -->
        <div class="bento-card span-4 hero-bento">
          <div class="bento-head">
            <span class="bento-title" style="color:#fff;">Barangay Registry</span>
            <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;">Brgy. <?= htmlspecialchars($barangay['name'] ?? '') ?></span>
          </div>
          <div class="bento-kpi-value"><?= count($localUsers) ?></div>
          <p class="bento-sub" style="color:#cbd5e1;">Kabuuang bilang ng mga rehistradong residente at aktibong tanod sa nasasakupan.</p>
        </div>

        <!-- BENTO 2: ADD TANOD RESPONDER -->
        <div class="bento-card span-8">
          <div class="bento-head">
            <h2 class="bento-title">+ Magrehistro ng Barangay Tanod (Responder)</h2>
            <span class="badge badge-primary">Local Emergency Unit</span>
          </div>
          <form method="POST">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 12px;">
              <div class="form-group">
                <label class="form-label">Pangalan ng Tanod</label>
                <input type="text" name="full_name" class="form-control" placeholder="hal. Tanod Pedro Gomez" required>
              </div>
              <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="tanod@mabalacat.gov.ph" required>
              </div>
              <div class="form-group">
                <label class="form-label">Contact Number</label>
                <input type="text" name="phone" class="form-control" placeholder="0917-000-0000" required>
              </div>
              <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" value="Tanod2026!" required>
              </div>
            </div>
            <button type="submit" name="create_responder" class="btn btn-gold btn-sm">+ Lumikha ng Tanod Account</button>
          </form>
        </div>

        <!-- BENTO 3: USERS TABLE -->
        <div class="bento-card span-12" style="padding: 0;">
          <div style="padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid var(--line); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
            <div>
              <h3 style="font-size: 1rem; font-weight: 700; color: var(--navy); margin: 0;">Master Listahan ng Residente at Tanod (CRUD)</h3>
              <p class="text-muted" style="font-size: 0.775rem; margin: 2px 0 0 0;">Pamahalaan ang mga account sa loob ng Brgy. <?= htmlspecialchars($barangay['name'] ?? '') ?>.</p>
            </div>
            <div style="display: flex; gap: 8px;">
              <a href="users.php" class="btn btn-outline-light btn-xs <?= empty($roleFilter) ? 'btn-gold' : '' ?>">Lahat (<?= count($localUsers) ?>)</a>
              <a href="users.php?role=citizen" class="btn btn-outline-light btn-xs <?= $roleFilter === 'citizen' ? 'btn-gold' : '' ?>">Mga Residente</a>
              <a href="users.php?role=responder" class="btn btn-outline-light btn-xs <?= $roleFilter === 'responder' ? 'btn-gold' : '' ?>">Mga Tanod</a>
            </div>
          </div>
          <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
              <thead>
                <tr style="background: #f1f5f9; text-align: left; border-bottom: 1px solid var(--line);">
                  <th style="padding: 10px 14px;">ID</th>
                  <th style="padding: 10px 14px;">Pangalan</th>
                  <th style="padding: 10px 14px;">Contact</th>
                  <th style="padding: 10px 14px;">Role</th>
                  <th style="padding: 10px 14px;">KYC Status / ID</th>
                  <th style="padding: 10px 14px;">Strikes</th>
                  <th style="padding: 10px 14px; text-align: right;">Aksyon</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($localUsers)): ?>
                  <tr>
                    <td colspan="7" style="padding: 2.5rem; text-align: center; color: var(--muted);">
                      Walang user records sa napiling filter para sa barangay na ito.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($localUsers as $u): ?>
                    <tr style="border-bottom: 1px solid var(--line);">
                      <td style="padding: 10px 14px; font-family: var(--font-mono); color: var(--muted);">#<?= $u['id'] ?></td>
                      <td style="padding: 10px 14px; font-weight: 700; color: var(--navy);"><?= htmlspecialchars($u['full_name']) ?></td>
                      <td style="padding: 10px 14px;">
                        <div><?= htmlspecialchars($u['email']) ?></div>
                        <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($u['phone']) ?></div>
                      </td>
                      <td style="padding: 10px 14px;">
                        <span class="badge <?= $u['role'] === 'responder' ? 'badge-gold' : 'badge-emerald' ?>" style="text-transform:uppercase; font-size:0.7rem; font-weight:700;">
                          <?= $u['role'] === 'responder' ? 'Tanod Responder' : 'Citizen' ?>
                        </span>
                      </td>
                      <td style="padding: 10px 14px;">
                        <?php if ($u['kyc_status'] === 'verified'): ?>
                          <span class="badge badge-emerald" style="font-size:0.7rem;">Verified</span>
                        <?php elseif ($u['kyc_status'] === 'pending'): ?>
                          <span class="badge badge-gold" style="font-size:0.7rem;">Pending Review</span>
                        <?php else: ?>
                          <span class="badge badge-alert" style="font-size:0.7rem;">Rejected</span>
                        <?php endif; ?>

                        <?php if ($u['kyc_id_image']): ?>
                          <div style="margin-top: 2px;">
                            <a href="../uploads/kyc/<?= htmlspecialchars($u['kyc_id_image']) ?>" target="_blank" style="font-size:0.75rem; color:#2563eb; text-decoration:underline;">
                              &#128065; ID Photo
                            </a>
                          </div>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 10px 14px; font-family: var(--font-mono);">
                        <?= (int)$u['strike_count'] ?>
                      </td>
                      <td style="padding: 10px 14px; text-align: right; white-space: nowrap;">
                        <a href="users.php?edit_id=<?= $u['id'] ?>" class="btn btn-outline-light btn-xs">Edit</a>
                        <a href="users.php?action=delete&id=<?= $u['id'] ?>" class="btn btn-outline-light btn-xs" style="color: #ef4444; border-color: #fca5a5;" onclick="return confirm('Sigurado ka bang nais mong burahin ang user na ito?');">Delete</a>
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

