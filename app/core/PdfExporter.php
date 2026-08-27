<?php
namespace App\Core;

/**
 * PDF exporter using mPDF — generates pixel-accurate PDFs from the same
 * HTML that the preview iframe renders. mPDF handles table-based layouts,
 * inline SVGs, data-URI images and CSS background colors far better than
 * Dompdf, eliminating the shrunk-content and blank-page issues.
 */
class PdfExporter
{
    private const MPDF_CLASS = '\Mpdf\Mpdf';

    public static function available(): bool
    {
        return class_exists(self::MPDF_CLASS);
    }

    /**
     * Build PDF-optimised HTML from the resume data.
     *
     * We take the same HTML output as Renderer::document() (which works
     * perfectly in the browser) and apply only minimal overrides:
     *  - Strip Google Fonts <link> (mPDF can't fetch them; we map to safe fonts)
     *  - Add @page rule with zero margins (the template has its own padding)
     *  - Remove box-shadow (not supported in print)
     *  - Fix .sheet to fill 100% width without min-height forcing extra pages
     */
    private static function pdfHtml(array $d): string
    {
        $html = Renderer::document($d);

        // Strip Google Fonts link tag (mPDF ships its own core fonts)
        $html = preg_replace('/<link[^>]+fonts\.googleapis\.com[^>]*>/i', '', $html);

        // Inject PDF-specific CSS overrides right before </style>
        $pdfCss = <<<'CSS'
/* === mPDF overrides === */
@page { margin: 0; }
html, body { margin: 0; padding: 0; background: #fff; }
body.rf-preview { margin: 0; padding: 0; background: #fff; }
.sheet { width: 100%; min-height: 0 !important; margin: 0; box-shadow: none; }
.photo img { object-fit: cover; }
CSS;
        $html = str_replace('</style>', $pdfCss . "\n</style>", $html);

        return $html;
    }

    /**
     * Export resume data to a PDF file.
     *
     * @return string|null PDF file path on success, null if mPDF is not installed
     */
    public static function export(array $d, string $dest): ?string
    {
        if (!self::available()) {
            return null;
        }

        try {
            // Determine temp directory inside the project (shared-host safe)
            $tmpDir = RF_STORAGE . '/mpdf_tmp';
            if (!is_dir($tmpDir)) {
                @mkdir($tmpDir, 0775, true);
            }

            $mpdf = new \Mpdf\Mpdf([
                'mode'             => 'utf-8',
                'format'           => 'A4',
                'orientation'      => 'P',
                'margin_left'      => 0,
                'margin_right'     => 0,
                'margin_top'       => 0,
                'margin_bottom'    => 0,
                'margin_header'    => 0,
                'margin_footer'    => 0,
                'default_font'     => 'helvetica',
                'tempDir'          => $tmpDir,
                'allow_charset_conversion' => true,
                'autoPageBreak'    => true,
            ]);

            // Map user's chosen web font to best mPDF built-in
            $font = $d['theme']['font'] ?? 'Inter';
            $safeMap = [
                'Inter'            => 'helvetica',
                'Roboto'           => 'helvetica',
                'Lato'             => 'helvetica',
                'Space Grotesk'    => 'helvetica',
                'Merriweather'     => 'times',
                'Playfair Display' => 'times',
            ];
            $mpdf->SetDefaultFont($safeMap[$font] ?? 'helvetica');

            $html = self::pdfHtml($d);
            $mpdf->WriteHTML($html);

            $pdfContent = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);

            if (!is_string($pdfContent) || strlen($pdfContent) < 500) {
                return null;
            }

            file_put_contents($dest, $pdfContent);
            return is_file($dest) ? $dest : null;

        } catch (\Throwable $e) {
            @unlink($dest);
            return null;
        }
    }
}