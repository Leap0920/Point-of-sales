<?php

require __DIR__ . '/../app/auth_only.php';

$currentUser = Auth::user();
if (!$currentUser || $currentUser['role'] !== 'Admin') {
    header('Location: pos.php');
    exit;
}

$pdo = Database::getConnection();
$pageTitle = 'Categories';
$flashMessage = null;

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Check if category has products
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = :id');
    $stmt->execute([':id' => $id]);
    $productCount = $stmt->fetchColumn();
    
    if ($productCount > 0) {
        $flashMessage = ['type' => 'danger', 'text' => 'Cannot delete category with existing products. Please delete or reassign products first.'];
    } else {
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $flashMessage = ['type' => 'success', 'text' => 'Category deleted successfully.'];
    }
    header('Location: categories.php');
    exit;
}

// Handle create/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($name === '') {
        $flashMessage = ['type' => 'danger', 'text' => 'Category name is required.'];
    } else {
        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE categories SET name = :name, is_active = :is_active WHERE id = :id');
            $stmt->execute([
                ':name' => $name,
                ':is_active' => $is_active,
                ':id' => $id,
            ]);
            $flashMessage = ['type' => 'success', 'text' => 'Category updated successfully.'];
        } else {
            $stmt = $pdo->prepare('INSERT INTO categories (name, is_active) VALUES (:name, :is_active)');
            $stmt->execute([
                ':name' => $name,
                ':is_active' => $is_active,
            ]);
            $flashMessage = ['type' => 'success', 'text' => 'Category created successfully.'];
        }
        header('Location: categories.php');
        exit;
    }
}

// Edit mode
$editCategory = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $editCategory = $stmt->fetch();
}

// List categories
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

ob_start();
?>
<div class="page-header">
    <div>
        <h1 class="page-title">📁 Categories</h1>
        <p class="page-subtitle">Manage product categories for your restaurant</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4"><?= $editCategory ? '✏️ Edit Category' : '➕ Add New Category' ?></h5>
                <form method="post">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($editCategory['id'] ?? 0) ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Category Name</label>
                        <input type="text" class="form-control form-control-lg" id="name" name="name"
                               value="<?= htmlspecialchars($editCategory['name'] ?? '') ?>" 
                               placeholder="e.g., Beverages, Main Course" required>
                    </div>
                    
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" role="switch"
                            <?= (!isset($editCategory['is_active']) || $editCategory['is_active']) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="is_active">
                            Active Status
                        </label>
                        <div class="form-text">Inactive categories won't appear in POS</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <?= $editCategory ? '💾 Update Category' : '➕ Create Category' ?>
                        </button>
                        <?php if ($editCategory): ?>
                            <a href="categories.php" class="btn btn-outline-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">📋 All Categories (<?= count($categories) ?>)</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th>Category Name</th>
                                <th style="width: 120px;">Status</th>
                                <th style="width: 120px;" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($categories) > 0): ?>
                                <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td><span class="badge bg-secondary">#<?= htmlspecialchars($cat['id']) ?></span></td>
                                        <td>
                                            <strong><?= htmlspecialchars($cat['name']) ?></strong>
                                        </td>
                                        <td>
                                            <?php if ($cat['is_active']): ?>
                                                <span class="badge bg-success">✓ Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">✗ Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="categories.php?edit=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                ✏️ Edit
                                            </a>
                                            <a href="categories.php?delete=<?= $cat['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete this category?')">
                                                🗑️ Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <div class="mb-3" style="font-size: 3rem;">📁</div>
                                        <p class="mb-0">No categories yet. Create your first category!</p>
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


