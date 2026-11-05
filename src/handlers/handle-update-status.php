<?php
// src/handlers/handle-update-status.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['responder', 'mini_admin', 'agency_admin'])) {
    die("Unauthorized action.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportId = $_POST['report_id'] ?? null;
    $newStatus = $_POST['new_status'] ?? '';

    if ($newStatus === 'Waiting Confirmation') {
        $photoName = '';
        if (isset($_FILES['resolution_photo']) && $_FILES['resolution_photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../public/uploads/resolutions/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = pathinfo($_FILES['resolution_photo']['name'], PATHINFO_EXTENSION);
            $photoName = 'RES_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['resolution_photo']['tmp_name'], $uploadDir . $photoName);
        }

        $remarks = $_POST['resolution_remarks'] ?? '';
        $refNo = $_POST['official_ref_no'] ?? '';

        $stmt = $pdo->prepare("
            UPDATE reports 
            SET status = 'Waiting Confirmation', 
                resolution_photo = ?, 
                resolution_remarks = ?, 
                official_ref_no = ?, 
                resolved_uploaded_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([$photoName, $remarks, $refNo, $reportId]);
    } else {
        $stmt = $pdo->prepare("UPDATE reports SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $reportId]);
    }

    $referer = $_SERVER['HTTP_REFERER'] ?? '../../public/responder/dashboard.php';
    header("Location: " . $referer);
    exit();
}
?>
