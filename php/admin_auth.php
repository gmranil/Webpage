<?php
// php/admin_auth.php
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Bejelentkezés ellenőrzése
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

// Admin jogosultság ellenőrzése (access_level >= 100)
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 100) {
    $_SESSION['error'] = 'Access denied. Admin privileges required.';
    header('Location: ../account.php');
    exit;
}

// Admin user adatok
$adminUsername = $_SESSION['username'];
$adminAccessLevel = $_SESSION['access_level'];

