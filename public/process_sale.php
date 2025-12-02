<?php
require __DIR__ . '/../app/auth_only.php';

header('Content-Type: application/json');

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['items']) || !isset($input['total'])) {
        throw new Exception('Invalid request data');
    }
    
    $items = $input['items'];
    $total = floatval($input['total']);
    $cash = floatval($input['cash']);
    $change = floatval($input['change']);
    
    if (empty($items)) {
        throw new Exception('Cart is empty');
    }
    
    if ($cash < $total) {
        throw new Exception('Insufficient cash tendered');
    }
    
    $pdo = Database::getConnection();
    $currentUser = Auth::user();
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Insert sale record
        $stmt = $pdo->prepare('INSERT INTO sales (user_id, order_type, total_amount, cash_tendered, change_due, created_at) 
                               VALUES (:user_id, :order_type, :total, :cash, :change, NOW())');
        $stmt->execute([
            ':user_id' => $currentUser['id'],
            ':order_type' => 'dine-in', // Default to dine-in
            ':total' => $total,
            ':cash' => $cash,
            ':change' => $change
        ]);
        
        $saleId = $pdo->lastInsertId();
        
        // Insert sale items and update stock
        $stmtItem = $pdo->prepare('INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) 
                                   VALUES (:sale_id, :product_id, :quantity, :price, :line_total)');
        
        $stmtStock = $pdo->prepare('UPDATE products SET stock = stock - :quantity WHERE id = :product_id');
        
        foreach ($items as $item) {
            // Insert sale item
            $stmtItem->execute([
                ':sale_id' => $saleId,
                ':product_id' => $item['id'],
                ':quantity' => $item['quantity'],
                ':price' => $item['price'],
                ':line_total' => $item['price'] * $item['quantity']
            ]);
            
            // Update stock
            $stmtStock->execute([
                ':quantity' => $item['quantity'],
                ':product_id' => $item['id']
            ]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'sale_id' => $saleId,
            'message' => 'Sale completed successfully'
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
