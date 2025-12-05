<?php
// test_register.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Test started...<br>";

try {
    echo "Loading config...<br>";
    require_once 'php/config.php';
    echo "Config OK<br>";
    
    echo "Loading db...<br>";
    require_once 'php/db.php';
    echo "DB OK<br>";
    
    echo "Testing DB connection (MySQLi)...<br>";
    $conn = getDBConnection(DB_LS);
    echo "Connection type: " . get_class($conn) . "<br>";
    echo "DB Connection OK<br>";
    
    echo "Testing query...<br>";
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM accounts");
    $stmt->execute();
    $result = $stmt->get_result(); // MySQLi method
    $row = $result->fetch_assoc();
    echo "Accounts in DB: " . $row['cnt'] . "<br>";
    
    echo "Testing email column...<br>";
    $stmt = $conn->prepare("SELECT login, email, email_verified FROM accounts LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        echo "Sample account: " . $row['login'] . " | Email: " . ($row['email'] ?? 'NULL') . " | Verified: " . ($row['email_verified'] ?? 'NULL') . "<br>";
    }
    
    echo "<br><strong style='color:green;'>✓ All tests passed!</strong>";
    
} catch (Exception $e) {
    echo "<br><strong style='color:red'>✗ ERROR: " . $e->getMessage() . "</strong>";
    echo "<br><pre>" . $e->getTraceAsString() . "</pre>";
}
