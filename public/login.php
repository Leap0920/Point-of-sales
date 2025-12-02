<?php
require_once __DIR__ . '/../app/Auth.php';

Auth::startSession();

// If already logged in, redirect to dashboard
if (Auth::check()) {
    header('Location: index.php');
    exit;
}

$config = require __DIR__ . '/../config/config.php';
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } else {
        if (Auth::login($username, $password)) {
            header('Location: index.php');
            exit;
        }
        $error = 'Invalid username or password. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($config['app']['name']) ?> - Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">🏪</div>
                <h1 class="login-title"><?= htmlspecialchars($config['app']['name']) ?></h1>
                <p class="login-subtitle">Sign in to access your dashboard</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <strong>⚠️ Error:</strong> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <strong>✓ Success:</strong> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="username" 
                        name="username" 
                        placeholder="Enter your username"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        required 
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary">
                    <span>Sign In</span>
                    <span>→</span>
                </button>
            </form>

            <div class="login-footer">
                <small>
                    <strong>Demo Credentials:</strong><br>
                    Admin: <code>admin</code> / <code>admin123</code><br>
                    Cashier: <code>cashier</code> / <code>cashier123</code>
                </small>
            </div>
        </div>
    </div>
</body>
</html>

