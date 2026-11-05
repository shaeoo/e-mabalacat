<?php
// public/citizen/report.php
session_start();
require_once '../../src/config/database.php';
require_once '../../src/includes/auth-guard.php';
requireRole('citizen');

$userId = (int)$_SESSION['user_id'];
$uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$uStmt->execute([$userId]);
$user = $uStmt->fetch(PDO::FETCH_ASSOC);

if ($user['kyc_status'] !== 'verified') {
    die("Pinaaalalahanan: Kailangang ma-verify muna ng iyong Barangay ang iyong Valid ID bago ka makapag-file ng incident report.");
}

$barangays = $pdo->query("SELECT * FROM barangays ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#063151">
<title>Mag-ulat ng Insidente o Hazard &bull; e-Mabalacat</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/vendor/leaflet/leaflet.css">
<link rel="stylesheet" href="../assets/css/site.css">
<style>
#report-map {
  height: 320px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--line);
  margin-top: 0.5rem;
}
.portal-card-label {
  display: block;
  border: 2px solid var(--line);
  border-radius: var(--radius-sm);
  padding: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
  background: #fff;
}
.portal-card-label:hover {
  border-color: var(--navy);
  background: #f8fafc;
}
</style>
</head>
<body>

<div class="app-shell">
  <?php include '../partials/sidebar.php'; ?>

  <div class="app-main">
    
    <header class="app-topbar">
      <div class="app-topbar-title">
        <span>📢</span>
        <span>Mag-ulat ng Insidente o Hazard &bull; Smart Civic Routing</span>
      </div>
      <div class="app-topbar-actions">
        <a href="dashboard.php" class="btn btn-outline-light btn-xs">&larr; Back to Dashboard</a>
      </div>
    </header>

    <main class="app-content">
      
      <div class="bento-grid" style="max-width: 900px; margin: 0 auto;">
        
        <div class="bento-card span-12 hero-bento" style="background:linear-gradient(135deg, #059669 0%, #063151 100%);">
          <div class="bento-head">
            <span class="bento-title" style="color:#fff;">Smart Civic Intake</span>
            <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;">50m Privacy Fuzzing</span>
          </div>
          <h2 style="font-size:1.35rem; color:#fff; margin:0 0 6px;">Mag-ulat ng Insidente o Hazard</h2>
          <p class="bento-sub" style="color:#cbd5e1; margin:0;">
            Piliin ang nararapat na ahensya o barangay. Ang mga emergency (Sunog, Krimen, Medikal) ay direktang aaksyunan ng mga kinauukulang tanggapan.
          </p>
        </div>

        <div class="bento-card span-12">
          <form action="../../src/handlers/handle-report.php" method="POST" enctype="multipart/form-data">
            
            <!-- STEP 1: DESTINATION PORTAL -->
            <div class="field" style="margin-bottom: 1.5rem;">
              <label style="font-weight: 700; font-size: 1rem; color:var(--navy); margin-bottom:8px; display:block;">1. Piliin ang Kaukulang Destinasyon (Smart Routing) <span class="req">*</span></label>
              
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
                
                <label class="portal-card-label">
                  <input type="radio" name="target_portal" value="barangay" checked onchange="updateCategoryOptions('barangay')">
                  <div class="portal-card-content" style="margin-top: 0.35rem;">
                    <strong style="color: #1d4ed8; font-size: 0.95rem;">🏛️ Barangay</strong>
                    <p class="small muted" style="margin: 0.25rem 0 0 0;">Basura, ingay, sirang ilaw, baradong kanal.</p>
                  </div>
                </label>

                <label class="portal-card-label">
                  <input type="radio" name="target_portal" value="bfp" onchange="updateCategoryOptions('bfp')">
                  <div class="portal-card-content" style="margin-top: 0.35rem;">
                    <strong style="color: #dc2626; font-size: 0.95rem;">🚒 BFP (Sunog)</strong>
                    <p class="small muted" style="margin: 0.25rem 0 0 0;">Sunog sa bahay o sasakyan, chemical spill.</p>
                  </div>
                </label>

                <label class="portal-card-label">
                  <input type="radio" name="target_portal" value="pnp" onchange="updateCategoryOptions('pnp')">
                  <div class="portal-card-content" style="margin-top: 0.35rem;">
                    <strong style="color: #1e1b4b; font-size: 0.95rem;">👮 PNP (Pulisya)</strong>
                    <p class="small muted" style="margin: 0.25rem 0 0 0;">Nakawan, karahasan, kaguluhan, droga.</p>
                  </div>
                </label>

                <label class="portal-card-label">
                  <input type="radio" name="target_portal" value="medical" onchange="updateCategoryOptions('medical')">
                  <div class="portal-card-content" style="margin-top: 0.35rem;">
                    <strong style="color: #0d9488; font-size: 0.95rem;">🚑 Medical / CDRRMO</strong>
                    <p class="small muted" style="margin: 0.25rem 0 0 0;">Vehicular accidents, pasyente, ambulansya.</p>
                  </div>
                </label>

              </div>
            </div>

            <!-- STEP 2: CATEGORY & BARANGAY -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
              <div class="field">
                <label style="font-weight:700;">Barangay ng Insidente <span class="req">*</span></label>
                <select name="barangay_id" class="select" required style="width:100%;">
                  <option value="">-- Piliin ang Barangay --</option>
                  <?php foreach ($barangays as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= $b['id'] == $user['barangay_id'] ? 'selected' : '' ?>>
                      Brgy. <?= htmlspecialchars($b['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="field">
                <label style="font-weight:700;">Uri o Kategorya ng Hazard <span class="req">*</span></label>
                <select name="category" id="category_select" class="select" required style="width:100%;">
                  <option value="Baradong Drainage / Kanal">Baradong Drainage / Kanal</option>
                  <option value="Tambak na Basura">Tambak na Basura</option>
                  <option value="Pundidong Streetlight">Pundidong Streetlight</option>
                  <option value="Lubak sa Kalsada">Lubak sa Kalsada</option>
                  <option value="Maingay na Kapitbahay">Maingay na Kapitbahay</option>
                </select>
              </div>
            </div>

            <!-- STEP 3: DESCRIPTION -->
            <div class="field" style="margin-bottom: 1.5rem;">
              <label style="font-weight:700;">Deskripsyon ng Pangyayari o Hazard <span class="req">*</span></label>
              <textarea name="description" class="input" rows="4" style="width:100%; height:auto;" required placeholder="Ilarawan nang malinaw ang detalye ng insidente, palatandaan (landmarks), at agarang tulong na kailangan..."></textarea>
            </div>

            <!-- STEP 4: PHOTO EVIDENCE -->
            <div class="field" style="margin-bottom: 1.5rem;">
              <label style="font-weight:700;">Litrato o Katibayan (Required Evidence Photo) <span class="req">*</span></label>
              <input type="file" name="evidence" accept="image/*" class="input" required style="width:100%;">
              <span class="small muted">Maaaring kumuha ng litrato gamit ang cellphone camera o mag-upload ng kuha.</span>
            </div>

            <!-- STEP 5: MAP PIN PICKER & PRIVACY FUZZING -->
            <div class="field" style="margin-bottom: 2rem;">
              <label style="font-weight: 700; color:var(--navy);">
                I-pin ang Lokasyon sa Mapa (Mabalacat City) <span class="req">*</span>
              </label>
              <p class="small muted" style="margin: 0 0 6px 0;">
                I-drag ang marker o mag-click sa mapa upang itakda ang kinalalagyan ng insidente.
              </p>
              <div id="report-map"></div>
              
              <input type="hidden" name="latitude" id="lat" value="15.2234">
              <input type="hidden" name="longitude" id="lng" value="120.5771">

              <div style="background: #f8fafc; border: 1px solid var(--line); border-radius: 8px; padding: 0.75rem 1rem; margin-top: 0.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; font-size: 0.8rem;">
                <div>
                  <strong>GPS Koordinado:</strong> <span id="coords-display" style="font-family: var(--font-mono);">15.223400, 120.577100</span>
                </div>
                <div style="color: #059669; font-weight: 600;">
                  🛡️ Privacy Fuzzing: Awtomatikong i-o-offset ng ~50m para sa proteksyon ng ulat.
                </div>
              </div>
            </div>

            <!-- SUBMIT BUTTONS -->
            <div class="flex-between flex-wrap gap-12 items-center pt-12" style="border-top:1px solid var(--line);">
              <a href="dashboard.php" class="btn btn-outline btn-sm">&larr; Kanselahin</a>
              <button type="submit" class="btn btn-gold btn-md" style="font-weight: 800;">
                🚀 Isumite ang Ulat
              </button>
            </div>

          </form>
        </div>

      </div>

    </main>
  </div>
</div>

<script src="../assets/vendor/leaflet/leaflet.js"></script>
<script>
var defaultLat = 15.2234;
var defaultLng = 120.5771;

var map = L.map('report-map').setView([defaultLat, defaultLng], 14);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap'
}).addTo(map);

var marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

function updateLocation(lat, lng) {
    document.getElementById('lat').value = lat.toFixed(6);
    document.getElementById('lng').value = lng.toFixed(6);
    document.getElementById('coords-display').innerText = lat.toFixed(6) + ', ' + lng.toFixed(6);
}

marker.on('dragend', function(e) {
    var pos = marker.getLatLng();
    updateLocation(pos.lat, pos.lng);
});

map.on('click', function(e) {
    marker.setLatLng(e.latlng);
    updateLocation(e.latlng.lat, e.latlng.lng);
});

// Try geolocation
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(pos) {
        var userLat = pos.coords.latitude;
        var userLng = pos.coords.longitude;
        map.setView([userLat, userLng], 16);
        marker.setLatLng([userLat, userLng]);
        updateLocation(userLat, userLng);
    }, function() {}, { enableHighAccuracy: true, timeout: 5000 });
}

