<?php
// src/handlers/handle-upvote.php
session_start();
require_once '../config/database.php';
require_once '../includes/auth-guard.php';
requireRole('citizen');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $reportId = $_POST['report_id'] ?? null;

    if ($reportId) {
        try {
            $stmt = $pdo->prepare("INSERT INTO report_upvotes (report_id, user_id) VALUES (?, ?)");
            $stmt->execute([$reportId, $userId]);

            $stmt = $pdo->prepare("UPDATE reports SET upvotes_count = upvotes_count + 1 WHERE id = ?");
            $stmt->execute([$reportId]);
        } catch (\PDOException $e) {
            // Already upvoted
        }
    }

    header("Location: ../../public/citizen/dashboard.php?upvoted=1");
    exit();
}
?>
