<?php
// public/agency/merge.php
session_start();
require_once '../../src/config/database.php';
require_once '../../src/includes/auth-guard.php';
requireRole(['agency_admin', 'super_admin', 'mini_admin']);

$msg = '';
$error = '';
$agencyType = $_SESSION['agency_type'] ?? 'bfp';
$agencyName = strtoupper($agencyType);

// Handle Merge POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['merge_action'])) {
    $primaryId = (int)$_POST['primary_id'];
    $secondaryId = (int)$_POST['secondary_id'];
    $mergeReason = trim($_POST['merge_reason'] ?? 'Duplicate incident reported by multiple callers');

    if ($primaryId === $secondaryId) {
        $error = "Hindi maaaring i-merge ang parehong ticket sa kanyang sarili.";
    } else {
        // Fetch tickets
        $stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ?");
        $stmt->execute([$primaryId]);
        $primary = $stmt->fetch();

        $stmt->execute([$secondaryId]);
        $secondary = $stmt->fetch();

        if (!$primary || !$secondary) {
            $error = "Hindi nahanap ang isa sa mga napiling ticket.";
        } else {
            try {
                $pdo->beginTransaction();

                // Add upvotes from secondary to primary
                $newUpvotes = $primary['upvotes_count'] + $secondary['upvotes_count'];
                $uStmt = $pdo->prepare("UPDATE reports SET upvotes_count = ? WHERE id = ?");
                $uStmt->execute([$newUpvotes, $primaryId]);

                // Append secondary photo and details to primary resolution/remarks
                $mergedNote = $primary['resolution_remarks'] . "\n[MERGED TICKET #" . $secondary['ticket_no'] . "]: " . $secondary['description'] . " (" . $mergeReason . ")";
                $nStmt = $pdo->prepare("UPDATE reports SET resolution_remarks = ? WHERE id = ?");
                $nStmt->execute([trim($mergedNote), $primaryId]);

                // Update secondary ticket to False Alarm / Duplicate Closed
                $cStmt = $pdo->prepare("UPDATE reports SET status = 'False Alarm', resolution_remarks = ? WHERE id = ?");
                $cStmt->execute(["Merged into Primary Ticket #" . $primary['ticket_no'] . ". " . $mergeReason, $secondaryId]);

                $pdo->commit();
                $msg = "Matagumpay na na-merge ang Ticket #{$secondary['ticket_no']} patungo sa Primary Ticket #{$primary['ticket_no']}. Naidagdag ang upvotes nito!";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Nagkaroon ng problema sa pag-merge: " . $e->getMessage();
            }
        }
    }
}

// Fetch open reports for selection
$primaryIdPreselect = (int)($_GET['primary_id'] ?? 0);
$openReports = $pdo->query("
    SELECT r.*, b.name as barangay_name, u.full_name as reporter_name 
    FROM reports r 
    JOIN barangays b ON r.barangay_id = b.id 
    JOIN users u ON r.user_id = u.id 
    WHERE r.status NOT IN ('Resolved', 'False Alarm')
    ORDER BY r.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#063151">
<title>Consolidate &amp; Merge Duplicate Incidents | e-Mabalacat</title>
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
        <span><?= $agencyName ?> Agency &bull; Incident De-duplication Engine</span>
      </div>
      <div class="app-topbar-actions">
        <a href="dashboard.php" class="btn btn-outline-light btn-xs">&larr; Back to Dashboard</a>
      </div>
    </header>

    <main class="app-content">
      
      <div class="bento-grid" style="max-width: 900px; margin: 0 auto;">
        
        <div class="bento-card span-12 hero-bento">
          <div class="bento-head">
            <span class="bento-title" style="color:#fff;">Incident De-Duplication Tool</span>
            <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;">Consolidation Engine</span>
          </div>
          <h2 style="font-size:1.35rem; color:#fff; margin:0 0 6px;">I-merge ang Duplicate Incident Tickets</h2>
          <p class="bento-sub" style="color:#cbd5e1; margin:0;">
            Pagsamahin ang maramihang ulat ng parehong sunog, aksidente, o kaguluhan upang maiwasan ang kalituhan sa field responders at maipon ang crowd confirmations.
          </p>
        </div>

        <?php if ($msg): ?>
          <div class="bento-card span-12" style="background: #ecfdf5; border-color: #10b981; color: #065f46; padding: 16px;">
            <strong>✓ Tagumpay:</strong> <?= htmlspecialchars($msg) ?>
          </div>
        <?php endif; ?>

        <?php if ($error): ?>
          <div class="bento-card span-12" style="background: #fef2f2; border-color: #ef4444; color: #991b1b; padding: 16px;">
            <strong>⚠️ Puna:</strong> <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <div class="bento-card span-12">
          <form method="POST" action="merge.php">
            
            <div class="field" style="margin-bottom: 1.25rem;">
              <label style="font-weight: 700; color:var(--navy);">1. Piliin ang Pangunahing Ticket (Primary Active Ticket) <span class="req">*</span></label>
              <p class="small muted" style="margin: 0 0 6px 0;">Ito ang mananatiling active ticket kung saan ididikit ang mga datos at upvotes.</p>
              <select name="primary_id" class="select" required style="width:100%;">
                <option value="">-- Piliin ang Primary Ticket --</option>
                <?php foreach ($openReports as $r): ?>
                  <option value="<?= $r['id'] ?>" <?= $r['id'] === $primaryIdPreselect ? 'selected' : '' ?>>
                    [<?= $r['ticket_no'] ?>] Brgy. <?= htmlspecialchars($r['barangay_name']) ?> &bull; <?= htmlspecialchars($r['category']) ?> (<?= $r['upvotes_count'] ?> votes) &bull; <?= htmlspecialchars($r['reporter_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="field" style="margin-bottom: 1.25rem;">
              <label style="font-weight: 700; color: #dc2626;">2. Piliin ang Duplicate Ticket na Isasama (Secondary / Duplicate Ticket) <span class="req">*</span></label>
              <p class="small muted" style="margin: 0 0 6px 0;">Ito ang mamarkahang "False Alarm / Merged" at ang upvotes nito ay ililipat sa primary.</p>
              <select name="secondary_id" class="select" required style="width:100%;">
                <option value="">-- Piliin ang Duplicate Ticket --</option>
                <?php foreach ($openReports as $r): ?>
                  <option value="<?= $r['id'] ?>">
                    [<?= $r['ticket_no'] ?>] Brgy. <?= htmlspecialchars($r['barangay_name']) ?> &bull; <?= htmlspecialchars($r['category']) ?> &bull; <?= htmlspecialchars($r['description']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="field" style="margin-bottom: 1.5rem;">
              <label style="font-weight:700;">Dahilan ng Pag-merge (Internal Audit Remarks)</label>
              <textarea name="merge_reason" class="input" rows="3" style="width:100%; height:auto;" placeholder="hal. Parehong insidente ng sunog sa Macapagal Village na inireport ng magkahiwalay na residente."></textarea>
            </div>

            <div class="flex-between flex-wrap gap-12 items-center pt-12" style="border-top: 1px solid var(--line);">
              <a href="dashboard.php" class="btn btn-outline btn-sm">&larr; Bumalik</a>
              <button type="submit" name="merge_action" class="btn btn-gold btn-md" onclick="return confirm('Sigurado ka bang nais mong pagsamahin ang dalawang ticket na ito? Ang upvotes ay iipunin sa primary.');">
                🔄 Kumpirmahin at I-merge ang mga Ticket
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
