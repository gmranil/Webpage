<?php
// php/register_process.php
session_start();

require_once 'config.php';
require_once 'db.php';
require_once 'security.php';
require_once 'email_service.php';

header('Content-Type: application/json');

// CSRF védelem
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Rate limiting
if (!checkRateLimit('register', 5, 300)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many registration attempts. Please try again later.']);
    exit;
}

try {
    // Input validáció
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    
    if (empty($username) || empty($password) || empty($email)) {
        throw new Exception('All fields are required');
    }
    
    // Username validáció
    if (!preg_match('/^[a-zA-Z0-9_]{3,16}$/', $username)) {
        throw new Exception('Username must be 3-16 characters (letters, numbers, underscore only)');
    }
    
    // Email validáció
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }
    
    // Jelszó erősség
    if (strlen($password) < 6) {
        throw new Exception('Password must be at least 6 characters');
    }
    
    $pdo = getDBConnection(DB_LS);
    
    // Username létezik már?
    $stmt = $pdo->prepare("SELECT login FROM accounts WHERE login = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        throw new Exception('Username already exists');
    }
    
    // Email létezik már?
    $stmt = $pdo->prepare("SELECT email FROM accounts WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('Email already registered');
    }
    
    // L2J Mobius password hash (Base64 SHA1)
    $passwordHash = base64_encode(sha1($password, true));
    
    // Account létrehozása (email_verified = 0)
    $stmt = $pdo->prepare("
        INSERT INTO accounts (login, password, email, email_verified, access_level, lastactive) 
        VALUES (?, ?, ?, 0, 0, ?)
    ");
    
    $now = time() * 1000; // L2J milliseconds (lastactive)
    $stmt->execute([$username, $passwordHash, $email, $now]);
    
    // Verification token generálása
    $token = bin2hex(random_bytes(32)); // 64 karakter hex
    $expiresAt = time() + (24 * 3600); // 24 óra
    
    $stmt = $pdo->prepare("
        INSERT INTO email_verification_tokens (username, email, token, expires_at) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$username, $email, $token, $expiresAt]);
    
    // Verification email küldése
    $emailService = new EmailService();
    $emailSent = $emailService->sendVerificationEmail($email, $username, $token);
    
    if (!$emailSent) {
        error_log("Warning: Registration successful but verification email failed for user: $username");
    }
    
    // Activity log
    logActivity($username, 'REGISTER', $_SERVER['REMOTE_ADDR']);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Registration successful! Please check your email to verify your account.',
        'email_sent' => $emailSent
    ]);
    
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
