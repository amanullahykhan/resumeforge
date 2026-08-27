<?php
namespace App\Core;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font as FontStyle;

/**
 * DOCX exporter using PHPWord — generates proper .docx files with
 * embedded images, styled tables, background colors and correct fonts.
 * Replaces the hand-built OOXML string approach that produced deformed
 * documents with no image support.
 */
class DocxExporter
{
    public static function available(): bool
    {
        return class_exists('\PhpOffice\PhpWord\PhpWord');
    }

    /**
     * Convert hex color (with or without #) to 6-digit hex for PHPWord.
     */
    private static function hex(string $color): string
    {
        return ltrim($color, '#');
    }

    /**
     * Resolve the photo source to an absolute file path.
     * Returns null if no valid local photo is found.
     */
    private static function resolvePhoto(array $d): ?string
    {
        $src = $d['profile']['photo'] ?? '';
        if (empty($src)) {
            return null;
        }

        // Data-URI: decode to a temp file
        if (preg_match('#^data:image/([a-z]+);base64,(.+)$#i', $src, $m)) {
            $ext = $m[1] === 'jpeg' ? 'jpg' : $m[1];
            $tmp = RF_STORAGE . '/docx_photo_tmp.' . $ext;
            $data = base64_decode($m[2]);
            if ($data !== false) {
                file_put_contents($tmp, $data);
                return is_file($tmp) ? $tmp : null;
            }
            return null;
        }

        // Remote URL
        if (preg_match('#^https?://#', $src)) {
            return $src;
        }

        // Local relative path
        $f = RF_ROOT . '/' . ltrim($src, '/');
        return is_file($f) ? $f : null;
    }

    public static function export(array $d, string $dest): string
    {
        $p = array_merge(Database::defaults()['profile'], $d['profile'] ?? []);
        $t = array_merge(Database::themeDefaults(), $d['theme'] ?? []);
        $accent  = self::hex($t['accent'] ?? '#2563eb');
        $sideBg  = self::hex($t['sidebar'] ?? '#1e293b');
        $sideTxt = self::hex($t['side_text'] ?? '#f8fafc');
        $fontName = $t['font'] ?? 'Arial';
        $basePt  = max(8, min(16, (int)($t['size'] ?? 13)));

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName($fontName);
        $phpWord->setDefaultFontSize($basePt);

        // Section with minimal margins (A4)
        $section = $phpWord->addSection([
            'pageSizeW'   => 11906, // A4 width in twips
            'pageSizeH'   => 16838, // A4 height in twips
            'marginTop'    => 284,
            'marginBottom' => 284,
            'marginLeft'   => 284,
            'marginRight'  => 284,
        ]);

        // === TWO-COLUMN TABLE ===
        $table = $section->addTable([
            'borderSize'  => 0,
            'cellMarginTop'    => 120,
            'cellMarginBottom' => 120,
            'cellMarginLeft'   => 140,
            'cellMarginRight'  => 140,
            'width'       => 100 * 50, // percentage
            'unit'        => 'pct',
        ]);

        $table->addRow();

        // --- LEFT CELL (sidebar) ---
        $leftCell = $table->addCell(3600, [
            'bgColor'     => $sideBg,
            'valign'      => 'top',
        ]);

        // Photo
        $photoPath = self::resolvePhoto($d);
        if ($photoPath !== null) {
            try {
                $leftCell->addImage($photoPath, [
                    'width'         => 80,
                    'height'        => 80,
                    'alignment'     => Jc::CENTER,
                    'wrappingStyle' => 'inline',
                ]);
            } catch (\Throwable $e) {
                // Skip photo if it fails to load
            }
        }

        // Name
        $leftCell->addText(
            $p['name'] ?: 'Your Name',
            ['bold' => true, 'size' => $basePt + 6, 'color' => $sideTxt, 'name' => $fontName],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 40]
        );

