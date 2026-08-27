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
    public static function document(array $d): string {
        $cssFile = RF_ROOT . '/assets/css/sheet.css';
        $css = is_file($cssFile) ? (string)file_get_contents($cssFile) : '';
        $title = htmlspecialchars((string)($d['profile']['name'] ?? 'Resume'), ENT_QUOTES, 'UTF-8');
        $fonts = '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Roboto:wght@400;500;700&family=Merriweather:wght@400;700&family=Playfair+Display:wght@500;700&family=Space+Grotesk:wght@400;600&family=Lato:wght@400;700&display=swap" rel="stylesheet">';
        
        $watermark = '';
        if (!\App\Core\LicenseManager::isValid()) {
            $watermark = '
            <htmlpagefooter name="freeWatermark">
                <div style="text-align: center; color: #94a3b8; font-size: 11px; padding-bottom: 5px; font-family: sans-serif;">
                    Created with ResumeForge.com
                </div>
            </htmlpagefooter>
            <sethtmlpagefooter name="freeWatermark" value="on" />
            
            <div style="text-align:center; padding:15px; margin-top:20px; color:#64748b; font-size:12px;" class="hide-in-mpdf">
                Built with ResumeForge.com
            </div>
            ';
        }

        return '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . $title . ' — Resume</title>'
            . $fonts . '<style>' . $css . ' @media print { .hide-in-mpdf { display:none; } }</style></head>'
            . '<body class="rf-preview">' . self::sheet($d) . $watermark . '</body></html>';
    }
}