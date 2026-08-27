<?php
namespace App\Core;

class ExportManager
{
    private const FORMATS = ['pdf', 'docx', 'png', 'html'];

    public static function export(array $d, string $format): array
    {
        $format = strtolower(trim($format));
        if (!in_array($format, self::FORMATS, true)) {
            throw new \RuntimeException('Unknown export format.');
        }
        if (!is_dir(RF_EXPORTS)) @mkdir(RF_EXPORTS, 0775, true);

        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower((string)($d['profile']['name'] ?? 'resume'))) ?: 'resume';
        $base = RF_EXPORTS . '/' . $slug . '-' . date('Ymd-His');

        switch ($format) {
            case 'html':
                file_put_contents($base . '.html', Renderer::document($d));
                return self::result($base . '.html');

            case 'docx':
                if (!DocxExporter::available()) {
                    throw new \RuntimeException('PHPWord is not installed — run "composer install" or "composer require phpoffice/phpword" to enable DOCX export.');
                }
                DocxExporter::export($d, $base . '.docx');
                return self::result($base . '.docx');

            case 'pdf':
                $pdf = PdfExporter::export($d, $base . '.pdf');
                if ($pdf !== null) return self::result($pdf);
                // Fallback: export print-ready HTML when mPDF is not installed
                file_put_contents($base . '.html', Renderer::document($d));
                return self::result(
                    $base . '.html',
                    'mPDF not installed — exported print-ready HTML instead. Open it and use "Save as PDF", or run: composer require mpdf/mpdf'
                );

            case 'png':
                $pdf = PdfExporter::export($d, $base . '.pdf');
                if ($pdf === null) {
                    // No PDF engine: signal client to use html2canvas
                    return ['ok' => true, 'clientFallback' => 'png', 'note' => 'PNG export requires mPDF. Using client-side capture instead.'];
                }
                $png = ImageExporter::export($d, $pdf, $base . '.png');
                if ($png !== null) {
                    @unlink($pdf); // Clean up intermediate PDF
                    return self::result($png);
                }
                // Imagick/GS not available: signal client-side capture
                @unlink($pdf);
                return ['ok' => true, 'clientFallback' => 'png', 'note' => 'Server lacks Imagick/Ghostscript. Using client-side capture.'];
        }
        throw new \RuntimeException('Unknown export format.');
    }

    private static function result(string $path, ?string $note = null): array
    {
        $out = ['url' => 'uploads/exports/' . basename($path)];
        if ($note !== null) $out['note'] = $note;
        return $out;
    }
}