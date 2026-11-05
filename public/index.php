<?php
// public/index.php
session_start();
require_once '../src/config/database.php';

// Fetch active reports with privacy-fuzzed coordinates for map
$stmt = $pdo->query("
    SELECT r.ticket_no, r.category, r.target_portal, r.fuzzed_lat, r.fuzzed_lng, r.status, r.upvotes_count,
           b.name as barangay_name, r.created_at
    FROM reports r
    JOIN barangays b ON r.barangay_id = b.id
    WHERE r.status NOT IN ('Resolved', 'False Alarm')
    ORDER BY r.upvotes_count DESC, r.created_at DESC 
    LIMIT 100
");
$mapReports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Metrics
$totalOpen = count($mapReports);
$totalUpvotes = 0;
foreach ($mapReports as $mr) {
    $totalUpvotes += (int)$mr['upvotes_count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#063151">
<title>e-Mabalacat &bull; Live Civic Incident Map & Command Dispatch</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/vendor/leaflet/leaflet.css">
<link rel="stylesheet" href="assets/css/site.css">
<style>
#public-incident-map {
  height: 540px;
  width: 100%;
  border-radius: var(--radius-md);
  border: 1px solid var(--line);
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}
.map-badge-counter {
  background: var(--navy);
  color: #fff;
  padding: 0.35rem 0.75rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 700;
}
</style>
</head>
<body data-route="/">
<a class="skip-link" href="#main-content">Skip to main content</a>

<?php include 'partials/header.php'; ?>

<main id="main-content">
  
  <!-- HERO INTRO SECTION -->
  <section class="hero" style="padding: 3rem 0 2rem 0; background: linear-gradient(180deg, #063151 0%, #0a4674 100%); color: #ffffff;">
    <div class="wrap">
      <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1.5rem;">
        <div style="max-width: 650px;">
          <span class="eyebrow on-dark" style="color: var(--gold);">CITY GOVERNMENT OF MABALACAT &bull; PAMPANGA</span>
          <h1 class="hero-title" style="color: #ffffff; font-size: 2.5rem; line-height: 1.15; margin: 0.5rem 0;">
            See it. Report it.<br><em style="color: var(--gold);">See it dispatched.</em>
          </h1>
          <p class="hero-lede" style="color: #cbd5e1; font-size: 1rem; margin: 0.75rem 0 0 0;">
            Mabalacat's unified citizen portal connecting all 27 Barangays to CDRRMO, BFP Fire, PNP Police, and Medical Dispatch. Fully encrypted with 50-meter coordinate privacy fuzzing.
          </p>
        </div>

        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
          <a class="btn btn-gold btn-md" href="citizen/report.php">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Mag-report ng Insidente
          </a>
          <a class="btn btn-outline-light btn-md" href="register.php" style="color: #ffffff; border-color: rgba(255,255,255,0.4);">
            Magrehistro (WebRTC KYC)
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- MAP & REALTIME FEED CONTAINER -->
  <div class="wrap" style="padding-top: 2rem; padding-bottom: 4rem;">
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;">
      
      <!-- MAP COLUMN -->
      <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
          <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--navy); margin: 0;">
              Live Interactive Hazard Map
            </h2>
            <p class="text-muted" style="font-size: 0.8rem; margin: 0.15rem 0 0 0;">
              Lahat ng koordinado ay pinoprotektahan ng ~50m random privacy offset alinsunod sa RA 10173.
            </p>
          </div>
          <span class="map-badge-counter">
            <?= $totalOpen ?> Aktibong Alerto sa Mabalacat
          </span>
        </div>

        <div id="public-incident-map"></div>

        <div style="margin-top: 1rem; display: flex; gap: 1rem; flex-wrap: wrap; font-size: 0.8rem; color: var(--muted); align-items: center;">
          <span><strong>Legend:</strong></span>
          <span style="display: inline-flex; align-items: center; gap: 0.35rem;">
            <span style="width:10px; height:10px; background:#dc2626; border-radius:50%; display:inline-block;"></span> BFP Fire
          </span>
          <span style="display: inline-flex; align-items: center; gap: 0.35rem;">
            <span style="width:10px; height:10px; background:#1e1b4b; border-radius:50%; display:inline-block;"></span> PNP Police
          </span>
          <span style="display: inline-flex; align-items: center; gap: 0.35rem;">
            <span style="width:10px; height:10px; background:#0d9488; border-radius:50%; display:inline-block;"></span> Medical / EMS
          </span>
          <span style="display: inline-flex; align-items: center; gap: 0.35rem;">
            <span style="width:10px; height:10px; background:#1d4ed8; border-radius:50%; display:inline-block;"></span> Barangay Community
          </span>
        </div>
      </div>

      <!-- SIDEBAR LIVE ALERTS LIST -->
      <div>
        <div class="card" style="padding: 1.25rem;">
          <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--navy); margin: 0 0 0.5rem 0; border-bottom: 1px solid var(--line); padding-bottom: 0.5rem;">
            🔥 Mga Pinakabagong Insidente
          </h3>
          <p class="text-muted" style="font-size: 0.775rem; margin: 0 0 1rem 0;">
            Real-time feed mula sa mga residente ng Mabalacat.
          </p>

          <?php if (empty($mapReports)): ?>
            <p style="text-align: center; color: var(--muted); padding: 1.5rem 0; font-size: 0.85rem;">
              Walang aktibong insidente sa mapa sa kasalukuyan.
            </p>
          <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 0.85rem; max-height: 480px; overflow-y: auto;">
              <?php foreach (array_slice($mapReports, 0, 10) as $mr): ?>
                <div style="border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
                  <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span class="badge badge-gold" style="font-size: 0.65rem; font-weight: 700;">
                      <?= htmlspecialchars($mr['category']) ?>
                    </span>
                    <span style="font-size: 0.7rem; color: var(--muted); font-family: var(--font-mono);">
                      ▲ <?= $mr['upvotes_count'] ?> votes
                    </span>
                  </div>
                  <div style="font-weight: 700; font-size: 0.85rem; color: var(--navy); margin-top: 0.25rem;">
                    Brgy. <?= htmlspecialchars($mr['barangay_name']) ?>
                  </div>
                  <div style="font-size: 0.75rem; color: var(--muted);">
                    Status: <b style="color: #d97706;"><?= $mr['status'] ?></b> &bull; <?= date('h:i A', strtotime($mr['created_at'])) ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div style="margin-top: 1.25rem; border-top: 1px solid var(--line); padding-top: 1rem;">
            <a href="login.php" class="btn btn-outline-light btn-sm" style="width: 100%; text-align: center;">
              Mag-sign in upang Mag-confirm (+1)
            </a>
          </div>
        </div>

        <div class="card" style="margin-top: 1.5rem; background: #f8fafc;">
          <h4 style="font-size: 0.9rem; font-weight: 700; color: var(--navy); margin: 0 0 0.5rem 0;">
            📞 24/7 Hotline Quick Dial
          </h4>
          <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.8rem; line-height: 1.8;">
            <li><strong>CDRRMO Rescue:</strong> (045) 893-0101</li>
            <li><strong>BFP Fire Dept:</strong> (045) 893-0000</li>
            <li><strong>PNP Police Station:</strong> 0998-598-7965</li>
            <li><strong>Emergency Hotline:</strong> 911</li>
          </ul>
        </div>
      </div>

    </div>

  </div>
