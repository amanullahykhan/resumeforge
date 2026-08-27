<?php
namespace App\Templates;
class TemplateRegistry {
    private static $cache = null;
    public static function all(): array {
        if (self::$cache) return self::$cache;
        $out = [];
        foreach (array_merge(glob(__DIR__ . '/*.php') ?: [], glob(__DIR__ . '/addons/*.php') ?: []) as $f) {
            if (in_array(basename($f), ['TemplateRegistry.php', 'TemplateInterface.php'])) continue;
            $r = @include $f;
            if (is_array($r) && isset($r['render'])) $out[$r['id'] ?? basename($f, '.php')] = $r;
            elseif (is_string($r) && class_exists($r) && in_array(TemplateInterface::class, class_implements($r)))
                $out[$r::meta()['id']] = $r::meta() + ['render' => [$r, 'render']];
        }
        self::$cache = $out; return $out;
    }
    public static function get(string $id): array { $a = self::all(); return $a[$id] ?? $a['modern']; }
}