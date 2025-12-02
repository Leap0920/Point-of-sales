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
$pageTitle = 'Categories';
$flashMessage = null;

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
<div class="row">
    <div class="col-md-4">
        <h1 class="h4 mb-3"><?= $editCategory ? 'Edit Category' : 'Add Category' ?></h1>
        <form method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($editCategory['id'] ?? 0) ?>">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name"
                       value="<?= htmlspecialchars($editCategory['name'] ?? '') ?>" required>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active"
                    <?= (!isset($editCategory['is_active']) || $editCategory['is_active']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_active">
                    Active
                </label>
            </div>
            <button type="submit" class="btn btn-primary"><?= $editCategory ? 'Update' : 'Save' ?></button>
            <?php if ($editCategory): ?>
                <a href="categories.php" class="btn btn-secondary ms-2">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="col-md-8">
        <h1 class="h4 mb-3">Category List</h1>
        <table class="table table-striped table-bordered">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?= htmlspecialchars($cat['id']) ?></td>
                    <td><?= htmlspecialchars($cat['name']) ?></td>
                    <td><?= $cat['is_active'] ? 'Active' : 'Inactive' ?></td>
                    <td>
                        <a href="categories.php?edit=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
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


