<?php
require __DIR__ . '/../app/auth_only.php';

$pdo = Database::getConnection();
$pageTitle = 'Point of Sale';
$flashMessage = null;

$currentUser = Auth::user();

// Get all active categories
$categories = $pdo->query('SELECT * FROM categories WHERE is_active = 1 ORDER BY name')->fetchAll();

// Get all active products with category info
$products = $pdo->query('SELECT p.*, c.name as category_name 
                         FROM products p 
                         JOIN categories c ON p.category_id = c.id 
                         WHERE p.is_active = 1 
                         ORDER BY c.name, p.name')->fetchAll();

// Get today's sales for this cashier
$todaySales = $pdo->prepare('SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total
                             FROM sales 
                             WHERE user_id = :user_id AND DATE(created_at) = :today');
$todaySales->execute([
    ':user_id' => $currentUser['id'],
    ':today' => date('Y-m-d')
]);
$cashierStats = $todaySales->fetch();

ob_start();
?>
<style>
    .main-content {
        padding: 0 !important;
        height: calc(100vh - 80px);
        overflow: hidden;
        max-height: calc(100vh - 80px);
    }

    .pos-wrapper {
        height: 100%;
        display: flex;
        flex-direction: column;
        max-width: 100vw;
    }

    .pos-header {
        background: white;
        padding: 1rem 1.5rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid var(--border-color);
    }

    .pos-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .cashier-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .cashier-stats {
        text-align: right;
        background: var(--bg-light);
        padding: 0.75rem 1rem;
        border-radius: var(--border-radius-sm);
        border: 1px solid var(--border-color);
    }

    .cashier-stats small {
        display: block;
        color: var(--text-secondary);
        font-size: 0.75rem;
        font-weight: 500;
    }

    .cashier-stats strong {
        color: var(--primary-color);
        font-size: 1.1rem;
        font-weight: 700;
    }

    .pos-main {
        flex: 1;
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 0;
        overflow: hidden;
        min-height: 0;
    }

    .products-section {
        background: var(--bg-light);
        padding: 1rem;
        overflow-y: auto;
        min-width: 0;
    }

    .category-filter {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .category-btn {
        padding: 0.5rem 1rem;
        border: 2px solid var(--border-color);
        background: white;
        border-radius: var(--border-radius-sm);
        cursor: pointer;
        transition: var(--transition);
        font-weight: 500;
        font-size: 0.9rem;
        color: var(--text-secondary);
    }

    .category-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
        transform: translateY(-1px);
    }

    .category-btn.active {
        border-color: var(--primary-color);
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(217, 119, 6, 0.3);
    }

    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 0.75rem;
    }

    .product-card {
        background: white;
        border-radius: var(--border-radius-sm);
        padding: 1.25rem;
        cursor: pointer;
        transition: var(--transition);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        text-align: center;
        position: relative;
        border: 2px solid var(--border-color);
    }

    .product-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        border-color: var(--primary-color);
    }

    .product-card.out-of-stock {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f8f9fa;
    }

    .product-card.out-of-stock:hover {
        transform: none;
        border-color: var(--border-color);
    }

    .product-icon {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }

    .product-name {
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 0.5rem;
        color: var(--text-primary);
    }

    .product-price {
        color: var(--primary-color);
        font-weight: 700;
        font-size: 1.1rem;
    }

    .product-stock {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: var(--warning-gradient);
        color: white;
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-weight: 600;
    }

    .cart-section {
        background: white;
        display: flex;
        flex-direction: column;
        box-shadow: -2px 0 10px rgba(0, 0, 0, 0.05);
        height: 100%;
        max-height: 100%;
        overflow: hidden;
    }

    .cart-header {
        padding: 0.75rem 1.5rem;
        border-bottom: 2px solid var(--bg-light);
        flex-shrink: 0;
    }

    .cart-header h2 {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
    }

    .cart-items {
        overflow-y: auto;
        padding: 1rem 1.5rem;
        min-height: 0;
        max-height: 400px;
        flex: 1 1 0;
    }

    .cart-items::-webkit-scrollbar {
        width: 6px;
    }

    .cart-items::-webkit-scrollbar-track {
        background: var(--bg-light);
        border-radius: 3px;
    }

    .cart-items::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 3px;
    }

    .cart-items::-webkit-scrollbar-thumb:hover {
        background: var(--text-secondary);
    }

    .cart-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        background: var(--bg-light);
        border-radius: var(--border-radius-sm);
        margin-bottom: 0.5rem;
    }

    .cart-item-info {
        flex: 1;
    }

    .cart-item-name {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .cart-item-price {
        color: var(--text-secondary);
        font-size: 0.9rem;
    }

    .cart-item-controls {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .qty-btn {
        width: 32px;
        height: 32px;
        border: none;
        background: var(--primary-color);
        color: white;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 700;
        transition: var(--transition);
    }

    .qty-btn:hover {
        background: var(--primary-dark);
    }

    .qty-display {
        font-weight: 700;
        min-width: 30px;
        text-align: center;
    }

    .remove-btn {
        background: #fc8181;
        color: white;
        border: none;
        padding: 0.5rem;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.9rem;
    }

    .remove-btn:hover {
        background: #e53e3e;
    }

    /* Ensure buttons are clickable */
    .qty-btn, .remove-btn {
        position: relative;
        z-index: 10;
        pointer-events: auto;
    }

    .qty-btn:active, .remove-btn:active {
        transform: scale(0.95);
    }

    .cart-summary {
        padding: 1rem 1.5rem 1.5rem;
        border-top: 2px solid var(--bg-light);
        flex: 0 0 auto;
        background: white;
        position: relative;
        z-index: 10;
        min-height: 180px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        font-size: 1rem;
    }

    .summary-row.total {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
        padding-top: 0.75rem;
        border-top: 2px solid var(--bg-light);
    }

    .checkout-btn {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        border-radius: var(--border-radius-sm);
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        transition: var(--transition);
        margin-top: 1rem;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .checkout-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
    }

    .checkout-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #9ca3af;
        box-shadow: none;
    }

    .clear-cart-btn {
        width: 100%;
        padding: 0.75rem;
        background: white;
        color: #e53e3e;
        border: 2px solid #e53e3e;
        border-radius: var(--border-radius-sm);
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        margin-top: 0.5rem;
    }

    .clear-cart-btn:hover {
        background: #e53e3e;
        color: white;
    }

    .empty-cart {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--text-secondary);
    }

    .empty-cart-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    @media (max-width: 992px) {
        .pos-main {
            grid-template-columns: 1fr;
        }

        .cart-section {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            max-height: 60vh;
            z-index: 1000;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }

        .products-section {
            padding-bottom: 60vh;
        }

        .pos-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .cashier-info {
            width: 100%;
            justify-content: space-between;
        }
    }

    @media (max-width: 768px) {
        .pos-header h1 {
            font-size: 1.25rem;
        }

        .product-grid {
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 0.75rem;
        }

        .product-card {
            padding: 1rem;
        }

        .cart-section {
            max-height: 50vh;
        }
    }

    /* Enhanced Checkout Modal Styles */
    .checkout-modal .modal-dialog {
        max-width: 500px;
    }

    .checkout-modal .modal-body {
        padding: 2rem;
    }

    .checkout-amount-display {
        background: var(--bg-light);
        padding: 1.5rem;
        border-radius: var(--border-radius-sm);
        text-align: center;
        margin-bottom: 1.5rem;
        border: 2px solid var(--border-color);
    }

    .checkout-amount-display h3 {
        font-size: 2rem;
        font-weight: 800;
        color: var(--primary-color);
        margin: 0;
    }

    .cash-input-group {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .cash-input-group input {
        font-size: 1.25rem;
        padding: 1rem 1.5rem;
        text-align: center;
        font-weight: 600;
    }

    .change-display {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: var(--border-radius-sm);
        text-align: center;
    }

    .change-display h4 {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
    }

    /* Enhanced Receipt Modal */
    .receipt-modal .modal-dialog {
        max-width: 450px;
        margin-top: 2rem;
    }

    .receipt-container {
        font-family: 'Inter', sans-serif;
        background: white;
        border-radius: var(--border-radius-sm);
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        border: 2px solid var(--border-color);
    }

    .receipt-header {
        text-align: center;
        border-bottom: 2px dashed var(--border-color);
        padding-bottom: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .receipt-header h4 {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }

    .receipt-header p {
        margin: 0.25rem 0;
        color: var(--text-secondary);
        font-size: 0.9rem;
    }

    .receipt-items {
        margin-bottom: 1.5rem;
    }

    .receipt-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.75rem;
        padding: 0.5rem 0;
        font-size: 0.95rem;
    }

    .receipt-item-name {
        flex: 1;
        font-weight: 500;
        color: var(--text-primary);
    }

    .receipt-item-price {
        font-weight: 600;
        color: var(--primary-color);
        text-align: right;
        min-width: 80px;
    }

    .receipt-footer {
        border-top: 2px dashed var(--border-color);
        padding-top: 1.5rem;
    }

    .receipt-total-section {
        margin-bottom: 1.5rem;
    }

    .receipt-total-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-size: 1rem;
    }

    .receipt-total-item.final-total {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--primary-color);
        padding-top: 0.75rem;
        border-top: 1px solid var(--border-color);
        margin-top: 0.75rem;
    }

    .receipt-thank-you {
        text-align: center;
        color: var(--text-secondary);
        font-style: italic;
        margin-top: 1rem;
    }

    .receipt-timestamp {
        text-align: center;
        font-size: 0.8rem;
        color: var(--text-light);
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
    }

    /* Enhanced Print Styles */
    @media print {
        @page {
            margin: 0.5in;
            size: A4 portrait;
        }

        * {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
            font-family: 'Courier New', monospace !important;
        }

        /* Hide everything except receipt */
        .pos-wrapper,
        .navbar,
        .modal-header,
        .modal-footer,
        .btn-close {
            display: none !important;
        }

        /* Show only the receipt modal */
        .modal {
            position: static !important;
            display: block !important;
            padding: 0 !important;
        }

        .modal-dialog {
            position: static !important;
            margin: 0 auto !important;
            max-width: 100% !important;
            transform: none !important;
        }

        .modal-content {
            border: none !important;
            box-shadow: none !important;
        }

        .modal-body {
            padding: 0 !important;
        }

        .modal-backdrop {
            display: none !important;
        }

        .receipt-container {
            position: static !important;
            width: 80mm !important;
            max-width: 80mm !important;
            margin: 0 auto !important;
            transform: none !important;
            box-shadow: none !important;
            border: 2px solid #000 !important;
            background: white !important;
            padding: 20px !important;
            font-size: 12px !important;
            line-height: 1.5 !important;
            color: #000 !important;
        }

        /* Receipt Header Styles */
        .receipt-header {
            text-align: center !important;
            border-bottom: 2px dashed #000 !important;
            padding-bottom: 15px !important;
            margin-bottom: 15px !important;
        }

        .receipt-header h4 {
            color: #000 !important;
            font-size: 18px !important;
            font-weight: bold !important;
            margin: 0 0 10px 0 !important;
        }

        .receipt-header p {
            color: #000 !important;
            font-size: 12px !important;
            margin: 3px 0 !important;
        }

        /* Receipt Items */
        .receipt-items {
            margin-bottom: 15px !important;
        }

        .receipt-item {
            display: flex !important;
            justify-content: space-between !important;
            margin-bottom: 8px !important;
            padding: 2px 0 !important;
        }

        .receipt-item-name {
            color: #000 !important;
            font-size: 11px !important;
            flex: 1 !important;
        }

        .receipt-item-price {
            color: #000 !important;
            font-size: 11px !important;
            font-weight: bold !important;
            text-align: right !important;
            min-width: 60px !important;
        }

        /* Receipt Footer */
        .receipt-footer {
            border-top: 2px dashed #000 !important;
            padding-top: 15px !important;
        }

        .receipt-total-section {
            margin-bottom: 15px !important;
        }

        .receipt-total-item {
            display: flex !important;
            justify-content: space-between !important;
            margin-bottom: 5px !important;
            font-size: 12px !important;
            color: #000 !important;
        }

        .receipt-total-item.final-total {
            font-size: 16px !important;
            font-weight: bold !important;
            border-top: 1px solid #000 !important;
            padding-top: 8px !important;
            margin-top: 8px !important;
        }

        .receipt-thank-you {
            text-align: center !important;
            color: #000 !important;
            font-size: 11px !important;
            margin-top: 15px !important;
        }

        .receipt-timestamp {
            text-align: center !important;
            color: #666 !important;
            font-size: 10px !important;
            margin-top: 10px !important;
            padding-top: 10px !important;
            border-top: 1px solid #ccc !important;
        }
    }
