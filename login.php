<?php
session_start();
require_once 'php/config.php';
require_once 'php/security.php';

$pageTitle = 'Login - L2 Savior';
$csrfToken = generateCSRFToken();
?>
<?php include 'includes/header.php'; ?>

<div class="container" style="max-width: 500px; margin: 100px auto;">
    <div class="card" style="padding: 40px;">
        <h2 style="font-size: 2rem; background: linear-gradient(90deg, #ffd700, #50c878); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin: 0; font-weight: 800; text-align: center;">Welcome Back</h2>
        <p style="text-align: center; color: #9ca3af; margin-bottom: 30px;">Login to your account</p>
        
        <form id="loginForm" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div style="margin-bottom: 20px;">
                <label for="username" style="display: block; margin-bottom: 8px; color: #d4af37;">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required 
                    autofocus
                    style="width: 100%; padding: 12px; background: rgba(0,0,0,0.3); border: 1px solid #d4af37; border-radius: 5px; color: #fff; font-size: 16px;"
                >
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="password" style="display: block; margin-bottom: 8px; color: #d4af37;">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    style="width: 100%; padding: 12px; background: rgba(0,0,0,0.3); border: 1px solid #d4af37; border-radius: 5px; color: #fff; font-size: 16px;"
                >
            </div>
            
            <div style="margin-bottom: 20px; text-align: right;">
                <a href="forgot_password.php" style="color: #10b981; text-decoration: none; font-size: 14px;">
                    Forgot password?
                </a>
            </div>
            
            <button 
                type="submit" 
                id="submitBtn"
                class="btn-primary" 
                style="width: 100%; padding: 14px; font-size: 16px; font-weight: bold;"
            >
                Login
            </button>
        </form>
        
        <p style="text-align: center; margin-top: 25px; color: #9ca3af;">
            Don't have an account? 
            <a href="register.php" style="color: #d4af37; text-decoration: none;">Register here</a>
        </p>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span style="display:inline-block;width:16px;height:16px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin 0.6s linear infinite;margin-right:8px;"></span> Logging in...';
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('php/login_process.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Sikeres login - átirányítás
            window.location.href = result.redirect;
        } else {
            // Hiba
            alert('❌ ' + result.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
        
    } catch (error) {
        console.error('Login error:', error);
        alert('❌ Login failed. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});
</script>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<?php include 'includes/footer.php'; ?>
