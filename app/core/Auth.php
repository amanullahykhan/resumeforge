<?php
namespace App\Core;

class Auth
{
    /**
     * Get the currently logged-in user.
     * Returns associative array of user data or null if not logged in.
     */
    public static function user(): ?array
    {
        Session::start();
        if (empty($_SESSION['user_id'])) return null;
        
        $pdo = Database::pdo();
        if (!$pdo) return null;
        
        $st = $pdo->prepare('SELECT id, email, is_pro, license_key FROM users WHERE id = ?');
        $st->execute([$_SESSION['user_id']]);
        $user = $st->fetch(\PDO::FETCH_ASSOC);
        
        return $user ?: null;
    }

    /**
     * Check if a user is logged in.
     */
    public static function check(): bool
    {
        return self::user() !== null;
    }

    /**
     * Ensure the user is logged in, otherwise redirect to login.
     */
    public static function requireLogin(): array
    {
        $user = self::user();
        if (!$user) {
            header('Location: login.php');
            exit;
        }
        return $user;
    }

    /**
     * Ensure the user is logged in AND has a Pro license.
     */
    public static function requirePro(): array
    {
        $user = self::requireLogin();
        if (empty($user['is_pro'])) {
            // For API endpoints, we might want to return JSON instead of redirecting.
            // But this is a generic middleware.
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                rf_json(['ok' => false, 'error' => 'This is a PRO feature. Please upgrade your license in the dashboard.'], 403);
            }
            die('This is a PRO feature. Please upgrade your license in the dashboard.');
        }
        return $user;
    }

    /**
     * Attempt to log in a user.
     * Returns ['ok' => true] or ['ok' => false, 'error' => '...']
     */
    public static function login(string $email, string $password): array
    {
        $pdo = Database::pdo();
        if (!$pdo) return ['ok' => false, 'error' => 'Database error'];
        
        $st = $pdo->prepare('SELECT id, password, is_verified FROM users WHERE email = ?');
        $st->execute([strtolower(trim($email))]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        
        if ($row && password_verify($password, $row['password'])) {
            // Backwards compatibility for older accounts that don't have is_verified column
            if (isset($row['is_verified']) && $row['is_verified'] == 0) {
                return ['ok' => false, 'error' => 'Please verify your email address before logging in.'];
            }
            Session::start();
            $_SESSION['user_id'] = $row['id'];
            session_regenerate_id(true);
            return ['ok' => true];
        }
        return ['ok' => false, 'error' => 'Invalid email or password.'];
    }

    /**
     * Register a new user.
     * $data should contain: email, password, name, phone, address, country, zipcode
     */
    public static function register(array $data): ?string
    {
        $pdo = Database::pdo();
        if (!$pdo) return null;
        
        $email = strtolower(trim($data['email'] ?? ''));
        $pass = $data['password'] ?? '';
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(16));
        
        try {
            $st = $pdo->prepare('INSERT INTO users (email, password, created_at, name, phone, address, country, zipcode, is_verified, verification_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?)');
            $st->execute([
                $email, $hash, time(),
                trim($data['name'] ?? ''),
                trim($data['phone'] ?? ''),
                trim($data['address'] ?? ''),
                trim($data['country'] ?? ''),
                trim($data['zipcode'] ?? ''),
                $token
            ]);
            
            // Do NOT auto-login. They must verify email first.
            return $token;
        } catch (\PDOException $e) {
            // Likely UNIQUE constraint violation on email
            return null;
        }
    }

    /**
     * Log out the current user.
     */
    public static function logout(): void
    {
        Session::start();
        $_SESSION = [];
        session_destroy();
    }
}
