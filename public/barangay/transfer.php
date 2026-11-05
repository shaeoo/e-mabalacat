<?php
// public/barangay/transfer.php
session_start();
require_once '../../src/config/database.php';
require_once '../../src/includes/auth-guard.php';
requireRole('mini_admin');

$reportId = $_GET['report_id'] ?? null;
if (!$reportId) {
    header("Location: dashboard.php");
    exit();
}

$stmt = $pdo->prepare("
    SELECT r.*, b.name as barangay_name, u.full_name as reporter_name 
    FROM reports r 
    JOIN barangays b ON r.barangay_id = b.id 
    JOIN users u ON r.user_id = u.id 
    WHERE r.id = ?
");
$stmt->execute([$reportId]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target = $_POST['target_portal'];
    $transferNote = trim($_POST['transfer_notes'] ?? '');
    
    $newDesc = $report['description'] . "\n[TRANSFERRED FROM BRGY " . $report['barangay_name'] . " TO " . strtoupper($target) . "]: " . $transferNote;

    $uStmt = $pdo->prepare("UPDATE reports SET target_portal = ?, description = ? WHERE id = ?");
    $uStmt->execute([$target, $newDesc, $reportId]);

    header("Location: dashboard.php?transferred=true");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#063151">
<title>1-Click Ticket Transfer &bull; Brgy. <?= htmlspecialchars($report['barangay_name']) ?> | e-Mabalacat</title>
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
        <span>🔄</span>
        <span>Barangay <?= htmlspecialchars($report['barangay_name']) ?> &bull; Smart Escalation Routing</span>
      </div>
      <div class="app-topbar-actions">
        <a href="dashboard.php" class="btn btn-outline-light btn-xs">&larr; Back to Triage Desk</a>
      </div>
    </header>

    <main class="app-content">
      
      <div class="bento-grid" style="max-width: 820px; margin: 0 auto;">
        
        <div class="bento-card span-12 hero-bento" style="background:linear-gradient(135deg, #1d4ed8 0%, #063151 100%);">
          <div class="bento-head">
            <span class="bento-title" style="color:#fff;">Smart Escalation Routing</span>
            <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;">1-Click Transfer</span>
          </div>
          <h2 style="font-size:1.35rem; color:#fff; margin:0 0 6px;">1-Click Ticket Transfer</h2>
          <p class="bento-sub" style="color:#cbd5e1; margin:0;">
            Kung ang insidenteng ito ay lampas sa kapasidad ng barangay (hal. malaking sunog o armed crime), ilipat agad ito sa kaukulang ahensya para sa agarang responde.
          </p>
        </div>

        <!-- TICKET PREVIEW CARD -->
        <div class="bento-card span-12" style="background: #f8fafc;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
            <span class="badge badge-gold" style="font-family: var(--font-mono); font-size: 0.8rem;">
              Ticket: <?= htmlspecialchars($report['ticket_no']) ?>
            </span>
            <span class="badge badge-outline" style="text-transform: uppercase;">
              Kasalukuyang Destinasyon: <?= $report['target_portal'] ?>
            </span>
          </div>
          <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--navy); margin: 0 0 0.25rem 0;">
            <?= htmlspecialchars($report['category']) ?> &bull; Brgy. <?= htmlspecialchars($report['barangay_name']) ?>
          </h3>
          <p class="small muted" style="margin: 0;">
            <?= htmlspecialchars($report['description']) ?>
          </p>
        </div>

        <!-- TRANSFER FORM -->
        <div class="bento-card span-12">
          <form method="POST">
            
            <div class="field" style="margin-bottom: 1.5rem;">
              <label style="font-weight: 700; color:var(--navy); margin-bottom: 8px; display:block;">Piliin ang Bagong Ahensya (Destination Portal) <span class="req">*</span></label>
              
              <div style="display: grid; grid-template-columns: 1fr; gap: 0.75rem;">
                
                <label style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid var(--line); border-radius: 10px; cursor: pointer; background:#fff;">
                  <input type="radio" name="target_portal" value="bfp" required style="width: 1.25rem; height: 1.25rem;">
                  <div>
                    <strong style="color: #dc2626; font-size: 0.95rem;">🚒 Bureau of Fire Protection (BFP)</strong>
                    <p class="small muted" style="margin: 0;">Para sa structural fires, vehicular flames, o rescue emergencies.</p>
                  </div>
                </label>

                <label style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid var(--line); border-radius: 10px; cursor: pointer; background:#fff;">
                  <input type="radio" name="target_portal" value="pnp" style="width: 1.25rem; height: 1.25rem;">
                  <div>
                    <strong style="color: #1e3a8a; font-size: 0.95rem;">👮 Philippine National Police (PNP)</strong>
                    <p class="small muted" style="margin: 0;">Para sa armed robbery, physical altercations, o law enforcement incidents.</p>
                  </div>
                </label>

                <label style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid var(--line); border-radius: 10px; cursor: pointer; background:#fff;">
                  <input type="radio" name="target_portal" value="medical" style="width: 1.25rem; height: 1.25rem;">
                  <div>
                    <strong style="color: #0d9488; font-size: 0.95rem;">🚑 Medical / CDRRMO Ambulance</strong>
                    <p class="small muted" style="margin: 0;">Para sa mga vehicular accidents, emergency medical triage, o patient transfer.</p>
                  </div>
                </label>

              </div>
            </div>

            <div class="field" style="margin-bottom: 1.5rem;">
              <label style="font-weight:700;">Dahilan ng Paglipat / Barangay Endorsement Remarks</label>
              <textarea name="transfer_notes" class="input" rows="3" style="width:100%; height:auto;" placeholder="hal. Inendorso ng Barangay Dau dahil kinakailangan ng pamatay-sunog na truck ng BFP..."></textarea>
            </div>

            <div class="flex-between flex-wrap gap-12 items-center pt-12" style="border-top: 1px solid var(--line);">
              <a href="dashboard.php" class="btn btn-outline btn-sm">&larr; Kanselahin</a>
              <button type="submit" class="btn btn-gold btn-md" onclick="return confirm('Sigurado ka bang nais mong ilipat ang tiket na ito sa napiling ahensya?');">
                ➡️ Kumpirmahin ang Paglipat
              </button>
            </div>

          </form>
        </div>

      </div>

    </main>
  </div>
</div>

</body>
</html>
