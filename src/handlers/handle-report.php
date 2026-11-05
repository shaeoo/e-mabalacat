<?php
// src/handlers/handle-report.php
session_start();
require_once '../config/database.php';
require_once '../includes/auth-guard.php';
requireRole('citizen');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $barangayId = $_POST['barangay_id'] ?? null;
    $targetPortal = $_POST['target_portal'] ?? 'barangay';
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $lat = floatval($_POST['latitude'] ?? 15.2234);
    $lng = floatval($_POST['longitude'] ?? 120.5771);

    if (empty($category) || empty($description) || empty($barangayId)) {
        die("Lahat ng kaukulang patlang ay kinakailangan.");
    }

    $evidencePhoto = '';
    if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../public/uploads/evidence/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $ext = pathinfo($_FILES['evidence']['name'], PATHINFO_EXTENSION);
        $evidencePhoto = 'EV_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['evidence']['tmp_name'], $uploadDir . $evidencePhoto);
    } else {
        die("Kinakailangan mag-upload ng litrato ng insidente.");
    }

    // Offset slight coordinates for privacy
    $fuzzLat = $lat + (rand(-10, 10) / 10000.0);
    $fuzzLng = $lng + (rand(-10, 10) / 10000.0);
    $ticketNo = 'TKT-' . date('Ymd') . '-' . rand(1000, 9999);

    $stmt = $pdo->prepare("
        INSERT INTO reports (
            ticket_no, user_id, barangay_id, target_portal, category, 
            description, evidence_photo, latitude, longitude, 
            fuzzed_lat, fuzzed_lng, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
    ");

    $stmt->execute([
        $ticketNo, $userId, $barangayId, $targetPortal, $category,
        $description, $evidencePhoto, $lat, $lng,
        $fuzzLat, $fuzzLng
    ]);

    header("Location: ../../public/citizen/dashboard.php?reported=success");
    exit();
}
?>
