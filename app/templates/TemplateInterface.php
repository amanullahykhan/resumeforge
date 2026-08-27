<?php
namespace App\Templates;
/* Addon contract: a template file returns ['id','name','desc','thumb','render'=>fn(array $d):string]
   or a class implementing this interface. See README.md in this folder. */
interface TemplateInterface {
    public static function meta(): array;
    public static function render(array $d): string;
}

class Tpl {
    const ICONS = [
        'email' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/></svg>',
        'phone' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.4 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.9.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>',
        'pin' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
        'globe' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
        'linkedin' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>',
        'github' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.9a3.4 3.4 0 0 0-.9-2.6c3.1-.4 6.4-1.5 6.4-7A5.4 5.4 0 0 0 20 4.8 5.1 5.1 0 0 0 19.9 1S18.7.7 16 2.5a13.4 13.4 0 0 0-7 0C6.3.7 5.1 1 5.1 1A5.1 5.1 0 0 0 5 4.8a5.4 5.4 0 0 0-1.5 3.7c0 5.5 3.3 6.6 6.4 7A3.4 3.4 0 0 0 9 18.1V22"/></svg>'];
    public static function e($v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
    public static function font(string $f): string {
        $m = ['Inter' => "'Inter',Arial,sans-serif", 'Roboto' => "'Roboto',Arial,sans-serif",
            'Merriweather' => "'Merriweather',Georgia,serif", 'Playfair Display' => "'Playfair Display',Georgia,serif",
            'Space Grotesk' => "'Space Grotesk',Arial,sans-serif", 'Lato' => "'Lato',Arial,sans-serif"];
        return $m[$f] ?? $m['Inter'];
    }
    public static function sheetOpen(array $d): string {
        $t = $d['theme'];
        return '<div class="sheet" style="font-family:' . self::font($t['font']) . ';font-size:' . (int)$t['size'] . 'px;line-height:1.45;color:#1f2937">';
    }
    public static function secH(array $d, string $text, bool $side = false): string {
        $t = $d['theme']; $a = $t['accent']; $col = $side ? $t['side_text'] : '#1f2937';
        $sz = round(((int)$t['size']) * 1.15);
        $up = !empty($t['upper']) ? 'text-transform:uppercase;letter-spacing:1px;' : '';
        switch ($t['heading'] ?? 'bar') {
            case 'underline': $s = "border-bottom:2px solid $a;padding-bottom:4px;"; break;
            case 'pill': $s = "background:$a;color:#fff;padding:3px 10px;border-radius:5px;display:inline-block;"; $col = '#fff'; break;
            case 'box': $s = "border:1.5px solid $a;padding:3px 10px;display:inline-block;border-radius:4px;"; break;
            case 'line': $s = "border-bottom:1px solid $a;padding-bottom:5px;"; break;
            case 'plain': $s = ''; $col = $a; break;
            default: $s = "border-left:4px solid $a;padding-left:8px;";
        }
        return '<h3 class="sec-h" style="font-size:' . $sz . 'px;color:' . $col . ';margin:0 0 10px;' . $up . $s . '">' . self::e($text) . '</h3>';
    }
        public static function photo(array $d, bool $center = false): string {
        $p = $d['profile']; if (empty($p['photo'])) return '';
        $t = $d['theme'];
        $src = $p['photo'];
        /* Embed local uploads as data-URIs so the preview iframe, Dompdf PDF
           and standalone HTML all render the photo (relative paths break in
           api/preview.php and Dompdf cannot fetch them). */
        if (!preg_match('#^(https?|data):#', $src)) {
            $f = RF_ROOT . '/' . ltrim($src, '/');
            if (is_file($f)) {
                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                $mime = $ext === 'png' ? 'image/png' : ($ext === 'gif' ? 'image/gif' : 'image/jpeg');
                $src = 'data:' . $mime . ';base64,' . base64_encode((string)file_get_contents($f));
            }
        }
        $r = ['circle' => '50%', 'rounded' => '14px', 'square' => '0', 'none' => '6px'][$t['frame']] ?? '50%';
        $sh = $t['frame_border'] === 'ring' ? 'box-shadow:0 0 0 3px #fff,0 0 0 6px ' . $t['accent'] . ';'
            : ($t['frame_border'] === 'solid' ? 'box-shadow:0 0 0 3px ' . $t['accent'] . ';' : '');
        return '<div class="photo" style="border-radius:' . $r . ';' . $sh . ($center ? 'margin:0 auto 14px;' : 'margin:0 0 14px;') . '"><img src="' . self::e($src) . '"></div>';
    }
    public static function contacts(array $d, string $mode = 'stack', bool $side = false): string {
        $p = $d['profile']; $rows = [];
        foreach (['email' => 'email', 'phone' => 'phone', 'location' => 'pin', 'website' => 'globe', 'linkedin' => 'linkedin', 'github' => 'github'] as $k => $ic)
            if (!empty($p[$k])) $rows[] = '<span class="c-item" style="display:' . ($mode === 'row' ? 'inline-block;margin-right:14px' : 'block') . ';margin-bottom:4px;font-size:' . ((int)$d['theme']['size'] - 1) . 'px">' . self::ICONS[$ic] . '<span>' . self::e($p[$k]) . '</span></span>';
        if (!$rows) return '';
        return '<div style="color:' . ($side ? $d['theme']['side_text'] : '#374151') . ';margin-bottom:14px">' . implode('', $rows) . '</div>';
    }
    public static function summary(array $d): string {
        $s = trim($d['profile']['summary'] ?? ''); if ($s === '') return '';
        return '<div style="margin-bottom:12px">' . self::secH($d, 'Professional Summary') . '<p style="margin:0">' . self::e($s) . '</p></div>';
    }
    public static function experience(array $d, bool $side = false): string {
        $jobs = array_filter($d['experience'] ?? [], fn($j) => trim(($j['title'] ?? '') . ($j['company'] ?? '')) !== '');
        if (!$jobs) return '';
        $a = $d['theme']['accent']; $b = '';
        foreach ($jobs as $j) {
            if ($side) { $b .= '<div style="margin-bottom:8px"><div style="font-weight:700">' . self::e($j['title']) . '</div><div style="font-size:12px">' . self::e($j['company']) . '</div><div style="font-size:11px;opacity:.75">' . self::e($j['date']) . '</div></div>'; continue; }
            $bul = '';
            foreach (preg_split('/\n+/', $j['bullets'] ?? '') as $bl) if (trim($bl) !== '') $bul .= '<li style="margin:1px 0">' . self::e(trim($bl)) . '</li>';
            $b .= '<div style="margin-bottom:8px;overflow:hidden"><div style="font-weight:700">' . self::e($j['title'])
                . '<span style="float:right;font-weight:400;font-size:11px;color:#6b7280">' . self::e($j['date']) . '</span></div>'
                . (trim(($j['company'] ?? '') . ($j['location'] ?? '')) !== '' ? '<div style="color:' . $a . ';font-weight:600;font-size:' . ((int)$d['theme']['size'] - 1) . 'px">' . self::e($j['company']) . ($j['location'] ? ' · ' . self::e($j['location']) : '') . '</div>' : '')
                . ($bul !== '' ? '<ul style="margin:3px 0 0;padding-left:14px">' . $bul . '</ul>' : '') . '</div>';
        }
        return '<div style="margin-bottom:12px">' . self::secH($d, 'Experience', $side) . $b . '</div>';
    }
    public static function education(array $d, bool $side = false): string {
        $items = array_filter($d['education'] ?? [], fn($x) => trim(($x['degree'] ?? '') . ($x['school'] ?? '')) !== '');
        if (!$items) return '';
        $b = '';
        foreach ($items as $x) $b .= '<div style="margin-bottom:7px;overflow:hidden"><div style="font-weight:700">' . self::e($x['degree'])
            . '<span style="float:right;font-weight:400;font-size:11px;color:#6b7280">' . self::e($x['date']) . '</span></div>'
            . '<div style="color:' . $d['theme']['accent'] . ';font-size:' . ((int)$d['theme']['size'] - 1) . 'px">' . self::e($x['school']) . '</div>'
            . ($x['note'] ? '<div style="font-size:11px;color:#6b7280">' . self::e($x['note']) . '</div>' : '') . '</div>';
        return '<div style="margin-bottom:12px">' . self::secH($d, 'Education', $side) . $b . '</div>';
    }
    public static function skills(array $d, bool $side = false): string {
        $items = array_filter($d['skills'] ?? [], fn($s) => trim($s['name'] ?? '') !== '');
        if (!$items) return '';
        $t = $d['theme']; $a = $t['accent']; $b = '';
        if ($t['skill_style'] === 'tags') {
            foreach ($items as $s) $b .= '<span style="display:inline-block;border:1px solid ' . ($side ? 'rgba(255,255,255,.5)' : $a) . ';border-radius:99px;padding:2px 8px;font-size:10.5px;margin:0 3px 4px 0">' . self::e($s['name']) . '</span>';
        } elseif ($t['skill_style'] === 'dots') {
            foreach ($items as $s) { $f = (int)round(((int)($s['level'] ?? 80)) / 20);
                $b .= '<div style="margin-bottom:5px;overflow:hidden">' . self::e($s['name']) . '<span style="float:right">' . implode('', array_map(fn($i) => '<span style="display:inline-block;width:8px;height:8px;border-radius:50%;margin-left:3px;background:' . ($i <= $f ? $a : ($side ? 'rgba(255,255,255,.3)' : '#e5e7eb')) . '"></span>', [1, 2, 3, 4, 5])) . '</span></div>'; }
        } elseif ($t['skill_style'] === 'list') {
            $b = '<ul style="margin:0;padding-left:14px">' . implode('', array_map(fn($s) => '<li style="margin:1px 0">' . self::e($s['name']) . '</li>', $items)) . '</ul>';
        } else {
            foreach ($items as $s) $b .= '<div style="margin-bottom:5px"><div style="font-size:' . ((int)$t['size'] - 1) . 'px">' . self::e($s['name']) . ($side ? '' : ' <span style="float:right;color:#9ca3af;font-size:10px">' . (int)$s['level'] . '%</span>') . '</div><div style="height:4px;background:' . ($side ? 'rgba(255,255,255,.25)' : '#e5e7eb') . ';border-radius:2px;margin-top:2px"><div style="height:4px;width:' . (int)($s['level'] ?? 80) . '%;background:' . $a . ';border-radius:2px"></div></div></div>';
        }
        return '<div style="margin-bottom:12px">' . self::secH($d, 'Skills', $side) . $b . '</div>';
    }
    public static function languages(array $d, bool $side = false): string {
        $items = array_filter($d['languages'] ?? [], fn($x) => trim($x['name'] ?? '') !== '');
        if (!$items) return '';
        $b = '';
        foreach ($items as $x) $b .= '<div style="margin-bottom:4px;overflow:hidden"><b>' . self::e($x['name']) . '</b><span style="float:right;font-size:' . ((int)$d['theme']['size'] - 2) . 'px;opacity:.8">' . self::e($x['level']) . '</span></div>';
        return '<div style="margin-bottom:12px">' . self::secH($d, 'Languages', $side) . $b . '</div>';
    }
    public static function custom(array $d, string $place, bool $side = false): string {
        $out = '';
        foreach ($d['custom'] ?? [] as $c) {
            if (($c['place'] ?? 'main') !== $place || trim($c['heading'] ?? '') === '') continue;
            $lines = array_filter(array_map('trim', preg_split('/\n+/', $c['lines'] ?? '')), 'strlen');
            if (!$lines) continue;
            $out .= '<div style="margin-bottom:12px">' . self::secH($d, $c['heading'], $side)
                . implode('', array_map(fn($l) => '<div style="margin-bottom:3px">' . self::e($l) . '</div>', $lines)) . '</div>';
        }
        return $out;
    }
    public static function mainSections(array $d): string { return self::experience($d) . self::custom($d, 'main'); }
    public static function sideSections(array $d): string { return self::education($d, true) . self::skills($d, true) . self::languages($d, true) . self::custom($d, 'side', true); }
}