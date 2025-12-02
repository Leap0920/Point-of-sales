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
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            🏪 <?= htmlspecialchars($config['app']['name']) ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if ($currentUser && $currentUser['role'] === 'Admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="index.php">📊 Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="categories.php">📁 Categories</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php">📦 Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="pos.php">💳 POS</a></li>
                    <li class="nav-item"><a class="nav-link" href="users.php">👥 Users</a></li>
                <?php elseif ($currentUser): ?>
                    <!-- Cashier / employee: POS only -->
                    <li class="nav-item"><a class="nav-link" href="pos.php">💳 Point of Sale</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php if ($currentUser): ?>
                    <li class="nav-item">
                        <span class="navbar-text me-3" style="color: var(--text-secondary);">
                            👤 <?= htmlspecialchars($currentUser['name']) ?> 
                            <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 600;">
                                (<?= htmlspecialchars($currentUser['role']) ?>)
                            </span>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">🚪 Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">🔐 Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container my-4">
    <?php if (!empty($flashMessage)): ?>
        <div class="alert alert-<?= htmlspecialchars($flashMessage['type'] ?? 'info') ?>">
            <?= htmlspecialchars($flashMessage['text'] ?? '') ?>
        </div>
    <?php endif; ?>

    <?= $content ?? '' ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

