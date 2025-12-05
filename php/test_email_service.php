<?php
// php/test_email_service.php

require_once __DIR__ . '/email_service.php';

$emailService = new EmailService();

// Teszt verification email
$testEmail = SMTP_FROM_EMAIL; // saját magadnak küldi
$testUsername = 'TestUser';
$testToken = bin2hex(random_bytes(32));

echo "<h3>Verification Email küldése...</h3>";
$result = $emailService->sendVerificationEmail($testEmail, $testUsername, $testToken);

if ($result) {
    echo "<p style='color:green;'>✅ Verification email sikeresen elküldve!</p>";
    echo "<p>Nézd meg a Gmail postafiókodat.</p>";
} else {
    echo "<p style='color:red;'>❌ Hiba a verification email küldésekor.</p>";
}
