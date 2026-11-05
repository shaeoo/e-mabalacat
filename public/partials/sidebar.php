<?php
// public/partials/sidebar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentUserRole = $_SESSION['role'] ?? 'citizen';
$currentUserName = $_SESSION['full_name'] ?? 'User';
$currentUserBrgy = $_SESSION['barangay_id'] ?? null;
$currentUserAgency = $_SESSION['agency_type'] ?? null;

// Determine relative path back to /public/ root
$currentScript = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$depth = substr_count(trim($currentScript, '/'), '/') - 1;
if (strpos($currentScript, '/public/') !== false) {
    $afterPublic = substr($currentScript, strpos($currentScript, '/public/') + 8);
    $depth = substr_count($afterPublic, '/');
}
$basePath = ($depth > 0) ? str_repeat('../', $depth) : '';
$currentFile = basename($currentScript);
?>
<aside class="app-sidebar" aria-label="Portal Navigation">
  
  <!-- BRAND LOCKUP -->
  <a class="sidebar-brand" href="<?= $basePath ?>index.php">
    <svg class="seal" viewBox="0 0 100 100" role="img" aria-label="Illustrative City Government of Mabalacat seal">
      <circle cx="50" cy="50" r="48" fill="#063151" stroke="#f5d10d" stroke-width="2.5"/>
      <circle cx="50" cy="50" r="41.5" fill="none" stroke="#f5d10d" stroke-width=".8" opacity=".65"/>
      <path d="M27 67c7-7 13-15 23-23 5 8 10 15 23 23Z" fill="#1766a1" stroke="#f5d10d" stroke-width=".9"/>
      <circle cx="50" cy="37" r="7.2" fill="#f5d10d"/>
      <path d="M30 68h40" stroke="#f5d10d" stroke-width="1.4"/>
      <text x="50" y="84.5" font-family="'Helvetica Neue', Helvetica, Arial, sans-serif" font-size="4.4" font-weight="700" letter-spacing="1.3" fill="#f5d10d" text-anchor="middle">PAMPANGA</text>
    </svg>
    <div class="sidebar-brand-text">
      <span class="sidebar-brand-name">e-<em>Mabalacat</em></span>
      <span class="sidebar-brand-sub">CommunityPulse v2.0</span>
    </div>
  </a>

  <!-- ACTIVE USER IDENTITY -->
  <div class="sidebar-user">
    <div class="sidebar-user-avatar">
      <?= strtoupper(substr($currentUserName, 0, 1)) ?>
    </div>
    <div class="sidebar-user-info">
      <div class="sidebar-user-name" title="<?= htmlspecialchars($currentUserName) ?>"><?= htmlspecialchars($currentUserName) ?></div>
      <div class="sidebar-user-role"><?= str_replace('_', ' ', $currentUserRole) ?> <?= $currentUserAgency ? '&bull; ' . strtoupper($currentUserAgency) : '' ?></div>
    </div>
  </div>

  <!-- NAVIGATION LINKS ACCORDING TO ROLE -->
  <nav class="sidebar-nav">
    
    <?php if ($currentUserRole === 'super_admin'): ?>
      <div class="sidebar-section-title">Command Center</div>
      <a class="sidebar-link <?= $currentFile === 'dashboard.php' ? 'is-active' : '' ?>" href="<?= $basePath ?>superadmin/dashboard.php">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
        <span>City Command Desk</span>
      </a>
      <a class="sidebar-link <?= $currentFile === 'users.php' ? 'is-active' : '' ?>" href="<?= $basePath ?>superadmin/users.php">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>User Management (CRUD)</span>
      </a>
      <a class="sidebar-link <?= $currentFile === 'admins.php' ? 'is-active' : '' ?>" href="<?= $basePath ?>superadmin/admins.php">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 5v14M5 12h14"/></svg>
        <span>Add Admin / Staff</span>
      </a>

    <?php elseif ($currentUserRole === 'agency_admin'): ?>
      <div class="sidebar-section-title"><?= strtoupper($currentUserAgency ?? 'AGENCY') ?> Dispatch</div>
      <a class="sidebar-link <?= $currentFile === 'dashboard.php' ? 'is-active' : '' ?>" href="<?= $basePath ?>agency/dashboard.php">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
        <span>Dispatch Console</span>
      </a>
      <a class="sidebar-link <?= $currentFile === 'users.php' ? 'is-active' : '' ?>" href="<?= $basePath ?>agency/users.php">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>Agency Personnel (CRUD)</span>
      </a>
      <a class="sidebar-link <?= $currentFile === 'merge.php' ? 'is-active' : '' ?>" href="<?= $basePath ?>agency/merge.php">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6 21V9a9 9 0 0 0 9 9"/></svg>
        <span>Merge Duplicate Tickets</span>
      </a>

    <?php elseif ($currentUserRole === 'mini_admin'): ?>
      <div class="sidebar-section-title">Barangay Triage</div>
      <a class="sidebar-link <?= $currentFile === 'dashboard.php' ? 'is-active' : '' ?>" href="<?= $basePath ?>barangay/dashboard.php">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
        <span>Barangay Desk</span>
      </a>
      <a class="sidebar-link <?= $currentFile === 'users.php' ? 'is-active' : '' ?>" href="<?= $basePath ?>barangay/users.php">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>Tanods &amp; Citizens (CRUD)</span>
      </a>

    <?php elseif ($currentUserRole === 'responder'): ?>
      <div class="sidebar-section-title">Field Responder</div>
      <a class="sidebar-link <?= $currentFile === 'dashboard.php' ? 'is-active' : '' ?>" href="<?= $basePath ?>responder/dashboard.php">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m21 3-7.3 18-3.4-7.3L3 10.3 21 3Z"/></svg>
        <span>Active &amp; Broadcast Pool</span>
      </a>

    <?php else: /* citizen */ ?>
      <div class="sidebar-section-title">Citizen Services</div>
      <a class="sidebar-link <?= $currentFile === 'dashboard.php' ? 'is-active' : '' ?>" href="<?= $basePath ?>citizen/dashboard.php">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
        <span>Community Feed &amp; Reports</span>
      </a>
      <a class="sidebar-link <?= $currentFile === 'report.php' ? 'is-active' : '' ?>" href="<?= $basePath ?>citizen/report.php">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 5v14M5 12h14"/></svg>
        <span>Mag-ulat ng Insidente</span>
      </a>
    <?php endif; ?>

    <div class="sidebar-section-title">Public Telemetry</div>
    <a class="sidebar-link" href="<?= $basePath ?>index.php" target="_blank">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path d="m10 15 5-3-5-3v6Z"/></svg>
      <span>Live Public Map &nearr;</span>
    </a>

  </nav>

  <!-- SIDEBAR FOOTER -->
  <div class="sidebar-footer">
    <a class="sidebar-logout" href="<?= $basePath ?>logout.php">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      <span>Mag-sign out</span>
    </a>
  </div>

</aside>

