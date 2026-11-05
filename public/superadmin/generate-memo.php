<?php
// public/superadmin/generate-memo.php
session_start();
require_once __DIR__ . '/../../src/config/database.php';
require_once __DIR__ . '/../../src/includes/auth-guard.php';
requireRole(['super_admin', 'agency_admin', 'mini_admin']);

$reportId = $_GET['report_id'] ?? null;
if (!$reportId) {
    die("Walang tinukoy na Ticket ID.");
}

// Fetch report details with reporter and barangay info
$stmt = $pdo->prepare("
    SELECT r.*, b.name as barangay_name, u.full_name as reporter_name, u.email as reporter_email, u.phone as reporter_phone,
           resp.full_name as responder_name
    FROM reports r 
    JOIN barangays b ON r.barangay_id = b.id 
    JOIN users u ON r.user_id = u.id 
    LEFT JOIN users resp ON r.assigned_responder_id = resp.id
    WHERE r.id = ?
");
$stmt->execute([$reportId]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    die("Hindi nahanap ang ticket na ito.");
}

$memoNo = "CDRRMO-" . date('Y') . "-" . str_pad($report['id'], 5, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Action Memorandum &bull; <?= htmlspecialchars($report['ticket_no']) ?> &bull; CDRRMO Mabalacat</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/site.css">
<style>
@media print {
  .no-print, .site-header, .site-footer { display: none !important; }
  body { background: #fff !important; padding: 0 !important; }
  .memo-sheet { box-shadow: none !important; border: none !important; max-width: 100% !important; margin: 0 !important; }
}
.memo-sheet {
  max-width: 800px;
  margin: 2rem auto;
  background: #ffffff;
  padding: 3rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--line);
  box-shadow: 0 10px 30px rgba(0,0,0,0.06);
  font-family: var(--font-sans);
  color: var(--navy);
}
.memo-gov {
  text-align: center;
  border-bottom: 2px solid var(--navy);
  padding-bottom: 1.5rem;
  margin-bottom: 2rem;
}
.memo-seal {
  width: 72px;
  height: 72px;
  margin: 0 auto 0.75rem auto;
}
.memo-title-block {
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.memo-meta-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  background: #f8fafc;
  padding: 1.25rem;
  border-radius: var(--radius-sm);
  margin-bottom: 2rem;
  border: 1px solid var(--line);
  font-size: 0.875rem;
}
.memo-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 2rem;
  font-size: 0.875rem;
}
.memo-table th, .memo-table td {
  padding: 0.75rem 1rem;
  border: 1px solid var(--line);
}
.memo-table th {
  background: #f1f5f9;
  text-align: left;
  font-weight: 700;
  color: var(--navy);
}
.memo-sign-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 3rem;
  margin-top: 3.5rem;
  padding-top: 1rem;
}
.sign-box {
  border-top: 1px solid var(--navy);
  padding-top: 0.5rem;
  text-align: center;
}
</style>
</head>
<body data-route="/superadmin/memo" style="background: #f1f5f9;">

<div class="no-print" style="max-width: 800px; margin: 1.5rem auto 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 0 1rem;">
  <a href="dashboard.php" class="btn btn-outline-light btn-sm">&larr; Bumalik sa Command Center</a>
  <div style="display:flex; gap:0.5rem;">
    <button onclick="window.print()" class="btn btn-gold btn-sm">&#128424; Print / Save as PDF</button>
  </div>
</div>

