<?php
// php/test_email.php

require_once __DIR__ . '/email_config.php';
require_once __DIR__ . '/../lib/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Alap SMTP beállítások
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port       = SMTP_PORT;

    // Debug (csak fejlesztéskor)
    $mail->SMTPDebug  = SMTP_DEBUG; // böngészőben látod a logot
    $mail->Debugoutput = 'html';

    // Feladó / címzett
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress(SMTP_FROM_EMAIL, 'Teszt Címzett'); // mehet saját magadnak

    // Tartalom
    $mail->isHTML(false);
    $mail->Subject = 'L2 Savior - Test Email V2';
    $mail->Body    = "Ez egy teszt email az Email v2 rendszerből.\nHa ezt látod a postafiókban, a PHPMailer működik.";

    // Küldés
    $mail->send();
    echo '<h3 style="color:green;">OK: Email sikeresen elküldve.</h3>';

} catch (Exception $e) {
    echo '<h3 style="color:red;">HIBA: Email nem ment el.</h3>';
    echo '<p><strong>Mailer Error:</strong> ' . htmlspecialchars($mail->ErrorInfo) . '</p>';
}
