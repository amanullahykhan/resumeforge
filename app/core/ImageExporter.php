<?php
namespace App\Core;

/**
 * PNG exporter — converts PDF page 1 to PNG using Imagick (if available).
 * Falls back gracefully: when Imagick is missing the ExportManager returns
 * a signal so the client can use html2canvas instead.
 */
class ImageExporter
{
    private static function cmdAvailable(string $fn): bool
    {
        if (!function_exists($fn)) return false;
        $dis = array_map('trim', explode(',', (string)ini_get('disable_functions')));
        return !in_array($fn, $dis, true);   // shared hosts often disable exec/shell_exec
    }

    /**
     * @return string|null PNG path on success, null when conversion not possible
     */
    public static function export(array $d, string $pdfPath, string $dest): ?string
    {
        if (!is_file($pdfPath)) return null;

        // Method 1: Imagick (best quality)
        if (class_exists('Imagick')) {
            try {
                $im = new \Imagick();
                $im->setResolution(200, 200);
                $im->readImage($pdfPath . '[0]');
                $im->setImageFormat('png');
                $im->setImageBackgroundColor('white');
                $im->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
                $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                $im->writeImage($dest);
                $im->clear();
                $im->destroy();
                return is_file($dest) ? $dest : null;
            } catch (\Throwable $e) { /* fall through to Ghostscript */ }
        }

        // Method 2: Ghostscript CLI
        if (self::cmdAvailable('shell_exec') || self::cmdAvailable('exec')) {
            // Try common Ghostscript locations
            $gsBin = '';
            if (PHP_OS_FAMILY === 'Windows') {
                foreach (['gswin64c', 'gswin32c', 'gs'] as $gs) {
                    $check = trim((string)@shell_exec("where $gs 2>NUL"));
                    if ($check !== '') { $gsBin = $check; break; }
                }
            } else {
                $gsBin = trim((string)@shell_exec('command -v gs 2>/dev/null'));
            }

            if ($gsBin !== '') {
                @exec(
                    escapeshellarg($gsBin)
                    . ' -sDEVICE=png16m -r200 -dFirstPage=1 -dLastPage=1'
                    . ' -dNOPAUSE -dBATCH -dSAFER'
                    . ' -o ' . escapeshellarg($dest)
                    . ' ' . escapeshellarg($pdfPath)
                    . ' 2>&1'
                );
                if (is_file($dest) && filesize($dest) > 100) {
                    return $dest;
                }
            }
        }

        return null;
    }
}