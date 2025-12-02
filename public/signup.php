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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    $adminCode = trim($_POST['admin_code'] ?? '');

    // Validation
    if (empty($name) || empty($username) || empty($email) || empty($password) || empty($confirmPassword) || empty($adminCode)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($adminCode !== 'ADMIN2024') { // Simple admin code for security
        $error = 'Invalid admin registration code.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if username or email already exists
        $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR name = ?');
        $checkStmt->execute([$username, $email]);
        
        if ($checkStmt->fetchColumn() > 0) {
            $error = 'Username or email already exists.';
        } else {
            // Get Admin role ID
            $roleStmt = $pdo->prepare('SELECT id FROM roles WHERE name = ?');
            $roleStmt->execute(['Admin']);
            $adminRoleId = $roleStmt->fetchColumn();
            
            if ($adminRoleId) {
                // Create new admin user
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $insertStmt = $pdo->prepare('INSERT INTO users (name, username, password_hash, role_id, is_active) VALUES (?, ?, ?, ?, 1)');
                
                if ($insertStmt->execute([$name, $username, $passwordHash, $adminRoleId])) {
                    $success = 'Admin account created successfully! You can now login.';
                } else {
                    $error = 'Failed to create account. Please try again.';
                }
            } else {
                $error = 'Admin role not found in system.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($config['app']['name']) ?> - Admin Signup</title>
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

        .signup-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            width: 100%;
            max-width: 480px;
            position: relative;
            overflow: hidden;
        }

        .signup-container::before {
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

        .role-badge {
            display: inline-block;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 0.5rem;
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
            align-items: center;
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

        .btn-signup {
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

        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(255, 107, 53, 0.4);
        }

        .btn-signup:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }

        .login-link p {
            color: #718096;
            font-size: 0.9rem;
        }

        .login-link a {
            color: #ff6b35;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .admin-code-info {
            background: linear-gradient(135deg, #e6fffa 0%, #f0fff4 100%);
            border: 1px solid #9ae6b4;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            color: #2f855a;
        }

        @media (max-width: 768px) {
            .signup-container {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }

            .welcome-title {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <!-- Logo -->
        <div class="brand-logo">
            <span class="logo-icon">🍽️</span>
            <span class="brand-name"><?= htmlspecialchars($config['app']['name']) ?></span>
            <div>
                <span class="role-badge">👨‍💼 Admin Registration</span>
            </div>
        </div>

        <!-- Welcome Text -->
        <div class="welcome-text">
            <h1 class="welcome-title">Create Admin Account</h1>
            <p class="welcome-subtitle">Register as a restaurant administrator</p>
        </div>

        <!-- Admin Code Info -->
        <div class="admin-code-info">
            <strong>🔐 Admin Registration Code Required</strong><br>
            Contact your system administrator for the registration code.
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

        <!-- Signup Form -->
        <form method="post" novalidate>
            <div class="form-field">
                <label class="form-label" for="name">Full Name</label>
                <input 
                    type="text" 
                    class="form-input" 
                    id="name" 
                    name="name" 
                    placeholder="Enter your full name"
                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                    required 
                    autofocus
                >
            </div>

            <div class="form-field">
                <label class="form-label" for="username">Username</label>
                <input 
                    type="text" 
                    class="form-input" 
                    id="username" 
                    name="username" 
                    placeholder="Choose a username"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    required
                >
                <div class="form-text">This will be used for login</div>
            </div>

            <div class="form-field">
                <label class="form-label" for="email">Email Address</label>
                <input 
                    type="email" 
                    class="form-input" 
                    id="email" 
                    name="email" 
                    placeholder="Enter your email address"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-field">
                <label class="form-label" for="password">Password</label>
                <input 
                    type="password" 
                    class="form-input" 
                    id="password" 
                    name="password" 
                    placeholder="Create a password"
                    required
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
                    placeholder="Confirm your password"
                    required
                >
            </div>

            <div class="form-field">
                <label class="form-label" for="admin_code">Admin Registration Code</label>
                <input 
                    type="password" 
                    class="form-input" 
                    id="admin_code" 
                    name="admin_code" 
                    placeholder="Enter admin registration code"
                    required
                >
                <div class="form-text">Required for admin account creation</div>
            </div>

            <button type="submit" class="btn-signup">Create Admin Account</button>
        </form>

        <!-- Login Link -->
        <div class="login-link">
            <p>Already have an account? <a href="admin_login.php">Sign in here</a></p>
        </div>
    </div>
</body>
</html>