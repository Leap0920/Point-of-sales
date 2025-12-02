<?php
/**
 * Fix Password Hashes
 * This will generate correct password hashes and update the database
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../app/Database.php';

echo "<h1>Password Hash Fix</h1>";
echo "<style>body { font-family: Arial, sans-serif; padding: 20px; } .success { color: green; } .error { color: red; } pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }</style>";

try {
    $pdo = Database::getConnection();
    
    // Generate correct password hashes
    $adminPassword = 'admin123';
    $cashierPassword = 'cashier123';
    
    $adminHash = password_hash($adminPassword, PASSWORD_DEFAULT);
    $cashierHash = password_hash($cashierPassword, PASSWORD_DEFAULT);
    
    echo "<h2>Generated Password Hashes</h2>";
    echo "<pre>";
    echo "Admin password: $adminPassword\n";
    echo "Admin hash: $adminHash\n\n";
    echo "Cashier password: $cashierPassword\n";
    echo "Cashier hash: $cashierHash\n";
    echo "</pre>";
    
    // Verify the hashes work
    echo "<h2>Verifying Hashes</h2>";
    if (password_verify($adminPassword, $adminHash)) {
        echo "<p class='success'>✓ Admin hash verification successful</p>";
    } else {
        echo "<p class='error'>✗ Admin hash verification failed</p>";
    }
    
    if (password_verify($cashierPassword, $cashierHash)) {
        echo "<p class='success'>✓ Cashier hash verification successful</p>";
    } else {
        echo "<p class='error'>✗ Cashier hash verification failed</p>";
    }
    
    // Update admin password
    echo "<h2>Updating Database</h2>";
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
    
    $stmt->execute([$adminHash, 'admin']);
    echo "<p class='success'>✓ Admin password updated</p>";
    
    $stmt->execute([$cashierHash, 'cashier']);
    echo "<p class='success'>✓ Cashier password updated</p>";
    
    // Verify the update
    echo "<h2>Verification</h2>";
    $stmt = $pdo->prepare("SELECT username, password_hash FROM users WHERE username IN ('admin', 'cashier')");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    foreach ($users as $user) {
        $testPassword = ($user['username'] === 'admin') ? $adminPassword : $cashierPassword;
        if (password_verify($testPassword, $user['password_hash'])) {
            echo "<p class='success'>✓ {$user['username']} password is now correct</p>";
        } else {
            echo "<p class='error'>✗ {$user['username']} password still incorrect</p>";
        }
    }
    
    echo "<hr>";
    echo "<h2>✅ Password Fix Complete!</h2>";
    echo "<p>You can now login with:</p>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> username: <code>admin</code>, password: <code>admin123</code></li>";
    echo "<li><strong>Cashier:</strong> username: <code>cashier</code>, password: <code>cashier123</code></li>";
    echo "</ul>";
    echo "<p><a href='login.php' style='display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px;'>Go to Login Page</a></p>";
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
