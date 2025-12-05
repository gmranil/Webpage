<?php
// php/login_process.php
session_start();

require_once 'config.php';
require_once 'db.php';
require_once 'security.php';

header('Content-Type: application/json');

// CSRF védelem
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Rate limiting
if (!checkRateLimit('login', 5, 300)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many login attempts. Please try again later.']);
    exit;
}

try {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        throw new Exception('Username and password are required');
    }
    
    $pdo = getDBConnection(DB_LS);
    
    // User lekérése
    $stmt = $pdo->prepare("
        SELECT login, password, email, email_verified, access_level, lastactive 
        FROM accounts 
        WHERE login = ?
    ");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('Invalid username or password');
    }
    
    // Email verification ellenőrzés
    if ($user['email_verified'] == 0) {
        throw new Exception('Please verify your email address before logging in. Check your inbox.');
    }
    
    // Jelszó ellenőrzés (L2J Mobius Base64 SHA1)
    $passwordHash = base64_encode(sha1($password, true));
    
    if ($passwordHash !== $user['password']) {
        throw new Exception('Invalid username or password');
    }
    
    // Sikeres login - session beállítása
    session_regenerate_id(true);
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = $user['login'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['access_level'] = (int)$user['access_level'];
    $_SESSION['login_time'] = time();
    
    // Lastactive frissítése
    $now = time() * 1000; // L2J milliseconds
    $stmt = $pdo->prepare("UPDATE accounts SET lastactive = ? WHERE login = ?");
    $stmt->execute([$now, $username]);
    
    // Activity log
    logActivity($username, 'LOGIN', $_SERVER['REMOTE_ADDR']);
    
    // Admin vagy user?
    $redirectUrl = ($user['access_level'] >= 100) ? 'admin/index.php' : 'account.php';
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'redirect' => $redirectUrl
    ]);
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
