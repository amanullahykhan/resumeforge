<?php
namespace App\Core;

class ResumeExtractor
{
    public static function extract(string $path, ?string $ext = null): string
    {
        if ($ext === null) $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, ['txt', 'md', 'csv'])) return (string)file_get_contents($path);
        if ($ext === 'docx') return self::docx($path);
        if ($ext === 'pdf') return self::pdf($path);
        throw new \RuntimeException('Unsupported file type .' . $ext);
    }

    private static function docx(string $p): string
    {
        $z = new \ZipArchive();
        if ($z->open($p) !== true) throw new \RuntimeException('Cannot open DOCX.');
        $xml = $z->getFromName('word/document.xml');
        $z->close();
        if ($xml === false) throw new \RuntimeException('Invalid DOCX.');
        $xml = str_replace(['</w:p>', '</w:tr>'], "\n", $xml);
        return trim(preg_replace('/[ \t]+/', ' ', strip_tags($xml)));
    }

    private static function pdf(string $p): string
    {
        $data = (string)file_get_contents($p);
        $out = [];
        if (function_exists('gzuncompress') && preg_match_all('/stream\r?\n(.*?)endstream/s', $data, $m)) {
            foreach ($m[1] as $s) {
                $u = @gzuncompress($s);
                if ($u === false) continue;
                if (preg_match_all('/\(((?:\\\\.|[^\\\\()])*)\)\s*Tj/', $u, $t))
                    foreach ($t[1] as $x) $out[] = self::unesc($x);
                if (preg_match_all('/\[([^\]]*)\]\s*TJ/', $u, $tj)) foreach ($tj[1] as $block) {
                    if (preg_match_all('/\(((?:\\\\.|[^\\\\()])*)\)/', $block, $pp))
                        foreach ($pp[1] as $x) $out[] = self::unesc($x);
                    $out[] = ' ';
                }
                $out[] = "\n";
            }
        }
        $txt = trim(implode('', $out));
        if (strlen($txt) < 40) throw new \RuntimeException('Could not extract text from this PDF (likely scanned). Upload DOCX/TXT or type manually.');
        return $txt;
    }

    private static function unesc(string $s): string
    {
        return str_replace(['\\(', '\\)', '\\\\'], ['(', ')', '\\'], $s);
    }
}