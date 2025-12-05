<?php
// php/db.php

require_once __DIR__ . '/config.php';

/**
 * PDO kapcsolat létrehozása
 * @param string $dbName DB_LS vagy DB_GS konstans
 * @return PDO
 */
function getDBConnection($dbName) {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . $dbName . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
        
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        throw new Exception("Database connection failed");
    }
}
