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
     */
    public static function login(string $email, string $password): bool
    {
        $pdo = Database::pdo();
        if (!$pdo) return false;
        
        $st = $pdo->prepare('SELECT id, password FROM users WHERE email = ?');
        $st->execute([strtolower(trim($email))]);
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        
        if ($row && password_verify($password, $row['password'])) {
            Session::start();
            $_SESSION['user_id'] = $row['id'];
            session_regenerate_id(true);
            return true;
        }
        return false;
    }

    /**
     * Register a new user.
     */
    public static function register(string $email, string $password): bool
    {
        $pdo = Database::pdo();
        if (!$pdo) return false;
        
        $email = strtolower(trim($email));
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $st = $pdo->prepare('INSERT INTO users (email, password, created_at) VALUES (?, ?, ?)');
            $st->execute([$email, $hash, time()]);
            
            // Auto login after registration
            Session::start();
            $_SESSION['user_id'] = $pdo->lastInsertId();
            return true;
        } catch (\PDOException $e) {
            // Likely UNIQUE constraint violation on email
            return false;
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
