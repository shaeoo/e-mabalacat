<?php
// public/citizen/dashboard.php
session_start();
require_once '../../src/config/database.php';
require_once '../../src/includes/auth-guard.php';
requireRole('citizen');

$userId = (int)$_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$barangayId = (int)$user['barangay_id'];

// Get Barangay Name
$bStmt = $pdo->prepare("SELECT name FROM barangays WHERE id = ?");
$bStmt->execute([$barangayId]);
$barangay = $bStmt->fetch(PDO::FETCH_ASSOC);

// Handle Confirmation of Resolution by Citizen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_resolution'])) {
    $rId = (int)$_POST['report_id'];
    $cStmt = $pdo->prepare("UPDATE reports SET status = 'Resolved', closed_at = NOW() WHERE id = ? AND user_id = ?");
    $cStmt->execute([$rId, $userId]);
    header("Location: dashboard.php?resolved=true");
    exit();
}

// Fetch user's own submitted reports
$myReportsStmt = $pdo->prepare("
    SELECT r.*, b.name as barangay_name, resp.full_name as responder_name 
    FROM reports r 
    JOIN barangays b ON r.barangay_id = b.id 
    LEFT JOIN users resp ON r.assigned_responder_id = resp.id 
    WHERE r.user_id = ? 
    ORDER BY r.created_at DESC
");
$myReportsStmt->execute([$userId]);
$myReports = $myReportsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch reports inside their barangay for Waze-style upvoting
$barangayReportsStmt = $pdo->prepare("
    SELECT r.*, u.full_name as reporter_name,
           (SELECT COUNT(*) FROM report_upvotes ru WHERE ru.report_id = r.id AND ru.user_id = ?) as has_upvoted
    FROM reports r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.barangay_id = ? AND r.status NOT IN ('Resolved', 'False Alarm')
    ORDER BY r.upvotes_count DESC, r.created_at DESC
");
$barangayReportsStmt->execute([$userId, $barangayId]);
$barangayReports = $barangayReportsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#059669">
<title>Citizen Feed &bull; e-Mabalacat</title>
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
        <span>🏡</span>
        <span>Brgy. <?= htmlspecialchars($barangay['name'] ?? 'Mabalacat') ?> Citizen Feed &bull; CommunityPulse</span>
      </div>
      <div class="app-topbar-actions">
        <?php if ($user['kyc_status'] === 'verified'): ?>
          <a href="report.php" class="btn btn-gold btn-xs">+ Mag-ulat ng Insidente</a>
        <?php else: ?>
          <span class="badge badge-gold">Pending KYC Verification</span>
        <?php endif; ?>
      </div>
    </header>

    <main class="app-content">

      <!-- KYC ALERT BANNER -->
      <?php if ($user['kyc_status'] === 'pending'): ?>
        <div class="callout callout-alert" style="margin-bottom: 24px; background: #fffbeb; border: 2px solid #f59e0b; padding: 16px;">
          <strong style="color: #b45309; display: flex; align-items: center; gap: 8px;">
            <span>⏳</span> Sinusuri pa ng Barangay <?= htmlspecialchars($barangay['name'] ?? '') ?> ang iyong Valid ID
          </strong>
          <p style="margin: 4px 0 0 0; color: #92400e; font-size: 0.85rem;">
            Matapos ma-verify ang iyong WebRTC ID capture, magkakaroon ka na ng access na mag-ulat at mag-upvote ng mga insidente.
          </p>
        </div>
      <?php endif; ?>

      <!-- BENTO GRID -->
      <div class="bento-grid">
        
        <!-- BENTO 1: HERO (SPAN 4) -->
        <div class="bento-card span-4 hero-bento" style="background: linear-gradient(135deg, #059669 0%, #064e3b 100%);">
          <div class="bento-head">
            <span class="bento-title" style="color:#fff;">Citizen Status</span>
            <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;"><?= strtoupper($user['kyc_status']) ?></span>
          </div>
          <div class="bento-kpi-value" style="color:#fff;"><?= count($myReports) ?></div>
          <p class="bento-sub" style="color:#cbd5e1;">Kabuuang ulat na iyong isinumite para sa iyong komunidad.</p>
          <div style="margin-top:16px;">
            <a href="report.php" class="btn btn-gold btn-xs">+ Bagong Ulat</a>
          </div>
        </div>

        <!-- BENTO 2: WAZE FEED STATS (SPAN 8) -->
        <div class="bento-card span-8">
          <div class="bento-head">
            <h2 class="bento-title">📢 Community Alerts &amp; Upvoting</h2>
            <span class="badge badge-gold">Waze-Style Crowd Verification</span>
          </div>
          <p class="small muted" style="margin:0 0 12px 0;">
            Aktibong mga insidente sa Brgy. <?= htmlspecialchars($barangay['name'] ?? '') ?>. Pindutin ang "+1 Confirm" upang suportahan ang ulat at mapabilis ang pagdating ng responder.
          </p>
          <div style="display:flex; gap:16px;">
            <div>
              <div class="stat-label">Barangay Active Feed</div>
              <div style="font-size:1.8rem; font-weight:800; font-family:var(--serif); color:var(--navy);"><?= count($barangayReports) ?></div>
            </div>
            <div style="border-left:1px solid var(--line); padding-left:16px;">
              <div class="stat-label">Two-Way Verification</div>
              <div style="font-size:0.85rem; color:#059669; font-weight:700; margin-top:4px;">May 24-Oras na Kumpirmasyon bago Isara</div>
            </div>
          </div>
        </div>

        <!-- BENTO 3: MY SUBMISSIONS (SPAN 12) -->
        <div class="bento-card span-12" style="padding:0;">
          <div style="padding: 14px 20px; background: #f8fafc; border-bottom: 1px solid var(--line); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:1rem; font-weight:700; color:var(--navy); margin:0;">Aking mga Isinumiteng Ulat</h3>
            <span class="badge badge-outline"><?= count($myReports) ?> Submissions</span>
          </div>
          <div style="overflow-x: auto;">
            <table class="data-table" style="width:100%; border-collapse:collapse; font-size:0.85rem;">
              <thead>
                <tr style="background:#f1f5f9; text-align:left; border-bottom:1px solid var(--line);">
                  <th style="padding:10px 14px;">Ticket Ref</th>
                  <th style="padding:10px 14px;">Destinasyon / Kategorya</th>
                  <th style="padding:10px 14px;">Deskripsyon</th>
                  <th style="padding:10px 14px;">Upvotes</th>
                  <th style="padding:10px 14px;">Status</th>
                  <th style="padding:10px 14px; text-align:right;">Aksyon</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($myReports)): ?>
                  <tr>
                    <td colspan="6" style="padding:2rem; text-align:center; color:var(--muted);">Wala ka pang nai-uulat na insidente.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($myReports as $mr): ?>
                    <tr style="border-bottom:1px solid var(--line);">
                      <td style="padding:10px 14px; font-family:var(--font-mono); font-size:0.825rem;"><strong><?= htmlspecialchars($mr['ticket_no']) ?></strong></td>
                      <td style="padding:10px 14px;">
                        <span class="badge badge-primary" style="font-size:0.65rem; text-transform:uppercase;"><?= $mr['target_portal'] ?></span>
                        <div style="font-weight:700; margin-top:2px;"><?= htmlspecialchars($mr['category']) ?></div>
                      </td>
                      <td style="padding:10px 14px; max-width:300px;"><?= htmlspecialchars($mr['description']) ?></td>
                      <td style="padding:10px 14px;"><span class="badge badge-gold">▲ <?= $mr['upvotes_count'] ?></span></td>
                      <td style="padding:10px 14px;">
                        <span class="badge <?= $mr['status'] === 'Resolved' ? 'badge-emerald' : 'badge-outline' ?>">
                          <?= $mr['status'] ?>
                        </span>
                      </td>
                      <td style="padding:10px 14px; text-align:right;">
                        <?php if ($mr['status'] === 'Waiting Confirmation'): ?>
                          <form method="POST" style="display:inline;">
                            <input type="hidden" name="report_id" value="<?= $mr['id'] ?>">
                            <button type="submit" name="confirm_resolution" class="btn btn-gold btn-xs" style="background:#059669; color:#fff; border:none;">✓ Kumpirmahin</button>
                          </form>
                        <?php else: ?>
                          <a href="../superadmin/generate-memo.php?report_id=<?= $mr['id'] ?>" target="_blank" class="btn btn-outline-light btn-xs">View Memo</a>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- BENTO 4: COMMUNITY LIVE FEED (SPAN 12) -->
        <div class="bento-card span-12" style="padding:0;">
          <div style="padding: 14px 20px; background: #f8fafc; border-bottom: 1px solid var(--line);">
            <h3 style="font-size:1rem; font-weight:700; color:var(--navy); margin:0;">Live Feed sa Barangay (Mag-confirm ng Upvote)</h3>
          </div>
          <div style="padding:16px; display:grid; gap:12px;">
            <?php if (empty($barangayReports)): ?>
              <p class="small muted" style="text-align:center; margin:16px 0;">Tahimik ang barangay ngayon. Walang aktibong insidente sa feed.</p>
            <?php else: ?>
              <?php foreach ($barangayReports as $br): ?>
                <div style="border:1px solid var(--line); border-radius:10px; padding:14px; background:#fff; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                  <div>
                    <div style="display:flex; gap:6px; align-items:center;">
                      <span class="badge badge-gold" style="font-size:0.7rem; font-weight:800;"><?= htmlspecialchars($br['category']) ?></span>
                      <span class="badge badge-primary" style="font-size:0.65rem; text-transform:uppercase;"><?= $br['target_portal'] ?></span>
                    </div>
                    <div style="font-weight:700; color:var(--navy); margin-top:4px;">Ticket #<?= htmlspecialchars($br['ticket_no']) ?></div>
                    <p style="font-size:0.85rem; color:#475569; margin:2px 0 0 0;"><?= htmlspecialchars($br['description']) ?></p>
                  </div>
                  <form action="../../src/handlers/handle-upvote.php" method="POST">
                    <input type="hidden" name="report_id" value="<?= $br['id'] ?>">
                    <?php if ($br['has_upvoted']): ?>
                      <button disabled class="btn btn-outline-light btn-sm" style="color:#059669; border-color:#a7f3d0; background:#ecfdf5; cursor:default;">
                        ✓ Na-confirm mo na (<?= $br['upvotes_count'] ?>)
                      </button>
                    <?php else: ?>
                      <button type="submit" class="btn btn-gold btn-sm">▲ +1 Confirm (<?= $br['upvotes_count'] ?>)</button>
                    <?php endif; ?>
                  </form>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

      </div>

    </main>
  </div>
</div>

</body>
</html>
