<?php
// public/register.php
session_start();
require_once '../src/config/database.php';

if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch all barangays for dropdown
$stmt = $pdo->query("SELECT * FROM barangays ORDER BY name ASC");
$barangays = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<meta
		name="viewport"
		content="width=device-width, initial-scale=1, viewport-fit=cover" />
	<meta name="theme-color" content="#063151" />
	<meta
		name="description"
		content="e-Mabalacat — the City Government of Mabalacat's civic hazard reporting, barangay triage, and field dispatch platform." />
	<title>e-Mabalacat — City Government of Mabalacat</title>
	<link rel="preconnect" href="https://fonts.googleapis.com" />
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
	<link
		href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&family=JetBrains+Mono:wght@400;500;600&display=swap"
		rel="stylesheet" />
	<link rel="stylesheet" href="assets/vendor/leaflet/leaflet.css" />
	<link rel="stylesheet" href="assets/css/site.css" />
</head>

<main id="main-content">
	<div class="page-head">
		<div class="wrap">
			<div class="breadcrumb"><a href="index.html">Home</a><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<path d="m9 5 7 7-7 7" />
				</svg><span>Create account</span></div>
			<h1 class="page-title">Join your barangay's service desk.</h1>
			<p class="page-lede">Create your resident profile with a secure password and verify your email.</p>
		</div>
	</div>

	<div class="report-page">
		<div class="wrap">
			<div class="wizard-shell" style="max-width:1000px;margin-inline:auto">
				<div>

					<div class="wizard-card">

						<!-- STEP 1: Account credentials -->
						<form method="POST" action="../src/handlers/handle-register.php">
							<input type="hidden" name="_csrf" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
							<input type="hidden" name="action" value="step1">
							<div class="wizard-body">
								<h2 class="wizard-step-title">Let's set up your account.</h2>
								<p class="wizard-step-sub">Enter your details and choose a secure password.</p>

								<div class="form-grid two mt-16">
									<div class="field">
										<label for="regName">Full name <span class="req">*</span></label>
										<input class="input" id="regName" type="text" name="name" autocomplete="name" placeholder="Juan Dela Cruz" value="">
										<span class="hint">Must match your government-issued ID.</span>
									</div>
									<div class="field">
										<label for="regEmail">Email address <span class="req">*</span></label>
										<input class="input" id="regEmail" type="email" name="email" autocomplete="email" placeholder="you@example.ph" value="">
										<span class="hint">We'll send your 4-digit verification code here.</span>
									</div>
								</div>

								<div class="form-grid two mt-16">
									<div class="field">
										<label for="regPassword">Password <span class="req">*</span></label>
										<input class="input" id="regPassword" type="password" name="password" autocomplete="new-password" placeholder="At least 6 characters" value="">
										<span class="hint">Use a strong password.</span>
									</div>
									<div class="field">
										<label for="regConfirmPassword">Confirm password <span class="req">*</span></label>
										<input class="input" id="regConfirmPassword" type="password" name="confirm_password" autocomplete="new-password" placeholder="Re-enter password" value="">
										<span class="hint">Make sure both passwords match.</span>
									</div>
								</div>

								<div class="form-grid mt-16">
									<div class="field">
										<label for="regPhone">Mobile number <span class="muted">(optional)</span></label>
										<input class="input" id="regPhone" type="tel" name="phone" autocomplete="tel" placeholder="+63 917 000 0000" value="">
										<span class="hint">Only used for urgent dispatch follow-ups.</span>
									</div>
								</div>

								<!-- STEP 2: Resident information -->
								<h2 class="wizard-step-title mt-32">Where is home?</h2>
								<p class="wizard-step-sub">Your barangay controls which desk sees your KYC submission and local reports.</p>
								
								<div class="field mt-16">
                  <label for="regBrgy">Resident Barangay <span class="req">*</span></label>
                  <select class="select" id="regBrgy" name="barangay" required>
										<?php foreach($barangays as $b): ?>
											<option value="<?php echo $b['id'] ?>"><?php echo $b['name'] ?></option>
											<?php endforeach; ?>
											<option value="Others">Others / outside a listed barangay</option>
                  </select>
                </div>

								<div class="form-grid two mt-16">
                  <div class="field">
                    <label for="regStreet">Street, purok or subdivision <span class="muted">(optional)</span></label>
                    <input class="input" id="regStreet" type="text" name="address" placeholder="e.g. Purok 4, Sto. Niño Street" value="">
                  </div>
                  <div class="field">
                    <label for="regLandmark">Nearest landmark <span class="muted">(optional)</span></label>
                    <input class="input" id="regLandmark" type="text" name="landmark" placeholder="e.g. Beside the covered court" value="">
                  </div>
                </div>

								<div class="callout mt-16">
                  <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="2.6"/></svg>                  <div>
                    <strong>Hazards are routed by the pin, not your home address.</strong>
                    You can report a hazard anywhere in Mabalacat. This address is only for your resident profile and barangay-scoped KYC review.
                  </div>
                </div>

								<h2 class="wizard-step-title mt-32">ID Verification &amp; Email Activation</h2>
                <p class="wizard-step-sub">Attach your government-issued ID and enter the 4-digit code sent to your email.</p>
								
								<div class="form-grid mt-16">
                  <div class="field">
                    <label for="regIdType">ID type <span class="req">*</span></label>
                    <select class="select" id="regIdType" name="id_type" required>
											<option value="PhilSys / ePhilID (National ID)">PhilSys / ePhilID (National ID)</option>
											<option value="Driver’s License (LTO)">Driver’s License (LTO)</option>
											<option value="Philippine Passport">Philippine Passport</option>
											<option value="UMID / SSS / GSIS">UMID / SSS / GSIS</option>
											<option value="Postal ID">Postal ID</option>
											<option value="Voter’s ID / Certificate">Voter’s ID / Certificate</option>
											<option value="Barangay Resident ID Card">Barangay Resident ID Card</option>
										</select>
                    <span class="hint">Ensure name and photo are visible. Do not cover the ID number.</span>
                  </div>
                </div>
								
								<div class="photo-upload-box" style="border:2px dashed var(--line-strong);border-radius:12px;padding:20px;text-align:center;background:var(--surface);">
								<!-- Camera stream -->
								<video id="idCameraStream" autoplay playsinline style="width:100%;max-width:320px;border-radius:8px;background:#000;display:none;margin:0 auto 10px;"></video>
								<!-- Snap preview -->
								<canvas id="idCanvas" style="display:none;"></canvas>
								<img id="idCapturedImg" alt="ID Snap" style="width:100%;max-width:320px;border-radius:8px;display:none;margin:0 auto 10px;border:1px solid var(--line);">
								<!-- Buttons -->
								<div class="flex-center gap-8" style="justify-content:center;">
									<button type="button" id="startCamBtn" class="btn btn-ghost">Open Camera</button>
									<button type="button" id="captureCamBtn" class="btn btn-navy" style="display:none;">Take Snapshot</button>
									<button type="button" id="retakeCamBtn" class="btn btn-ghost" style="display:none;">Retake</button>
								</div>
								<!-- Store image data for form POST -->
								<input type="hidden" name="id_photo_base64" id="idPhotoBase64" required>
							</div>
                <!-- <div class="field mt-16">
                  <label for="idPhotoUpload">Government ID Photo Upload</label>
                  <div class="photo-upload-box" style="border:2px dashed var(--line-strong);border-radius:12px;padding:20px;text-align:center;background:var(--surface);">
                    <input class="file-input" type="file" id="idPhotoUpload" name="id_photo" accept="image/*" data-photo-preview="regIdPreview" style="display:block;margin:0 auto 10px;">
                    <p class="tiny muted">Upload a clear photo of your ID. Formats: JPG, PNG, WEBP.</p>
                    <div id="regIdPreview" class="photo-preview-container mt-12" style="display:none;max-width:280px;margin:12px auto 0;border-radius:8px;overflow:hidden;border:1px solid var(--line);">
                      <img src="" alt="ID Preview" style="width:100%;display:block;">
                    </div>
                  </div>
                </div> -->
							</div>

							<div class="wizard-foot flex-between flex-wrap gap-12 mt-24">
								<a class="btn btn-ghost" href="login.html">Already registered? Sign in</a>
								<div class="wizard-actions">
									<span class="small muted" style="align-self:center">Step 1 of 3</span>
									<button class="btn btn-navy" type="submit">
										Continue <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
											<path d="m9 5 7 7-7 7" />
										</svg> </button>
								</div>
							</div>
						</form>
					</div>
				</div>

				<aside class="wizard-aside">
					<div class="aside-card">
						<span class="icon-tile gold"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<rect x="2.5" y="5" width="19" height="14" rx="2" />
								<circle cx="8" cy="11" r="2.2" />
								<path d="M4.8 16a3.6 3.6 0 0 1 6.4 0M14 10h4M14 13h4M14 16h2.5" />
							</svg></span>
						<h3>Pending KYC = access now</h3>
						<p class="small muted">Your account activates immediately as &ldquo;Pending KYC&rdquo; upon email verification. You can report hazards right away while the desk reviews your ID.</p>
					</div>

					<div class="aside-card mt-16">
						<span class="icon-tile"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<path d="M12 3 4.5 6v5.5c0 4.6 3.1 7.8 7.5 9.5 4.4-1.7 7.5-4.9 7.5-9.5V6L12 3Z" />
								<path d="m8.5 12 2.3 2.3 4.7-4.6" />
							</svg></span>
						<h3>Secure credentials</h3>
						<p class="small muted">Your password gives you instant login on any device. The 4-digit email OTP protects registration against fake signups.</p>
					</div>

					<div class="aside-card mt-16">
						<span class="icon-tile"><svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<rect x="4.5" y="10" width="15" height="11" rx="2" />
								<path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3" />
							</svg></span>
						<h3>Data Privacy (RA 10173)</h3>
						<p class="small muted">Your ID and address are strictly scoped to barangay administrators. See our <a href="privacy.html">RA 10173 privacy notice</a>.</p>
					</div>
				</aside>

			</div>
		</div>
	</div>
