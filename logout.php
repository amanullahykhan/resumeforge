<?php
require __DIR__ . '/app/bootstrap.php';
use App\Core\Auth;

Auth::logout();
header('Location: login.php');
exit;
