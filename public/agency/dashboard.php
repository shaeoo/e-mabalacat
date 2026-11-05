<?php
// public/agency/dashboard.php
// session_start();
// require_once '../../src/config/database.php';
// require_once '../../src/includes/auth-guard.php';
// requireRole('agency_admin');

$agencyType = $_SESSION['agency_type'] ?? 'bfp';
$agencyName = strtoupper($agencyType);

$agencyColorMap = [
    'bfp' => ['theme' => '#dc2626', 'title' => 'Bureau of Fire Protection (BFP)', 'tag' => 'FIRE & RESCUE COMMAND'],
    'pnp' => ['theme' => '#1e1b4b', 'title' => 'Philippine National Police (PNP)', 'tag' => 'POLICE STATION DISPATCH'],
    'medical' => ['theme' => '#0d9488', 'title' => 'CDRRMO Medical Emergency Dispatch', 'tag' => 'EMERGENCY MEDICAL SERVICES']
];
$agencyTheme = $agencyColorMap[$agencyType] ?? $agencyColorMap['bfp'];

// Fetch reports assigned to this specific agency
$stmt = $pdo->prepare("
    SELECT r.*, b.name as barangay_name, u.full_name as reporter_name, u.phone as reporter_phone,
           resp.full_name as responder_name
    FROM reports r 
    JOIN barangays b ON r.barangay_id = b.id 
    JOIN users u ON r.user_id = u.id 
    LEFT JOIN users resp ON r.assigned_responder_id = resp.id
    WHERE r.target_portal = ? 
    ORDER BY r.upvotes_count DESC, r.created_at DESC
");
$stmt->execute([$agencyType]);
$agencyReports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Personnel count
$pStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE agency_type = ? AND role = 'responder'");
$pStmt->execute([$agencyType]);
$personnelCount = (int)$pStmt->fetchColumn();

// Counts
$pendingCount = 0;
$activeCount = 0;
$resolvedCount = 0;
foreach ($agencyReports as $ar) {
    if ($ar['status'] === 'Pending') $pendingCount++;
    elseif (in_array($ar['status'], ['Dispatched', 'On-Scene'])) $activeCount++;
    elseif ($ar['status'] === 'Resolved') $resolvedCount++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="<?= $agencyTheme['theme'] ?>">
<title><?= $agencyName ?> Emergency Dispatch | e-Mabalacat</title>
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
        <span>🚨</span>
        <span><?= $agencyTheme['title'] ?> &bull; Emergency Dispatch Console</span>
      </div>
      <div class="app-topbar-actions">
        <a href="users.php" class="btn btn-gold btn-xs">👥 Agency Personnel (CRUD)</a>
        <a href="merge.php" class="btn btn-outline-light btn-xs">📑 Merge Tickets</a>
      </div>
    </header>

    <main class="app-content">

      <!-- BENTO GRID -->
      <div class="bento-grid">
        
        <!-- BENTO 1: HERO METRIC (SPAN 4) -->
        <div class="bento-card span-4 hero-bento" style="background: linear-gradient(135deg, <?= $agencyTheme['theme'] ?> 0%, #063151 100%);">
          <div class="bento-head">
            <span class="bento-title" style="color:#fff;"><?= $agencyTheme['tag'] ?></span>
            <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;"><?= $agencyName ?></span>
          </div>
          <div class="bento-kpi-value" style="color:#fff;"><?= count($agencyReports) ?></div>
          <p class="bento-sub" style="color:#cbd5e1;">Papasok na emergency calls na naka-prioritize sa ilalim ng <?= $agencyName ?>.</p>
          <div style="margin-top:16px;">
            <a href="users.php" class="btn btn-gold btn-xs">Units on Duty: <?= $personnelCount ?> &rarr;</a>
          </div>
        </div>

        <!-- BENTO 2: TRI-STATUS METRICS (SPAN 8) -->
        <div class="bento-card span-8">
          <div class="bento-head">
            <h2 class="bento-title">⚡ Rapid Mission Telemetry</h2>
            <span class="badge badge-gold">Crowd-Ranked Urgency</span>
          </div>
          <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:16px; margin-top:8px;">
            <div style="border-right:1px solid var(--line); padding-right:12px;">
              <div class="stat-label" style="color:#ef4444;">Pending First-to-Accept</div>
              <div style="font-size:2rem; font-weight:800; font-family:var(--serif); color:#ef4444;"><?= $pendingCount ?></div>
              <div class="small muted">Grab-style unassigned</div>
            </div>
            <div style="border-right:1px solid var(--line); padding-right:12px;">
              <div class="stat-label" style="color:#d97706;">En Route / On-Scene</div>
              <div style="font-size:2rem; font-weight:800; font-family:var(--serif); color:#d97706;"><?= $activeCount ?></div>
              <div class="small muted">Active units deployed</div>
            </div>
            <div>
              <div class="stat-label" style="color:#059669;">Resolved Cases</div>
              <div style="font-size:2rem; font-weight:800; font-family:var(--serif); color:#059669;"><?= $resolvedCount ?></div>
              <div class="small muted">Two-way confirmed</div>
            </div>
          </div>
        </div>

        <!-- BENTO 3: INCIDENTS STREAM (SPAN 12) -->
        <div class="bento-card span-12" style="padding:0;">
          <div style="padding: 14px 20px; background: #f8fafc; border-bottom: 1px solid var(--line); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
            <div>
              <h3 style="font-size:1.05rem; font-weight:700; color:var(--navy); margin:0;">Emergency Queue Ranked by Upvotes</h3>
              <p class="text-muted" style="font-size:0.775rem; margin:2px 0 0 0;">Ang mga insidenteng may pinakamaraming +1 confirmations ay nasa itaas.</p>
            </div>
            <a href="merge.php" class="btn btn-outline-light btn-xs">📑 Merge Duplicates</a>
          </div>

          <div style="overflow-x: auto;">
            <table class="data-table" style="width:100%; border-collapse:collapse; font-size:0.85rem;">
              <thead>
                <tr style="background:#f1f5f9; text-align:left; border-bottom:1px solid var(--line);">
                  <th style="padding:10px 14px;">Ticket Ref</th>
                  <th style="padding:10px 14px;">Barangay &amp; Kategorya</th>
                  <th style="padding:10px 14px;">Deskripsyon &amp; Telemetry</th>
                  <th style="padding:10px 14px;">Crowd Confirm (+1)</th>
                  <th style="padding:10px 14px;">Status</th>
                  <th style="padding:10px 14px; text-align:right;">Aksyon</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($agencyReports)): ?>
                  <tr>
                    <td colspan="6" style="padding:2.5rem; text-align:center; color:var(--muted);">
                      Walang nakabinbing emergency report sa ilalim ng <?= $agencyName ?>.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($agencyReports as $ar): ?>
                    <tr style="border-bottom:1px solid var(--line);">
                      <td style="padding:10px 14px; font-family:var(--font-mono); font-size:0.825rem;">
                        <strong><?= htmlspecialchars($ar['ticket_no']) ?></strong>
                        <div style="font-size:0.725rem; color:var(--muted);"><?= date('M d, H:i', strtotime($ar['created_at'])) ?></div>
                      </td>
                      <td style="padding:10px 14px;">
                        <span class="badge badge-primary" style="font-size:0.65rem; font-weight:700;">
                          Brgy. <?= htmlspecialchars($ar['barangay_name']) ?>
                        </span>
                        <div style="font-weight:700; color:var(--navy); margin-top:2px;"><?= htmlspecialchars($ar['category']) ?></div>
                      </td>
                      <td style="padding:10px 14px; max-width:320px;">
                        <div style="font-size:0.825rem; color:#334155; line-height:1.4;">
                          <?= htmlspecialchars($ar['description']) ?>
                        </div>
                        <?php if ($agencyType === 'bfp'): ?>
                          <div style="font-size:0.725rem; color:#dc2626; font-weight:700; margin-top:2px;">
                            BFP Telemetry: Alarm Level Bravo &bull; Est. Damage: Structural
                          </div>
                        <?php elseif ($agencyType === 'pnp'): ?>
                          <div style="font-size:0.725rem; color:#1e3a8a; font-weight:700; margin-top:2px;">
                            PNP Blotter: <?= htmlspecialchars($ar['official_ref_no'] ?? 'Pending') ?>
                          </div>
                        <?php elseif ($agencyType === 'medical'): ?>
                          <div style="font-size:0.725rem; color:#0d9488; font-weight:700; margin-top:2px;">
                            EMS: Patient Count: 1 &bull; Dest: Mabalacat Dist. Hospital
                          </div>
                        <?php endif; ?>
                      </td>
                      <td style="padding:10px 14px;">
                        <span class="badge badge-gold" style="font-weight:800; font-size:0.75rem;">
                          ▲ <?= $ar['upvotes_count'] ?> Confirmed
                        </span>
                      </td>
                      <td style="padding:10px 14px;">
                        <span class="badge <?= $ar['status'] === 'Pending' ? 'badge-alert' : 'badge-emerald' ?>" style="font-size:0.7rem;">
                          <?= $ar['status'] ?>
                        </span>
                      </td>
                      <td style="padding:10px 14px; text-align:right; white-space:nowrap;">
                        <a href="../superadmin/generate-memo.php?report_id=<?= $ar['id'] ?>" target="_blank" class="btn btn-outline-light btn-xs">Memo</a>
                        <a href="merge.php?primary_id=<?= $ar['id'] ?>" class="btn btn-outline-light btn-xs" style="color:#ca8a04; border-color:#fde047;">Merge</a>
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