</style>

<!-- Enhanced Checkout Modal -->
<div class="modal fade checkout-modal" id="checkoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"
                style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%); color: white;">
                <h5 class="modal-title">💳 Process Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="checkout-amount-display">
                    <label class="form-label fw-bold text-secondary mb-2">Total Amount</label>
                    <h3 id="modalTotal">₱0.00</h3>
                </div>

                <div class="cash-input-group">
                    <label for="cashInput" class="form-label fw-bold">Cash Tendered</label>
                    <input type="number" class="form-control form-control-lg" id="cashInput"
                        placeholder="Enter cash amount" step="0.01" min="0" autofocus>
                </div>

                <div class="change-display">
                    <label class="form-label fw-bold mb-2" style="color: rgba(255,255,255,0.9);">Change</label>
                    <h4 id="changeAmount">₱0.00</h4>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    ✕ Cancel
                </button>
                <button type="button" class="btn btn-success btn-lg" id="confirmCheckoutBtn" disabled>
                    ✓ Complete Sale
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Receipt Modal -->
<div class="modal fade receipt-modal" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"
                style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                <h5 class="modal-title">🧾 Transaction Receipt</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <div class="receipt-container" id="receiptContent">
                    <!-- Receipt will be generated here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    ✕ Close
                </button>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    🖨️ Print Receipt
                </button>
                <button type="button" class="btn btn-success" data-bs-dismiss="modal" onclick="location.reload()">
                    ✓ New Transaction
                </button>
            </div>
        </div>
    </div>
