<?php
// public/superadmin/admins.php
session_start();
require_once '../../src/config/database.php';
require_once '../../src/includes/auth-guard.php';
requireRole('super_admin');

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role']; // 'mini_admin', 'agency_admin', 'responder'
    $agencyType = ($role === 'agency_admin') ? $_POST['agency_type'] : null;
    $barangayId = in_array($role, ['mini_admin', 'responder']) && !empty($_POST['barangay_id']) ? (int)$_POST['barangay_id'] : null;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, email, phone, password_hash, role, agency_type, barangay_id, email_verified, kyc_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, 'verified')
        ");
        $stmt->execute([$fullName, $email, $phone, $password, $role, $agencyType, $barangayId]);
        $successMsg = "Matagumpay na nalikha ang opisyal na account para kay " . htmlspecialchars($fullName) . " bilang " . strtoupper(str_replace('_', ' ', $role)) . "!";
    } catch (PDOException $e) {
        $errorMsg = "May naganap na error: " . $e->getMessage();
    }
}

$barangays = $pdo->query("SELECT * FROM barangays ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch existing staff & admins
$admins = $pdo->query("
    SELECT u.*, b.name as barangay_name 
    FROM users u 
    LEFT JOIN barangays b ON u.barangay_id = b.id 
    WHERE u.role IN ('super_admin', 'agency_admin', 'mini_admin', 'responder')
    ORDER BY u.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#063151">
<title>Official Admin & Staff Provisioning &bull; Super Admin | e-Mabalacat</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/site.css">
</head>
<body>

<div class="app-shell">
  <?php include '../partials/sidebar.php'; ?>

  <div class="app-main">
    
    <header class="app-topbar">
      <div class="app-topbar-title">
        <span>🛡️</span>
        <span>CDRRMO Super Admin &bull; Official Account Provisioning</span>
      </div>
      <div class="app-topbar-actions">
        <a href="users.php" class="btn btn-gold btn-xs">👥 View Full User CRUD Table</a>
        <a href="dashboard.php" class="btn btn-outline-light btn-xs">&larr; Back to Dashboard</a>
      </div>
    </header>

    <main class="app-content">
      
      <div class="bento-grid">
        
        <div class="bento-card span-12 hero-bento">
          <div class="bento-head">
            <span class="bento-title" style="color:#fff;">ADMIN PROVISIONING &bull; CDRRMO</span>
            <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;">RBAC Access Control</span>
          </div>
          <h2 style="font-size:1.35rem; color:#fff; margin:0 0 6px;">Magtalaga ng Barangay at Agency Admins</h2>
          <p class="bento-sub" style="color:#cbd5e1; margin:0;">
            Lumikha ng mga opisyal na credential para sa mga Desk Officers ng BFP, PNP, Medical CDRRMO, at Barangay Mini Admins.
          </p>
        </div>

        <?php if ($successMsg): ?>
          <div class="bento-card span-12" style="background: #ecfdf5; border-color: #10b981; color: #065f46; padding: 16px;">
            <strong>✓ Tagumpay:</strong> <?= $successMsg ?>
          </div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
          <div class="bento-card span-12" style="background: #fef2f2; border-color: #ef4444; color: #991b1b; padding: 16px;">
            <strong>⚠️ Error:</strong> <?= $errorMsg ?>
          </div>
        <?php endif; ?>

        <!-- FORM (SPAN 5) -->
        <div class="bento-card span-5">
          <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; color: var(--navy);">Lumikha ng Admin Account</h3>
          <form method="POST">
            <div class="field" style="margin-bottom: 1rem;">
              <label>Uri ng Admin (Role) <span class="req">*</span></label>
              <select name="role" id="role" onchange="toggleFields(this.value)" required class="select" style="width:100%;">
                <option value="">-- Pumili ng Role --</option>
                <option value="mini_admin">Barangay Mini-Admin</option>
                <option value="agency_admin">Agency Dispatch Admin (BFP / PNP / Medical)</option>
                <option value="responder">Field Responder / Tanod</option>
              </select>
            </div>

            <div class="field" style="margin-bottom: 1rem;">
              <label>Buong Pangalan <span class="req">*</span></label>
              <input type="text" name="full_name" required class="input" style="width:100%;" placeholder="hal. Capt. Renato Dizon">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
              <div class="field">
                <label>Email Address <span class="req">*</span></label>
                <input type="email" name="email" required class="input" style="width:100%;" placeholder="officer@mabalacat.gov.ph">
              </div>
              <div class="field">
                <label>Contact Number <span class="req">*</span></label>
                <input type="text" name="phone" required class="input" style="width:100%;" placeholder="0917-000-0000">
              </div>
            </div>

            <div class="field" style="margin-bottom: 1rem;">
              <label>Password <span class="req">*</span></label>
              <input type="password" name="password" required class="input" style="width:100%;" placeholder="Matibay na password">
            </div>

            <!-- Conditional for Barangay -->
            <div id="barangayField" class="field" style="display: none; margin-bottom: 1rem;">
              <label>Nakatalagang Barangay</label>
              <select name="barangay_id" class="select" style="width:100%;">
                <option value="">-- Piliin ang Barangay --</option>
                <?php foreach ($barangays as $b): ?>
                  <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Conditional for Agency Admin -->
            <div id="agencyField" class="field" style="display: none; margin-bottom: 1rem;">
              <label>Emergency Agency Mandate</label>
              <select name="agency_type" class="select" style="width:100%;">
                <option value="">-- Piliin ang Ahensya --</option>
                <option value="bfp">BFP (Bureau of Fire Protection)</option>
                <option value="pnp">PNP (Philippine National Police)</option>
                <option value="medical">Medical / CDRRMO Ambulance</option>
              </select>
            </div>

            <div style="margin-top: 1.5rem;">
              <button type="submit" class="btn btn-gold btn-md" style="width: 100%;">
                + Lumikha ng Opisyal na Account
              </button>
            </div>
          </form>
        </div>

        <!-- RECENT ADMINS LIST (SPAN 7) -->
        <div class="bento-card span-7" style="padding: 0; overflow: hidden;">
          <div style="padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 0.95rem; font-weight: 700; color: var(--navy); margin: 0;">Kasalukuyang Mga Admins &amp; Responders</h3>
            <span class="badge badge-gold"><?= count($admins) ?> Accounts</span>
          </div>
          <div style="max-height: 480px; overflow-y: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 0.825rem;">
              <thead>
                <tr style="background: #f1f5f9; border-bottom: 1px solid var(--line); text-align: left;">
                  <th style="padding: 0.65rem 0.85rem;">Pangalan</th>
                  <th style="padding: 0.65rem 0.85rem;">Role / Scope</th>
                  <th style="padding: 0.65rem 0.85rem;">Jurisdiction</th>
                  <th style="padding: 0.65rem 0.85rem; text-align: right;">Aksyon</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($admins as $ad): ?>
                  <tr style="border-bottom: 1px solid var(--line);">
                    <td style="padding: 0.65rem 0.85rem;">
                      <strong><?= htmlspecialchars($ad['full_name']) ?></strong>
                      <div class="small muted"><?= htmlspecialchars($ad['email']) ?></div>
                    </td>
                    <td style="padding: 0.65rem 0.85rem;">
                      <span class="chip chip-navy" style="font-size: 0.65rem; text-transform: uppercase;">
                        <?= str_replace('_', ' ', $ad['role']) ?>
                      </span>
                      <?php if ($ad['agency_type']): ?>
                        <span class="chip chip-gold" style="font-size: 0.65rem; text-transform: uppercase; margin-left: 4px;">
                          <?= $ad['agency_type'] ?>
                        </span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 0.65rem 0.85rem; font-size: 0.775rem;">
                      <?= htmlspecialchars($ad['barangay_name'] ?? 'City-Wide') ?>
                    </td>
                    <td style="padding: 0.65rem 0.85rem; text-align: right;">
                      <a href="users.php?edit_id=<?= $ad['id'] ?>" class="btn btn-outline btn-xs">Edit</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>

    </main>
  </div>
</div>

<script>
function toggleFields(role) {
    document.getElementById('barangayField').style.display = (role === 'mini_admin' || role === 'responder') ? 'block' : 'none';
    document.getElementById('agencyField').style.display = (role === 'agency_admin') ? 'block' : 'none';
}
</script>
</body>
</html>
