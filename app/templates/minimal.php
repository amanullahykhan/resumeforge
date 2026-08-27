<?php
use App\Templates\Tpl;
return ['id' => 'minimal', 'name' => 'Minimal', 'desc' => 'Clean single column, ATS-safe',
'thumb' => '<svg viewBox="0 0 60 80"><rect width="60" height="80" fill="#fff"/><rect x="7" y="8" width="24" height="5" fill="#111827"/><rect x="7" y="16" width="14" height="2.5" fill="#4f46e5"/><rect x="7" y="23" width="12" height="2" fill="#4f46e5"/><rect x="7" y="31" width="46" height="2.5" fill="#cbd5e1"/><rect x="7" y="36" width="46" height="2.5" fill="#cbd5e1"/><rect x="7" y="44" width="15" height="3" fill="#94a3b8"/><rect x="7" y="51" width="46" height="2.5" fill="#cbd5e1"/></svg>',
'render' => function (array $d): string {
    $t = $d['theme']; $p = $d['profile'];
    return Tpl::sheetOpen($d) . '<div style="padding:44px 48px">'
    . Tpl::photo($d)
    . '<h1 style="margin:0;font-size:' . round($t['size'] * 2.4) . 'px">' . Tpl::e($p['name']) . '</h1>'
    . '<div style="color:' . $t['accent'] . ';font-weight:600;margin:5px 0 10px">' . Tpl::e($p['title']) . '</div>'
    . Tpl::contacts($d, 'row')
    . '<div style="height:3px;width:64px;background:' . $t['accent'] . ';margin:16px 0 22px"></div>'
    . Tpl::summary($d) . Tpl::mainSections($d) . Tpl::education($d) . Tpl::skills($d) . Tpl::languages($d) . Tpl::custom($d, 'side')
    . '</div></div>';
}];