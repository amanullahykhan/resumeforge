<?php
use App\Core\{Session, Database, ExportManager};
require dirname(__DIR__) . '/app/bootstrap.php';
Session::start();
$in = json_decode((string)file_get_contents('php://input'), true);
if (!is_array($in)) $in = $_POST;
try {
    rf_json(['ok' => true] + ExportManager::export(Database::draft(), (string)($in['format'] ?? '')));
} catch (\Throwable $e) {
    rf_json(['ok' => false, 'error' => $e->getMessage()], 500);
}