<?php

require __DIR__ . '/../app/auth_only.php';
require __DIR__ . '/../app/Auth.php';

// Redirect based on role:
// - Admin  -> admin dashboard (back office)
// - Others -> POS screen (cashier)
$user = Auth::user();

if ($user && $user['role'] === 'Admin') {
    header('Location: admin_dashboard.php');
    exit;
}

header('Location: pos.php');
exit;

