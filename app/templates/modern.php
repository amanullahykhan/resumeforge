<?php
use App\Templates\Tpl;
return ['id' => 'modern', 'name' => 'Modern Sidebar', 'desc' => 'Dark sidebar, photo & contacts left',
'thumb' => '<svg viewBox="0 0 60 80"><rect width="60" height="80" fill="#fff"/><rect width="21" height="80" fill="#0f172a"/><circle cx="10.5" cy="12" r="5" fill="#64748b"/><rect x="4" y="22" width="13" height="2.5" fill="#475569"/><rect x="4" y="27" width="13" height="2.5" fill="#475569"/><rect x="26" y="8" width="20" height="4" fill="#4f46e5"/><rect x="26" y="17" width="29" height="2.5" fill="#cbd5e1"/><rect x="26" y="22" width="29" height="2.5" fill="#cbd5e1"/><rect x="26" y="30" width="15" height="3" fill="#94a3b8"/><rect x="26" y="37" width="29" height="2.5" fill="#cbd5e1"/></svg>',
'render' => function (array $d): string {
    $t = $d['theme']; $p = $d['profile'];
    return Tpl::sheetOpen($d)
    . '<table style="width:100%;border-collapse:collapse;table-layout:fixed"><tr>'
    . '<td style="width:34%;background:' . $t['sidebar'] . ';color:' . $t['side_text'] . ';padding:24px 18px;vertical-align:top">'
    . Tpl::photo($d, true)
    . '<div style="text-align:center;margin-bottom:12px"><h1 style="margin:0;font-size:' . round($t['size'] * 2.0) . 'px;color:' . $t['side_text'] . '">' . Tpl::e($p['name']) . '</h1>'
    . '<div style="margin-top:4px;opacity:.85">' . Tpl::e($p['title']) . '</div></div>'
    . Tpl::contacts($d, 'stack', true) . Tpl::sideSections($d) . '</td>'
    . '<td style="width:66%;vertical-align:top;padding:24px 22px">' . Tpl::summary($d) . Tpl::mainSections($d) . '</td>'
    . '</tr></table></div>';
}];