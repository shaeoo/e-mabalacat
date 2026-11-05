<?php
// public/superadmin/dashboard.php
session_start();
require_once '../../src/config/database.php';
require_once '../../src/includes/auth-guard.php';
require_once '../../src/includes/auto-close.php';
requireRole('super_admin');

// Run auto-close background checker
runAutoCloseChecker($pdo);

// Statistics
$totalReports = $pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();
$pendingReports = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'Pending'")->fetchColumn();
$dispatchedReports = $pdo->query("SELECT COUNT(*) FROM reports WHERE status IN ('Dispatched', 'On-Scene')")->fetchColumn();
$resolvedReports = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'Resolved'")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// SLA Breaches (> 5 minutes pending)
$slaBreaches = $pdo->query("
    SELECT COUNT(*) 
    FROM reports 
    WHERE status = 'Pending' AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) > 5
")->fetchColumn();

// Fetch reports with SLA check
$stmt = $pdo->query("
    SELECT r.*, b.name as barangay_name, u.full_name as reporter_name,
    (CASE WHEN r.status = 'Pending' AND TIMESTAMPDIFF(MINUTE, r.created_at, NOW()) > 5 THEN 1 ELSE 0 END) as is_sla_breach
    FROM reports r 
    JOIN barangays b ON r.barangay_id = b.id 
    JOIN users u ON r.user_id = u.id 
    ORDER BY is_sla_breach DESC, r.upvotes_count DESC, r.created_at DESC
");
$allReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#063151">
<title>City Command Center &bull; CDRRMO Super Admin | e-Mabalacat</title>
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
        <span>CDRRMO City Command Center &bull; Mabalacat City, Pampanga</span>
      </div>
      <div class="app-topbar-actions">
        <a href="users.php" class="btn btn-gold btn-xs">👥 Manage Users (CRUD)</a>
        <a href="admins.php" class="btn btn-outline-light btn-xs">+ Add Admin</a>
      </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="app-content">

      <!-- SLA BREACH ALERT IF PRESENT -->
      <?php if ($slaBreaches > 0): ?>
        <div class="alarm-banner mb-16">
          <span class="alarm-icon"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 18v-5a6 6 0 0 1 12 0v5M4 21h16M12 3v2M4.5 5.5 6 7M19.5 5.5 18 7M2 12h2M20 12h2"/></svg></span>
          <div>
            <strong><?= $slaBreaches ?> SLA Breach Alerts Active</strong>
            <span>Work orders have passed their mandated service delivery target. Prioritize field dispatch or update status immediately.</span>
          </div>
        </div>
        <!-- <div class="callout callout-alert" style="margin-bottom: 24px; background: #fef2f2; border: 2px solid #ef4444; border-radius: var(--radius-md); padding: 16px 20px;">
          <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
              <strong style="color: #b91c1c; font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                <span>🚨</span> CRITICAL SLA BREACH DETECTED: <?= $slaBreaches ?> Incident(s) Unattended!
              </strong>
              <p style="margin: 4px 0 0 0; color: #7f1d1d; font-size: 0.85rem;">
                Ang mga emergency tickets na ito ay lampas na sa 5 minutong maximum response window nang walang tumatanggap na responder.
              </p>
            </div>
            <span class="badge badge-alert" style="font-size: 0.85rem; padding: 6px 12px; font-weight:800;">
              <?= $slaBreaches ?> SLA Breached
            </span>
          </div>
        </div> -->
      <?php endif; ?>

      <!-- BENTO GRID SYSTEM -->
      <div class="bento-grid">
        
        <!-- BENTO 1: HERO METRIC (SPAN 4) -->
        <div class="bento-card span-4 hero-bento">
          <div class="bento-head">
            <span class="bento-title" style="color:#fff;">Incident Response Fleet</span>
            <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;">CDRRMO Master</span>
          </div>
          <div class="bento-kpi-value"><?= number_format($totalReports) ?></div>
          <div class="bento-sub" style="color:#cbd5e1;">Lahat ng nai-ulat na hazard sa 27 barangays ng Mabalacat.</div>
          <div style="margin-top: 16px; display:flex; gap:8px;">
            <a href="users.php" class="btn btn-gold btn-xs">User Accounts: <?= number_format($totalUsers) ?></a>
          </div>
        </div>

        <!-- BENTO 2: TRI-METRIC BREAKDOWN (SPAN 8) -->
        <div class="bento-card span-8">
          <div class="bento-head">
            <h2 class="bento-title">📊 Real-Time Operations Telemetry</h2>
            <span class="badge badge-outline">24h Auto-Close Active</span>
          </div>
          <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px; margin-top:8px;">
            <div style="border-right:1px solid var(--line); padding-right:12px;">
              <div class="stat-label" style="color:#ef4444;">Unassigned / Pending</div>
              <div style="font-size:2rem; font-weight:800; font-family:var(--serif); color:#ef4444;"><?= $pendingReports ?></div>
              <div class="small muted"><?= $slaBreaches ?> breach(es) (>5m)</div>
            </div>
            <div style="border-right:1px solid var(--line); padding-right:12px;">
              <div class="stat-label" style="color:#d97706;">En Route / On-Scene</div>
              <div style="font-size:2rem; font-weight:800; font-family:var(--serif); color:#d97706;"><?= $dispatchedReports ?></div>
              <div class="small muted">Active units deployed</div>
            </div>
            <div>
              <div class="stat-label" style="color:#059669;">Resolved &amp; Closed</div>
              <div style="font-size:2rem; font-weight:800; font-family:var(--serif); color:#059669;"><?= $resolvedReports ?></div>
              <div class="small muted">Two-way verified</div>
            </div>
          </div>
        </div>

        <!-- BENTO 3: MASTER FEED TABLE (SPAN 12) -->
        <div class="bento-card span-12" style="padding: 0;">
          <div style="padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid var(--line); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
            <div>
              <h3 style="font-size:1.05rem; font-weight:700; color:var(--navy); margin:0;">City-Wide Incident Feed &amp; Audit Queue</h3>
              <p class="text-muted" style="font-size:0.775rem; margin:2px 0 0 0;">Nakaayos ayon sa SLA breach status, upvotes (+1 confirm), at oras ng pagkaka-ulat.</p>
            </div>
            <span class="badge badge-outline">Total: <?= count($allReports) ?> tickets</span>
          </div>

          <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
              <thead>
                <tr style="background: #f1f5f9; text-align: left; border-bottom: 1px solid var(--line);">
                  <th style="padding: 10px 14px;">Ticket Ref</th>
                  <th style="padding: 10px 14px;">Target &amp; Location</th>
                  <th style="padding: 10px 14px;">Category / Description</th>
                  <th style="padding: 10px 14px;">Crowd Confirm (+1)</th>
                  <th style="padding: 10px 14px;">Status &amp; SLA</th>
                  <th style="padding: 10px 14px; text-align: right;">Official Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($allReports)): ?>
                  <tr>
                    <td colspan="6" style="padding: 2.5rem; text-align: center; color: var(--muted);">
                      Walang nakatalang insidente sa database sa kasalukuyan.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($allReports as $r): ?>
                    <tr style="border-bottom: 1px solid var(--line); <?= $r['is_sla_breach'] ? 'background: #fff5f5;' : '' ?>">
                      <td style="padding: 10px 14px; font-family: var(--font-mono); font-size: 0.825rem;">
                        <strong><?= htmlspecialchars($r['ticket_no']) ?></strong>
                        <div style="font-size: 0.725rem; color: var(--muted);"><?= date('M d, H:i', strtotime($r['created_at'])) ?></div>
                      </td>
                      <td style="padding: 10px 14px;">
                        <span class="badge <?= $r['target_portal'] === 'bfp' ? 'badge-alert' : ($r['target_portal'] === 'pnp' ? 'badge-blue' : ($r['target_portal'] === 'medical' ? 'badge-emerald' : 'badge-primary')) ?>" style="font-size: 0.65rem; text-transform: uppercase; font-weight: 700;">
                          <?= strtoupper($r['target_portal']) ?>
                        </span>
                        <div style="font-weight: 600; color: var(--navy); margin-top: 2px;">
                          Brgy. <?= htmlspecialchars($r['barangay_name']) ?>
                        </div>
                      </td>
                      <td style="padding: 10px 14px; max-width: 320px;">
                        <div style="font-weight: 700; color: var(--navy);"><?= htmlspecialchars($r['category']) ?></div>
                        <div style="font-size: 0.8rem; color: #475569; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                          <?= htmlspecialchars($r['description']) ?>
                        </div>
                        <div style="font-size: 0.725rem; color: var(--muted); margin-top: 2px;">
                          Reporter: <?= htmlspecialchars($r['reporter_name']) ?>
                          <?php if ($r['evidence_photo']): ?>
                            &bull; <a href="../uploads/evidence/<?= htmlspecialchars($r['evidence_photo']) ?>" target="_blank" style="color: #2563eb; text-decoration: underline;">View Photo</a>
                          <?php endif; ?>
                        </div>
                      </td>
                      <td style="padding: 10px 14px;">
                        <span class="badge badge-gold" style="font-weight: 800; font-size: 0.75rem;">
                          ▲ <?= $r['upvotes_count'] ?> Confirmed
                        </span>
                      </td>
                      <td style="padding: 10px 14px;">
                        <?php if ($r['is_sla_breach']): ?>
                          <span class="badge badge-alert" style="font-size: 0.7rem; font-weight: 700;">
                            ⚠ SLA BREACH (>5m)
                          </span>
                        <?php else: ?>
                          <span class="badge badge-outline" style="font-size: 0.7rem; font-weight: 700;">
                            <?= htmlspecialchars($r['status']) ?>
                          </span>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 10px 14px; text-align: right; white-space: nowrap;">
                        <a href="generate-memo.php?report_id=<?= $r['id'] ?>" target="_blank" class="btn btn-outline-light btn-xs" style="color: #1e3a8a; border-color: #bfdbfe;">
                          📄 Action Memo
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
