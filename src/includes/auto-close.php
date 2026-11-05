<?php
// src/includes/auto-close.php

function runAutoCloseChecker($pdo) {
    // Auto-resolve reports in 'Waiting Confirmation' for > 24 hours
    try {
        $stmt = $pdo->prepare("
            UPDATE reports 
            SET status = 'Resolved',
                closed_at = NOW()
            WHERE status = 'Waiting Confirmation' 
              AND resolved_uploaded_at IS NOT NULL 
              AND TIMESTAMPDIFF(HOUR, resolved_uploaded_at, NOW()) >= 24
        ");
        $stmt->execute();
    } catch (\PDOException $e) {
        error_log("Auto-close checker error: " . $e->getMessage());
    }
}