</main>

<script>
	const video = document.getElementById('idCameraStream');
	const canvas = document.getElementById('idCanvas');
	const img = document.getElementById('idCapturedImg');
	const hiddenInput = document.getElementById('idPhotoBase64');
	const startBtn = document.getElementById('startCamBtn');
	const captureBtn = document.getElementById('captureCamBtn');
	const retakeBtn = document.getElementById('retakeCamBtn');
	let stream = null;

	startBtn.onclick = async () => {
		try {
			stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment', width: 1280, height: 720 } });
			video.srcObject = stream;
			video.style.display = 'block';
			captureBtn.style.display = 'inline-flex';
			startBtn.style.display = 'none';
		} catch (err) {
			alert('Camera permission denied or unavailable');
		}
	};

	captureBtn.onclick = () => {
		canvas.width = video.videoWidth;
		canvas.height = video.videoHeight;
		canvas.getContext('2d').drawImage(video, 0, 0);
		
		const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
		hiddenInput.value = dataUrl;
		img.src = dataUrl;
		
		// Stop cam & toggle view
		if (stream) stream.getTracks().forEach(t => t.stop());
		video.style.display = 'none';
		img.style.display = 'block';
		captureBtn.style.display = 'none';
		retakeBtn.style.display = 'inline-flex';
	};

	retakeBtn.onclick = () => {
		img.style.display = 'none';
		retakeBtn.style.display = 'none';
		hiddenInput.value = '';
		startBtn.click();
	};
</script>