<?php
namespace App\Core;
use App\Templates\TemplateRegistry;
class Renderer {
    /** Renders just the .sheet markup using the registry-selected template addon. */
    public static function sheet(array $d): string {
        $d['theme'] = array_merge(Database::themeDefaults(), $d['theme'] ?? []);
        $d['profile'] = array_merge(Database::defaults()['profile'], $d['profile'] ?? []);
        return TemplateRegistry::get((string)($d['theme']['template'] ?? 'modern'))['render']($d);
    }
    /** Full standalone document: shared sheet.css + webfonts. Used by preview iframe, PDF engine and HTML export. */
    public static function document(array $d): string {
        $cssFile = RF_ROOT . '/assets/css/sheet.css';
        $css = is_file($cssFile) ? (string)file_get_contents($cssFile) : '';
        $title = htmlspecialchars((string)($d['profile']['name'] ?? 'Resume'), ENT_QUOTES, 'UTF-8');
        $fonts = '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Roboto:wght@400;500;700&family=Merriweather:wght@400;700&family=Playfair+Display:wght@500;700&family=Space+Grotesk:wght@400;600&family=Lato:wght@400;700&display=swap" rel="stylesheet">';
        return '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . $title . ' — Resume</title>'
            . $fonts . '<style>' . $css . '</style></head>'
            . '<body class="rf-preview">' . self::sheet($d) . '</body></html>';
    }
}