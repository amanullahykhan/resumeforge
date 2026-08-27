<?php
require __DIR__ . '/app/bootstrap.php';
\App\Core\Auth::requireLogin();
use App\Core\{Session, Database};
use App\Templates\TemplateRegistry;
Session::start(); $d = Database::draft();
$p = $d['profile']; $t = $d['theme']; $step = (int)$d['step']; if ($step < 1 || $step > 4) $step = 1;
$e = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
$tpls = TemplateRegistry::all();
$fonts = ['Inter', 'Roboto', 'Merriweather', 'Playfair Display', 'Space Grotesk', 'Lato'];
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>ResumeForge — Wizard</title><link rel="stylesheet" href="assets/css/app.css">
<script>window.RF_IS_PRO = <?= \App\Core\Auth::user()['is_pro'] ? 'true' : 'false' ?>;</script></head>
<body>
<div class="top"><div class="brand">Resume<b>Forge</b></div><span style="font-size:11px;background:#1e293b;padding:3px 9px;border-radius:99px">PRO · AI</span>
<div class="spacer"></div><span id="saved">✓ Saved</span>
<a class="btn primary" href="builder.php">Open Visual Builder →</a></div>
<div class="wrap" id="wizard" data-step="<?= $step ?>">
<div class="pills">
 <?php foreach ([1 => '1 · Profile & Import', 2 => '2 · Experience', 3 => '3 · Skills & More', 4 => '4 · Design'] as $n => $l): ?>
  <button class="pill<?= $step === $n ? ' on' : '' ?>" data-go="<?= $n ?>"><?= $l ?></button><?php endforeach; ?>
</div>

<div class="stepbox" data-step="1" <?= $step !== 1 ? 'hidden' : '' ?>>
 <div class="card"><h2>AI resume import</h2><p class="sub">Upload an old PDF/DOCX (or paste text) and Gemini fills the whole wizard for you.</p>
  <label class="drop" for="importFile">📄 Drop or click — PDF, DOCX, TXT</label>
  <input type="file" id="importFile" accept=".pdf,.docx,.txt,.md" hidden>
  <textarea id="importText" rows="3" placeholder="…or paste resume text here" style="margin-top:10px"></textarea>
  <div class="btnrow"><?= \App\Core\Hook::do('wizard_import_buttons') ?></div>
 </div>
 <div class="card"><h2>Profile</h2><p class="sub">The basics — everything else is optional.</p>
  <div class="grid">
   <div><label class="f">Full name</label><input data-p="name" value="<?= $e($p['name']) ?>"></div>
   <div><label class="f">Job title / headline</label><input data-p="title" value="<?= $e($p['title']) ?>"></div>
   <div><label class="f">Email</label><input data-p="email" value="<?= $e($p['email']) ?>"></div>
   <div><label class="f">Phone</label><input data-p="phone" value="<?= $e($p['phone']) ?>"></div>
   <div><label class="f">Location</label><input data-p="location" value="<?= $e($p['location']) ?>"></div>
   <div><label class="f">Website</label><input data-p="website" value="<?= $e($p['website']) ?>"></div>
   <div><label class="f">LinkedIn</label><input data-p="linkedin" value="<?= $e($p['linkedin']) ?>"></div>
   <div><label class="f">GitHub</label><input data-p="github" value="<?= $e($p['github']) ?>"></div>
   <div class="full"><label class="f">Photo</label>
    <div style="display:flex;gap:12px;align-items:center">
     <img id="photoPrev" src="<?= $e($p['photo']) ?>" style="width:56px;height:56px;border-radius:50%;object-fit:cover;background:#e2e8f0">
     <label class="btn" for="photoFile">Upload photo</label><input type="file" id="photoFile" accept="image/*" hidden>
    </div></div>
   <div class="full"><label class="f">Professional summary</label>
    <textarea data-p="summary" rows="4"><?= $e($p['summary']) ?></textarea>
    <?= \App\Core\Hook::do('wizard_summary_buttons') ?></div>
  </div></div>
</div>

