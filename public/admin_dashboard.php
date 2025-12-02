<?php

require __DIR__ . '/../app/auth_only.php';

if (!Auth::isAdmin()) {
    header('Location: pos.php');
    exit;
}

$pageTitle = 'Admin Dashboard';
$flashMessage = null;

$pdo = Database::getConnection();

// Get statistics
$today = date('Y-m-d');
$thisMonth = date('Y-m');

// Today's sales
$stmt = $pdo->prepare('SELECT COALESCE(SUM(total_amount), 0) AS total_sales, COUNT(*) AS sale_count 
                       FROM sales WHERE DATE(created_at) = :today');
$stmt->execute([':today' => $today]);
$todayStats = $stmt->fetch();

// This month's sales
$stmt = $pdo->prepare('SELECT COALESCE(SUM(total_amount), 0) AS total_sales, COUNT(*) AS sale_count 
                       FROM sales WHERE DATE_FORMAT(created_at, "%Y-%m") = :month');
$stmt->execute([':month' => $thisMonth]);
$monthStats = $stmt->fetch();

// Product statistics
$productCount = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn();
$totalProducts = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();

// Low stock items
$lowStockCount = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE stock <= 5 AND is_active = 1')->fetchColumn();

// Category count
$categoryCount = (int)$pdo->query('SELECT COUNT(*) FROM categories WHERE is_active = 1')->fetchColumn();

// Recent sales
$recentSales = $pdo->query('SELECT s.*, u.name as cashier_name 
                            FROM sales s 
                            JOIN users u ON s.user_id = u.id 
                            ORDER BY s.created_at DESC 
                            LIMIT 5')->fetchAll();

// Top selling products
$topProducts = $pdo->query('SELECT p.name, SUM(si.quantity) as total_sold, SUM(si.line_total) as revenue
                            FROM sale_items si
                            JOIN products p ON si.product_id = p.id
                            GROUP BY p.id
                            ORDER BY total_sold DESC
                            LIMIT 5')->fetchAll();

ob_start();
?>
<div class="page-header">
    <h1 class="page-title">Admin Dashboard</h1>
    <p class="page-subtitle">Welcome back, <?= htmlspecialchars(Auth::user()['name']) ?>! Here's your business overview.</p>
</div>

<div class="row g-4 mb-4">
    <!-- Today's Sales -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon primary">💰</div>
            <div class="stat-value">₱<?= number_format($todayStats['total_sales'], 2) ?></div>
            <div class="stat-label">Today's Sales</div>
            <div class="stat-change positive">
                <?= (int)$todayStats['sale_count'] ?> transactions
            </div>
        </div>
    </div>

    <!-- Monthly Sales -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon success">📈</div>
            <div class="stat-value">₱<?= number_format($monthStats['total_sales'], 2) ?></div>
            <div class="stat-label">This Month</div>
            <div class="stat-change positive">
                <?= (int)$monthStats['sale_count'] ?> transactions
            </div>
        </div>
    </div>

    <!-- Active Products -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon info">📦</div>
            <div class="stat-value"><?= $productCount ?></div>
            <div class="stat-label">Active Products</div>
            <div class="stat-change">
                <?= $totalProducts ?> total
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon warning">⚠️</div>
            <div class="stat-value"><?= $lowStockCount ?></div>
            <div class="stat-label">Low Stock Items</div>
            <div class="stat-change <?= $lowStockCount > 0 ? 'negative' : 'positive' ?>">
                <?= $lowStockCount > 0 ? 'Needs attention' : 'All good' ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Sales -->
    <div class="col-lg-7">
        <div class="dashboard-card">
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text-primary);">
                📋 Recent Transactions
            </h3>
            <?php if (count($recentSales) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cashier</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentSales as $sale): ?>
                                <tr>
                                    <td><strong>#<?= $sale['id'] ?></strong></td>
                                    <td><?= htmlspecialchars($sale['cashier_name']) ?></td>
                                    <td><strong>₱<?= number_format($sale['total_amount'], 2) ?></strong></td>
                                    <td>
                                        <span class="badge bg-primary"><?= htmlspecialchars($sale['order_type']) ?></span>
                                    </td>
                                    <td><?= date('M d, g:i A', strtotime($sale['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted text-center py-4">No sales recorded yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top Products & Quick Actions -->
    <div class="col-lg-5">
        <!-- Top Products -->
        <div class="dashboard-card mb-4">
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text-primary);">
                🏆 Top Selling Products
            </h3>
            <?php if (count($topProducts) > 0): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($topProducts as $product): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                            <div>
                                <strong><?= htmlspecialchars($product['name']) ?></strong>
                                <br>
                                <small class="text-muted"><?= (int)$product['total_sold'] ?> sold</small>
                            </div>
                            <span class="badge bg-success rounded-pill">
                                ₱<?= number_format($product['revenue'], 2) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted text-center py-3">No sales data yet.</p>
            <?php endif; ?>
        </div>


    </div>
</div>

<?php
$content = ob_get_clean();

require __DIR__ . '/../views/layout.php';

