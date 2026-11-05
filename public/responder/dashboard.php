<?php
// public/responder/dashboard.php
session_start();
require_once '../../src/config/database.php';
require_once '../../src/includes/auth-guard.php';
requireRole('responder');

$responderId = (int)$_SESSION['user_id'];

// Get user info
$uStmt = $pdo->prepare("
    SELECT u.*, b.name as barangay_name 
    FROM users u 
    LEFT JOIN barangays b ON u.barangay_id = b.id 
    WHERE u.id = ?
");
$uStmt->execute([$responderId]);
$responder = $uStmt->fetch(PDO::FETCH_ASSOC);

// 1. Fetch currently accepted/assigned reports by this responder
$myStmt = $pdo->prepare("
    SELECT r.*, b.name as barangay_name, u.full_name as reporter_name, u.phone as reporter_phone 
    FROM reports r 
    JOIN barangays b ON r.barangay_id = b.id 
    JOIN users u ON r.user_id = u.id 
    WHERE r.assigned_responder_id = ? AND r.status IN ('Dispatched', 'On-Scene', 'Waiting Confirmation')
    ORDER BY r.dispatched_at DESC
");
$myStmt->execute([$responderId]);
$myActiveReports = $myStmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Fetch Pending reports available for Grab-Style First-to-Accept broadcast
$pendingStmt = $pdo->query("
    SELECT r.*, b.name as barangay_name, u.full_name as reporter_name, u.phone as reporter_phone 
    FROM reports r 
    JOIN barangays b ON r.barangay_id = b.id 
    JOIN users u ON r.user_id = u.id 
    WHERE r.status = 'Pending' AND r.assigned_responder_id IS NULL 
    ORDER BY r.upvotes_count DESC, r.created_at DESC
");
$pendingReports = $pendingStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#dc2626">
<title>Responder Dispatch Desk &bull; e-Mabalacat</title>
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
        <span>🚒</span>
        <span>Field Responder Console &bull; <?= htmlspecialchars($responder['full_name']) ?></span>
      </div>
      <div class="app-topbar-actions">
        <span class="badge badge-emerald">● ON DUTY</span>
      </div>
    </header>

    <main class="app-content">

      <!-- BENTO GRID -->
      <div class="bento-grid">
        
        <!-- BENTO 1: HERO (SPAN 4) -->
        <div class="bento-card span-4 hero-bento" style="background: linear-gradient(135deg, #dc2626 0%, #7f1d1d 100%);">
          <div class="bento-head">
            <span class="bento-title" style="color:#fff;">Active Mission Unit</span>
            <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;">Field Lead</span>
          </div>
          <div class="bento-kpi-value" style="color:#fff;"><?= count($myActiveReports) ?></div>
          <p class="bento-sub" style="color:#cbd5e1;">Aktibong insidenteng kasalukuyan mong hinahawakan sa field.</p>
        </div>

        <!-- BENTO 2: BROADCAST POOL (SPAN 8) -->
        <div class="bento-card span-8">
          <div class="bento-head">
            <h2 class="bento-title">⚡ Broadcast Queue (First-to-Accept)</h2>
            <span class="badge badge-alert"><?= count($pendingReports) ?> Available for Claim</span>
          </div>
          <p class="small muted" style="margin:0 0 12px 0;">
            Grab-Style atomic work order dispatch. Ang unang unit na mag-click ng "Accept &amp; En Route" ang makakakuha ng assignment.
          </p>
          <div style="font-size:1.8rem; font-weight:800; font-family:var(--serif); color:#dc2626;"><?= count($pendingReports) ?> Unclaimed Tasks</div>
        </div>

        <!-- BENTO 3: MY ACTIVE TASK CONTROLS (SPAN 12) -->
        <div class="bento-card span-12" style="padding:0;">
          <div style="padding:14px 20px; background:#f8fafc; border-bottom:1px solid var(--line);">
            <h3 style="font-size:1rem; font-weight:700; color:var(--navy); margin:0;">🎯 Aking Kasalukuyang Nirerespondehan (Active)</h3>
          </div>
          <div style="padding:16px;">
            <?php if (empty($myActiveReports)): ?>
              <p class="small muted" style="text-align:center; padding:1.5rem 0; margin:0;">Wala kang aktibong insidente ngayon. Pumili sa broadcast pool sa ibaba.</p>
            <?php else: ?>
              <div style="display:grid; gap:16px;">
                <?php foreach ($myActiveReports as $mar): ?>
                  <div style="border:1px solid var(--line); border-left:5px solid #f59e0b; border-radius:10px; padding:16px; background:#fffcf0;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
                      <div>
                        <span class="badge badge-gold">STATUS: <?= strtoupper($mar['status']) ?></span>
                        <span class="badge badge-primary">Brgy. <?= htmlspecialchars($mar['barangay_name']) ?></span>
                        <h4 style="font-size:1.15rem; font-weight:800; color:var(--navy); margin:6px 0 2px;">Ticket #<?= htmlspecialchars($mar['ticket_no']) ?> &bull; <?= htmlspecialchars($mar['category']) ?></h4>
                        <p style="font-size:0.875rem; color:#334155; margin:0;"><?= htmlspecialchars($mar['description']) ?></p>
                        <div class="small muted" style="margin-top:6px;">Reporter: <?= htmlspecialchars($mar['reporter_name']) ?> (<?= htmlspecialchars($mar['reporter_phone']) ?>)</div>
                      </div>
                      <div>
                        <a href="https://maps.google.com/?q=<?= $mar['latitude'] ?>,<?= $mar['longitude'] ?>" target="_blank" class="btn btn-outline-light btn-xs">📍 Google Maps</a>
                      </div>
                    </div>

                    <div style="background:#fff; border:1px solid var(--line); border-radius:8px; padding:12px; margin-top:12px;">
                      <form action="../../src/handlers/handle-update-status.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="report_id" value="<?= $mar['id'] ?>">
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:10px; margin-bottom:10px;">
                          <div>
                            <label class="form-label">Next Action / Status</label>
                            <select name="new_status" class="form-control" onchange="toggleResField(this.value, <?= $mar['id'] ?>)">
                              <option value="On-Scene" <?= $mar['status'] === 'On-Scene' ? 'selected' : '' ?>>🚨 Arrived On-Scene</option>
                              <option value="Waiting Confirmation" <?= $mar['status'] === 'Waiting Confirmation' ? 'selected' : '' ?>>✅ Resolved (Upload Proof Photo)</option>
                            </select>
                          </div>
                          <div>
                            <label class="form-label">Blotter / Official Case Ref</label>
                            <input type="text" name="official_ref_no" class="form-control" placeholder="BLOT-2026-0000" value="<?= htmlspecialchars($mar['official_ref_no'] ?? '') ?>">
                          </div>
                        </div>

                        <div id="res_field_<?= $mar['id'] ?>" style="display:<?= $mar['status'] === 'Waiting Confirmation' ? 'block' : 'none' ?>; border-top:1px dashed var(--line); padding-top:10px; margin-bottom:10px;">
                          <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                            <div>
                              <label class="form-label">Resolution Proof Photo</label>
                              <input type="file" name="resolution_photo" accept="image/*" class="form-control">
                            </div>
                            <div>
                              <label class="form-label">Resolution Remarks</label>
                              <input type="text" name="resolution_remarks" class="form-control" placeholder="Action taken narrative" value="<?= htmlspecialchars($mar['resolution_remarks'] ?? '') ?>">
                            </div>
                          </div>
                        </div>

                        <button type="submit" class="btn btn-gold btn-xs">I-update ang Kalagayan</button>
                      </form>
                    </div>

                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- BENTO 4: UNCLAIMED BROADCAST STREAM (SPAN 12) -->
        <div class="bento-card span-12" style="padding:0;">
          <div style="padding:14px 20px; background:#f8fafc; border-bottom:1px solid var(--line);">
            <h3 style="font-size:1rem; font-weight:700; color:var(--navy); margin:0;">⚡ Unclaimed Broadcast Pool (Click to Claim)</h3>
          </div>
          <div style="padding:16px; display:grid; gap:12px;">
            <?php if (empty($pendingReports)): ?>
              <p class="small muted" style="text-align:center; padding:1.5rem 0; margin:0;">Lahat ng reports ay may nakatalaga nang unit.</p>
            <?php else: ?>
              <?php foreach ($pendingReports as $pr): ?>
                <div style="border:1px solid var(--line); border-left:4px solid #ef4444; border-radius:10px; padding:14px; background:#fff; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                  <div>
                    <div style="display:flex; gap:6px; align-items:center;">
                      <span class="badge badge-alert" style="font-size:0.65rem; text-transform:uppercase; font-weight:800;"><?= $pr['target_portal'] ?></span>
                      <span class="badge badge-primary" style="font-size:0.7rem;">Brgy. <?= htmlspecialchars($pr['barangay_name']) ?></span>
                      <span class="badge badge-gold" style="font-size:0.7rem;">▲ <?= $pr['upvotes_count'] ?></span>
                    </div>
                    <div style="font-weight:700; color:var(--navy); margin-top:4px;">Ticket #<?= htmlspecialchars($pr['ticket_no']) ?> &bull; <?= htmlspecialchars($pr['category']) ?></div>
                    <p style="font-size:0.85rem; color:#475569; margin:2px 0 0;"><?= htmlspecialchars($pr['description']) ?></p>
                  </div>
                  <form action="../../src/handlers/handle-accept.php" method="POST">
                    <input type="hidden" name="report_id" value="<?= $pr['id'] ?>">
                    <button type="submit" class="btn btn-gold btn-sm" style="background:#dc2626; color:#fff; border:none; font-weight:800;">⚡ Accept &amp; En Route</button>
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

<script>
function toggleResField(val, id) {
  var box = document.getElementById('res_field_' + id);
  if (box) box.style.display = (val === 'Waiting Confirmation') ? 'block' : 'none';
}
</script>
</body>
</html>
