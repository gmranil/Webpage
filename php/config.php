<?php
// php/config.php

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'hapci159');
define('DB_LS', 'l2jmobiusclassic');  // Login Server DB
define('DB_GS', 'l2jmobiusclassic');  // Game Server DB

// Debug Mode
define('DEBUG_MODE', true);  // false = production mode

// Site Settings
define('SITE_NAME', 'L2 SAVIOR');
define('SITE_VERSION', 'v0.9.5');

// Server Rates (display only)
define('RATE_XP', '5x');
define('RATE_SP', '5x');
define('RATE_ADENA', '3x');
define('RATE_DROP', '2x');
define('RATE_SPOIL', '2x');

// Paths
define('BASE_PATH', __DIR__ . '/..');
define('LOG_PATH', BASE_PATH . '/logs');

// Error Reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
