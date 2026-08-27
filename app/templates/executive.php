<?php
use App\Templates\Tpl;
return ['id' => 'executive', 'name' => 'Executive', 'desc' => 'Bold header band + two columns',
'thumb' => '<svg viewBox="0 0 60 80"><rect width="60" height="80" fill="#fff"/><rect x="6" y="7" width="24" height="5" fill="#111827"/><rect x="6" y="15" width="48" height="2.5" fill="#4f46e5"/><rect x="6" y="24" width="30" height="2.5" fill="#cbd5e1"/><rect x="6" y="29" width="30" height="2.5" fill="#cbd5e1"/><rect x="6" y="37" width="15" height="3" fill="#94a3b8"/><rect x="6" y="44" width="30" height="2.5" fill="#cbd5e1"/><rect x="42" y="24" width="12" height="2.5" fill="#cbd5e1"/><rect x="42" y="29" width="12" height="2.5" fill="#cbd5e1"/></svg>',
'render' => function (array $d): string {
    $t = $d['theme']; $p = $d['profile'];
    return Tpl::sheetOpen($d)
    . '<div style="padding:22px 28px 14px;border-bottom:4px solid ' . $t['accent'] . '"><table style="width:100%;border-collapse:collapse;table-layout:fixed"><tr>'
    . '<td style="width:60%;vertical-align:middle"><h1 style="margin:0;font-size:' . round($t['size'] * 2.2) . 'px">' . Tpl::e($p['name']) . '</h1>'
    . '<div style="color:' . $t['accent'] . ';font-weight:600;margin-top:4px">' . Tpl::e($p['title']) . '</div></td>'
    . '<td style="width:40%;text-align:right;vertical-align:middle;font-size:' . ((int)$t['size'] - 2) . 'px;color:#4b5563">'
    . implode('<br>', array_filter([Tpl::e($p['email']), Tpl::e($p['phone']), Tpl::e($p['location'])])) . '</td>'
    . '</tr></table></div>'
    . '<table style="width:100%;border-collapse:collapse;table-layout:fixed"><tr>'
    . '<td style="width:66%;padding:20px 22px 24px 28px;vertical-align:top">' . Tpl::summary($d) . Tpl::mainSections($d) . '</td>'
    . '<td style="width:34%;padding:20px 28px 24px 18px;border-left:1px solid #e5e7eb;vertical-align:top">'
    . Tpl::education($d, true) . Tpl::skills($d, true) . Tpl::languages($d, true) . Tpl::custom($d, 'side', true) . '</td>'
    . '</tr></table></div>';
}];