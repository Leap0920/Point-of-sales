<?php
require_once __DIR__ . '/../app/Auth.php';

Auth::startSession();

// If already logged in, redirect to dashboard
if (Auth::check()) {
    header('Location: index.php');
    exit;
}

$config = require __DIR__ . '/../config/config.php';
$pdo = Database::getConnection();
$error = null;
$success = null;
$step = 'request'; // request, verify, reset

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'request';
    
    if ($action === 'request') {
        $username = trim($_POST['username'] ?? '');
        
        if (empty($username)) {
            $error = 'Please enter your username or email.';
        } else {
            // Check if user exists
            $userStmt = $pdo->prepare('SELECT id, name, username FROM users WHERE username = ? OR name = ?');
            $userStmt->execute([$username, $username]);
            $user = $userStmt->fetch();
            
            if ($user) {
                // Generate reset code (in real app, this would be sent via email)
                $resetCode = sprintf('%06d', mt_rand(100000, 999999));
                
                // Store reset code in session (in real app, store in database with expiry)
                $_SESSION['reset_user_id'] = $user['id'];
                $_SESSION['reset_code'] = $resetCode;
                $_SESSION['reset_expires'] = time() + 900; // 15 minutes
                
                $success = "Reset code generated: <strong>{$resetCode}</strong><br><small>In a real application, this would be sent to your email.</small>";
                $step = 'verify';
            } else {
                $error = 'No account found with that username or email.';
            }
        }
    } elseif ($action === 'verify') {
        $code = trim($_POST['code'] ?? '');
        
        if (empty($code)) {
            $error = 'Please enter the reset code.';
        } elseif (!isset($_SESSION['reset_code']) || !isset($_SESSION['reset_expires'])) {
            $error = 'Reset session expired. Please start over.';
        } elseif (time() > $_SESSION['reset_expires']) {
            $error = 'Reset code expired. Please request a new one.';
            unset($_SESSION['reset_code'], $_SESSION['reset_user_id'], $_SESSION['reset_expires']);
        } elseif ($code !== $_SESSION['reset_code']) {
            $error = 'Invalid reset code. Please try again.';
        } else {
            $success = 'Code verified! You can now reset your password.';
            $step = 'reset';
        }
    } elseif ($action === 'reset') {
        $password = trim($_POST['password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');
        
        if (empty($password) || empty($confirmPassword)) {
            $error = 'Please enter and confirm your new password.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif (!isset($_SESSION['reset_user_id'])) {
            $error = 'Reset session expired. Please start over.';
        } else {
            // Update password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            
            if ($updateStmt->execute([$passwordHash, $_SESSION['reset_user_id']])) {
                // Clear reset session
                unset($_SESSION['reset_code'], $_SESSION['reset_user_id'], $_SESSION['reset_expires']);
                $success = 'Password reset successfully! You can now login with your new password.';
                $step = 'complete';
            } else {
                $error = 'Failed to update password. Please try again.';
            }
        }
    }
}

