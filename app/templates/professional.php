<?php
use App\Templates\Tpl;
return ['id' => 'professional', 'name' => 'Professional (ATS)', 'desc' => 'Classic single column layout, highly ATS optimized',
'thumb' => '<svg viewBox="0 0 60 80"><rect width="60" height="80" fill="#fff"/><rect x="15" y="8" width="30" height="3" fill="#111827"/><rect x="10" y="14" width="40" height="1.5" fill="#64748b"/><rect x="6" y="22" width="12" height="2" fill="#111827"/><rect x="6" y="26" width="48" height="1" fill="#cbd5e1"/><rect x="6" y="30" width="48" height="1.5" fill="#94a3b8"/><rect x="6" y="34" width="48" height="1.5" fill="#94a3b8"/><rect x="6" y="42" width="12" height="2" fill="#111827"/><rect x="6" y="46" width="48" height="1" fill="#cbd5e1"/><rect x="6" y="50" width="48" height="1.5" fill="#94a3b8"/></svg>',
'render' => function (array $d): string {
    $t = $d['theme']; $p = $d['profile']; $pad = (int)($t['page_padding'] ?? 24);
    
    // For single column, we combine main and side sections
    $mainSections = Tpl::mainSections($d);
    $sideSections = Tpl::education($d) . Tpl::skills($d) . Tpl::languages($d) . Tpl::custom($d, 'side');
    
    return Tpl::sheetOpen($d)
    . '<div style="padding:' . ($pad + 6) . 'px ' . ($pad + 16) . 'px">'
    . '<div style="text-align:center;margin-bottom:20px">'
    . '<h1 style="margin:0 0 6px 0;font-size:' . round($t['size'] * 2.2) . 'px;color:#111827;text-transform:uppercase;letter-spacing:1px">' . Tpl::e($p['name']) . '</h1>'
    . '<div style="font-size:' . round($t['size'] * 1.1) . 'px;color:' . $t['accent'] . ';font-weight:600;margin-bottom:8px">' . Tpl::e($p['title']) . '</div>'
    . '<div style="font-size:' . ($t['size'] - 1) . 'px;color:#374151">' 
    . implode(' &nbsp;|&nbsp; ', array_filter([Tpl::e($p['email']), Tpl::e($p['phone']), Tpl::e($p['location']), Tpl::e($p['linkedin'])])) 
    . '</div>'
    . '</div>'
    . '<div style="margin-bottom:20px">' . Tpl::summary($d) . '</div>'
    . $mainSections
    . $sideSections
    . '</div></div>';
}];
