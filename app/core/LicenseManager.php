<?php
namespace App\Core;

class LicenseManager
{
    private const MASTER_SERVER = 'https://license.yourdomain.com/api/verify';

    /**
     * Verifies the license key remotely and updates local user state.
     */
    public static function verify(string $key): array
    {
        $user = Auth::user();
        if (!$user) return ['ok' => false, 'error' => 'Must be logged in'];

        $domain = $_SERVER['HTTP_HOST'] ?? 'unknown';

        // Make HTTP request to master server
        $ch = curl_init(self::MASTER_SERVER);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'key' => $key,
            'domain' => $domain,
            'email' => $user['email']
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // If the license server is totally down, fail gracefully or strictly?
        // Let's assume strict: if we can't reach it, they can't verify right now.
        if (!$resp || $httpCode !== 200) {
            // For local dev/testing without the license server built yet, we will mock success:
            // TODO: Remove this mock once Phase 4 is built.
            if ($key === 'TEST-KEY-PRO') {
                self::updateLocalLicense($user['id'], $key, 1);
                return ['ok' => true, 'message' => 'Test key activated!'];
            }
            return ['ok' => false, 'error' => 'Could not contact license server.'];
        }

        $data = json_decode($resp, true);
        if ($data['ok']) {
            self::updateLocalLicense($user['id'], $key, 1);
            return ['ok' => true, 'message' => 'License activated successfully!'];
        }

        return ['ok' => false, 'error' => $data['error'] ?? 'Invalid license'];
    }

    private static function updateLocalLicense(int $userId, string $key, int $isPro): void
    {
        $pdo = Database::pdo();
        if (!$pdo) return;
        $st = $pdo->prepare('UPDATE users SET license_key = ?, is_pro = ? WHERE id = ?');
        $st->execute([$key, $isPro, $userId]);
        
        // Refresh session
        $_SESSION['user']['license_key'] = $key;
        $_SESSION['user']['is_pro'] = $isPro;
    }

    /**
     * Checks if the current user has a valid Pro license.
     * Addons should call this before executing premium logic.
     */
    public static function isValid(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        return (bool)$user['is_pro'];
    }
}
