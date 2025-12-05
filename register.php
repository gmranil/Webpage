<?php
session_start();
require_once 'php/config.php';
require_once 'php/security.php';

$pageTitle = 'Register - L2 Savior';
$csrfToken = generateCSRFToken();
?>
<?php include 'includes/header.php'; ?>

<div class="container" style="max-width: 500px; margin: 80px auto;">
    <div class="card" style="padding: 40px;">
        <h2 style="font-size: 2rem; background: linear-gradient(90deg, #ffd700, #50c878); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin: 0; font-weight: 800; text-align: center;">Create Account</h2>
        <p style="text-align: center; color: #9ca3af; margin-bottom: 30px;">Join L2 Savior today</p>
        
        <form id="registerForm" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div style="margin-bottom: 20px;">
                <label for="username" style="display: block; margin-bottom: 8px; color: #d4af37;">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required 
                    pattern="[a-zA-Z0-9_]{3,16}"
                    title="3-16 characters (letters, numbers, underscore)"
                    style="width: 100%; padding: 12px; background: rgba(0,0,0,0.3); border: 1px solid #d4af37; border-radius: 5px; color: #fff; font-size: 16px;"
                >
                <small style="color: #9ca3af; font-size: 12px;">3-16 characters (letters, numbers, underscore only)</small>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="email" style="display: block; margin-bottom: 8px; color: #d4af37;">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required
                    style="width: 100%; padding: 12px; background: rgba(0,0,0,0.3); border: 1px solid #d4af37; border-radius: 5px; color: #fff; font-size: 16px;"
                >
                <small style="color: #9ca3af; font-size: 12px;">You will receive a verification email</small>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="password" style="display: block; margin-bottom: 8px; color: #d4af37;">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    minlength="6"
                    style="width: 100%; padding: 12px; background: rgba(0,0,0,0.3); border: 1px solid #d4af37; border-radius: 5px; color: #fff; font-size: 16px;"
                >
                <small style="color: #9ca3af; font-size: 12px;">Minimum 6 characters</small>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="password_confirm" style="display: block; margin-bottom: 8px; color: #d4af37;">Confirm Password</label>
                <input 
                    type="password" 
                    id="password_confirm" 
                    name="password_confirm" 
                    required
                    minlength="6"
                    style="width: 100%; padding: 12px; background: rgba(0,0,0,0.3); border: 1px solid #d4af37; border-radius: 5px; color: #fff; font-size: 16px;"
                >
            </div>
            
            <button 
                type="submit" 
                id="submitBtn"
                class="btn-primary" 
                style="width: 100%; padding: 14px; font-size: 16px; font-weight: bold; margin-top: 10px;"
            >
                Create Account
            </button>
        </form>
        
        <p style="text-align: center; margin-top: 25px; color: #9ca3af;">
            Already have an account? 
            <a href="login.php" style="color: #d4af37; text-decoration: none;">Login here</a>
        </p>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Jelszó egyezés ellenőrzése
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    
    if (password !== passwordConfirm) {
        alert('❌ Passwords do not match!');
        return;
    }
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span style="display:inline-block;width:16px;height:16px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin 0.6s linear infinite;margin-right:8px;"></span> Registering...';
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('php/register_process.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Sikeres regisztráció
            alert('✅ Registration successful!\n\n' + result.message);
            window.location.href = 'login.php';
        } else {
            // Hiba
            alert('❌ ' + result.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
        
    } catch (error) {
        console.error('Registration error:', error);
        alert('❌ Registration failed. Please try again.');
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
