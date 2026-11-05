<?php
// src/handlers/handle-accept.php
session_start();
require_once '../config/database.php';
require_once '../includes/auth-guard.php';
requireRole('responder');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $responderId = $_SESSION['user_id'];
    $reportId = $_POST['report_id'] ?? null;

    if ($reportId) {
        $stmt = $pdo->prepare("
            UPDATE reports 
            SET assigned_responder_id = ?, 
                status = 'Dispatched', 
                dispatched_at = NOW() 
            WHERE id = ? AND status = 'Pending' AND assigned_responder_id IS NULL
        ");
        $stmt->execute([$responderId, $reportId]);
    }

    header("Location: ../../public/responder/dashboard.php");
    exit();
}
?>