<div class="stepbox" data-step="2" <?= $step !== 2 ? 'hidden' : '' ?>>
 <div class="card"><h2>Work experience</h2><p class="sub">Bullets: one per line. “✨ Polish” rewrites them with AI.</p>
  <div id="expList"><?php foreach ($d['experience'] as $j): ?>
   <div class="rep" data-kind="experience">
    <div class="grid">
     <div><label class="f">Job title</label><input data-f="title" value="<?= $e($j['title']) ?>"></div>
     <div><label class="f">Company</label><input data-f="company" value="<?= $e($j['company']) ?>"></div>
     <div><label class="f">Dates</label><input data-f="date" value="<?= $e($j['date']) ?>" placeholder="2021 — Present"></div>
     <div><label class="f">Location</label><input data-f="location" value="<?= $e($j['location']) ?>"></div>
     <div class="full"><label class="f">Achievement bullets (one per line)</label><textarea data-f="bullets" rows="4"><?= $e($j['bullets']) ?></textarea></div>
    </div>
    <?= \App\Core\Hook::do('wizard_experience_buttons') ?>
    <button class="rm" type="button">✕</button>
   </div><?php endforeach; ?></div>
  <button class="btn" id="addExp">＋ Add position</button></div>
 <div class="card"><h2>Education</h2>
  <div id="eduList"><?php foreach ($d['education'] as $x): ?>
   <div class="rep" data-kind="education"><div class="grid">
    <div><label class="f">Degree</label><input data-f="degree" value="<?= $e($x['degree']) ?>"></div>
    <div><label class="f">School</label><input data-f="school" value="<?= $e($x['school']) ?>"></div>
    <div><label class="f">Dates</label><input data-f="date" value="<?= $e($x['date']) ?>"></div>
    <div><label class="f">Note</label><input data-f="note" value="<?= $e($x['note']) ?>"></div>
   </div><button class="rm" type="button">✕</button></div><?php endforeach; ?></div>
  <button class="btn" id="addEdu">＋ Add education</button></div>
</div>

<div class="stepbox" data-step="3" <?= $step !== 3 ? 'hidden' : '' ?>>
 <div class="card"><h2>Skills</h2><div id="skillList"><?php foreach ($d['skills'] as $s): ?>
   <div class="srow rep" data-kind="skills"><input data-f="name" value="<?= $e($s['name']) ?>" placeholder="Skill">
    <input type="range" min="0" max="100" step="5" data-f="level" value="<?= (int)($s['level'] ?? 80) ?>"><button class="rm" type="button">✕</button></div>
  <?php endforeach; ?></div><button class="btn" id="addSkill">＋ Add skill</button></div>
 <div class="card"><h2>Languages</h2><div id="langList"><?php foreach ($d['languages'] as $l): ?>
   <div class="srow rep" data-kind="languages"><input data-f="name" value="<?= $e($l['name']) ?>" placeholder="Language">
    <select data-f="level"><?php foreach (['Native', 'Fluent', 'Proficient', 'Intermediate', 'Basic'] as $v) echo '<option' . ($v === ($l['level'] ?? '') ? ' selected' : '') . ">$v</option>"; ?></select>
    <button class="rm" type="button">✕</button></div><?php endforeach; ?></div>
  <button class="btn" id="addLang">＋ Add language</button></div>
 <div class="card"><h2>Custom sections</h2><p class="sub">Certifications, projects, awards… choose which column they land in.</p>
  <div id="customList"><?php foreach ($d['custom'] as $c): ?>
   <div class="rep" data-kind="custom"><div class="grid">
    <div><label class="f">Heading</label><input data-f="heading" value="<?= $e($c['heading']) ?>"></div>
    <div><label class="f">Column</label><select data-f="place"><option value="main"<?= ($c['place'] ?? '') === 'main' ? ' selected' : '' ?>>Main</option><option value="side"<?= ($c['place'] ?? '') === 'side' ? ' selected' : '' ?>>Sidebar</option></select></div>
    <div class="full"><label class="f">Lines (one per line)</label><textarea data-f="lines" rows="3"><?= $e($c['lines']) ?></textarea></div>
   </div><button class="rm" type="button">✕</button></div><?php endforeach; ?></div>
  <button class="btn" id="addCustom">＋ Add section</button></div>
</div>

