<?php
require __DIR__ . '/app/bootstrap.php';
use App\Core\Auth;

if (Auth::check()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $pass = $_POST['password'] ?? '';
    if (Auth::login($email, $pass)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login - ResumeForge Pro</title>
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        body { background: #f8fafc; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .auth-card { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        .auth-card h1 { margin: 0 0 20px 0; font-size: 24px; color: #0f172a; }
        .auth-card .form-group { margin-bottom: 20px; text-align: left; }
        .auth-card label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: #475569; }
        .auth-card input { width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; }
        .auth-card .btn.primary { width: 100%; padding: 12px; font-size: 15px; margin-top: 10px; }
        .auth-card .error { color: #dc2626; background: #fee2e2; padding: 10px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; }
        .auth-card .links { margin-top: 20px; font-size: 14px; color: #64748b; }
        .auth-card .links a { color: #4f46e5; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="auth-card">
        <h1>Welcome Back</h1>
        <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn primary">Sign In</button>
        </form>
        <div class="links">
            Don't have an account? <a href="register.php">Sign up</a>
        </div>
    </div>
</body>
</html>
