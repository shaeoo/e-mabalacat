<?php
// src/includes/notifier.php

function sendBrevoEmail($toEmail, $toName, $subject, $htmlContent) {
    // If Brevo API key is not configured or in local dev environment, log and return true
    $apiKey = getenv('BREVO_API_KEY') ?: '';
    
    if (empty($apiKey)) {
        // Fallback: log for local dev
        error_log("[Brevo Email Mock] To: $toEmail ($toName) | Subject: $subject");
        return true;
    }

    $url = 'https://api.brevo.com/v3/smtp/email';
    $data = [
        'sender' => ['name' => 'e-Sumbong Mabalacat', 'email' => 'no-reply@mabalacat.gov.ph'],
        'to' => [['email' => $toEmail, 'name' => $toName]],
        'subject' => $subject,
        'htmlContent' => $htmlContent
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'api-key: ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode >= 200 && $httpCode < 300);
}

function sendBrevoSMS($toPhone, $message) {
    $apiKey = getenv('BREVO_API_KEY') ?: '';
    
    if (empty($apiKey)) {
        error_log("[Brevo SMS Mock] To: $toPhone | Message: $message");
        return true;
    }

    $url = 'https://api.brevo.com/v3/transactionalSMS/send';
    $data = [
        'sender' => 'eSumbong',
        'recipient' => $toPhone,
        'content' => $message
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'api-key: ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode >= 200 && $httpCode < 300);
}
