<?php
use App\Core\{Session, Database};
require dirname(__DIR__) . '/app/bootstrap.php';
Session::start();

$in = json_decode((string)file_get_contents('php://input'), true);
if (!is_array($in)) $in = $_POST;
$d = Database::draft();

if (isset($in['step'])) $d['step'] = max(1, min(4, (int)$in['step']));
/* Client sends COMPLETE arrays per step -> replace wholesale */
if (isset($in['profile']) && is_array($in['profile'])) {
    $d['profile'] = array_merge($d['profile'] ?? [], $in['profile']);
}
foreach (['experience', 'education', 'skills', 'languages', 'custom'] as $k)
    if (isset($in[$k]) && is_array($in[$k])) $d[$k] = $in[$k];
/* defaults < existing draft < incoming : never clobber keys a panel doesn't edit */
if (isset($in['theme']) && is_array($in['theme']))
    $d['theme'] = array_merge(Database::themeDefaults(), $d['theme'] ?? [], $in['theme']);

Database::save(Session::draftId(), $d);
rf_json(['ok' => true, 'step' => $d['step']]);