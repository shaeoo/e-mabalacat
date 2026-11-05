<?php
// public/superadmin/users.php
session_start();
require_once '../../src/config/database.php';
require_once '../../src/includes/auth-guard.php';
requireRole('super_admin');

$action = $_GET['action'] ?? '';
$error = '';
$success = '';

// Handle Delete
if ($action === 'delete' && isset($_GET['id'])) {
    $delId = (int)$_GET['id'];
    if ($delId === (int)$_SESSION['user_id']) {
        $error = "Hindi mo maaaring burahin ang iyong sariling Super Admin account.";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$delId]);
            $success = "Matagumpay na nabura ang user (ID: $delId).";
        } catch (PDOException $e) {
            $error = "Hindi mabura ang user dahil may mga nakatalagang reports o aksyon ito: " . $e->getMessage();
        }
    }
}

// Handle Status & Role Updates (Edit CRUD)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $editId = (int)$_POST['user_id'];
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    $agencyType = ($role === 'agency_admin') ? $_POST['agency_type'] : null;
    $barangayId = !empty($_POST['barangay_id']) ? (int)$_POST['barangay_id'] : null;
    $kycStatus = $_POST['kyc_status'];
    $emailVerified = isset($_POST['email_verified']) ? 1 : 0;
    $strikeCount = (int)$_POST['strike_count'];

    try {
        $stmt = $pdo->prepare("
            UPDATE users SET 
                full_name = ?, 
                email = ?, 
                phone = ?, 
                role = ?, 
                agency_type = ?, 
                barangay_id = ?, 
                kyc_status = ?, 
                email_verified = ?, 
                strike_count = ?
            WHERE id = ?
        ");
        $stmt->execute([$fullName, $email, $phone, $role, $agencyType, $barangayId, $kycStatus, $emailVerified, $strikeCount, $editId]);
        
        // Optional password change
        if (!empty($_POST['new_password'])) {
            $pHash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $pStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $pStmt->execute([$pHash, $editId]);
        }

        $success = "Matagumpay na na-update ang user account para kay " . htmlspecialchars($fullName);
    } catch (PDOException $e) {
        $error = "May error sa pag-update: " . $e->getMessage();
    }
}

// Handle User Creation from modal/form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $agencyType = ($role === 'agency_admin') ? $_POST['agency_type'] : null;
    $barangayId = !empty($_POST['barangay_id']) ? (int)$_POST['barangay_id'] : null;
    $kycStatus = $_POST['kyc_status'] ?? 'verified';

    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, email, phone, password_hash, role, agency_type, barangay_id, kyc_status, email_verified) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([$fullName, $email, $phone, $password, $role, $agencyType, $barangayId, $kycStatus]);
        $success = "Matagumpay na nalikha ang account para kay $fullName!";
    } catch (PDOException $e) {
        $error = "Error sa paggawa ng account (posibleng duplicate email): " . $e->getMessage();
    }
}

// Filter query
$roleFilter = $_GET['role'] ?? '';
$search = trim($_GET['search'] ?? '');
$params = [];
$sql = "SELECT u.*, b.name AS barangay_name FROM users u LEFT JOIN barangays b ON u.barangay_id = b.id WHERE 1=1";

