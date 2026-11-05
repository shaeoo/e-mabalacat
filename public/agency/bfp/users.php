<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#063151">
<title>Bureau of Fire Protection Responders — e-Mabalacat</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../assets/vendor/leaflet/leaflet.css">
<link rel="stylesheet" href="../../assets/css/site.css">
</head>
<body data-role="bfp" data-route="/agency-bfp">

<div class="app-shell">
  
  <!-- PERSISTENT SIDEBAR -->
  <aside class="app-sidebar" aria-label="Portal Navigation">
    <a class="sidebar-brand" href="../../public/index.html">
      <svg class="seal" viewBox="0 0 100 100" role="img" aria-label="Illustrative City Government of Mabalacat seal">
        <circle cx="50" cy="50" r="48" fill="#063151" stroke="#f5d10d" stroke-width="2.5"/>
        <circle cx="50" cy="50" r="41.5" fill="none" stroke="#f5d10d" stroke-width=".8" opacity=".65"/>
        <path id="seal-arc" d="M 50,50 m -34,0 a 34,34 0 1,1 68,0" fill="none"/>
        <text font-family="'Helvetica Neue', Helvetica, Arial, sans-serif" font-size="4.4" font-weight="700" letter-spacing="1.15" fill="#f5d10d">
          <textPath href="#seal-arc" startOffset="50%" text-anchor="middle">CITY GOVERNMENT OF MABALACAT</textPath>
        </text>
        <path d="M27 67c7-7 13-15 23-23 5 8 10 15 23 23Z" fill="#1766a1" stroke="#f5d10d" stroke-width=".9" stroke-linejoin="round"/>
        <circle cx="50" cy="37" r="7.2" fill="#f5d10d"/>
        <g stroke="#f5d10d" stroke-width="1.15" stroke-linecap="round">
          <path d="M50 24.5v-3M50 52.5v-8M37.5 37h-3M65.5 37h-3M41.2 28.2l-2.1-2.1M58.8 28.2l2.1-2.1M41.2 45.8l-2.1 2.1M58.8 45.8l2.1 2.1"/>
        </g>
        <path d="M30 68h40" stroke="#f5d10d" stroke-width="1.4" stroke-linecap="round"/>
        <text x="50" y="84.5" font-family="'Helvetica Neue', Helvetica, Arial, sans-serif" font-size="4.4" font-weight="700" letter-spacing="1.3" fill="#f5d10d" text-anchor="middle">PAMPANGA</text>
      </svg>
      <div class="sidebar-brand-text">
        <span class="sidebar-brand-name">e-<em>Mabalacat</em></span>
        <span class="sidebar-brand-sub">CommunityPulse v2.0</span>
      </div>
    </a>

    <div class="sidebar-user">
      <div class="sidebar-user-avatar" style="background:#dc2626;color:#fff;">C</div>
      <div class="sidebar-user-info">
        <div class="sidebar-user-name">Capt. Renato Dizon</div>
        <div class="sidebar-user-role">BFP Fire Marshal &bull; Central Station</div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="sidebar-section-title">BFP Dispatch</div>
      <a class="sidebar-link " href="dashboard.html">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
        <span>BFP Queue</span>
      </a>
      <a class="sidebar-link is-active" href="users.html">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>Station Units (CRUD)</span>
      </a>
      <a class="sidebar-link " href="dispatch.html">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="9"/><polygon points="12 8 8 12 12 16 12 13 16 13 16 11 12 11 12 8"/></svg>
        <span>Deploy Units</span>
      </a>
      <a class="sidebar-link " href="responders.html">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>Fleet Roster</span>
      </a>
      <a class="sidebar-link " href="merge.html">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6 21V9a9 9 0 0 0 9 9"/><path d="M18 21V9a9 9 0 0 1-9 9"/></svg>
        <span>Merge Duplicates</span>
      </a>
      <a class="sidebar-link " href="reports.html">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        <span>Logsheets &amp; Analytics</span>
      </a>

      <div class="sidebar-section-title">Field Responder UI</div>
      <a class="sidebar-link " href="responder/queue.html">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m21 3-7.3 18-3.4-7.3L3 10.3 21 3Z"/></svg>
        <span>Responder Terminal</span>
      </a>
      <a class="sidebar-link " href="responder/active-mission.html">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span>Active Mission State</span>
      </a>
      <a class="sidebar-link " href="responder/history.html">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <span>Mission Proofs</span>
      </a>

      <div class="sidebar-section-title">City Telemetry</div>
      <a class="sidebar-link" href="../../public/map.html">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m9 18-6 3V6l6-3 6 3 6-3v15l-6 3-6-3Z"/><path d="M9 3v15M15 6v15"/></svg>
        <span>Live Map</span>
      </a>
      <a class="sidebar-link" href="../../public/hotlines.html">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M7 3H4a1 1 0 0 0-1 1c0 9.4 7.6 17 17 17a1 1 0 0 0 1-1v-3l-5-2-2.5 2.5a14.5 14.5 0 0 1-6-6L10 9 7 3Z"/></svg>
        <span>City Hotlines</span>
      </a>
      <a class="sidebar-link" href="../../index.html">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
        <span>Template Hub</span>
      </a>
    </nav>

    <div class="sidebar-footer">
      <a class="sidebar-logout" href="../../auth/login.html">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        <span>Sign out</span>
      </a>
    </div>
  </aside>

  <!-- MAIN APP CONTAINER -->
  <div class="app-main">
    <header class="app-topbar">
      <div class="app-topbar-title">
        <span>BFP Station Personnel</span>
      </div>
      <div class="app-topbar-actions">
        <span class="chip chip-green">&bull; Connected</span>
        <a class="btn btn-gold btn-sm" href="../../resident/report.html">+ File Incident</a>
      </div>
    </header>

    <main id="main-content" class="app-content"><div class="wrap" style="padding-top:20px;padding-bottom:48px;">
  
  <div class="flex-between flex-wrap gap-12 items-center mb-16">
    <div>
      <div class="breadcrumb">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>Bureau of Fire Protection &bull; Station Personnel Management</span>
      </div>
      <h1 class="page-title" style="margin:4px 0 0;">BFP Emergency Responders &amp; Field Units</h1>
      <p class="small muted" style="margin:2px 0 0;">Manage Engine / Ladder Companies, rescue crew personnel, and field dispatch assignments for Mabalacat City.</p>
    </div>
    <div class="flex gap-8">
      <!-- <a href="users-edit.html" class="btn btn-outline btn-sm">Edit Modal State &rarr;</a>
      <a href="users-empty.html" class="btn btn-outline btn-sm">Empty State &rarr;</a> -->
      <a href="users-edit.html" class="btn btn-gold btn-sm">+ Add Responder</a>
    </div>
  </div>

  <!-- BENTO METRICS -->
  <div class="bento-grid mb-20">
    <div class="bento-card span-4 hero-bento" style="background:linear-gradient(135deg, #dc2626 0%, #063151 100%);">
      <span class="bento-title" style="color:#fff;">Station Personnel</span>
      <div class="bento-kpi-value">28</div>
      <span class="bento-sub">Active Fire & Rescue Responders</span>
    </div>
    <div class="bento-card span-4">
      <span class="bento-title">Currently On-Duty</span>
      <div class="bento-kpi-value" style="color:#059669;">19</div>
      <span class="bento-sub">Ready for first-to-accept dispatch</span>
    </div>
    <div class="bento-card span-4">
      <span class="bento-title">Assigned Engines / Trucks</span>
      <div class="bento-kpi-value" style="color:#d97706;">6</div>
      <span class="bento-sub">Tanker 1, Pumper 2, Rescue 1-3</span>
    </div>
  </div>

  <!-- RESPONDER ROSTER TABLE (Picture 2 Exact Match) -->
  <div class="card" style="padding:0;overflow:hidden;">
    <div class="card-head flex-between flex-wrap gap-8 items-center">
      <div>
        <h2 style="font-size:1.1rem;margin:0;color:var(--navy);">BFP Station Roster (Field Responders)</h2>
        <p class="small muted" style="margin:2px 0 0;">Only personnel under your agency mandate can receive and accept fire/rescue tickets.</p>
      </div>
      <span class="chip chip-red">BFP Command</span>
    </div>

    <div style="overflow-x:auto;">
      <table class="data-table">
        <thead>
          <tr>
            <th>Personnel ID</th>
            <th>Full Name &amp; Rank</th>
            <th>Contact / Radio Call</th>
            <th>Duty Status</th>
            <th>Active Ticket</th>
            <th style="text-align:right;">CRUD Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="mono muted">#0102</td>
            <td>
              <strong>FO2 Mark Mendoza</strong>
              <div class="small muted">Lead Commander &bull; Unit 1</div>
            </td>
            <td>
              <div>lead.bfp@mabalacat.gov.ph</div>
              <div class="small muted">0917-334-1122 &bull; Call: BFP-1</div>
            </td>
            <td><span class="chip chip-green">On-Duty</span></td>
            <td><span class="chip chip-red">MBL-2026-0447 (En Route)</span></td>
            <td style="text-align:right;">
              <a href="users-edit.html" class="btn btn-outline btn-xs">Edit</a>
              <button class="btn btn-outline btn-xs" style="color:#dc2626;" onclick="confirm('Delete personnel?');">Delete</button>
            </td>
          </tr>
          <tr>
            <td class="mono muted">#0105</td>
            <td>
              <strong>FO1 Arnel Macaspac</strong>
              <div class="small muted">Pump Operator &bull; Heavy Tanker</div>
            </td>
            <td>
              <div>macaspac.bfp@mabalacat.gov.ph</div>
              <div class="small muted">0920-887-3456 &bull; Call: TANK-2</div>
            </td>
            <td><span class="chip chip-green">On-Duty</span></td>
            <td><span class="chip chip-blue">Available</span></td>
            <td style="text-align:right;">
              <a href="users-edit.html" class="btn btn-outline btn-xs">Edit</a>
              <button class="btn btn-outline btn-xs" style="color:#dc2626;" onclick="confirm('Delete personnel?');">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div>    
</main>
  </div>
</div>

<script src="../../assets/vendor/leaflet/leaflet.js" defer></script>
<script src="../../assets/js/core-interactions.js" defer></script>
</body>
</html>