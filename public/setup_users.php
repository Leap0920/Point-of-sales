<?php
/**
 * Quick setup script to add cashier user
 * Run this file once to ensure the cashier user exists
 */

require __DIR__ . '/../app/Database.php';

try {
    $pdo = Database::getConnection();
    
    echo "Checking database setup...\n\n";
    
    // Check if cashier role exists
    $stmt = $pdo->query("SELECT id FROM roles WHERE name = 'Cashier'");
    $cashierRole = $stmt->fetch();
    
    if (!$cashierRole) {
        echo "❌ Cashier role not found. Please run database/seed.sql first.\n";
        exit(1);
    }
    
    echo "✓ Cashier role exists (ID: {$cashierRole['id']})\n";
    
    // Check if cashier user exists
    $stmt = $pdo->query("SELECT id, username FROM users WHERE username = 'cashier'");
    $cashierUser = $stmt->fetch();
    
    if ($cashierUser) {
        echo "✓ Cashier user already exists (ID: {$cashierUser['id']})\n";
    } else {
        // Add cashier user
        $stmt = $pdo->prepare("INSERT INTO users (name, username, password_hash, role_id, is_active) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            'Cashier Employee',
            'cashier',
            '$2y$10$2ZFHqgCXlCbzNd7wx0jv7eKq9hR3.kX5JziWSi0JyoQo47FwAoXfK', // cashier123
            $cashierRole['id'],
            1
        ]);
        
        echo "✓ Cashier user created successfully!\n";
    }
    
    // Check if admin user exists
    $stmt = $pdo->query("SELECT id, username FROM users WHERE username = 'admin'");
    $adminUser = $stmt->fetch();
    
    if ($adminUser) {
        echo "✓ Admin user exists (ID: {$adminUser['id']})\n";
    } else {
        echo "⚠️  Admin user not found. Please run database/seed.sql\n";
    }
    
    echo "\n=== Setup Complete ===\n";
    echo "Login credentials:\n";
    echo "  Admin:   admin / admin123\n";
    echo "  Cashier: cashier / cashier123\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
