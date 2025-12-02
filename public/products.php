<?php

require __DIR__ . '/../app/auth_only.php';

$currentUser = Auth::user();
if (!$currentUser || $currentUser['role'] !== 'Admin') {
    header('Location: pos.php');
    exit;
}

$pdo = Database::getConnection();
$pageTitle = 'Products';
$flashMessage = null;

// Fetch categories for select
$categoriesStmt = $pdo->query('SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name');
$categories = $categoriesStmt->fetchAll();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $flashMessage = ['type' => 'success', 'text' => 'Product deleted successfully.'];
    header('Location: products.php');
    exit;
}

// Handle create/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $cost = ($_POST['cost'] === '' ? null : (float)$_POST['cost']);
    $stock = (int)($_POST['stock'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($name === '' || $category_id === 0 || $price <= 0) {
        $flashMessage = ['type' => 'danger', 'text' => 'Name, category, and price are required.'];
    } else {
        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE products 
                SET name = :name, category_id = :category_id, price = :price, cost = :cost, stock = :stock, is_active = :is_active 
                WHERE id = :id');
            $stmt->execute([
                ':name' => $name,
                ':category_id' => $category_id,
                ':price' => $price,
                ':cost' => $cost,
                ':stock' => $stock,
                ':is_active' => $is_active,
                ':id' => $id,
            ]);
            $flashMessage = ['type' => 'success', 'text' => 'Product updated successfully.'];
        } else {
            $stmt = $pdo->prepare('INSERT INTO products (name, category_id, price, cost, stock, is_active) 
                VALUES (:name, :category_id, :price, :cost, :stock, :is_active)');
            $stmt->execute([
                ':name' => $name,
                ':category_id' => $category_id,
                ':price' => $price,
                ':cost' => $cost,
                ':stock' => $stock,
                ':is_active' => $is_active,
            ]);
            $flashMessage = ['type' => 'success', 'text' => 'Product created successfully.'];
        }
        header('Location: products.php');
        exit;
    }
}

// Edit mode
$editProduct = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $editProduct = $stmt->fetch();
}

// List products with category name
$productsStmt = $pdo->query('
    SELECT p.*, c.name AS category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    ORDER BY p.name
');
$products = $productsStmt->fetchAll();

ob_start();
?>
<div class="page-header">
    <div>
        <h1 class="page-title">📦 Products</h1>
        <p class="page-subtitle">Manage your menu items and inventory</p>
    </div>
    <a href="categories.php" class="btn btn-outline-primary">
        📁 Manage Categories
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4"><?= $editProduct ? '✏️ Edit Product' : '➕ Add New Product' ?></h5>
                <form method="post">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($editProduct['id'] ?? 0) ?>">

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>" 
                               placeholder="e.g., Chicken Burger" required>
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label fw-semibold">Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"
                                    <?= isset($editProduct['category_id']) && (int)$editProduct['category_id'] === (int)$cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (count($categories) === 0): ?>
                            <div class="form-text text-danger">⚠️ Please create a category first</div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label fw-semibold">Price (₱)</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price"
                                   value="<?= htmlspecialchars($editProduct['price'] ?? '') ?>" 
                                   placeholder="0.00" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="cost" class="form-label fw-semibold">Cost (₱)</label>
                            <input type="number" step="0.01" class="form-control" id="cost" name="cost"
                                   value="<?= htmlspecialchars($editProduct['cost'] ?? '') ?>"
                                   placeholder="Optional">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="stock" class="form-label fw-semibold">Stock Quantity</label>
                        <input type="number" class="form-control" id="stock" name="stock"
                               value="<?= htmlspecialchars($editProduct['stock'] ?? 0) ?>" required>
                        <div class="form-text">Current inventory count</div>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" role="switch"
                            <?= (!isset($editProduct['is_active']) || $editProduct['is_active']) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="is_active">
                            Active Status
                        </label>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <?= $editProduct ? '💾 Update Product' : '➕ Create Product' ?>
                        </button>
                        <?php if ($editProduct): ?>
                            <a href="products.php" class="btn btn-outline-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">📋 All Products (<?= count($products) ?>)</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th style="width: 100px;">Price</th>
                                <th style="width: 80px;">Stock</th>
                                <th style="width: 100px;">Status</th>
                                <th style="width: 100px;" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($products) > 0): ?>
                                <?php foreach ($products as $prod): ?>
                                    <tr>
                                        <td><span class="badge bg-secondary">#<?= htmlspecialchars($prod['id']) ?></span></td>
                                        <td><strong><?= htmlspecialchars($prod['name']) ?></strong></td>
                                        <td><span class="badge bg-info"><?= htmlspecialchars($prod['category_name']) ?></span></td>
                                        <td><strong>₱<?= number_format($prod['price'], 2) ?></strong></td>
                                        <td>
                                            <?php if ($prod['stock'] <= 5): ?>
                                                <span class="badge bg-warning text-dark"><?= htmlspecialchars($prod['stock']) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-success"><?= htmlspecialchars($prod['stock']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($prod['is_active']): ?>
                                                <span class="badge bg-success">✓ Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">✗ Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="products.php?edit=<?= $prod['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                ✏️ Edit
                                            </a>
                                            <a href="products.php?delete=<?= $prod['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete this product?')">
                                                🗑️ Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <div class="mb-3" style="font-size: 3rem;">📦</div>
                                        <p class="mb-0">No products yet. Add your first product!</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();

require __DIR__ . '/../views/layout.php';