// Check if we're in verify or reset step from session
if (isset($_SESSION['reset_code']) && $step === 'request') {
    $step = 'verify';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($config['app']['name']) ?> - Forgot Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .forgot-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
        }

        .forgot-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff6b35 0%, #ff9f40 50%, #ffc107 100%);
        }

        .brand-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-logo .logo-icon {
            font-size: 3rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .brand-name {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ff6b35 0%, #ff9f40 50%, #ffc107 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
            margin: 0 0.5rem;
            position: relative;
        }

        .step.active {
            background: linear-gradient(135deg, #ff6b35 0%, #ff9f40 100%);
            color: white;
        }

        .step.completed {
            background: #48bb78;
            color: white;
        }

        .step.inactive {
            background: #e2e8f0;
            color: #a0aec0;
        }

        .step::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 100%;
            width: 30px;
            height: 2px;
            background: #e2e8f0;
            transform: translateY(-50%);
        }

        .step:last-child::after {
            display: none;
        }

        .step.completed::after {
            background: #48bb78;
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 2rem;
        }

        .welcome-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .welcome-subtitle {
            color: #718096;
            font-size: 0.9rem;
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
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

        .form-field {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #4a5568;
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
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

        .form-text {
            font-size: 0.8rem;
            color: #718096;
            margin-top: 0.25rem;
        }

        .btn-primary {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #ff6b35 0%, #ff9f40 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
            margin-top: 1rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(255, 107, 53, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .back-link {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }

        .back-link a {
            color: #ff6b35;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .code-display {
            background: linear-gradient(135deg, #e6fffa 0%, #f0fff4 100%);
            border: 1px solid #9ae6b4;
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            margin: 1rem 0;
            font-family: 'Courier New', monospace;
        }

        @media (max-width: 768px) {
            .forgot-container {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }

            .welcome-title {
                font-size: 1.4rem;
            }

            .step {
                width: 35px;
                height: 35px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <!-- Logo -->
        <div class="brand-logo">
            <span class="logo-icon">🔐</span>
            <span class="brand-name"><?= htmlspecialchars($config['app']['name']) ?></span>
        </div>

        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step <?= $step === 'request' ? 'active' : ($step !== 'request' ? 'completed' : 'inactive') ?>">1</div>
            <div class="step <?= $step === 'verify' ? 'active' : ($step === 'reset' || $step === 'complete' ? 'completed' : 'inactive') ?>">2</div>
            <div class="step <?= $step === 'reset' ? 'active' : ($step === 'complete' ? 'completed' : 'inactive') ?>">3</div>
        </div>

        <!-- Welcome Text -->
        <div class="welcome-text">
            <?php if ($step === 'request'): ?>
                <h1 class="welcome-title">Forgot Password?</h1>
                <p class="welcome-subtitle">Enter your username to reset your password</p>
            <?php elseif ($step === 'verify'): ?>
                <h1 class="welcome-title">Verify Reset Code</h1>
                <p class="welcome-subtitle">Enter the 6-digit code to continue</p>
            <?php elseif ($step === 'reset'): ?>
                <h1 class="welcome-title">Reset Password</h1>
                <p class="welcome-subtitle">Enter your new password</p>
            <?php else: ?>
                <h1 class="welcome-title">Password Reset Complete</h1>
                <p class="welcome-subtitle">Your password has been successfully updated</p>
            <?php endif; ?>
        </div>

        <!-- Error/Success Messages -->
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <strong>⚠️</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <strong>✓</strong> <?= $success ?>
            </div>
        <?php endif; ?>

        <!-- Forms based on step -->
        <?php if ($step === 'request'): ?>
            <form method="post" novalidate>
                <input type="hidden" name="action" value="request">
                <div class="form-field">
                    <label class="form-label" for="username">Username or Email</label>
                    <input 
                        type="text" 
                        class="form-input" 
                        id="username" 
                        name="username" 
                        placeholder="Enter your username or email"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        required 
                        autofocus
                    >
                </div>
                <button type="submit" class="btn-primary">Send Reset Code</button>
            </form>

        <?php elseif ($step === 'verify'): ?>
            <form method="post" novalidate>
                <input type="hidden" name="action" value="verify">
                <div class="form-field">
                    <label class="form-label" for="code">Reset Code</label>
                    <input 
                        type="text" 
                        class="form-input" 
                        id="code" 
                        name="code" 
                        placeholder="Enter 6-digit code"
                        maxlength="6"
                        required 
                        autofocus
                        style="text-align: center; font-family: 'Courier New', monospace; font-size: 1.2rem; letter-spacing: 0.2em;"
                    >
                    <div class="form-text">Code expires in 15 minutes</div>
                </div>
                <button type="submit" class="btn-primary">Verify Code</button>
            </form>

        <?php elseif ($step === 'reset'): ?>
            <form method="post" novalidate>
                <input type="hidden" name="action" value="reset">
                <div class="form-field">
                    <label class="form-label" for="password">New Password</label>
                    <input 
                        type="password" 
                        class="form-input" 
                        id="password" 
                        name="password" 
                        placeholder="Enter new password"
                        required 
                        autofocus
                    >
                    <div class="form-text">Minimum 6 characters</div>
                </div>
                <div class="form-field">
                    <label class="form-label" for="confirm_password">Confirm Password</label>
                    <input 
                        type="password" 
                        class="form-input" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Confirm new password"
                        required
                    >
                </div>
                <button type="submit" class="btn-primary">Reset Password</button>
            </form>

        <?php else: ?>
            <div style="text-align: center; padding: 2rem 0;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">✅</div>
                <p style="color: #2f855a; font-weight: 600;">Password successfully reset!</p>
            </div>
        <?php endif; ?>

        <!-- Back Link -->
        <div class="back-link">
            <?php if ($step === 'complete'): ?>
                <a href="admin_login.php">← Back to Login</a>
            <?php else: ?>
                <a href="admin_login.php">← Back to Login</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>