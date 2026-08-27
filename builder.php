<?php
require __DIR__ . '/app/bootstrap.php';
use App\Core\{Session, Database};
use App\Templates\TemplateRegistry;
Session::start(); $d = Database::draft(); $t = $d['theme'];
$e = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$tpls = TemplateRegistry::all();
$fonts = ['Inter', 'Roboto', 'Merriweather', 'Playfair Display', 'Space Grotesk', 'Lato'];
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>ResumeForge — Builder</title>
<link rel="stylesheet" href="assets/css/app.css"></head>
<body>
<div class="top"><div class="brand">Resume<b>Forge</b></div><span style="font-size:11px;background:#1e293b;padding:3px 9px;border-radius:99px">BUILDER</span>
 <div class="spacer"></div><span id="saved">✓ Saved</span>
 <a class="btn" href="index.php">← Wizard (content)</a></div>
<div class="builder">
 <div class="left">
  <div class="card"><h2>Template</h2><div class="tpl-grid" style="grid-template-columns:repeat(2,1fr)">
   <?php foreach ($tpls as $id => $tp): ?>
   <label class="tpl-card<?= $t['template'] === $id ? ' sel' : '' ?>"><input type="radio" name="template" value="<?= $e($id) ?>"<?= $t['template'] === $id ? ' checked' : '' ?>><?= $tp['thumb'] ?><span><?= $e($tp['name']) ?></span></label>
   <?php endforeach; ?></div></div>
  <div class="card"><h2>Theme</h2><div class="grid">
   <div><label class="f">Accent</label><input type="color" data-t="accent" value="<?= $e($t['accent']) ?>"></div>
   <div><label class="f">Sidebar</label><input type="color" data-t="sidebar" value="<?= $e($t['sidebar']) ?>"></div>
   <div><label class="f">Sidebar text</label><input type="color" data-t="side_text" value="<?= $e($t['side_text']) ?>"></div>
   <div><label class="f">Size</label><input type="range" min="11" max="16" data-t="size" value="<?= (int)$t['size'] ?>"></div>
   <div class="full"><label class="f">Font</label><select data-t="font"><?php foreach ($fonts as $f) echo '<option' . ($f === $t['font'] ? ' selected' : '') . ">$f</option>"; ?></select></div>
   <div><label class="f">Headings</label><select data-t="heading"><?php foreach (['bar' => 'Bar', 'underline' => 'Underline', 'pill' => 'Pill', 'line' => 'Rule', 'box' => 'Box', 'plain' => 'Plain'] as $v => $l) echo "<option value=$v" . ($v === $t['heading'] ? ' selected' : '') . ">$l</option>"; ?></select></div>
   <div><label class="f">Skills</label><select data-t="skill_style"><?php foreach (['bars' => 'Bars', 'tags' => 'Tags', 'dots' => 'Dots', 'list' => 'List'] as $v => $l) echo "<option value=$v" . ($v === $t['skill_style'] ? ' selected' : '') . ">$l</option>"; ?></select></div>
   <div><label class="f">Frame</label><select data-t="frame"><?php foreach (['circle' => 'Circle', 'rounded' => 'Rounded', 'square' => 'Square', 'none' => 'Soft'] as $v => $l) echo "<option value=$v" . ($v === $t['frame'] ? ' selected' : '') . ">$l</option>"; ?></select></div>
   <div><label class="f">Border</label><select data-t="frame_border"><?php foreach (['ring' => 'Ring', 'solid' => 'Solid', 'none' => 'None'] as $v => $l) echo "<option value=$v" . ($v === $t['frame_border'] ? ' selected' : '') . ">$l</option>"; ?></select></div>
  </div></div>
<?= \App\Core\Hook::do('builder_left_panel') ?>
  <div class="card"><h2>Export — real files</h2><p class="sub">Generated server-side into <code>uploads/exports/</code>.</p>
   <div class="btnrow">
    <button class="btn primary" data-x="pdf"> PDF</button>
    <?= \App\Core\Hook::do('builder_export_buttons_pro') ?>
    <button class="btn" data-x="png">⬇ PNG</button>
    <button class="btn" data-x="html">⬇ HTML</button>
   </div><div class="status" id="xStatus"></div></div>
 </div>
 <div class="right"><iframe id="preview" src="api/preview.php?v=<?= time() ?>"></iframe></div>
</div>
<script src="assets/js/builder.js"></script>
<?= \App\Core\Hook::do('builder_footer_scripts') ?>
</body></html>