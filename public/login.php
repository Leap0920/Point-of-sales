<?php
require_once __DIR__ . '/../app/Auth.php';

Auth::startSession();

// If already logged in, redirect to dashboard
if (Auth::check()) {
    header('Location: index.php');
    exit;
}

// Redirect to cashier login by default
header('Location: cashier_login.php');
exit;
