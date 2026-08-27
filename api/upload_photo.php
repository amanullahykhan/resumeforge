<?php
use App\Core\{Session, Database};
require dirname(__DIR__) . '/app/bootstrap.php';
Session::start();
if (!function_exists('imagecreatefromstring') || !function_exists('imagecopyresampled'))
    rf_json(['ok' => false, 'error' => 'PHP GD extension is required for photo uploads.'], 500);
$f = $_FILES['photo'] ?? null;
if (!$f || $f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name']))
    rf_json(['ok' => false, 'error' => 'Upload failed.'], 400);
$info = @getimagesize($f['tmp_name']);
if (!$info) rf_json(['ok' => false, 'error' => 'Not an image.'], 400);
$im = imagecreatefromstring((string)file_get_contents($f['tmp_name']));
if ($im === false) rf_json(['ok' => false, 'error' => 'Unsupported image.'], 400);
$max = 480;
$w = imagesx($im); $h = imagesy($im);
$r = min(1, $max / max($w, $h));
$nw = max(1, (int)round($w * $r)); $nh = max(1, (int)round($h * $r));
$out = imagecreatetruecolor($nw, $nh);
imagecopyresampled($out, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);
$dest = RF_PHOTOS . '/' . Session::draftId() . '.jpg';
imagejpeg($out, $dest, 86);
$url = 'uploads/photos/' . basename($dest);
$d = Database::draft();
$d['profile']['photo'] = $url;
Database::save(Session::draftId(), $d);
rf_json(['ok' => true, 'url' => $url]);