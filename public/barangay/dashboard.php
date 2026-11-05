<?php
// public/barangay/dashboard.php
session_start();
require_once '../../src/config/database.php';
require_once '../../src/includes/auth-guard.php';
requireRole('mini_admin');

$barangayId = (int)$_SESSION['barangay_id'];

// Get Barangay Name
$bStmt = $pdo->prepare("SELECT name FROM barangays WHERE id = ?");
$bStmt->execute([$barangayId]);
$barangay = $bStmt->fetch(PDO::FETCH_ASSOC);

// Handle KYC Action if submitted
$kycMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_kyc'])) {
    $targetUserId = (int)$_POST['user_id'];
    $newStatus = $_POST['action_kyc'] === 'approve' ? 'verified' : 'rejected';
    
    $upStmt = $pdo->prepare("UPDATE users SET kyc_status = ? WHERE id = ? AND barangay_id = ?");
    $upStmt->execute([$newStatus, $targetUserId, $barangayId]);
    $kycMsg = "Matagumpay na na-update ang KYC status ng residente bilang " . strtoupper($newStatus);
}

// Fetch pending KYC users in this barangay
$kycStmt = $pdo->prepare("SELECT * FROM users WHERE barangay_id = ? AND role = 'citizen' AND kyc_status = 'pending' ORDER BY created_at ASC");
$kycStmt->execute([$barangayId]);
$pendingCitizens = $kycStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all local responders in this barangay
$respStmt = $pdo->prepare("SELECT * FROM users WHERE barangay_id = ? AND role = 'responder' ORDER BY full_name ASC");
$respStmt->execute([$barangayId]);
$localResponders = $respStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch reports assigned to this barangay
$repStmt = $pdo->prepare("
    SELECT r.*, u.full_name as reporter_name, u.phone as reporter_phone, resp.full_name as responder_name
    FROM reports r 
    JOIN users u ON r.user_id = u.id 
    LEFT JOIN users resp ON r.assigned_responder_id = resp.id
    WHERE r.barangay_id = ? 
    ORDER BY r.created_at DESC
");
$repStmt->execute([$barangayId]);
$reports = $repStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#1d4ed8">
<title>Brgy. <?= htmlspecialchars($barangay['name'] ?? 'Local') ?> Desk &bull; e-Mabalacat</title>
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
        <span>🏛️</span>
        <span>Barangay <?= htmlspecialchars($barangay['name'] ?? 'Local') ?> Triage Desk</span>
      </div>
      <div class="app-topbar-actions">
        <a href="users.php" class="btn btn-gold btn-xs">👥 Manage Citizens &amp; Tanod</a>
      </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="app-content">

      <?php if ($kycMsg): ?>
        <div class="callout callout-info" style="margin-bottom: 20px; background: #ecfdf5; border-color: #10b981; color: #065f46;">
          <strong>✓ Tagumpay:</strong> <?= htmlspecialchars($kycMsg) ?>
        </div>
      <?php endif; ?>

      <!-- BENTO GRID -->
      <div class="bento-grid">
        
        <!-- BENTO 1: HERO (SPAN 4) -->
        <div class="bento-card span-4 hero-bento" style="background: linear-gradient(135deg, #1d4ed8 0%, #172554 100%);">
          <div class="bento-head">
            <span class="bento-title" style="color:#fff;">Barangay Triage</span>
            <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;">Brgy. <?= htmlspecialchars($barangay['name'] ?? '') ?></span>
          </div>
          <div class="bento-kpi-value" style="color:#fff;"><?= count($reports) ?></div>
          <p class="bento-sub" style="color:#cbd5e1;">Kabuuang bilang ng mga ulat sa komunidad na sumasailalim sa pagsusuri.</p>
          <div style="margin-top:16px;">
            <a href="users.php" class="btn btn-gold btn-xs">Tanods: <?= count($localResponders) ?> | Citizens CRUD &rarr;</a>
          </div>
        </div>

        <!-- BENTO 2: KYC STATUS & STATS (SPAN 8) -->
        <div class="bento-card span-8">
          <div class="bento-head">
            <h2 class="bento-title">📋 Local Verification &amp; Workforce</h2>
            <span class="badge badge-gold"><?= count($pendingCitizens) ?> For Verification</span>
          </div>
          <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-top:8px;">
            <div style="border-right:1px solid var(--line); padding-right:12px;">
              <div class="stat-label" style="color:#d97706;">Pending Citizen KYC</div>
              <div style="font-size:2rem; font-weight:800; font-family:var(--serif); color:#d97706;"><?= count($pendingCitizens) ?></div>
              <p class="small muted">Nangangailangan ng ID approval bago makapag-file ng ulat.</p>
            </div>
            <div>
              <div class="stat-label" style="color:#059669;">Barangay Tanod Responders</div>
              <div style="font-size:2rem; font-weight:800; font-family:var(--serif); color:#059669;"><?= count($localResponders) ?></div>
              <p class="small muted">Mga field responders sa ilalim ng barangay.</p>
            </div>
          </div>
        </div>

        <!-- BENTO 3: KYC APPROVAL QUEUE (SPAN 12) -->
        <div class="bento-card span-12" style="padding: 0;">
          <div style="padding: 14px 20px; background: #f8fafc; border-bottom: 1px solid var(--line); display:flex; justify-content:space-between; align-items:center;">
            <div>
              <h3 style="font-size: 1rem; font-weight: 700; color: var(--navy); margin: 0;">🪪 Pending Citizen KYC Approval Queue</h3>
              <p class="text-muted" style="font-size: 0.775rem; margin: 2px 0 0 0;">Mga bagong residenteng nag-upload ng Valid ID via WebRTC camera o snapshot.</p>
            </div>
            <span class="badge badge-gold"><?= count($pendingCitizens) ?> Residente</span>
          </div>

          <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
              <thead>
                <tr style="background: #f1f5f9; text-align: left; border-bottom: 1px solid var(--line);">
                  <th style="padding: 10px 14px;">Residente</th>
                  <th style="padding: 10px 14px;">Contact Info</th>
                  <th style="padding: 10px 14px;">Valid ID Snapshot</th>
                  <th style="padding: 10px 14px;">Petsa ng Rehistro</th>
                  <th style="padding: 10px 14px; text-align: right;">Desisyon (Approval)</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($pendingCitizens)): ?>
                  <tr>
                    <td colspan="5" style="padding: 2rem; text-align: center; color: var(--muted);">
                      Walang naghihintay na resident verification para sa Brgy. <?= htmlspecialchars($barangay['name'] ?? '') ?>.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($pendingCitizens as $pc): ?>
                    <tr style="border-bottom: 1px solid var(--line);">
                      <td style="padding: 10px 14px; font-weight: 700; color: var(--navy);">
                        <?= htmlspecialchars($pc['full_name']) ?>
                      </td>
                      <td style="padding: 10px 14px; font-size: 0.8rem;">
                        <div><?= htmlspecialchars($pc['email']) ?></div>
                        <div class="text-muted"><?= htmlspecialchars($pc['phone']) ?></div>
                      </td>
                      <td style="padding: 10px 14px;">
                        <?php if ($pc['kyc_id_image']): ?>
                          <a href="../uploads/kyc/<?= htmlspecialchars($pc['kyc_id_image']) ?>" target="_blank" class="btn btn-outline-light btn-xs" style="color: #2563eb; border-color: #93c5fd;">
                            &#128065; Buksan ang ID
                          </a>
                        <?php else: ?>
                          <span class="text-muted" style="font-size: 0.75rem;">Walang imahe</span>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 10px 14px; font-size: 0.75rem; color: var(--muted);">
                        <?= date('M d, Y h:i A', strtotime($pc['created_at'])) ?>
                      </td>
                      <td style="padding: 10px 14px; text-align: right;">
                        <form method="POST" style="display: inline-flex; gap: 4px;">
                          <input type="hidden" name="user_id" value="<?= $pc['id'] ?>">
                          <button type="submit" name="action_kyc" value="approve" class="btn btn-gold btn-xs" style="background:#059669; color:#fff; border:none;">
                            ✓ Aprubahan
                          </button>
                          <button type="submit" name="action_kyc" value="reject" class="btn btn-outline-light btn-xs" style="color:#ef4444; border-color:#fca5a5;" onclick="return confirm('Tanggihan ang KYC ng residenteng ito?');">
                            &times; Tanggihan
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- BENTO 4: BARANGAY INCIDENTS FEED (SPAN 12) -->
        <div class="bento-card span-12" style="padding: 0;">
          <div style="padding: 14px 20px; background: #f8fafc; border-bottom: 1px solid var(--line); display:flex; justify-content:space-between; align-items:center;">
            <div>
              <h3 style="font-size: 1rem; font-weight: 700; color: var(--navy); margin: 0;">📋 Mga Insidente sa Barangay Feed</h3>
              <p class="text-muted" style="font-size: 0.775rem; margin: 2px 0 0 0;">Mga reklamo sa basura, ingay, ilaw, o minor disputes sa komunidad.</p>
            </div>
            <span class="badge badge-outline"><?= count($reports) ?> Reports</span>
          </div>

          <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
              <thead>
                <tr style="background: #f1f5f9; text-align: left; border-bottom: 1px solid var(--line);">
                  <th style="padding: 10px 14px;">Ticket Ref</th>
                  <th style="padding: 10px 14px;">Target / Kategorya</th>
                  <th style="padding: 10px 14px;">Deskripsyon</th>
                  <th style="padding: 10px 14px;">Upvotes (+1)</th>
                  <th style="padding: 10px 14px;">Status</th>
                  <th style="padding: 10px 14px; text-align: right;">Aksyon</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($reports)): ?>
                  <tr>
                    <td colspan="6" style="padding: 2.5rem; text-align: center; color: var(--muted);">
                      Walang mga aktibong ulat sa Brgy. <?= htmlspecialchars($barangay['name'] ?? '') ?>.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($reports as $r): ?>
                    <tr style="border-bottom: 1px solid var(--line);">
                      <td style="padding: 10px 14px; font-family: var(--font-mono); font-size: 0.825rem;">
                        <strong><?= htmlspecialchars($r['ticket_no']) ?></strong>
                        <div style="font-size: 0.725rem; color: var(--muted);"><?= date('M d, H:i', strtotime($r['created_at'])) ?></div>
                      </td>
                      <td style="padding: 10px 14px;">
                        <span class="badge badge-primary" style="font-size: 0.65rem; text-transform: uppercase;">
                          <?= $r['target_portal'] ?>
                        </span>
                        <div style="font-weight: 700; color: var(--navy); margin-top: 2px;"><?= htmlspecialchars($r['category']) ?></div>
                      </td>
                      <td style="padding: 10px 14px; max-width: 300px;">
                        <div style="font-size: 0.825rem; color: #334155; line-height: 1.4;">
                          <?= htmlspecialchars($r['description']) ?>
                        </div>
                        <div style="font-size: 0.725rem; color: var(--muted); margin-top: 2px;">
                          Nai-ulat ni: <?= htmlspecialchars($r['reporter_name']) ?>
                          <?php if ($r['evidence_photo']): ?>
                            &bull; <a href="../uploads/evidence/<?= htmlspecialchars($r['evidence_photo']) ?>" target="_blank" style="color: #2563eb; text-decoration: underline;">View Photo</a>
                          <?php endif; ?>
                        </div>
                      </td>
                      <td style="padding: 10px 14px;">
                        <span class="badge badge-gold" style="font-weight: 800; font-size: 0.75rem;">
                          ▲ <?= $r['upvotes_count'] ?> Confirm
                        </span>
                      </td>
                      <td style="padding: 10px 14px;">
                        <span class="badge <?= $r['status'] === 'Pending' ? 'badge-alert' : 'badge-emerald' ?>" style="font-size: 0.7rem;">
                          <?= $r['status'] ?>
                        </span>
                      </td>
                      <td style="padding: 10px 14px; text-align: right; white-space: nowrap;">
                        <a href="transfer.php?report_id=<?= $r['id'] ?>" class="btn btn-outline-light btn-xs" style="color: #d97706; border-color: #fde68a;">
                          🔄 1-Click Transfer
                        </a>
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
