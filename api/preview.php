<?php
use App\Core\{Session, Database, Renderer};
require dirname(__DIR__) . '/app/bootstrap.php';
Session::start();
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
echo Renderer::document(Database::draft());