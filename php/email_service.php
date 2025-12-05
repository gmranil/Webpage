<?php
// php/email_service.php

require_once __DIR__ . '/email_config.php';
require_once __DIR__ . '/../lib/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }
    
    private function configure() {
        $this->mailer->isSMTP();
        $this->mailer->Host       = SMTP_HOST;
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = SMTP_USERNAME;
        $this->mailer->Password   = SMTP_PASSWORD;
        $this->mailer->SMTPSecure = SMTP_SECURE;
        $this->mailer->Port       = SMTP_PORT;
        $this->mailer->CharSet    = 'UTF-8';
        
        // Debug csak fejlesztéskor (0 = ki, 2 = be)
        $this->mailer->SMTPDebug  = 0; // éles verzióban 0-ra állítjuk
        $this->mailer->Debugoutput = 'html';
        
        // Feladó
        $this->mailer->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    }
    
    /**
     * Verification email küldése regisztrációkor
     */
    public function sendVerificationEmail($toEmail, $username, $token) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail, $username);
            
            $verifyUrl = "http://" . $_SERVER['HTTP_HOST'] . "/verify_email.php?token=" . urlencode($token);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'L2 Savior - Email Verification';
            
            $this->mailer->Body = "
                <h2>Welcome to L2 Savior, {$username}!</h2>
                <p>Please verify your email address by clicking the link below:</p>
                <p><a href='{$verifyUrl}' style='background:#d4af37;color:#000;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;'>Verify Email</a></p>
                <p>Or copy this link to your browser:<br>{$verifyUrl}</p>
                <p>This link will expire in 24 hours.</p>
                <hr>
                <p style='color:#666;font-size:12px;'>If you did not create this account, please ignore this email.</p>
            ";
            
            $this->mailer->AltBody = "Welcome to L2 Savior, {$username}!\n\n"
                . "Please verify your email by visiting:\n{$verifyUrl}\n\n"
                . "This link will expire in 24 hours.";
            
            $this->mailer->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Email send failed: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Password reset email küldése
     */
    public function sendPasswordResetEmail($toEmail, $username, $token) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail, $username);
            
            $resetUrl = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . urlencode($token);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'L2 Savior - Password Reset Request';
            
            $this->mailer->Body = "
                <h2>Password Reset Request</h2>
                <p>Hello {$username},</p>
                <p>We received a request to reset your password. Click the link below to set a new password:</p>
                <p><a href='{$resetUrl}' style='background:#d4af37;color:#000;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;'>Reset Password</a></p>
                <p>Or copy this link to your browser:<br>{$resetUrl}</p>
                <p>This link will expire in 1 hour.</p>
                <hr>
                <p style='color:#666;font-size:12px;'>If you did not request a password reset, please ignore this email.</p>
            ";
            
            $this->mailer->AltBody = "Password Reset Request\n\n"
                . "Hello {$username},\n\n"
                . "Reset your password by visiting:\n{$resetUrl}\n\n"
                . "This link will expire in 1 hour.";
            
            $this->mailer->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Email send failed: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
}
