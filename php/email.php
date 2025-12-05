<?php
// php/email.php
require_once 'email_config.php';
require_once __DIR__ . '/../lib/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configureMailer();
    }
    
    private function configureMailer() {
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = SMTP_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = SMTP_USERNAME;
            $this->mailer->Password = SMTP_PASSWORD;
            $this->mailer->SMTPSecure = SMTP_ENCRYPTION;
            $this->mailer->Port = SMTP_PORT;
            $this->mailer->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
            $this->mailer->CharSet = 'UTF-8';
            
            if (EMAIL_DEBUG) {
                $this->mailer->SMTPDebug = 2;
            }
            
        } catch (Exception $e) {
            error_log("Email config error: " . $e->getMessage());
        }
    }
    
    public function send($to, $subject, $body, $isHTML = true) {
        if (!EMAIL_ENABLED) {
            error_log("Email disabled - To: $to | Subject: $subject");
            return true;
        }
        
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            $this->mailer->isHTML($isHTML);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            
            if ($isHTML) {
                $this->mailer->AltBody = strip_tags($body);
            }
            
            return $this->mailer->send();
            
        } catch (Exception $e) {
            error_log("Email send error: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
    
    public function loadTemplate($templateName, $variables = []) {
        $templatePath = __DIR__ . "/../email_templates/{$templateName}.html";
        
        if (!file_exists($templatePath)) {
            error_log("Email template not found: $templatePath");
            return false;
        }
        
        $template = file_get_contents($templatePath);
        
        foreach ($variables as $key => $value) {
            $template = str_replace("{{" . $key . "}}", $value, $template);
        }
        
        return $template;
    }
    
    public function sendVerificationEmail($email, $username, $token) {
        $verifyUrl = SITE_URL . "/verify_email.php?token=" . urlencode($token);
        
        $body = $this->loadTemplate('verification', [
            'username' => htmlspecialchars($username),
            'verify_url' => $verifyUrl,
            'site_name' => EMAIL_FROM_NAME
        ]);
        
        if (!$body) {
            return false;
        }
        
        return $this->send($email, 'Verify Your Email - ' . EMAIL_FROM_NAME, $body);
    }
    
    public function sendPasswordResetEmail($email, $username, $token) {
        $resetUrl = SITE_URL . "/reset_password.php?token=" . urlencode($token);
        
        $body = $this->loadTemplate('password_reset', [
            'username' => htmlspecialchars($username),
            'reset_url' => $resetUrl,
            'expiry_time' => '1 hour',
            'site_name' => EMAIL_FROM_NAME
        ]);
        
        if (!$body) {
            return false;
        }
        
        return $this->send($email, 'Password Reset - ' . EMAIL_FROM_NAME, $body);
    }
    
    public function sendEmailChangeConfirmation($oldEmail, $username, $newEmail) {
        $body = $this->loadTemplate('email_changed', [
            'username' => htmlspecialchars($username),
            'old_email' => htmlspecialchars($oldEmail),
            'new_email' => htmlspecialchars($newEmail),
            'change_date' => date('Y-m-d H:i:s'),
            'site_name' => EMAIL_FROM_NAME
        ]);
        
        if (!$body) {
            return false;
        }
        
        $this->send($oldEmail, 'Email Address Changed - ' . EMAIL_FROM_NAME, $body);
        return $this->send($newEmail, 'Welcome to Your New Email - ' . EMAIL_FROM_NAME, $body);
    }
}

// Token helper functions
function generateToken() {
    return bin2hex(random_bytes(32));
}

function createVerificationToken($conn, $username, $email, $type = 'verification') {
    $token = generateToken();
    $expiry = $type === 'password_reset' ? RESET_TOKEN_EXPIRY : VERIFICATION_TOKEN_EXPIRY;
    $expiresAt = time() + $expiry;
    
    $stmt = $conn->prepare("
        INSERT INTO email_verification_tokens 
        (username, token, email, type, expires_at) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssssi", $username, $token, $email, $type, $expiresAt);
    
    if ($stmt->execute()) {
        return $token;
    }
    return false;
}

function validateToken($conn, $token, $type = null) {
    $query = "
        SELECT username, email, type, expires_at, used 
        FROM email_verification_tokens 
        WHERE token = ?
    ";
    
    if ($type) {
        $query .= " AND type = ?";
    }
    
    $stmt = $conn->prepare($query);
    
    if ($type) {
        $stmt->bind_param("ss", $token, $type);
    } else {
        $stmt->bind_param("s", $token);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['expires_at'] < time()) {
            return ['valid' => false, 'error' => 'Token expired'];
        }
        
        if ($row['used'] == 1) {
            return ['valid' => false, 'error' => 'Token already used'];
        }
        
        return ['valid' => true, 'data' => $row];
    }
    
    return ['valid' => false, 'error' => 'Invalid token'];
}

function markTokenAsUsed($conn, $token) {
    $stmt = $conn->prepare("UPDATE email_verification_tokens SET used = 1 WHERE token = ?");
    $stmt->bind_param("s", $token);
    return $stmt->execute();
}