</div>

<div class="pos-wrapper">
    <!-- POS Header -->
    <div class="pos-header">
        <div>
            <h1>🏪 Point of Sale Terminal</h1>
            <small style="color: var(--text-secondary);">Cashier: <?= htmlspecialchars($currentUser['name']) ?></small>
        </div>
        <div class="cashier-info">
            <div class="cashier-stats">
                <small>Today's Sales</small>
                <strong>₱<?= number_format($cashierStats['total'], 2) ?></strong>
                <small><?= (int) $cashierStats['count'] ?> transactions</small>
            </div>
        </div>
    </div>

    <!-- POS Main Content -->
    <div class="pos-main">
        <!-- Products Section -->
        <div class="products-section">
            <div class="category-filter">
                <button class="category-btn active" data-category="all">All Products</button>
                <?php foreach ($categories as $cat): ?>
                    <button class="category-btn" data-category="<?= $cat['id'] ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="product-grid" id="productGrid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card <?= $product['stock'] <= 0 ? 'out-of-stock' : '' ?>"
                        data-category="<?= $product['category_id'] ?>" data-product='<?= json_encode([
                              'id' => $product['id'],
                              'name' => $product['name'],
                              'price' => $product['price'],
                              'stock' => $product['stock']
                          ]) ?>'>
                        <?php if ($product['stock'] <= 5 && $product['stock'] > 0): ?>
                            <span class="product-stock"><?= $product['stock'] ?> left</span>
                        <?php endif; ?>
                        <div class="product-icon">
                            <?php if (!empty($product['image'])): ?>
                                <img src="images/products/<?= htmlspecialchars($product['image']) ?>"
                                    alt="<?= htmlspecialchars($product['name']) ?>"
                                    style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                            <?php else: ?>
                                <?php
                                $icons = ['🍔', '🍕', '🍗', '🥤', '🍰', '🍜', '🍱', '🌮', '🍦', '☕'];
                                echo $icons[$product['id'] % count($icons)];
                                ?>
                            <?php endif; ?>
                        </div>
                        <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                        <div class="product-price">₱<?= number_format($product['price'], 2) ?></div>
                        <?php if ($product['stock'] <= 0): ?>
                            <small style="color: #e53e3e;">Out of Stock</small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Cart Section -->
        <div class="cart-section">
            <div class="cart-header">
                <h2>🛒 Current Order</h2>
            </div>

            <div class="cart-items" id="cartItems">
                <div class="empty-cart">
                    <div class="empty-cart-icon">🛒</div>
                    <p>Cart is empty<br><small>Click on products to add them</small></p>
                </div>
            </div>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="subtotal">₱0.00</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span id="total">₱0.00</span>
                </div>

                <button class="checkout-btn" id="checkoutBtn" disabled>
                    💳 Checkout
                </button>
                <button class="clear-cart-btn" id="clearCartBtn" style="display: none;">
                    🗑️ Clear Cart
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Cart management
    let cart = [];

    // Store product data for stock checking
    const productsData = {};
    document.querySelectorAll('.product-card').forEach(card => {
        try {
            const productData = JSON.parse(card.dataset.product);
            productsData[productData.id] = productData;
        } catch (e) {
            console.error('Error parsing product data:', e);
        }
    });

    // Category filtering
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const category = this.dataset.category;
            document.querySelectorAll('.product-card').forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Add to cart when clicking product card
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', function (e) {
            // Don't add to cart if clicking inside cart controls
            if (e.target.closest('.cart-item-controls')) return;
            if (this.classList.contains('out-of-stock')) return;

            const product = JSON.parse(this.dataset.product);
            // Ensure ID is a number
            product.id = parseInt(product.id);
            
            const existingItem = cart.find(item => item.id === product.id);

            if (existingItem) {
                if (existingItem.quantity < product.stock) {
                    existingItem.quantity++;
                } else {
                    alert('Not enough stock available!');
                    return;
                }
            } else {
                cart.push({
                    ...product,
                    quantity: 1
                });
            }
            
            updateCart();
        });
    });

    function updateCart() {
        const cartItemsEl = document.getElementById('cartItems');
        const subtotalEl = document.getElementById('subtotal');
        const totalEl = document.getElementById('total');
        const checkoutBtn = document.getElementById('checkoutBtn');
        const clearCartBtn = document.getElementById('clearCartBtn');

        if (cart.length === 0) {
            cartItemsEl.innerHTML = `
                <div class="empty-cart">
                    <div class="empty-cart-icon">🛒</div>
                    <p>Cart is empty<br><small>Click on products to add them</small></p>
                </div>
            `;
            checkoutBtn.disabled = true;
            clearCartBtn.style.display = 'none';
        } else {
            let html = '';
            cart.forEach(function(item) {
                html += `
                <div class="cart-item">
                    <div class="cart-item-info">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">₱${(item.price * item.quantity).toFixed(2)}</div>
                    </div>
                    <div class="cart-item-controls">
                        <button class="qty-btn decrease-btn" data-action="decrease" data-id="${item.id}" type="button">−</button>
                        <span class="qty-display">${item.quantity}</span>
                        <button class="qty-btn increase-btn" data-action="increase" data-id="${item.id}" type="button">+</button>
                        <button class="remove-btn" data-action="remove" data-id="${item.id}" type="button">🗑️</button>
                    </div>
                </div>`;
            });
            cartItemsEl.innerHTML = html;
            checkoutBtn.disabled = false;
            clearCartBtn.style.display = 'block';
        }

        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        subtotalEl.textContent = '₱' + subtotal.toFixed(2);
        totalEl.textContent = '₱' + subtotal.toFixed(2);
    }

    // Event delegation for cart buttons
    document.getElementById('cartItems').addEventListener('click', function(e) {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        
        const action = btn.getAttribute('data-action');
        const productId = parseInt(btn.getAttribute('data-id'));
        
        let changed = false;
        
        if (action === 'increase') {
            const item = cart.find(i => i.id === productId);
            if (item) {
                item.quantity++;
                changed = true;
            }
        } else if (action === 'decrease') {
            const item = cart.find(i => i.id === productId);
            if (item) {
                item.quantity--;
                if (item.quantity <= 0) {
                    cart = cart.filter(i => i.id !== productId);
                }
                changed = true;
            }
        } else if (action === 'remove') {
            cart = cart.filter(i => i.id !== productId);
            changed = true;
        }
        
        if (changed) {
            updateCart();
        }
    });

    document.getElementById('clearCartBtn').addEventListener('click', function () {
        if (confirm('Are you sure you want to clear the cart?')) {
            cart = [];
            updateCart();
        }
    });

    // Checkout Modal
    document.getElementById('checkoutBtn').addEventListener('click', function () {
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        document.getElementById('modalTotal').textContent = '₱' + total.toFixed(2);
        document.getElementById('cashInput').value = '';
        document.getElementById('changeAmount').textContent = '₱0.00';
        document.getElementById('confirmCheckoutBtn').disabled = true;

        const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
        checkoutModal.show();

        // Focus on cash input after modal is shown
        setTimeout(() => {
            document.getElementById('cashInput').focus();
        }, 500);
    });

    // Calculate change with enhanced feedback
    document.getElementById('cashInput').addEventListener('input', function () {
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const cash = parseFloat(this.value) || 0;
        const change = cash - total;

        const changeEl = document.getElementById('changeAmount');
        const confirmBtn = document.getElementById('confirmCheckoutBtn');

        if (cash >= total && cash > 0) {
            changeEl.textContent = '₱' + change.toFixed(2);
            changeEl.style.color = 'white';
            confirmBtn.disabled = false;
            confirmBtn.textContent = '✓ Complete Sale';
        } else if (cash > 0) {
            changeEl.textContent = '₱' + change.toFixed(2) + ' (Insufficient)';
            changeEl.style.color = '#ffeb3b';
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Insufficient Cash';
        } else {
            changeEl.textContent = '₱0.00';
            changeEl.style.color = 'white';
            confirmBtn.disabled = true;
            confirmBtn.textContent = '✓ Complete Sale';
        }
    });

    // Allow Enter key to complete checkout
    document.getElementById('cashInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter' && !document.getElementById('confirmCheckoutBtn').disabled) {
            document.getElementById('confirmCheckoutBtn').click();
        }
    });

    // Confirm checkout
    document.getElementById('confirmCheckoutBtn').addEventListener('click', function () {
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const cash = parseFloat(document.getElementById('cashInput').value);
        const change = cash - total;

        // Process checkout
        fetch('process_sale.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                items: cart,
                total: total,
                cash: cash,
                change: change
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hide checkout modal
                    bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();

                    // Generate receipt
                    generateReceipt(data.sale_id, cart, total, cash, change);

                    // Clear cart
                    cart = [];
                    updateCart();
                } else {
                    alert('Error processing sale: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error processing sale. Please try again.');
                console.error(error);
            });
    });

    function generateReceipt(saleId, items, total, cash, change) {
        const now = new Date();
        const receiptHTML = `
                <div class="receipt-header">
                    <h4>🍽️ Restaurant POS System</h4>
                    <p><strong>Receipt #${saleId}</strong></p>
                    <p>${now.toLocaleDateString()} ${now.toLocaleTimeString()}</p>
                    <p>Cashier: <?= htmlspecialchars($currentUser['name']) ?></p>
                </div>
                
                <div class="receipt-items">
                    ${items.map(item => `
                        <div class="receipt-item">
                            <div class="receipt-item-name">
                                ${item.name}<br>
                                <small style="color: var(--text-secondary);">₱${parseFloat(item.price).toFixed(2)} × ${item.quantity}</small>
                            </div>
                            <div class="receipt-item-price">₱${(item.price * item.quantity).toFixed(2)}</div>
                        </div>
                    `).join('')}
                </div>
                
                <div class="receipt-footer">
                    <div class="receipt-total-section">
                        <div class="receipt-total-item">
                            <span>Subtotal:</span>
                            <span>₱${total.toFixed(2)}</span>
                        </div>
                        <div class="receipt-total-item final-total">
                            <span>TOTAL:</span>
                            <span>₱${total.toFixed(2)}</span>
                        </div>
                    </div>
                    
                    <div class="receipt-total-section">
                        <div class="receipt-total-item">
                            <span>Cash Tendered:</span>
                            <span>₱${cash.toFixed(2)}</span>
                        </div>
                        <div class="receipt-total-item">
                            <span>Change:</span>
                            <span>₱${change.toFixed(2)}</span>
                        </div>
                    </div>
                    
                    <div class="receipt-thank-you">
                        <p>Thank you for your purchase!</p>
                        <p>Please come again soon! 😊</p>
                    </div>
                    
                    <div class="receipt-timestamp">
                        Transaction processed on ${now.toLocaleString()}
                    </div>
                </div>
            `;

        document.getElementById('receiptContent').innerHTML = receiptHTML;
        const receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
        receiptModal.show();
    }
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../views/layout.php';

