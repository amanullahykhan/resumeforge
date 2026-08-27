<?php
use App\Templates\TemplateRegistry;
require dirname(__DIR__) . '/app/bootstrap.php';
rf_json([
    'ok'        => true,
    'php'       => PHP_VERSION,
    'extensions'=> [
        'zip'        => extension_loaded('zip'),
        'curl'       => extension_loaded('curl'),
        'gd'         => extension_loaded('gd'),
        'pdo_sqlite' => extension_loaded('pdo_sqlite'),
        'imagick'    => extension_loaded('imagick'),
    ],
    'writable'  => [
        'uploads'         => is_writable(RF_UPLOADS),
        'uploads/photos'  => is_writable(RF_PHOTOS),
        'uploads/exports' => is_writable(RF_EXPORTS),
        'uploads/storage' => is_writable(RF_STORAGE),
    ],
    'dompdf'     => class_exists('Dompdf\Dompdf'),
    'gemini_key' => RF_GEMINI_KEY !== '',
    'templates'  => array_keys(TemplateRegistry::all()),
]);