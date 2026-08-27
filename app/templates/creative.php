<?php
use App\Templates\Tpl;
return ['id' => 'creative', 'name' => 'Creative', 'desc' => 'Bold left accent bar and colored headings',
'thumb' => '<svg viewBox="0 0 60 80"><rect width="60" height="80" fill="#fff"/><rect width="4" height="80" fill="#4f46e5"/><circle cx="16" cy="14" r="6" fill="#cbd5e1"/><rect x="26" y="10" width="24" height="3.5" fill="#1e293b"/><rect x="26" y="16" width="16" height="2" fill="#64748b"/><rect x="10" y="26" width="12" height="2" fill="#4f46e5"/><rect x="10" y="30" width="40" height="2" fill="#94a3b8"/><rect x="10" y="34" width="40" height="2" fill="#94a3b8"/><rect x="10" y="42" width="12" height="2" fill="#4f46e5"/><rect x="10" y="46" width="40" height="2" fill="#94a3b8"/></svg>',
'render' => function (array $d): string {
    $t = $d['theme']; $p = $d['profile']; $pad = (int)($t['page_padding'] ?? 24);
    $acc = $t['accent'];
    return Tpl::sheetOpen($d)
    . '<div style="display:flex;min-height:100%">'
    . '<div style="width:16px;background:' . $acc . ';flex-shrink:0"></div>'
    . '<div style="flex-grow:1;padding:' . ($pad + 6) . 'px ' . ($pad + 16) . 'px">'
    . '<table style="width:100%;margin-bottom:24px"><tr>'
    . '<td style="width:80px;vertical-align:top">' . Tpl::photo($d, false) . '</td>'
    . '<td style="padding-left:20px;vertical-align:middle">'
    . '<h1 style="margin:0;font-size:' . round($t['size'] * 2.5) . 'px;color:#111827;letter-spacing:-0.5px">' . Tpl::e($p['name']) . '</h1>'
    . '<div style="font-size:' . round($t['size'] * 1.1) . 'px;color:' . $acc . ';font-weight:600;margin-top:4px">' . Tpl::e($p['title']) . '</div>'
    . '<div style="margin-top:8px;font-size:' . ($t['size'] - 2) . 'px;color:#4b5563">' . implode(' &nbsp;•&nbsp; ', array_filter([Tpl::e($p['location']), Tpl::e($p['phone']), Tpl::e($p['email']), Tpl::e($p['website'])])) . '</div>'
    . '</td></tr></table>'
    . '<div style="margin-bottom:24px">' . Tpl::summary($d) . '</div>'
    . '<table style="width:100%;table-layout:fixed;border-spacing:0"><tr>'
    . '<td style="width:65%;vertical-align:top;padding-right:24px">' . Tpl::mainSections($d) . '</td>'
    . '<td style="width:35%;vertical-align:top;padding-left:24px;border-left:2px solid #f1f5f9">'
    . Tpl::skills($d) . Tpl::education($d) . Tpl::languages($d) . Tpl::custom($d, 'side')
    . '</td></tr></table>'
    . '</div></div></div>';
}];
