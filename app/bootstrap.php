<?php
require __DIR__ . '/config/config.php';
if (is_file(RF_ROOT . '/vendor/autoload.php')) require RF_ROOT . '/vendor/autoload.php';

/* App\* autoloader (owns all project classes — composer only supplies Dompdf).
   App\Core\Database      -> app/core/Database.php
   App\Templates\Registry -> app/templates/TemplateRegistry.php
   App\Templates\Tpl      -> app/templates/TemplateInterface.php (lives with interface) */
spl_autoload_register(function (string $c) {
    if (strpos($c, 'App\\') !== 0) return;
    $rel = str_replace('\\', '/', substr($c, 4));
    $map = ['Templates/Tpl' => 'templates/TemplateInterface.php'];
    if (isset($map[$rel])) { require_once RF_APP . '/' . $map[$rel]; return; }
    $parts = explode('/', $rel);
    $parts[0] = strtolower($parts[0]);
    $f = RF_APP . '/' . implode('/', $parts) . '.php';
    if (is_file($f)) require_once $f;
});

function rf_json($data, int $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
/* API mode: never leak HTML errors; always answer JSON (even on fatal/parse errors) */
function rf_api_mode(): void {
    ini_set('display_errors', '0');
    register_shutdown_function(function () {
        $e = error_get_last();
        if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            if (!headers_sent()) { http_response_code(500); header('Content-Type: application/json'); }
            echo json_encode(['ok' => false, 'error' => 'PHP FATAL: ' . $e['message'] . ' in ' . basename($e['file']) . ':' . $e['line']]);
        }
    });
}

// Initialize Addon Engine
\App\Core\AddonManager::init();

// Inject Upsell Modal into footers for Free Users
\App\Core\Hook::addAction('footer_scripts', function() {
    require RF_APP . '/templates/components/upsell_modal.php';
});
\App\Core\Hook::addAction('builder_footer_scripts', function() {
    require RF_APP . '/templates/components/upsell_modal.php';
});