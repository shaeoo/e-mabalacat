<?php
// public/partials/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentUserRole = $_SESSION['role'] ?? null;
$currentUserName = $_SESSION['full_name'] ?? null;
$currentUserBrgy = $_SESSION['barangay_id'] ?? null;
$currentUserAgency = $_SESSION['agency_type'] ?? null;

// Determine relative path back to /public/ root
$currentScript = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$depth = substr_count(trim($currentScript, '/'), '/') - 1; // if in /public/ it's 0, in /public/superadmin/ it's 1
if (strpos($currentScript, '/public/') !== false) {
    $afterPublic = substr($currentScript, strpos($currentScript, '/public/') + 8);
    $depth = substr_count($afterPublic, '/');
}
$basePath = ($depth > 0) ? str_repeat('../', $depth) : '';
?>
<header class="site-header">
  <div class="header-inner">
    <a class="brand" href="<?= $basePath ?>index.php" aria-label="e-Mabalacat home">
      <svg class="seal" viewBox="0 0 100 100" role="img" aria-label="Illustrative City Government of Mabalacat seal">
        <title>City Government of Mabalacat &mdash; seal</title>
        <circle cx="50" cy="50" r="48" fill="#063151" stroke="#f5d10d" stroke-width="2.5"/>
        <circle cx="50" cy="50" r="41.5" fill="none" stroke="#f5d10d" stroke-width=".8" opacity=".65"/>
        <path id="seal-arc-hdr" d="M 50,50 m -34,0 a 34,34 0 1,1 68,0" fill="none"/>
        <text font-family="'Helvetica Neue', Helvetica, Arial, sans-serif" font-size="4.4" font-weight="700" letter-spacing="1.15" fill="#f5d10d">
          <textPath href="#seal-arc-hdr" startOffset="50%" text-anchor="middle">CITY GOVERNMENT OF MABALACAT</textPath>
        </text>
        <path d="M27 67c7-7 13-15 23-23 5 8 10 15 23 23Z" fill="#1766a1" stroke="#f5d10d" stroke-width=".9" stroke-linejoin="round"/>
        <circle cx="50" cy="37" r="7.2" fill="#f5d10d"/>
        <g stroke="#f5d10d" stroke-width="1.15" stroke-linecap="round">
          <path d="M50 24.5v-3M50 52.5v-8M37.5 37h-3M65.5 37h-3M41.2 28.2l-2.1-2.1M58.8 28.2l2.1-2.1M41.2 45.8l-2.1 2.1M58.8 45.8l2.1 2.1"/>
        </g>
        <path d="M30 68h40" stroke="#f5d10d" stroke-width="1.4" stroke-linecap="round"/>
        <text x="50" y="84.5" font-family="'Helvetica Neue', Helvetica, Arial, sans-serif" font-size="4.4" font-weight="700" letter-spacing="1.3" fill="#f5d10d" text-anchor="middle">PAMPANGA</text>
      </svg>
      <span>
        <span class="brand-name">e-<em>Mabalacat</em></span>
        <span class="brand-sub">CommunityPulse v2.0 &bull; Incident Dispatch</span>
      </span>
    </a>

    <nav class="main-nav" aria-label="Main navigation">
      <a href="<?= $basePath ?>index.php" <?= basename($currentScript) === 'index.php' ? 'aria-current="page"' : '' ?>>Live Incident Map</a>
      
      <?php if ($currentUserRole === 'citizen'): ?>
        <a href="<?= $basePath ?>citizen/dashboard.php">Citizen Feed</a>
        <a href="<?= $basePath ?>citizen/report.php">Mag-ulat ng Insidente</a>
      <?php elseif ($currentUserRole === 'mini_admin'): ?>
        <a href="<?= $basePath ?>barangay/dashboard.php">Barangay Triage</a>
      <?php elseif ($currentUserRole === 'agency_admin'): ?>
        <a href="<?= $basePath ?>agency/dashboard.php"><?= strtoupper($currentUserAgency ?? 'AGENCY') ?> Dispatch</a>
        <a href="<?= $basePath ?>agency/merge.php">Merge Reports</a>
      <?php elseif ($currentUserRole === 'responder'): ?>
        <a href="<?= $basePath ?>responder/dashboard.php">Field Responder</a>
      <?php elseif ($currentUserRole === 'super_admin'): ?>
        <a href="<?= $basePath ?>superadmin/dashboard.php">CDRRMO Command</a>
        <a href="<?= $basePath ?>superadmin/users.php">User Management</a>
        <a href="<?= $basePath ?>superadmin/admins.php">Add Staff/Admin</a>
      <?php endif; ?>
    </nav>

    <div class="header-actions">
      <?php if ($currentUserRole): ?>
        <span class="badge badge-gold" style="font-size:0.75rem; text-transform:uppercase; font-weight:700;">
          <?= htmlspecialchars(str_replace('_', ' ', $currentUserRole)) ?>
        </span>
        <a class="header-signin" href="<?= $basePath ?>logout.php" style="color:#ef4444; font-weight:600;">Sign out</a>
      <?php else: ?>
        <a class="header-signin" href="<?= $basePath ?>login.php">Sign in</a>
        <a class="btn btn-gold btn-sm header-cta" href="<?= $basePath ?>register.php">
          Register with ID
        </a>
      <?php endif; ?>
    </div>
  </div>
</header>