var categoryOptions = {
    'barangay': [
        'Baradong Drainage / Kanal',
        'Tambak na Basura',
        'Pundidong Streetlight',
        'Lubak sa Kalsada',
        'Maingay na Kapitbahay'
    ],
    'bfp': [
        'Residential Structure Fire (Sunog)',
        'Commercial Building Fire',
        'Vehicular Fire',
        'Grass / Forest Fire',
        'Chemical / Gas Leak'
    ],
    'pnp': [
        'Armed Robbery / Holdap',
        'Physical Injury / Kaguluhan',
        'Theft / Nakawan',
        'Illegal Drugs Activity',
        'Public Disturbance / Harassment'
    ],
    'medical': [
        'Vehicular Collision with Injuries',
        'Pedestrian Hit and Run',
        'Severe Trauma / Cardiac Arrest',
        'Maternal / Labor Emergency',
        'Ambulance Transport Needed'
    ]
};

function updateCategoryOptions(portal) {
    var sel = document.getElementById('category_select');
    sel.innerHTML = '';
    var list = categoryOptions[portal] || categoryOptions['barangay'];
    list.forEach(function(item) {
        var opt = document.createElement('option');
        opt.value = item;
        opt.innerText = item;
        sel.appendChild(opt);
    });
}
</script>
</body>
</html>