if ($roleFilter) {
    $sql .= " AND u.role = ?";
    $params[] = $roleFilter;
}
if ($search) {
    $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY u.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usersList = $stmt->fetchAll(PDO::FETCH_ASSOC);

$barangays = $pdo->query("SELECT * FROM barangays ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Check if editing a specific user
$editUser = null;
if (isset($_GET['edit_id'])) {
    $eStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $eStmt->execute([(int)$_GET['edit_id']]);
    $editUser = $eStmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#063151">
<title>Master User Management &bull; Super Admin | e-Mabalacat</title>
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
        <span>👥</span>
        <span>CDRRMO Super Admin &bull; Master User Management &amp; Access Control</span>
      </div>
      <div class="app-topbar-actions">
        <a href="dashboard.php" class="btn btn-outline-light btn-xs">&larr; Command Desk</a>
        <a href="#addUserForm" class="btn btn-gold btn-xs">+ Add User</a>
      </div>
    </header>

    <main class="app-content">

      <?php if ($success): ?>
        <div class="callout callout-info" style="margin-bottom: 20px; background: #ecfdf5; border-color: #10b981; color: #065f46;">
          <strong>✓ Tagumpay:</strong> <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="callout callout-alert" style="margin-bottom: 20px; background: #fef2f2; border-color: #ef4444; color: #991b1b;">
          <strong>⚠ Puna:</strong> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <!-- EDIT USER CARD IF ACTIVE -->
      <?php if ($editUser): ?>
        <div class="bento-card span-12" style="border: 2px solid var(--gold); margin-bottom: 24px;">
          <div class="bento-head">
            <h2 class="bento-title">I-edit ang User #<?= $editUser['id'] ?> &mdash; <?= htmlspecialchars($editUser['full_name']) ?></h2>
            <a href="users.php" class="btn btn-outline-light btn-xs">&times; Kanselahin</a>
          </div>

          <form method="POST" action="users.php">
            <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
              <div class="form-group">
                <label class="form-label">Full Name</label>
                <input class="input" type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($editUser['full_name']) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Email Address</label>
                <input class="input" type="email" name="email" class="form-control" value="<?= htmlspecialchars($editUser['email']) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input class="input" type="text" name="phone" class="form-control" value="<?= htmlspecialchars($editUser['phone']) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">System Role</label>
                <select class="select" name="role" class="form-control" id="edit_role_select" onchange="toggleEditFields(this.value)">
                  <option value="citizen" <?= $editUser['role'] === 'citizen' ? 'selected' : '' ?>>Citizen / Resident</option>
                  <option value="responder" <?= $editUser['role'] === 'responder' ? 'selected' : '' ?>>Field Responder / Tanod</option>
                  <option value="mini_admin" <?= $editUser['role'] === 'mini_admin' ? 'selected' : '' ?>>Barangay Mini-Admin</option>
                  <option value="agency_admin" <?= $editUser['role'] === 'agency_admin' ? 'selected' : '' ?>>Agency Dispatch Admin</option>
                  <option value="super_admin" <?= $editUser['role'] === 'super_admin' ? 'selected' : '' ?>>CDRRMO Super Admin</option>
                </select>
              </div>
              <div class="form-group" id="edit_agency_box" style="display: <?= $editUser['role'] === 'agency_admin' ? 'block' : 'none' ?>;">
                <label class="form-label">Agency Mandate</label>
                <select class="select" name="agency_type" class="form-control">
                  <option value="bfp" <?= $editUser['agency_type'] === 'bfp' ? 'selected' : '' ?>>BFP (Fire)</option>
                  <option value="pnp" <?= $editUser['agency_type'] === 'pnp' ? 'selected' : '' ?>>PNP (Police)</option>
                  <option value="medical" <?= $editUser['agency_type'] === 'medical' ? 'selected' : '' ?>>Medical / CDRRMO</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Barangay Jurisdiction</label>
                <select class="select" name="barangay_id" class="form-control">
                  <option value="">-- Walang Nakatalagang Barangay --</option>
                  <?php foreach ($barangays as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= $editUser['barangay_id'] == $b['id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($b['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">KYC Status</label>
                <select class="select" name="kyc_status" class="form-control">
                  <option value="verified" <?= $editUser['kyc_status'] === 'verified' ? 'selected' : '' ?>>Verified</option>
                  <option value="pending" <?= $editUser['kyc_status'] === 'pending' ? 'selected' : '' ?>>Pending Review</option>
                  <option value="rejected" <?= $editUser['kyc_status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Strike Count (False Alarms)</label>
                <input class="input" type="number" name="strike_count" class="form-control" value="<?= (int)$editUser['strike_count'] ?>" min="0" max="10">
              </div>
              <div class="form-group">
                <label class="form-label">Reset Password</label>
                <input class="input" type="password" name="new_password" class="form-control" placeholder="Bagong password kung babaguhin">
              </div>
            </div>

            <div style="margin-top: 16px; display: flex; gap: 10px;">
              <button type="submit" name="update_user" class="btn btn-gold btn-sm">I-save ang mga Pagbabago</button>
              <a href="users.php" class="btn btn-outline-light btn-sm">Kanselahin</a>
            </div>
          </form>
        </div>
      <?php endif; ?>

      <!-- BENTO GRID -->
      <div class="bento-grid">
        
        <!-- BENTO 1: SEARCH & FILTER (SPAN 12) -->
        <div class="bento-card span-12" style="padding: 16px 20px;">
          <form method="GET" action="users.php" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 240px;">
              <label class="form-label" style="font-size: 0.8rem;">Maghanap (Pangalan, Email, Telepono)</label>
              <input class="input" type="text" name="search" class="form-control" placeholder="Search user..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div style="width: 200px;">
              <label class="form-label" style="font-size: 0.8rem;">I-filter ayon sa Role</label>
              <select name="role" class="select" onchange="this.form.submit()">
                <option value="">Lahat ng Roles</option>
                <option value="citizen" <?= $roleFilter === 'citizen' ? 'selected' : '' ?>>Citizens</option>
                <option value="responder" <?= $roleFilter === 'responder' ? 'selected' : '' ?>>Responders</option>
                <option value="mini_admin" <?= $roleFilter === 'mini_admin' ? 'selected' : '' ?>>Barangay Admins</option>
                <option value="agency_admin" <?= $roleFilter === 'agency_admin' ? 'selected' : '' ?>>Agency Admins</option>
                <option value="super_admin" <?= $roleFilter === 'super_admin' ? 'selected' : '' ?>>Super Admins</option>
              </select>
            </div>
            <div>
              <button type="submit" class="btn btn-gold btn-sm">I-filter</button>
              <?php if ($roleFilter || $search): ?>
                <a href="users.php" class="btn btn-outline-light btn-sm" style="margin-left: 6px;">Clear</a>
              <?php endif; ?>
            </div>
          </form>
        </div>

        <!-- BENTO 2: USERS MASTER TABLE (SPAN 12) -->
        <div class="bento-card span-12" style="padding: 0;">
          <div style="padding: 14px 20px; background: #f8fafc; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1rem; font-weight: 700; color: var(--navy); margin: 0;">Master User Records (<?= count($usersList) ?> users found)</h3>
            <span class="badge badge-gold">5 Roles Supported</span>
          </div>

          <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
              <thead>
                <tr style="background: #f1f5f9; text-align: left; border-bottom: 1px solid var(--line);">
                  <th style="padding: 10px 14px;">ID</th>
                  <th style="padding: 10px 14px;">User Info</th>
                  <th style="padding: 10px 14px;">Role / Scope</th>
                  <th style="padding: 10px 14px;">Barangay</th>
                  <th style="padding: 10px 14px;">KYC / Status</th>
                  <th style="padding: 10px 14px;">Petsa ng Rehistro</th>
                  <th style="padding: 10px 14px; text-align: right;">Aksyon (CRUD)</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($usersList)): ?>
                  <tr>
                    <td colspan="7" style="padding: 2.5rem; text-align: center; color: var(--muted);">Walang nahanap na user record sa napiling filter.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($usersList as $u): ?>
                    <tr style="border-bottom: 1px solid var(--line);">
                      <td style="padding: 10px 14px; font-family: var(--font-mono); font-size: 0.8rem; color: var(--muted);">
                        #<?= $u['id'] ?>
                      </td>
                      <td style="padding: 10px 14px;">
                        <div style="font-weight: 700; color: var(--navy);"><?= htmlspecialchars($u['full_name']) ?></div>
                        <div style="font-size: 0.775rem; color: var(--muted);"><?= htmlspecialchars($u['email']) ?> &bull; <?= htmlspecialchars($u['phone']) ?></div>
                        <?php if ($u['kyc_id_image']): ?>
                          <div style="margin-top: 2px;">
                            <a href="../uploads/kyc/<?= htmlspecialchars($u['kyc_id_image']) ?>" target="_blank" style="font-size: 0.75rem; color: #2563eb; text-decoration: underline;">
                              &#128065; View KYC ID Image
                            </a>
                          </div>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 10px 14px;">
                        <span class="badge <?= $u['role'] === 'super_admin' ? 'badge-primary' : ($u['role'] === 'agency_admin' ? 'badge-alert' : ($u['role'] === 'mini_admin' ? 'badge-blue' : ($u['role'] === 'responder' ? 'badge-gold' : 'badge-emerald'))) ?>" style="text-transform: uppercase; font-size: 0.7rem; font-weight: 700;">
                          <?= str_replace('_', ' ', $u['role']) ?>
                        </span>
                        <?php if ($u['agency_type']): ?>
                          <span class="badge badge-outline" style="font-size: 0.65rem; margin-left: 2px; font-weight: 700; text-transform: uppercase;">
                            <?= $u['agency_type'] ?>
                          </span>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 10px 14px;">
                        <?= htmlspecialchars($u['barangay_name'] ?? 'City-Wide / N/A') ?>
                      </td>
                      <td style="padding: 10px 14px;">
                        <?php if ($u['kyc_status'] === 'verified'): ?>
                          <span class="chip chip-green" style="font-size: 0.7rem;">Verified</span>
                        <?php elseif ($u['kyc_status'] === 'pending'): ?>
                          <span class="chip chip-gold" style="font-size: 0.7rem;">Pending KYC</span>
                        <?php else: ?>
                          <span class="chip chip-red" style="font-size: 0.7rem;">Rejected</span>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 10px 14px; font-size: 0.75rem; color: var(--muted);">
                        <?= date('M d, Y', strtotime($u['created_at'])) ?>
                      </td>
                      <td style="padding: 10px 14px; text-align: right; white-space: nowrap;">
                        <a href="users.php?edit_id=<?= $u['id'] ?>" class="btn btn-outline btn-xs" style="margin-right: 4px;">Edit</a>
                        <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
                          <a href="users.php?action=delete&id=<?= $u['id'] ?>" onclick="return confirm('Sigurado ka bang nais mong burahin ang user na ito?');" class="btn btn-outline-light btn-xs" style="color: #ef4444; border-color: #fca5a5;">Delete</a>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- BENTO 3: QUICK ADD USER (SPAN 12) -->
        <div id="addUserForm" class="bento-card span-12">
          <div class="bento-head">
            <h2 class="bento-title">+ Magdagdag ng Bagong User sa Sistema</h2>
            <span class="badge badge-gold">All 5 Personas</span>
          </div>

          <form method="POST" action="users.php">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
              <div class="form-group">
                <label class="form-label">Role</label>
                <select name="role" class="select " id="create_role_select" onchange="toggleCreateFields(this.value)" required>
                  <option value="citizen">Citizen / Resident</option>
                  <option value="responder">Field Responder / Tanod</option>
                  <option value="mini_admin">Barangay Mini-Admin</option>
                  <option value="agency_admin">Agency Dispatch Admin</option>
                  <option value="super_admin">CDRRMO Super Admin</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="input" required placeholder="Juan Dela Cruz">
              </div>
              <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="input" required placeholder="juan@example.com">
              </div>
              <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="input" required placeholder="09123456789">
              </div>
              <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="input" required placeholder="Default Password">
              </div>
              <div class="form-group" id="create_agency_box" style="display: none;">
                <label class="form-label">Agency Mandate</label>
                <select name="agency_type" class="input">
                  <option value="bfp">BFP (Bureau of Fire Protection)</option>
                  <option value="pnp">PNP (Philippine National Police)</option>
                  <option value="medical">Medical / CDRRMO Ambulance</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Barangay</label>
                <select name="barangay_id" class="select">
                  <option value="">-- Piliin ang Barangay (Optional) --</option>
                  <?php foreach ($barangays as $b): ?>
                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Initial KYC Status</label>
                <select name="kyc_status" class="input">
                  <option value="verified">Verified (Agad magagamit)</option>
                  <option value="pending">Pending</option>
                </select>
              </div>
            </div>

            <div style="margin-top: 16px;">
              <button type="submit" name="create_user" class="btn btn-gold btn-sm">+ Lumikha ng Account</button>
            </div>
          </form>
        </div>

      </div>

    </main>
  </div>
</div>

<script>
function toggleEditFields(role) {
  var b = document.getElementById('edit_agency_box');
  if (b) b.style.display = (role === 'agency_admin') ? 'block' : 'none';
}
function toggleCreateFields(role) {
  var b = document.getElementById('create_agency_box');
  if (b) b.style.display = (role === 'agency_admin') ? 'block' : 'none';
}
</script>
</body>
</html>