        // Title
        if (!empty($p['title'])) {
            $leftCell->addText(
                $p['title'],
                ['size' => $basePt - 1, 'color' => $accent, 'name' => $fontName],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 160]
            );
        }

        // Contact
        $contact = array_filter([
            $p['email'] ?? '', $p['phone'] ?? '', $p['location'] ?? '',
            $p['website'] ?? '', $p['linkedin'] ?? '', $p['github'] ?? ''
        ]);
        if ($contact) {
            self::sectionHeading($leftCell, 'Contact', $accent, $sideTxt, $fontName, $basePt);
            foreach ($contact as $c) {
                $leftCell->addText($c, ['size' => $basePt - 2, 'color' => $sideTxt, 'name' => $fontName], ['spaceAfter' => 30]);
            }
            $leftCell->addText('', [], ['spaceAfter' => 100]);
        }

        // Education (sidebar)
        $edu = array_filter($d['education'] ?? [], fn($e) => trim(($e['degree'] ?? '') . ($e['school'] ?? '')) !== '');
        if ($edu) {
            self::sectionHeading($leftCell, 'Education', $accent, $sideTxt, $fontName, $basePt);
            foreach ($edu as $e) {
                $leftCell->addText($e['degree'] ?? '', ['bold' => true, 'size' => $basePt - 1, 'color' => $sideTxt, 'name' => $fontName], ['spaceAfter' => 20]);
                if (!empty($e['school'])) {
                    $leftCell->addText($e['school'], ['size' => $basePt - 2, 'color' => $accent, 'name' => $fontName], ['spaceAfter' => 20]);
                }
                if (!empty($e['date'])) {
                    $leftCell->addText($e['date'], ['size' => $basePt - 2, 'color' => $sideTxt, 'name' => $fontName], ['spaceAfter' => 60]);
                }
            }
            $leftCell->addText('', [], ['spaceAfter' => 100]);
        }

        // Skills (sidebar)
        $sk = array_filter($d['skills'] ?? [], fn($s) => trim($s['name'] ?? '') !== '');
        if ($sk) {
            self::sectionHeading($leftCell, 'Skills', $accent, $sideTxt, $fontName, $basePt);
            foreach ($sk as $s) {
                $leftCell->addText($s['name'], ['size' => $basePt - 1, 'color' => $sideTxt, 'name' => $fontName], ['spaceAfter' => 30]);
            }
            $leftCell->addText('', [], ['spaceAfter' => 100]);
        }

        // Languages (sidebar)
        $lg = array_filter($d['languages'] ?? [], fn($l) => trim($l['name'] ?? '') !== '');
        if ($lg) {
            self::sectionHeading($leftCell, 'Languages', $accent, $sideTxt, $fontName, $basePt);
            foreach ($lg as $l) {
                $leftCell->addText(
                    ($l['name'] ?? '') . ' (' . ($l['level'] ?? '') . ')',
                    ['size' => $basePt - 1, 'color' => $sideTxt, 'name' => $fontName],
                    ['spaceAfter' => 30]
                );
            }
            $leftCell->addText('', [], ['spaceAfter' => 100]);
        }

        // Custom sections (sidebar)
        foreach ($d['custom'] ?? [] as $c) {
            if (($c['place'] ?? 'main') !== 'side') continue;
            $lines = array_filter(array_map('trim', preg_split('/\n+/', $c['lines'] ?? '')), 'strlen');
            if (!trim($c['heading'] ?? '') || !$lines) continue;
            self::sectionHeading($leftCell, $c['heading'], $accent, $sideTxt, $fontName, $basePt);
            foreach ($lines as $l) {
                $leftCell->addText($l, ['size' => $basePt - 1, 'color' => $sideTxt, 'name' => $fontName], ['spaceAfter' => 30]);
            }
            $leftCell->addText('', [], ['spaceAfter' => 100]);
        }

        // --- RIGHT CELL (main content) ---
        $rightCell = $table->addCell(7200, ['valign' => 'top']);

        // Summary
        if (trim($p['summary'] ?? '') !== '') {
            self::sectionHeading($rightCell, 'Professional Summary', $accent, '1f2937', $fontName, $basePt);
            $rightCell->addText($p['summary'], ['size' => $basePt, 'color' => '374151', 'name' => $fontName], ['spaceAfter' => 160]);
        }

        // Experience
        $jobs = array_filter($d['experience'] ?? [], fn($j) => trim(($j['title'] ?? '') . ($j['company'] ?? '')) !== '');
        if ($jobs) {
            self::sectionHeading($rightCell, 'Experience', $accent, '1f2937', $fontName, $basePt);
            foreach ($jobs as $j) {
                // Title + date
                $titleRun = $rightCell->addTextRun(['spaceAfter' => 20]);
                $titleRun->addText(
                    ($j['title'] ?? ''),
                    ['bold' => true, 'size' => $basePt, 'color' => '1f2937', 'name' => $fontName]
                );
                if (!empty($j['date'])) {
                    $titleRun->addText(
                        '  —  ' . $j['date'],
                        ['size' => $basePt - 2, 'color' => '6b7280', 'name' => $fontName]
                    );
                }

                // Company + location
                $comp = trim(($j['company'] ?? '') . (!empty($j['location']) ? ', ' . $j['location'] : ''));
                if ($comp) {
                    $rightCell->addText($comp, ['size' => $basePt - 1, 'color' => $accent, 'bold' => true, 'name' => $fontName], ['spaceAfter' => 40]);
                }

                // Bullets
                foreach (preg_split('/\n+/', $j['bullets'] ?? '') as $bl) {
                    if (trim($bl) !== '') {
                        $rightCell->addListItem(
                            trim($bl),
                            0,
                            ['size' => $basePt, 'color' => '374151', 'name' => $fontName],
                            null,
                            ['spaceAfter' => 20]
                        );
                    }
                }
                $rightCell->addText('', [], ['spaceAfter' => 100]);
            }
        }

        // Custom sections (main)
        foreach ($d['custom'] ?? [] as $c) {
            if (($c['place'] ?? 'main') !== 'main') continue;
            $lines = array_filter(array_map('trim', preg_split('/\n+/', $c['lines'] ?? '')), 'strlen');
            if (!trim($c['heading'] ?? '') || !$lines) continue;
            self::sectionHeading($rightCell, $c['heading'], $accent, '1f2937', $fontName, $basePt);
            foreach ($lines as $l) {
                $rightCell->addText($l, ['size' => $basePt, 'color' => '374151', 'name' => $fontName], ['spaceAfter' => 30]);
            }
            $rightCell->addText('', [], ['spaceAfter' => 100]);
        }

        // === SAVE ===
        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($dest);
        return $dest;
    }

    /**
     * Add a styled section heading with accent-colored bottom border.
     */
    private static function sectionHeading($container, string $text, string $accent, string $textColor, string $fontName, int $basePt): void
    {
        $container->addText(
            strtoupper($text),
            [
                'bold'  => true,
                'size'  => $basePt - 1,
                'color' => $accent,
                'name'  => $fontName,
            ],
            [
                'spaceAfter'   => 60,
                'borderBottomSize' => 6,
                'borderBottomColor' => $accent,
            ]
        );
    }
}