<?php

require __DIR__ . '/../../app/auth_only.php';

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();

    // Get all active categories
    $categoriesStmt = $pdo->query('SELECT * FROM categories WHERE is_active = 1 ORDER BY name');
    $categories = $categoriesStmt->fetchAll();

    // Get all active products with category info
    $productsStmt = $pdo->query('SELECT p.*, c.name as category_name 
                                  FROM products p 
                                  JOIN categories c ON p.category_id = c.id 
                                  WHERE p.is_active = 1 
                                  ORDER BY c.name, p.name');
    $products = $productsStmt->fetchAll();

    echo json_encode([
        'success' => true,
        'categories' => $categories,
        'products' => $products
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching data: ' . $e->getMessage()
    ]);
}
