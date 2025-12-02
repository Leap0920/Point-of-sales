<?php

require __DIR__ . '/../app/auth_only.php';
require __DIR__ . '/../app/Auth.php';
require __DIR__ . '/../app/Database.php';

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
<div class="row">
    <div class="col-md-5">
        <h1 class="h4 mb-3"><?= $editProduct ? 'Edit Product' : 'Add Product' ?></h1>
        <form method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($editProduct['id'] ?? 0) ?>">

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name"
                       value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Select category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"
                            <?= isset($editProduct['category_id']) && (int)$editProduct['category_id'] === (int)$cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price"
                       value="<?= htmlspecialchars($editProduct['price'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label for="cost" class="form-label">Cost (optional)</label>
                <input type="number" step="0.01" class="form-control" id="cost" name="cost"
                       value="<?= htmlspecialchars($editProduct['cost'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="stock" class="form-label">Stock</label>
                <input type="number" class="form-control" id="stock" name="stock"
                       value="<?= htmlspecialchars($editProduct['stock'] ?? 0) ?>" required>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active"
                    <?= (!isset($editProduct['is_active']) || $editProduct['is_active']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_active">
                    Active
                </label>
            </div>

            <button type="submit" class="btn btn-primary"><?= $editProduct ? 'Update' : 'Save' ?></button>
            <?php if ($editProduct): ?>
                <a href="products.php" class="btn btn-secondary ms-2">Cancel</a>
            <?php endif; ?>
        </form>
        <p class="mt-3">
            Manage categories here: <a href="categories.php">Categories</a>
        </p>
    </div>
    <div class="col-md-7">
        <h1 class="h4 mb-3">Product List</h1>
        <table class="table table-striped table-bordered">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $prod): ?>
                <tr>
                    <td><?= htmlspecialchars($prod['id']) ?></td>
                    <td><?= htmlspecialchars($prod['name']) ?></td>
                    <td><?= htmlspecialchars($prod['category_name']) ?></td>
                    <td><?= number_format($prod['price'], 2) ?></td>
                    <td><?= htmlspecialchars($prod['stock']) ?></td>
                    <td><?= $prod['is_active'] ? 'Active' : 'Inactive' ?></td>
                    <td>
                        <a href="products.php?edit=<?= $prod['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();

require __DIR__ . '/../views/layout.php';


