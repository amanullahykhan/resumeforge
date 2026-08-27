# Template addons
Drop a `.php` file in `addons/` that returns an array — the registry discovers it automatically.

    <?php
    use App\Templates\Tpl;
    return [
      'id'    => 'mytemplate',        // unique key
      'name'  => 'My Template',       // shown in pickers
      'desc'  => 'Short description',
      'thumb' => '<svg viewBox="0 0 60 80">…</svg>',   // mini preview
      'render'=> function (array $d): string {
          // $d = full draft: profile, experience, education, skills, languages, custom, theme
          // Use Tpl helpers: sheetOpen, secH, photo, contacts, summary, experience,
          // education, skills, languages, custom, mainSections, sideSections.
          return Tpl::sheetOpen($d) . '…your layout…' . '</div>';
      },
    ];

Rules:
- A4 canvas is 794px wide; use tables / inline-block (Dompdf-safe), NOT flexbox.
- Use inline styles for colors/sizes — keeps browser preview, PDF and HTML export identical.
- No external assets; the registry caches the discovery list per request.