<?php
namespace App\Core;

class AddonManager
{
    private static array $addons = [];

    /**
     * Initialize the addon system by scanning the addons directory
     * and loading any active addons.
     */
    public static function init(): void
    {
        $dir = dirname(__DIR__) . '/addons';
        if (!is_dir($dir)) return;

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $addonFile = $dir . '/' . $item . '/addon.php';
            if (is_file($addonFile)) {
                // Read addon metadata (simple comment parsing)
                $content = file_get_contents($addonFile, false, null, 0, 1024);
                $meta = self::parseMetadata($content);
                
                self::$addons[$item] = [
                    'id' => $item,
                    'name' => $meta['Name'] ?? $item,
                    'description' => $meta['Description'] ?? '',
                    'version' => $meta['Version'] ?? '1.0',
                    'file' => $addonFile
                ];
                
                // Load the addon
                require_once $addonFile;
            }
        }
    }

    /**
     * Parse WordPress-style metadata headers from the top of the PHP file
     */
    private static function parseMetadata(string $content): array
    {
        $meta = [];
        if (preg_match_all('/^[ \t]*\*[ \t]*([A-Za-z]+):[ \t]*(.+)$/m', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $meta[$match[1]] = trim($match[2]);
            }
        }
        return $meta;
    }

    /**
     * Get all loaded addons
     */
    public static function all(): array
    {
        return self::$addons;
    }
}
