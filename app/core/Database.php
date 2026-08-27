<?php
namespace App\Core;
class Database {
    private static $pdo = null;
    public static function pdo(): ?\PDO {
        if (!extension_loaded('pdo_sqlite')) return null;
        if (self::$pdo === null) {
            self::$pdo = new \PDO('sqlite:' . RF_STORAGE . '/resumeforge.sqlite');
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // Core SaaS Tables
            self::$pdo->exec('CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT, 
                email TEXT UNIQUE, 
                password TEXT, 
                license_key TEXT, 
                is_pro INTEGER DEFAULT 0, 
                created_at INTEGER
            )');
            
            self::$pdo->exec('CREATE TABLE IF NOT EXISTS resumes (
                id TEXT PRIMARY KEY, 
                user_id INTEGER, 
                title TEXT, 
                data TEXT, 
                updated_at INTEGER,
                FOREIGN KEY(user_id) REFERENCES users(id)
            )');
            
            // Legacy/Guest drafts
            self::$pdo->exec('CREATE TABLE IF NOT EXISTS drafts (id TEXT PRIMARY KEY, data TEXT, updated_at INTEGER)');
        }
        return self::$pdo;
    }
    private static function file(string $id): string {
        return RF_STORAGE . '/draft-' . preg_replace('/[^a-z0-9_-]/i', '', $id) . '.json';
    }
    public static function load(string $id): array {
        if ($p = self::pdo()) {
            $user = Auth::user();
            if ($user) {
                // Multi-tenant load
                $st = $p->prepare('SELECT data FROM resumes WHERE id = ? AND user_id = ?');
                $st->execute([$id, $user['id']]);
                $r = $st->fetchColumn();
            } else {
                // Legacy/Guest load
                $st = $p->prepare('SELECT data FROM drafts WHERE id = ?');
                $st->execute([$id]);
                $r = $st->fetchColumn();
            }
            if ($r) return json_decode($r, true) ?: [];
        } elseif (is_file(self::file($id))) {
            return json_decode((string)file_get_contents(self::file($id)), true) ?: [];
        }
        return [];
    }
    public static function save(string $id, array $data): void {
        $data['updated_at'] = time();
        if ($p = self::pdo()) {
            $user = Auth::user();
            if ($user) {
                // Update existing resume
                $st = $p->prepare('UPDATE resumes SET data = ?, updated_at = ? WHERE id = ? AND user_id = ?');
                $st->execute([json_encode($data), time(), $id, $user['id']]);
            } else {
                $st = $p->prepare('REPLACE INTO drafts(id,data,updated_at) VALUES(?,?,?)');
                $st->execute([$id, json_encode($data), time()]);
            }
        } else file_put_contents(self::file($id), json_encode($data, JSON_PRETTY_PRINT));
    }
    public static function themeDefaults(): array {
        return ['template' => 'modern', 'accent' => '#4f46e5', 'sidebar' => '#0f172a', 'side_text' => '#e2e8f0',
            'size' => 13, 'font' => 'Inter', 'heading' => 'bar', 'skill_style' => 'bars',
            'frame' => 'circle', 'frame_border' => 'ring', 'upper' => true];
    }
    public static function defaults(): array {
        return ['step' => 1,
            'profile' => ['name' => '', 'title' => '', 'summary' => '', 'email' => '', 'phone' => '',
                'location' => '', 'website' => '', 'linkedin' => '', 'github' => '', 'photo' => ''],
            'experience' => [], 'education' => [], 'skills' => [], 'languages' => [], 'custom' => [],
            'theme' => self::themeDefaults()];
    }
    public static function draft(): array {
        $id = $_GET['id'] ?? Session::draftId();
        $d = self::load($id);
        if (!$d) { $d = self::defaults(); self::save($id, $d); }
        $d['theme'] = array_merge(self::themeDefaults(), $d['theme'] ?? []);
        $d['profile'] = array_merge(self::defaults()['profile'], $d['profile'] ?? []);
        foreach (['experience', 'education', 'skills', 'languages', 'custom'] as $k) {
            if (!isset($d[$k]) || !is_array($d[$k])) $d[$k] = [];
        }
        return $d;
    }
}