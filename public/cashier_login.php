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
            // Check if user is a cashier
            $currentUser = Auth::user();
            if ($currentUser && $currentUser['role'] === 'Cashier') {
                header('Location: pos.php');
                exit;
            } else {
                // Not a cashier, logout and show error
                Auth::logout();
                $error = 'Access denied. This login is for cashiers only. Administrators should use the admin login.';
            }
        } else {
            $error = 'Invalid username or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($config['app']['name']) ?> - Cashier Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            height: 100vh;
            overflow: hidden;
        }

        .login-page {
            height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            position: relative;
            overflow: hidden;
        }

        .login-form-section {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            background: white;
            overflow-y: auto;
        }

        .login-form-container {
            background: white;
            padding: 1.5rem 1.5rem;
            width: 100%;
            max-width: 380px;
        }

        .image-section {
            position: relative;
            background: url('images/restaurant_bg.jpg') center/cover no-repeat;
        }

        .brand-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-logo .logo-icon {
            font-size: 2.5rem;
            margin-bottom: 0.3rem;
            display: block;
        }

        .brand-name {
            font-size: 1.4rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ff6b35 0%, #ff9f40 50%, #ffc107 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .role-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 16px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .welcome-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.3rem;
        }

        .welcome-subtitle {
            color: #718096;
            font-size: 0.85rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .alert-danger {
            background: #fff5f5;
            color: #c53030;
            border: 1px solid #feb2b2;
        }

        .alert-success {
            background: #f0fff4;
            color: #2f855a;
            border: 1px solid #9ae6b4;
        }

        .login-form {
            margin-bottom: 1.5rem;
        }

        .form-field {
            margin-bottom: 1rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #f7fafc;
        }

        .form-input:focus {
            outline: none;
            border-color: #ff6b35;
            background: white;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }

        .form-options {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #4a5568;
            cursor: pointer;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #ff6b35;
        }

        .btn-signin {
            width: 100%;
            padding: 0.85rem;
            background: linear-gradient(135deg, #ff6b35 0%, #ff9f40 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
        }

        .btn-signin:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(255, 107, 53, 0.4);
        }

        .btn-signin:active {
            transform: translateY(0);
        }

        .help-text {
            text-align: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }

        .help-text p {
            color: #718096;
            font-size: 0.8rem;
            line-height: 1.5;
        }

        .help-text strong {
            color: #2d3748;
        }

        .switch-login {
            text-align: center;
            margin-top: 1rem;
        }

        .switch-login a {
            color: #ff6b35;
            text-decoration: none;
            font-weight: 600;
        }

        .switch-login a:hover {
            text-decoration: underline;
        }

        @media (max-width: 992px) {
            .login-page {
                grid-template-columns: 1fr;
            }

            .image-section {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .login-form-section {
                padding: 2rem 1.5rem;
            }

            .login-form-container {
                padding: 2rem 1.5rem;
            }

            .welcome-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-form-section">
            <div class="login-form-container">
                <!-- Logo -->
                <div class="brand-logo">
                    <span class="logo-icon">🍽️</span>
                    <span class="brand-name"><?= htmlspecialchars($config['app']['name']) ?></span>
                    <div style="margin-top: 1rem;">
                        <span class="role-badge">👤 Cashier Login</span>
                    </div>
                </div>

                <!-- Welcome Text -->
                <div class="welcome-text">
                    <h1 class="welcome-title">Welcome Back</h1>
                    <p class="welcome-subtitle">Sign in to access POS terminal</p>
                </div>

                <!-- Error/Success Messages -->
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <strong>⚠️</strong> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <strong>✓</strong> <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="post" class="login-form" novalidate>
                    <div class="form-field">
                        <input 
                            type="text" 
                            class="form-input" 
                            id="username" 
                            name="username" 
                            placeholder="Username"
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                            required 
                            autofocus
                        >
                    </div>

                    <div class="form-field">
                        <input 
                            type="password" 
                            class="form-input" 
                            id="password" 
                            name="password" 
                            placeholder="Password"
                            required
                        >
                    </div>

                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-signin">Sign In</button>
                </form>

                <!-- Help Text -->
                <div class="help-text">
                    <p><strong>Need help?</strong><br>Contact your administrator to reset your password or create an account.</p>
                </div>

                <!-- Switch Login -->
                <div class="switch-login">
                    <small>Are you an admin? <a href="admin_login.php">Login here</a></small>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Restaurant Image -->
        <div class="image-section"></div>
    </div>
</body>
</html>
