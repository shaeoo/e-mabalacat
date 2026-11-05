<?php
// public/partials/footer.php
$currentScript = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$depth = substr_count(trim($currentScript, '/'), '/') - 1;
if (strpos($currentScript, '/public/') !== false) {
    $afterPublic = substr($currentScript, strpos($currentScript, '/public/') + 8);
    $depth = substr_count($afterPublic, '/');
}
$basePath = ($depth > 0) ? str_repeat('../', $depth) : '';
?>
<footer class="site-footer">
  <div class="wrap footer-inner">
    <div class="footer-top">
      <div class="footer-brand-col">
        <div class="footer-brand-lockup">
          <svg class="seal" viewBox="0 0 100 100" role="img" aria-label="City Government of Mabalacat seal">
            <circle cx="50" cy="50" r="48" fill="#063151" stroke="#f5d10d" stroke-width="2.5"/>
            <path d="M27 67c7-7 13-15 23-23 5 8 10 15 23 23Z" fill="#1766a1" stroke="#f5d10d" stroke-width=".9"/>
            <circle cx="50" cy="37" r="7.2" fill="#f5d10d"/>
            <text x="50" y="84.5" font-family="sans-serif" font-size="4.4" font-weight="700" letter-spacing="1.3" fill="#f5d10d" text-anchor="middle">PAMPANGA</text>
          </svg>
          <div>
            <div class="footer-brand-title">City Government of Mabalacat</div>
            <div class="footer-brand-sub">e-Sumbong / CommunityPulse (v2.0) &bull; CDRRMO Incident Command</div>
          </div>
        </div>
        <p class="footer-purpose">Official citizen incident triage, multi-agency emergency dispatch, and barangay response portal. Designed for real-time accountability and civic safety across all 27 Barangays.</p>
      </div>

      <div class="footer-col">
        <h4>Emergency Numbers</h4>
        <ul class="footer-list">
          <li><strong>CDRRMO:</strong> (045) 893-0101</li>
          <li><strong>BFP Mabalacat:</strong> (045) 893-0000</li>
          <li><strong>PNP Mabalacat:</strong> 0998-598-7965</li>
          <li><strong>National Emergency:</strong> 911</li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>System Scopes</h4>
        <ul class="footer-list">
          <li><a href="<?= $basePath ?>index.php">Public Live Map</a></li>
          <li><a href="<?= $basePath ?>citizen/report.php">Incident Reporting</a></li>
          <li><a href="<?= $basePath ?>login.php">Portal Unified Login</a></li>
          <li><a href="<?= $basePath ?>register.php">Citizen KYC Registration</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-btm">
      <div class="footer-btm-wrap">
        <div>&copy; <?= date('Y') ?> City Government of Mabalacat, Pampanga. Republic Act 10173 (Data Privacy Act) Compliant.</div>
        <div class="footer-btm-links">
          <span>Coordinate Fuzzing Protected (50m Offset)</span>
          &bull;
          <span>Grab-Style Atomic Dispatch</span>
        </div>
      </div>
    </div>
  </div>
</footer>

