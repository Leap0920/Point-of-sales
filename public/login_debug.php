<?php
/**
 * Login Test Script - Debug Version
 * This will show you exactly what's happening during login
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../app/Auth.php';

Auth::startSession();

$config = require __DIR__ . '/../config/config.php';
$error = null;
$success = null;
$debug = [];

// Check if already logged in
if (Auth::check()) {
    $debug[] = "Already logged in as: " . Auth::user()['username'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    $debug[] = "POST request received";
    $debug[] = "Username: " . htmlspecialchars($username);
    $debug[] = "Password length: " . strlen($password);

    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
        $debug[] = "Error: Empty username or password";
    } else {
        $debug[] = "Attempting login...";
        
        try {
            if (Auth::login($username, $password)) {
                $debug[] = "Login successful!";
                $debug[] = "User session: " . print_r($_SESSION['user'] ?? 'No session', true);
                $success = "Login successful! Redirecting...";
                
                // Add a meta refresh as backup
                echo "<!DOCTYPE html><html><head>";
                echo "<meta http-equiv='refresh' content='2;url=index.php'>";
                echo "</head><body>";
                echo "<h2>Login Successful!</h2>";
                echo "<p>Redirecting to dashboard...</p>";
                echo "<p>If not redirected, <a href='index.php'>click here</a></p>";
                echo "</body></html>";
                exit;
            } else {
                $error = 'Invalid username or password. Please try again.';
                $debug[] = "Login failed: Invalid credentials";
            }
        } catch (Exception $e) {
            $error = 'Login error: ' . $e->getMessage();
            $debug[] = "Exception: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($config['app']['name']) ?> - Login (Debug)</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .debug-panel {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #fff;
            border: 2px solid #667eea;
            border-radius: 8px;
            padding: 15px;
            max-width: 300px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-size: 12px;
        }
        .debug-panel h3 {
            margin: 0 0 10px 0;
            color: #667eea;
            font-size: 14px;
        }
        .debug-panel ul {
            margin: 0;
            padding-left: 20px;
        }
        .debug-panel li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <?php if (!empty($debug)): ?>
    <div class="debug-panel">
        <h3>🔍 Debug Info</h3>
        <ul>
            <?php foreach ($debug as $msg): ?>
                <li><?= htmlspecialchars($msg) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">🏪</div>
                <h1 class="login-title"><?= htmlspecialchars($config['app']['name']) ?></h1>
                <p class="login-subtitle">Sign in to access your dashboard (Debug Mode)</p>
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
                <br><br>
                <small>
                    <a href="check_db.php">🔍 Run Database Diagnostics</a> |
                    <a href="login.php">← Back to Normal Login</a>
                </small>
            </div>
        </div>
    </div>
</body>
</html>
