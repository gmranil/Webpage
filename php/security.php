<?php
// php/security.php

/**
 * CSRF Token generálása
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF Token validálása
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Rate limiting ellenőrzés
 * 
 * @param string $action Az action neve (pl. 'login', 'register')
 * @param int $maxAttempts Maximum próbálkozások száma
 * @param int $timeWindow Idő ablak másodpercben
 * @return bool True ha engedélyezett, false ha rate limit túllépve
 */
function checkRateLimit($action, $maxAttempts = 5, $timeWindow = 300) {
    $key = 'rate_limit_' . $action;
    $now = time();
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [];
    }
    
    // Régi bejegyzések törlése (időablakon kívül)
    $_SESSION[$key] = array_filter($_SESSION[$key], function($timestamp) use ($now, $timeWindow) {
        return ($now - $timestamp) < $timeWindow;
    });
    
    // Ellenőrzés
    if (count($_SESSION[$key]) >= $maxAttempts) {
        return false;
    }
    
    // Új próbálkozás rögzítése
    $_SESSION[$key][] = $now;
    
    return true;
}

/**
 * Activity logging
 */
function logActivity($username, $action, $ip = null) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/activity_' . date('Y-m-d') . '.log';
    $ip = $ip ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $timestamp = date('Y-m-d H:i:s');
    
    $logEntry = "[$timestamp] User: $username | Action: $action | IP: $ip\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * XSS védelem - HTML kimenethez
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * SQL injection védelem emlékeztető
 * Mindig prepared statements-t használj PDO-val!
 */