<div class="memo-sheet">
  
  <div class="memo-gov">
    <svg class="seal memo-seal" viewBox="0 0 100 100">
      <circle cx="50" cy="50" r="48" fill="#063151" stroke="#f5d10d" stroke-width="2.5"/>
      <path d="M27 67c7-7 13-15 23-23 5 8 10 15 23 23Z" fill="#1766a1" stroke="#f5d10d" stroke-width=".9"/>
      <circle cx="50" cy="37" r="7.2" fill="#f5d10d"/>
      <text x="50" y="84.5" font-family="sans-serif" font-size="4.4" font-weight="700" letter-spacing="1.3" fill="#f5d10d" text-anchor="middle">PAMPANGA</text>
    </svg>
    <div class="memo-title-block">
      <div style="font-size: 0.8rem; font-weight: 700; color: var(--muted);">Republic of the Philippines &bull; Province of Pampanga</div>
      <div style="font-size: 1.25rem; font-weight: 800; color: var(--navy); margin: 0.25rem 0;">CITY GOVERNMENT OF MABALACAT</div>
      <div style="font-size: 0.95rem; font-weight: 700; color: #b45309;">City Disaster Risk Reduction and Management Office (CDRRMO)</div>
      <div style="font-size: 0.75rem; color: var(--muted); font-family: var(--font-mono);">e-Sumbong / CommunityPulse Incident Response Division</div>
    </div>
  </div>

  <div style="text-align: center; margin-bottom: 2rem;">
    <h1 style="font-size: 1.35rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; text-decoration: underline;">
      Official Action Memorandum
    </h1>
    <div style="font-size: 0.85rem; font-family: var(--font-mono); color: var(--muted); margin-top: 0.35rem;">
      Memorandum Control No: <strong><?= $memoNo ?></strong>
    </div>
  </div>

  <div class="memo-meta-grid">
    <div>
      <div><strong>TICKET REFERENCE:</strong> <span style="font-family: var(--font-mono);"><?= htmlspecialchars($report['ticket_no']) ?></span></div>
      <div style="margin-top: 0.35rem;"><strong>TARGET DESTINATION:</strong> <?= strtoupper($report['target_portal']) ?></div>
      <div style="margin-top: 0.35rem;"><strong>DATE & TIME FILED:</strong> <?= date('F d, Y h:i A', strtotime($report['created_at'])) ?></div>
    </div>
    <div>
      <div><strong>JURISDICTION:</strong> Barangay <?= htmlspecialchars($report['barangay_name']) ?></div>
      <div style="margin-top: 0.35rem;"><strong>CURRENT STATUS:</strong> <?= strtoupper($report['status']) ?></div>
      <div style="margin-top: 0.35rem;"><strong>CROWD UPVOTES:</strong> <?= $report['upvotes_count'] ?> Citizen Confirmations</div>
    </div>
  </div>

  <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--navy);">I. Incident Particulars & Verification</h3>
  <table class="memo-table">
    <tr>
      <th style="width: 25%;">Category</th>
      <td><strong><?= htmlspecialchars($report['category']) ?></strong></td>
    </tr>
    <tr>
      <th>Complainant / Reporter</th>
      <td><?= htmlspecialchars($report['reporter_name']) ?> (<?= htmlspecialchars($report['reporter_phone']) ?>)</td>
    </tr>
    <tr>
      <th>Narrative Description</th>
      <td><?= nl2br(htmlspecialchars($report['description'])) ?></td>
    </tr>
    <tr>
      <th>GPS Coordinates (Exact / Fuzzed)</th>
      <td style="font-family: var(--font-mono); font-size: 0.8rem;">
        Exact: <?= $report['latitude'] ?>, <?= $report['longitude'] ?><br>
        Fuzzed (~50m privacy): <?= $report['fuzzed_lat'] ?>, <?= $report['fuzzed_lng'] ?>
      </td>
    </tr>
    <tr>
      <th>Assigned Responder</th>
      <td><?= htmlspecialchars($report['responder_name'] ?? 'None / Pending First-to-Accept Broadcast') ?></td>
    </tr>
    <?php if ($report['official_ref_no']): ?>
      <tr>
        <th>Official Blotter / Case Ref</th>
        <td style="font-family: var(--font-mono); font-weight: 700;"><?= htmlspecialchars($report['official_ref_no']) ?></td>
      </tr>
    <?php endif; ?>
    <?php if ($report['resolution_remarks']): ?>
      <tr>
        <th>Resolution Remarks</th>
        <td><?= htmlspecialchars($report['resolution_remarks']) ?></td>
      </tr>
    <?php endif; ?>
  </table>

  <?php if ($report['evidence_photo'] || $report['resolution_photo']): ?>
    <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--navy);">II. Attached Visual Evidences</h3>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
      <?php if ($report['evidence_photo']): ?>
        <div style="border: 1px solid var(--line); padding: 0.5rem; text-align: center; border-radius: var(--radius-sm);">
          <div style="font-size: 0.75rem; font-weight: 700; margin-bottom: 0.5rem;">INITIAL INCIDENT PHOTO</div>
          <img src="../uploads/evidence/<?= htmlspecialchars($report['evidence_photo']) ?>" style="max-height: 180px; width: auto; max-width: 100%; object-fit: contain; border-radius: 4px;" alt="Initial Evidence">
        </div>
      <?php endif; ?>
      <?php if ($report['resolution_photo']): ?>
        <div style="border: 1px solid var(--line); padding: 0.5rem; text-align: center; border-radius: var(--radius-sm);">
          <div style="font-size: 0.75rem; font-weight: 700; margin-bottom: 0.5rem;">RESPONDER PROOF OF RESOLUTION</div>
          <img src="../uploads/resolutions/<?= htmlspecialchars($report['resolution_photo']) ?>" style="max-height: 180px; width: auto; max-width: 100%; object-fit: contain; border-radius: 4px;" alt="Resolution Proof">
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="memo-sign-row">
    <div class="sign-box">
      <div style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">
        <?= htmlspecialchars($_SESSION['full_name'] ?? 'Command Duty Officer') ?>
      </div>
      <div style="font-size: 0.75rem; color: var(--muted);">Duty Dispatch Officer / Station Officer</div>
    </div>
    <div class="sign-box">
      <div style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">
        Hon. City Mayor / CDRRMO Head
      </div>
      <div style="font-size: 0.75rem; color: var(--muted);">City Government of Mabalacat, Pampanga</div>
    </div>
  </div>

  <div style="margin-top: 3rem; text-align: center; font-size: 0.7rem; color: var(--muted); border-top: 1px dashed var(--line); padding-top: 1rem;">
    This document was generated automatically by e-Sumbong / CommunityPulse (v2.0) on <?= date('Y-m-d H:i:s') ?>. Verified authentic and tamper-evident under Republic Act 10173.
  </div>

</div>

</body>
</html>
