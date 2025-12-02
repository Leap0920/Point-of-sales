<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Dashboard';
}
$config = require __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Auth.php';
$currentUser = Auth::user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($config['app']['name'] . ' - ' . $pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <span class="brand-icon">🍽️</span>
            <span class="brand-text"><?= htmlspecialchars($config['app']['name']) ?></span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if ($currentUser && $currentUser['role'] === 'Admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'admin_dashboard.php' || basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>" href="index.php">
                            <span class="nav-icon">📊</span> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : '' ?>" href="categories.php">
                            <span class="nav-icon">📁</span> Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : '' ?>" href="products.php">
                            <span class="nav-icon">📦</span> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'pos.php' ? 'active' : '' ?>" href="pos.php">
                            <span class="nav-icon">💳</span> POS
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : '' ?>" href="users.php">
                            <span class="nav-icon">👥</span> Users
                        </a>
                    </li>
                <?php elseif ($currentUser): ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="pos.php">
                            <span class="nav-icon">💳</span> Point of Sale
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto align-items-center">
                <?php if ($currentUser): ?>
                    <li class="nav-item me-3">
                        <div class="user-info">
                            <span class="user-icon">👤</span>
                            <div class="user-details">
                                <span class="user-name"><?= htmlspecialchars($currentUser['name']) ?></span>
                                <span class="user-role"><?= htmlspecialchars($currentUser['role']) ?></span>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-danger btn-sm" href="logout.php">
                            <span class="nav-icon">🚪</span> Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm" href="login.php">
                            <span class="nav-icon">🔐</span> Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="main-content">
    <div class="container-fluid px-4 py-4">
        <?php if (!empty($flashMessage)): ?>
            <div class="alert alert-<?= htmlspecialchars($flashMessage['type'] ?? 'info') ?> alert-dismissible fade show" role="alert">
                <strong><?= $flashMessage['type'] === 'success' ? '✓' : '⚠️' ?></strong>
                <?= htmlspecialchars($flashMessage['text'] ?? $flashMessage['message'] ?? '') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </div>
</main>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">⚠️ Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage" class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmYesBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Custom confirm function
function customConfirm(message, callback) {
    document.getElementById('confirmMessage').textContent = message;
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    const yesBtn = document.getElementById('confirmYesBtn');
    
    // Remove old event listeners
    const newYesBtn = yesBtn.cloneNode(true);
    yesBtn.parentNode.replaceChild(newYesBtn, yesBtn);
    
    // Add new event listener
    newYesBtn.addEventListener('click', function() {
        modal.hide();
        callback(true);
    });
    
    modal.show();
}
</script>
</body>
</html>