</main>

<?php include 'partials/footer.php'; ?>

<script src="assets/vendor/leaflet/leaflet.js"></script>
<script>
var map = L.map('public-incident-map').setView([15.2234, 120.5771], 13);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

var incidents = <?= json_encode($mapReports) ?>;

incidents.forEach(function(inc) {
    if (inc.fuzzed_lat && inc.fuzzed_lng) {
        var marker = L.circleMarker([parseFloat(inc.fuzzed_lat), parseFloat(inc.fuzzed_lng)], {
            radius: 8,
            fillColor: inc.target_portal === 'bfp' ? '#dc2626' : (inc.target_portal === 'pnp' ? '#1e1b4b' : (inc.target_portal === 'medical' ? '#0d9488' : '#1d4ed8')),
            color: '#ffffff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9
        }).addTo(map);

        var popupContent = '<div style="font-family: sans-serif; font-size: 13px; line-height: 1.4;">' +
            '<strong style="color: #063151;">' + inc.category + '</strong><br>' +
            '<span style="color: #64748b;">Brgy. ' + inc.barangay_name + '</span><br>' +
            '<span>Status: <b>' + inc.status + '</b></span><br>' +
            '<span style="color: #b45309; font-weight: 700;">▲ ' + inc.upvotes_count + ' Confirmations</span><br>' +
            '<span style="font-size: 10px; color: #94a3b8;">~50m Privacy Fuzzed</span>' +
            '</div>';

        marker.bindPopup(popupContent);
    }
});
</script>
</body>
</html>
