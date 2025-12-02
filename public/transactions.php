<?php
require __DIR__ . '/../app/auth_only.php';

$currentUser = Auth::user();
if (!$currentUser || $currentUser['role'] !== 'Admin') {
    header('Location: pos.php');
    exit;
}

$pdo = Database::getConnection();
$pageTitle = 'All Transactions';
$flashMessage = null;

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get total count
$countStmt = $pdo->query('SELECT COUNT(*) FROM sales');
$totalTransactions = $countStmt->fetchColumn();
$totalPages = ceil($totalTransactions / $limit);

// Get all transactions with pagination
$transactionsStmt = $pdo->prepare('
    SELECT s.*, u.name as cashier_name 
    FROM sales s 
    JOIN users u ON s.user_id = u.id 
    ORDER BY s.created_at DESC 
    LIMIT :limit OFFSET :offset
');
$transactionsStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$transactionsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$transactionsStmt->execute();
$transactions = $transactionsStmt->fetchAll();

// Get transaction details for each sale
$transactionDetails = [];
if (!empty($transactions)) {
    $saleIds = array_column($transactions, 'id');
    $placeholders = str_repeat('?,', count($saleIds) - 1) . '?';
    
    $detailsStmt = $pdo->prepare("
        SELECT si.*, p.name as product_name, s.id as sale_id
        FROM sale_items si 
        JOIN products p ON si.product_id = p.id 
        JOIN sales s ON si.sale_id = s.id
        WHERE si.sale_id IN ($placeholders)
        ORDER BY si.sale_id, si.id
    ");
    $detailsStmt->execute($saleIds);
    $details = $detailsStmt->fetchAll();
    
    foreach ($details as $detail) {
        $transactionDetails[$detail['sale_id']][] = $detail;
    }
}

ob_start();
?>
<div class="page-header">
    <div>
        <h1 class="page-title">📋 All Transactions</h1>
        <p class="page-subtitle">Complete transaction history and details</p>
    </div>
    <a href="index.php" class="btn btn-outline-primary">
        ← Back to Dashboard
    </a>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">
                        💳 Transaction History (<?= number_format($totalTransactions) ?> total)
                    </h5>
                    <div class="text-muted">
                        Page <?= $page ?> of <?= $totalPages ?>
                    </div>
                </div>

                <?php if (count($transactions) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;">ID</th>
                                    <th>Cashier</th>
                                    <th style="width: 120px;">Amount</th>
                                    <th style="width: 100px;">Type</th>
                                    <th style="width: 150px;">Date & Time</th>
                                    <th style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">#<?= $transaction['id'] ?></span>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($transaction['cashier_name']) ?></strong>
                                        </td>
                                        <td>
                                            <strong class="text-success">₱<?= number_format($transaction['total_amount'], 2) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?= htmlspecialchars($transaction['order_type']) ?></span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?= date('M d, Y', strtotime($transaction['created_at'])) ?></strong><br>
                                                <small class="text-muted"><?= date('g:i A', strtotime($transaction['created_at'])) ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="toggleDetails(<?= $transaction['id'] ?>)">
                                                👁️ Details
                                            </button>
                                        </td>
                                    </tr>
                                    <tr id="details-<?= $transaction['id'] ?>" style="display: none;">
                                        <td colspan="6" class="bg-light">
                                            <div class="p-3">
                                                <h6 class="mb-3">🛒 Transaction Items:</h6>
                                                <?php if (isset($transactionDetails[$transaction['id']])): ?>
                                                    <div class="row">
                                                        <?php foreach ($transactionDetails[$transaction['id']] as $item): ?>
                                                            <div class="col-md-6 mb-2">
                                                                <div class="d-flex justify-content-between">
                                                                    <span><?= htmlspecialchars($item['product_name']) ?> × <?= $item['quantity'] ?></span>
                                                                    <strong>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></strong>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <p class="text-muted mb-0">No items found for this transaction.</p>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Transaction pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="text-center py-5">
                        <div style="font-size: 4rem; opacity: 0.3; margin-bottom: 1rem;">📋</div>
                        <h5 class="text-muted">No transactions found</h5>
                        <p class="text-muted">Transactions will appear here once sales are made.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDetails(transactionId) {
    const detailsRow = document.getElementById('details-' + transactionId);
    if (detailsRow.style.display === 'none') {
        detailsRow.style.display = 'table-row';
    } else {
        detailsRow.style.display = 'none';
    }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../views/layout.php';