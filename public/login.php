<?php
// public/login.php
session_start();
require_once '../src/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  die("ok");
}
// // Redirect if already logged in
// if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
//   $r = $_SESSION['role'];
//   if ($r === 'super_admin') header("Location: superadmin/dashboard.php");
//   elseif ($r === 'agency_admin') header("Location: agency/dashboard.php");
//   elseif ($r === 'mini_admin') header("Location: barangay/dashboard.php");
//   elseif ($r === 'responder') header("Location: responder/dashboard.php");
//   else header("Location: citizen/dashboard.php");
//   exit();
// }

// if (empty($_SESSION['csrf_token'])) {
//   $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
// }

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#063151">
  <meta name="description" content="e-Mabalacat — the City Government of Mabalacat's civic hazard reporting, barangay triage, and field dispatch platform.">
  <title>Sign In — e-Mabalacat</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/vendor/leaflet/leaflet.css">
  <link rel="stylesheet" href="assets/css/site.css">
</head>

<body>

  <main id="main-content">
    <div class="auth-page">
      <section class="auth-brand-panel">
        <div class="auth-brand-inner">
          <a class="brand" href="index.html">
            <svg class="seal" viewBox="0 0 100 100" role="img" aria-label="Illustrative City Government of Mabalacat seal">
              <title>City Government of Mabalacat &mdash; illustrative seal</title>
              <circle cx="50" cy="50" r="48" fill="#063151" stroke="#f5d10d" stroke-width="2.5" />
              <circle cx="50" cy="50" r="41.5" fill="none" stroke="#f5d10d" stroke-width=".8" opacity=".65" />
              <path id="seal-arc-17" d="M 50,50 m -34,0 a 34,34 0 1,1 68,0" fill="none" />
              <text font-family="'Helvetica Neue', Helvetica, Arial, sans-serif" font-size="4.4" font-weight="700" letter-spacing="1.15" fill="#f5d10d">
                <textPath href="#seal-arc-17" startOffset="50%" text-anchor="middle">CITY GOVERNMENT OF MABALACAT</textPath>
              </text>
              <path d="M27 67c7-7 13-15 23-23 5 8 10 15 23 23Z" fill="#1766a1" stroke="#f5d10d" stroke-width=".9" stroke-linejoin="round" />
              <circle cx="50" cy="37" r="7.2" fill="#f5d10d" />
              <g stroke="#f5d10d" stroke-width="1.15" stroke-linecap="round">
                <path d="M50 24.5v-3M50 52.5v-8M37.5 37h-3M65.5 37h-3M41.2 28.2l-2.1-2.1M58.8 28.2l2.1-2.1M41.2 45.8l-2.1 2.1M58.8 45.8l2.1 2.1" />
              </g>
              <path d="M30 68h40" stroke="#f5d10d" stroke-width="1.4" stroke-linecap="round" />
              <path d="M35 73c0-3 3-5 4.5-7 1.5 2 4.5 4 4.5 7a4.5 4.5 0 0 1-9 0Z" fill="none" stroke="#f5d10d" stroke-width=".8" />
              <path d="M56 73c0-3 3-5 4.5-7 1.5 2 4.5 4 4.5 7a4.5 4.5 0 0 1-9 0Z" fill="none" stroke="#f5d10d" stroke-width=".8" />
              <path d="M32 66c2-5 4-9 8-12M68 66c-2-5-4-9-8-12" fill="none" stroke="#f5d10d" stroke-width=".8" opacity=".8" />
              <text x="50" y="84.5" font-family="'Helvetica Neue', Helvetica, Arial, sans-serif" font-size="4.4" font-weight="700" letter-spacing="1.3" fill="#f5d10d" text-anchor="middle">PAMPANGA</text>
            </svg> <span>
              <span class="brand-name">e-<em>Mabalacat</em></span>
              <span class="brand-sub">City Government of Mabalacat</span>
            </span>
          </a>
          <div class="auth-quote">
            <span class="eyebrow on-dark">Your city, one tap away</span>
            <h1>From a pin on the road to <em>proof of repair.</em></h1>
            <p>One secure account for hazard reports, barangay updates and the before-and-after record of every fix.</p>
            <ul class="auth-trust">
              <li><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M12 3 4.5 6v5.5c0 4.6 3.1 7.8 7.5 9.5 4.4-1.7 7.5-4.9 7.5-9.5V6L12 3Z" />
                  <path d="m8.5 12 2.3 2.3 4.7-4.6" />
                </svg> 1-Click Google sign-in or Email + Password</li>
              <li><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <rect x="4.5" y="10" width="15" height="11" rx="2" />
                  <path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3" />
                </svg> 30-day trusted device, revocable anytime</li>
              <li><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <rect x="2.5" y="5" width="19" height="14" rx="2" />
                  <circle cx="8" cy="11" r="2.2" />
                  <path d="M4.8 16a3.6 3.6 0 0 1 6.4 0M14 10h4M14 13h4M14 16h2.5" />
                </svg> Verified resident ID review at the barangay desk</li>
              <li><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <rect x="3" y="5" width="18" height="14" rx="2" />
                  <path d="m3 7 9 6 9-6" />
                </svg> Transactional email updates via Brevo</li>
            </ul>
          </div>
        </div>
      </section>

      <section class="auth-panel">
        <div class="auth-card">
          <div class="auth-mark">
            <svg class="seal" viewBox="0 0 100 100" role="img" aria-label="Illustrative City Government of Mabalacat seal">
              <title>City Government of Mabalacat &mdash; illustrative seal</title>
              <circle cx="50" cy="50" r="48" fill="#063151" stroke="#f5d10d" stroke-width="2.5" />
              <circle cx="50" cy="50" r="41.5" fill="none" stroke="#f5d10d" stroke-width=".8" opacity=".65" />
              <path id="seal-arc-18" d="M 50,50 m -34,0 a 34,34 0 1,1 68,0" fill="none" />
              <text font-family="'Helvetica Neue', Helvetica, Arial, sans-serif" font-size="4.4" font-weight="700" letter-spacing="1.15" fill="#f5d10d">
                <textPath href="#seal-arc-18" startOffset="50%" text-anchor="middle">CITY GOVERNMENT OF MABALACAT</textPath>
              </text>
              <path d="M27 67c7-7 13-15 23-23 5 8 10 15 23 23Z" fill="#1766a1" stroke="#f5d10d" stroke-width=".9" stroke-linejoin="round" />
              <circle cx="50" cy="37" r="7.2" fill="#f5d10d" />
              <g stroke="#f5d10d" stroke-width="1.15" stroke-linecap="round">
                <path d="M50 24.5v-3M50 52.5v-8M37.5 37h-3M65.5 37h-3M41.2 28.2l-2.1-2.1M58.8 28.2l2.1-2.1M41.2 45.8l-2.1 2.1M58.8 45.8l2.1 2.1" />
              </g>
              <path d="M30 68h40" stroke="#f5d10d" stroke-width="1.4" stroke-linecap="round" />
              <path d="M35 73c0-3 3-5 4.5-7 1.5 2 4.5 4 4.5 7a4.5 4.5 0 0 1-9 0Z" fill="none" stroke="#f5d10d" stroke-width=".8" />
              <path d="M56 73c0-3 3-5 4.5-7 1.5 2 4.5 4 4.5 7a4.5 4.5 0 0 1-9 0Z" fill="none" stroke="#f5d10d" stroke-width=".8" />
              <path d="M32 66c2-5 4-9 8-12M68 66c-2-5-4-9-8-12" fill="none" stroke="#f5d10d" stroke-width=".8" opacity=".8" />
              <text x="50" y="84.5" font-family="'Helvetica Neue', Helvetica, Arial, sans-serif" font-size="4.4" font-weight="700" letter-spacing="1.3" fill="#f5d10d" text-anchor="middle">PAMPANGA</text>
            </svg>
            <div>
              <strong>e-Mabalacat</strong>
              <span>City Government of Mabalacat</span>
            </div>
          </div>

          <h1>Welcome back.</h1>
          <p class="auth-sub">Sign in to your resident account or access the city staff back-office.</p>



          <!-- Account Type Tabs -->
          <div class="auth-tabs" role="tablist" aria-label="Choose account type">
            <button type="button" class="active" onclick="switchAuthTab('resident')" role="tab" aria-selected="true">Resident</button>
            <button type="button" class="" onclick="switchAuthTab('staff')" role="tab" aria-selected="false">City Staff</button>
          </div>

          <!-- RESIDENT TAB -->
          <div id="residentPanel" style="">

            <!-- Password & Google 1-Click Login Step -->
            <div class="mt-16">
              <!-- 1-Click Google Login -->

              <form id="googleSignInForm" method="POST" action="" style="">
                <input type="hidden" name="_csrf" value="fe2c6e84f00955304770f4ae0f918ca785ed9f59d4b1be42fcedc6c3cc0ffea4"> <input type="hidden" name="type" value="google">
                <input type="hidden" id="googleEmailInput" name="google_email" value="">
                <input type="hidden" id="googleCredentialInput" name="credential" value="">
                <button class="google-btn" type="button" onclick="triggerGoogleSignIn()">
                  <svg viewBox="0 0 48 48" aria-hidden="true" style="width:18px;height:18px;">
                    <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.1 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.4-.4-3.5Z" />
                    <path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.1 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7Z" />
                    <path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.3 0-9.7-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44Z" />
                    <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.3 4.2-4.1 5.6l6.2 5.2C36.9 39.2 44 34 44 24c0-1.3-.1-2.4-.4-3.5Z" />
                  </svg> Continue with Google
                </button>
              </form>

              <div class="auth-divider">or sign in with password</div>

              <form method="POST" action="../src/handlers/handle-login.php">
                <input type="hidden" name="_csrf" value="fe2c6e84f00955304770f4ae0f918ca785ed9f59d4b1be42fcedc6c3cc0ffea4"> <input type="hidden" name="type" value="resident_password">

                <div class="field">
                  <label for="resEmail">Email address</label>
                  <input class="input" id="resEmail" type="email" name="email" autocomplete="email" placeholder="you@example.ph" value="">
                  <span class="hint">Your registered resident email address.</span>
                </div>

                <div class="field mt-12">
                  <label for="resPassword">Password</label>
                  <input class="input" id="resPassword" type="password" name="password" autocomplete="current-password" placeholder="Enter your password">
                </div>

                <label class="check-row mt-12" style="display:flex;gap:8px;align-items:center;">
                  <input type="checkbox" name="remember" checked>
                  <span class="small">Remember this device for 30 days</span>
                </label>

                <button class="btn btn-navy btn-lg btn-block mt-16" type="submit">
                  <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="4.5" y="10" width="15" height="11" rx="2" />
                    <path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3" />
                  </svg> Sign in
                </button>
              </form>

              <p class="auth-switch mt-16">
                New to e-Mabalacat? <a href="register.php">Create a resident account</a>
              </p>
              <p class="auth-fine mt-8 tiny muted">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <rect x="4.5" y="10" width="15" height="11" rx="2" />
                  <path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3" />
                </svg> Rate limited to 5 authentication attempts per 15 minutes. Passwords are stored as secure one-way hashes.
              </p>
            </div>

          </div>

          <!-- STAFF TAB -->
          <div id="staffPanel" style="display:none;">
            <div class="mt-16">
              <div class="callout info mb-16">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <rect x="4.5" y="10" width="15" height="11" rx="2" />
                  <path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3" />
                </svg>
                <div><strong>Restricted back-office access</strong>City staff sessions use role-scoped workspaces, stronger rate limits, and an auditable login trail.</div>
              </div>

              <form method="POST" action="../src/handlers/handle-login.php">
                <input type="hidden" name="_csrf" value="fe2c6e84f00955304770f4ae0f918ca785ed9f59d4b1be42fcedc6c3cc0ffea4"> <input type="hidden" name="type" value="staff">

                <div class="field">
                  <label for="staffEmail">City Staff Email</label>
                  <input class="input" id="staffEmail" type="email" name="email" autocomplete="username" placeholder="name@mabalacat.gov.ph" required>
                </div>

                <div class="field mt-12">
                  <label for="staffPassword">Password</label>
                  <input class="input" id="staffPassword" type="password" name="password" autocomplete="current-password" placeholder="Enter password" required>
                </div>

                <button class="btn btn-navy btn-lg btn-block mt-16" type="submit">
                  <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3 4.5 6v5.5c0 4.6 3.1 7.8 7.5 9.5 4.4-1.7 7.5-4.9 7.5-9.5V6L12 3Z" />
                  </svg> Sign in to Staff Portal
                </button>
              </form>

              <p class="auth-fine mt-16 tiny muted">
                Unauthorized access to government systems is prohibited. Staff sessions expire after 30 minutes of inactivity.
              </p>
              <p class="auth-switch mt-12">
                Resident instead? <button class="text-btn" type="button" onclick="switchAuthTab('resident')">Go to resident login</button>
              </p>
            </div>
          </div>

        </div>
      </section>
    </div>

    <script>
      function handleGoogleGisResponse(response) {
        if (response && response.credential) {
          const credInput = document.getElementById("googleCredentialInput");
          if (credInput) credInput.value = response.credential;
          document.getElementById("googleSignInForm").submit();
        }
      }

      function triggerGoogleSignIn() {
        const email = prompt("Google Sign-In:\nEnter your Google Email account:", "maria.santos@example.ph");
        if (email && email.trim() !== "") {
          const input = document.getElementById("googleEmailInput");
          if (input) input.value = email.trim();
          document.getElementById("googleSignInForm").submit();
        }
      }

      function switchAuthTab(tab) {
        document.querySelectorAll('.auth-tabs button').forEach(b => b.classList.remove('active'));
        if (tab === 'resident') {
          document.querySelector('.auth-tabs button:first-child').classList.add('active');
          document.getElementById('residentPanel').style.display = 'block';
          document.getElementById('staffPanel').style.display = 'none';
        } else {
          document.querySelector('.auth-tabs button:last-child').classList.add('active');
          document.getElementById('residentPanel').style.display = 'none';
          document.getElementById('staffPanel').style.display = 'block';
        }
      }
    </script>
  </main>