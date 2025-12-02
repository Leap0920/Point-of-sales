<?php
// Simple migration script to add image column to products table

require __DIR__ . '/../app/Database.php';

try {
    $pdo = Database::getConnection();
    
    // Check if image column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'image'");
    if ($stmt->rowCount() == 0) {
        // Add image column
        $pdo->exec("ALTER TABLE products ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER cost");
        echo "✅ Successfully added image column to products table.\n";
    } else {
        echo "ℹ️ Image column already exists in products table.\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>