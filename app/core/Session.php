<?php
namespace App\Core;
class Session {
    public static function start(): void {
        if (session_status() === PHP_SESSION_ACTIVE) return;
        session_name('RFSESS');
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
        session_start();
    }
    public static function draftId(): string {
        self::start();
        if (empty($_SESSION['rf_draft'])) $_SESSION['rf_draft'] = bin2hex(random_bytes(8));
        return $_SESSION['rf_draft'];
    }
}