<?php
// verify_email.php
require_once 'php/config.php';
require_once 'php/db.php';

$pageTitle = 'Email Verification - L2 Savior';
$message = '';
$success = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        $pdo = getDBConnection(DB_LS);
        
        // Token keresése
        $stmt = $pdo->prepare("
            SELECT username, email, expires_at, used 
            FROM email_verification_tokens 
            WHERE token = ?
        ");
        $stmt->execute([$token]);
        $tokenData = $stmt->fetch();
        
        if (!$tokenData) {
            $message = 'Invalid verification token.';
        } elseif ($tokenData['used'] == 1) {
            $message = 'This verification link has already been used.';
        } elseif ($tokenData['expires_at'] < time()) {
            $message = 'This verification link has expired. Please register again.';
        } else {
            // Token érvényes - email verified flag beállítása
            $stmt = $pdo->prepare("UPDATE accounts SET email_verified = 1 WHERE login = ?");
            $stmt->execute([$tokenData['username']]);
            
            // Token használt jelölése
            $stmt = $pdo->prepare("UPDATE email_verification_tokens SET used = 1 WHERE token = ?");
            $stmt->execute([$token]);
            
            $message = 'Email successfully verified! You can now log in.';
            $success = true;
        }
        
    } catch (Exception $e) {
        error_log("Email verification error: " . $e->getMessage());
        $message = 'Verification failed. Please try again later.';
    }
} else {
    $message = 'No verification token provided.';
}
?>
<?php include 'includes/header.php'; ?>

<div class="container" style="max-width: 600px; margin: 100px auto; text-align: center;">
    <div class="card" style="padding: 40px;">
        <?php if ($success): ?>
            <div style="color: #10b981; font-size: 48px; margin-bottom: 20px;">✅</div>
            <h2 style="color: #10b981;">Email Verified!</h2>
        <?php else: ?>
            <div style="color: #ef4444; font-size: 48px; margin-bottom: 20px;">❌</div>
            <h2 style="color: #ef4444;">Verification Failed</h2>
        <?php endif; ?>
        
        <p style="margin: 20px 0; font-size: 18px;"><?php echo htmlspecialchars($message); ?></p>
        
        <?php if ($success): ?>
            <a href="login.php" class="btn-primary" style="display: inline-block; margin-top: 20px;">
                Go to Login
            </a>
        <?php else: ?>
            <a href="register.php" class="btn-secondary" style="display: inline-block; margin-top: 20px;">
                Back to Registration
            </a>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