<div class="stepbox" data-step="4" <?= $step !== 4 ? 'hidden' : '' ?>>
 <div class="card"><h2>Template</h2><p class="sub">Addons in <code>app/templates/addons/</code> appear here automatically.</p>
  <div class="tpl-grid"><?php foreach ($tpls as $id => $tp): ?>
   <label class="tpl-card<?= $t['template'] === $id ? ' sel' : '' ?>"><input type="radio" name="template" value="<?= $e($id) ?>"<?= $t['template'] === $id ? ' checked' : '' ?>><?= $tp['thumb'] ?><span><?= $e($tp['name']) ?></span></label>
  <?php endforeach; ?></div></div>
 <div class="card"><h2>Theme & typography</h2>
  <div class="grid">
   <div><label class="f">Accent color</label><input type="color" data-t="accent" value="<?= $e($t['accent']) ?>"></div>
   <div><label class="f">Sidebar color</label><input type="color" data-t="sidebar" value="<?= $e($t['sidebar']) ?>"></div>
   <div><label class="f">Sidebar text</label><input type="color" data-t="side_text" value="<?= $e($t['side_text']) ?>"></div>
   <div><label class="f">Base size (<?= (int)$t['size'] ?>px)</label><input type="range" min="11" max="16" data-t="size" value="<?= (int)$t['size'] ?>"></div>
   <div><label class="f">Font</label><select data-t="font"><?php foreach ($fonts as $f) echo '<option' . ($f === $t['font'] ? ' selected' : '') . ">$f</option>"; ?></select></div>
   <div><label class="f">Heading style</label><select data-t="heading"><?php foreach (['bar' => 'Accent bar', 'underline' => 'Underline', 'pill' => 'Pill', 'line' => 'Rule line', 'box' => 'Box', 'plain' => 'Plain'] as $v => $l) echo "<option value=$v" . ($v === $t['heading'] ? ' selected' : '') . ">$l</option>"; ?></select></div>
   <div><label class="f">Skill display</label><select data-t="skill_style"><?php foreach (['bars' => 'Bars', 'tags' => 'Tags', 'dots' => 'Dots', 'list' => 'List'] as $v => $l) echo "<option value=$v" . ($v === $t['skill_style'] ? ' selected' : '') . ">$l</option>"; ?></select></div>
   <div><label class="f">Photo frame</label><select data-t="frame"><?php foreach (['circle' => 'Circle', 'rounded' => 'Rounded', 'square' => 'Square', 'none' => 'Soft'] as $v => $l) echo "<option value=$v" . ($v === $t['frame'] ? ' selected' : '') . ">$l</option>"; ?></select></div>
   <div><label class="f">Frame border</label><select data-t="frame_border"><?php foreach (['ring' => 'Ring', 'solid' => 'Solid', 'none' => 'None'] as $v => $l) echo "<option value=$v" . ($v === $t['frame_border'] ? ' selected' : '') . ">$l</option>"; ?></select></div>
   <div><label class="f">Uppercase headings</label><select data-t="upper"><option value="1"<?= $t['upper'] ? ' selected' : '' ?>>Yes</option><option value="0"<?= !$t['upper'] ? ' selected' : '' ?>>No</option></select></div>
  </div>
  <div class="btnrow"><a class="btn primary" href="builder.php">Open Visual Builder →</a></div></div>
</div>
</div>

<template id="tpl-exp"><div class="rep" data-kind="experience"><div class="grid">
 <div><label class="f">Job title</label><input data-f="title"></div><div><label class="f">Company</label><input data-f="company"></div>
 <div><label class="f">Dates</label><input data-f="date"></div><div><label class="f">Location</label><input data-f="location"></div>
 <div class="full"><label class="f">Achievement bullets (one per line)</label><textarea data-f="bullets" rows="4"></textarea></div></div>
 <?= \App\Core\Hook::do('wizard_experience_buttons') ?><button class="rm" type="button">✕</button></div></template>
<template id="tpl-edu"><div class="rep" data-kind="education"><div class="grid">
 <div><label class="f">Degree</label><input data-f="degree"></div><div><label class="f">School</label><input data-f="school"></div>
 <div><label class="f">Dates</label><input data-f="date"></div><div><label class="f">Note</label><input data-f="note"></div></div>
 <button class="rm" type="button">✕</button></div></template>
<template id="tpl-skill"><div class="srow rep" data-kind="skills"><input data-f="name" placeholder="Skill"><input type="range" min="0" max="100" step="5" data-f="level" value="80"><button class="rm" type="button">✕</button></div></template>
<template id="tpl-lang"><div class="srow rep" data-kind="languages"><input data-f="name" placeholder="Language"><select data-f="level"><option>Native</option><option>Fluent</option><option selected>Proficient</option><option>Intermediate</option><option>Basic</option></select><button class="rm" type="button">✕</button></div></template>
<template id="tpl-custom"><div class="rep" data-kind="custom"><div class="grid">
 <div><label class="f">Heading</label><input data-f="heading"></div>
 <div><label class="f">Column</label><select data-f="place"><option value="main">Main</option><option value="side">Sidebar</option></select></div>
 <div class="full"><label class="f">Lines (one per line)</label><textarea data-f="lines" rows="3"></textarea></div></div>
 <button class="rm" type="button">✕</button></div></template>
<script src="assets/js/wizard.js"></script>
<?= \App\Core\Hook::do('footer_scripts') ?>
</body></html>