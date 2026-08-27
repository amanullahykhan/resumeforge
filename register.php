<?php
require __DIR__ . '/app/bootstrap.php';
use App\Core\Auth;

if (Auth::check()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$simulatedLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $pass = $_POST['password'] ?? '';
    
    // Strict PHP-side validation
    if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $pass)) {
        $error = 'Password does not meet complexity requirements.';
    } else {
        $token = Auth::register($_POST);
        if ($token) {
            $simulatedLink = "login.php?verify=" . urlencode($token);
        } else {
            $error = 'Registration failed. Email might already be in use.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sign Up - ResumeForge Pro</title>
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        body { background: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 40px 20px; }
        .auth-card { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
        .auth-card h1 { margin: 0 0 20px 0; font-size: 24px; color: #0f172a; text-align: center; }
        .auth-card p.sub { color: #64748b; text-align: center; margin-bottom: 24px; font-size: 14px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: #475569; }
        .form-group input { width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; }
        .form-group input:invalid:not(:placeholder-shown) { border-color: #ef4444; }
        .auth-card .btn.primary { width: 100%; padding: 12px; font-size: 15px; margin-top: 10px; }
        .auth-card .error { color: #dc2626; background: #fee2e2; padding: 10px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; }
        .auth-card .success-box { color: #047857; background: #d1fae5; padding: 20px; border-radius: 6px; margin-bottom: 20px; text-align: center; }
        .auth-card .success-box a { color: #059669; font-weight: bold; }
        .auth-card .links { margin-top: 20px; font-size: 14px; color: #64748b; text-align: center; }
        .auth-card .links a { color: #4f46e5; text-decoration: none; font-weight: 600; }
        #pw-strength { margin-top: 6px; font-size: 12px; color: #94a3b8; }
        .req { color: #ef4444; }
    </style>
</head>
<body>
    <div class="auth-card">
        <h1>Create Account</h1>
        
        <?php if ($simulatedLink): ?>
            <div class="success-box">
                <h3 style="margin-top:0">Registration Successful!</h3>
                <p>We've sent a verification link to your email address.</p>
                <div style="background:#fff; padding:10px; border:1px dashed #10b981; margin-top:15px; font-size:13px; word-break:break-all;">
                    <b>[SIMULATED EMAIL INBOX]</b><br>
                    Please click here to verify your account:<br>
                    <a href="<?= htmlspecialchars($simulatedLink) ?>">Verify Email Address</a>
                </div>
            </div>
        <?php else: ?>
            <p class="sub">Join ResumeForge Pro to build unlimited resumes.</p>
            <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <form method="POST" action="register.php" id="regForm">
                <div class="grid-2">
                    <div class="form-group">
                        <label>Full Name <span class="req">*</span></label>
                        <input type="text" name="name" required placeholder="John Doe">
                    </div>
                    <div class="form-group">
                        <label>Phone Number <span class="req">*</span></label>
                        <input type="tel" name="phone" required pattern="^\+?[0-9\s\-]{7,15}$" placeholder="+1 234 567 8900" title="Enter a valid phone number (7-15 digits)">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Street Address <span class="req">*</span></label>
                    <input type="text" name="address" required placeholder="123 Main St, Apt 4B">
                </div>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label>Country <span class="req">*</span></label>
                        <input type="text" name="country" required placeholder="United States">
                    </div>
                    <div class="form-group">
                        <label>Zip / Postal Code <span class="req">*</span></label>
                        <input type="text" name="zipcode" required pattern="^[A-Za-z0-9\s\-]{3,10}$" placeholder="10001">
                    </div>
                </div>

                <div class="form-group" style="margin-top:10px; border-top:1px solid #e2e8f0; padding-top:20px;">
                    <label>Email Address <span class="req">*</span></label>
                    <input type="email" name="email" required placeholder="john@example.com">
                </div>
                <div class="form-group">
                    <label>Password <span class="req">*</span></label>
                    <input type="password" name="password" id="pw" required 
                           pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}"
                           title="Must contain at least one number, one uppercase and lowercase letter, one special character, and at least 8 or more characters">
                    <div id="pw-strength">Minimum 8 chars, 1 uppercase, 1 number, 1 special character.</div>
                </div>
                <button type="submit" class="btn primary">Sign Up</button>
            </form>
            <div class="links">
                Already have an account? <a href="login.php">Sign in</a>
            </div>
            
            <script>
                document.getElementById('pw').addEventListener('input', function(e) {
                    const v = e.target.value;
                    const st = document.getElementById('pw-strength');
                    let passed = 0;
                    if(v.length >= 8) passed++;
                    if(/[A-Z]/.test(v)) passed++;
                    if(/[0-9]/.test(v)) passed++;
                    if(/[\W_]/.test(v)) passed++;
                    
                    if(passed === 0) { st.textContent = 'Minimum 8 chars, 1 uppercase, 1 number, 1 special character.'; st.style.color = '#94a3b8'; }
                    else if(passed < 3) { st.textContent = 'Weak password'; st.style.color = '#ef4444'; }
                    else if(passed === 3) { st.textContent = 'Good password'; st.style.color = '#eab308'; }
                    else { st.textContent = 'Strong password! ✓'; st.style.color = '#10b981'; }
                });
            </script>
        <?php endif; ?>
    </div>
</body>
</html>
