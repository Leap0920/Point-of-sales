<?php
/**
 * Database Diagnostic Script
 * This will help identify login issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Diagnostic Check</h1>";
echo "<style>body { font-family: Arial, sans-serif; padding: 20px; } .success { color: green; } .error { color: red; } .warning { color: orange; } pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }</style>";

// Step 1: Check if Database.php exists
echo "<h2>1. Checking Database Connection Class</h2>";
if (file_exists(__DIR__ . '/../app/Database.php')) {
    echo "<p class='success'>✓ Database.php exists</p>";
    require_once __DIR__ . '/../app/Database.php';
} else {
    echo "<p class='error'>✗ Database.php not found!</p>";
    exit;
}

// Step 2: Try to connect to database
echo "<h2>2. Testing Database Connection</h2>";
try {
    $pdo = Database::getConnection();
    echo "<p class='success'>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// Step 3: Check if tables exist
echo "<h2>3. Checking Database Tables</h2>";
$tables = ['roles', 'users', 'categories', 'products', 'sales', 'sale_items'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "<p class='success'>✓ Table '$table' exists with $count records</p>";
    } catch (Exception $e) {
        echo "<p class='error'>✗ Table '$table' not found or error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Step 4: Check roles
echo "<h2>4. Checking Roles</h2>";
try {
    $stmt = $pdo->query("SELECT * FROM roles");
    $roles = $stmt->fetchAll();
    if (count($roles) > 0) {
        echo "<p class='success'>✓ Found " . count($roles) . " role(s):</p>";
        echo "<pre>";
        foreach ($roles as $role) {
            echo "ID: {$role['id']}, Name: {$role['name']}\n";
        }
        echo "</pre>";
    } else {
        echo "<p class='error'>✗ No roles found! Please run database/seed.sql</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error checking roles: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Step 5: Check users
echo "<h2>5. Checking Users</h2>";
try {
    $stmt = $pdo->query("SELECT u.id, u.name, u.username, u.is_active, r.name as role_name 
                         FROM users u 
                         LEFT JOIN roles r ON u.role_id = r.id");
    $users = $stmt->fetchAll();
    if (count($users) > 0) {
        echo "<p class='success'>✓ Found " . count($users) . " user(s):</p>";
        echo "<pre>";
        foreach ($users as $user) {
            $active = $user['is_active'] ? 'Active' : 'Inactive';
            echo "ID: {$user['id']}, Username: {$user['username']}, Name: {$user['name']}, Role: {$user['role_name']}, Status: $active\n";
        }
        echo "</pre>";
    } else {
        echo "<p class='error'>✗ No users found! Please run database/seed.sql</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error checking users: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Step 6: Test password verification
echo "<h2>6. Testing Password Hash</h2>";
$testPassword = 'admin123';
$expectedHash = '$2y$10$2ZFHqgCXlCbzNd7wx0jv7eKq9hR3.kX5JziWSi0JyoQo47FwAoXfK';

if (password_verify($testPassword, $expectedHash)) {
    echo "<p class='success'>✓ Password verification works correctly</p>";
    echo "<p>The password 'admin123' matches the hash in the database</p>";
} else {
    echo "<p class='error'>✗ Password verification failed!</p>";
}

// Step 7: Test actual login
echo "<h2>7. Testing Login Function</h2>";
try {
    require_once __DIR__ . '/../app/Auth.php';
    
    // Test admin login
    $stmt = $pdo->prepare('SELECT u.id, u.name, u.username, u.password_hash, u.is_active, r.name AS role_name 
                           FROM users u 
                           JOIN roles r ON u.role_id = r.id 
                           WHERE u.username = :username LIMIT 1');
    $stmt->execute([':username' => 'admin']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<p class='success'>✓ Admin user found in database</p>";
        echo "<pre>";
        echo "Username: {$user['username']}\n";
        echo "Name: {$user['name']}\n";
        echo "Role: {$user['role_name']}\n";
        echo "Active: " . ($user['is_active'] ? 'Yes' : 'No') . "\n";
        echo "Password Hash: {$user['password_hash']}\n";
        echo "</pre>";
        
        if (password_verify('admin123', $user['password_hash'])) {
            echo "<p class='success'>✓ Password 'admin123' is correct for admin user</p>";
        } else {
            echo "<p class='error'>✗ Password 'admin123' does NOT match the hash in database!</p>";
            echo "<p class='warning'>The password hash in your database might be incorrect.</p>";
        }
    } else {
        echo "<p class='error'>✗ Admin user NOT found in database!</p>";
        echo "<p class='warning'>Please run the seed.sql file to create the admin user.</p>";
    }
    
    // Test cashier login
    $stmt->execute([':username' => 'cashier']);
    $cashier = $stmt->fetch();
    
    if ($cashier) {
        echo "<p class='success'>✓ Cashier user found in database</p>";
        if (password_verify('cashier123', $cashier['password_hash'])) {
            echo "<p class='success'>✓ Password 'cashier123' is correct for cashier user</p>";
        } else {
            echo "<p class='error'>✗ Password 'cashier123' does NOT match!</p>";
        }
    } else {
        echo "<p class='warning'>⚠ Cashier user NOT found (this is optional)</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error testing login: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Step 8: Check session configuration
echo "<h2>8. Session Configuration</h2>";
echo "<pre>";
echo "Session Save Path: " . session_save_path() . "\n";
echo "Session Name: " . session_name() . "\n";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "\n";
echo "</pre>";

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p>If all checks above are green (✓), your database is set up correctly.</p>";
echo "<p>If you see any red (✗) errors, please fix them before trying to login.</p>";
echo "<p><a href='login.php'>← Back to Login</a></p>";
