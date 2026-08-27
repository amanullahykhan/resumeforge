<?php
/* .env loader + paths */
$__env = __DIR__ . '/.env';
if (is_file($__env)) foreach (file($__env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $__l) {
    $__l = trim($__l);
    if ($__l === '' || $__l[0] === '#' || strpos($__l, '=') === false) continue;
    [$__k, $__v] = explode('=', $__l, 2);
    putenv(trim($__k) . '=' . trim($__v)); $_ENV[trim($__k)] = trim($__v);
}
function env(string $k, $d = null) { $v = getenv($k); return $v === false ? $d : $v; }

define('RF_ROOT', dirname(__DIR__, 2));
define('RF_APP', dirname(__DIR__));
define('RF_UPLOADS', RF_ROOT . '/uploads');
define('RF_PHOTOS', RF_UPLOADS . '/photos');
define('RF_EXPORTS', RF_UPLOADS . '/exports');
define('RF_STORAGE', RF_UPLOADS . '/storage');
foreach ([RF_UPLOADS, RF_PHOTOS, RF_EXPORTS, RF_STORAGE] as $__d) if (!is_dir($__d)) @mkdir($__d, 0775, true);

define('RF_GEMINI_KEY', env('GEMINI_API_KEY', ''));
define('RF_GEMINI_MODEL', env('GEMINI_MODEL', 'gemini-flash-latest'));