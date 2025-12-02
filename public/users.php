<?php

require __DIR__ . '/../app/auth_only.php';

$currentUser = Auth::user();
if (!$currentUser || $currentUser['role'] !== 'Admin') {
    header('Location: pos.php');
    exit;
}

$pdo = Database::getConnection();
$pageTitle = 'User Management';
$flashMessage = null;

// Get user statistics
$userStats = $pdo->query('
    SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
        SUM(CASE WHEN r.name = "Admin" THEN 1 ELSE 0 END) as admin_count,
        SUM(CASE WHEN r.name = "Cashier" THEN 1 ELSE 0 END) as cashier_count
    FROM users u 
    JOIN roles r ON u.role_id = r.id
')->fetch();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleId = (int)($_POST['role_id'] ?? 0);
        
        if ($name && $username && $password && $roleId) {
            // Check if username already exists
            $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
            $checkStmt->execute([$username]);
            
            if ($checkStmt->fetchColumn() > 0) {
                $flashMessage = ['type' => 'danger', 'message' => 'Username already exists. Please choose a different username.'];
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (name, username, password_hash, role_id, is_active) VALUES (?, ?, ?, ?, 1)');
                if ($stmt->execute([$name, $username, $passwordHash, $roleId])) {
                    $flashMessage = ['type' => 'success', 'message' => 'User added successfully!'];
                } else {
                    $flashMessage = ['type' => 'danger', 'message' => 'Failed to add user.'];
                }
            }
        } else {
            $flashMessage = ['type' => 'danger', 'message' => 'All fields are required.'];
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $roleId = (int)($_POST['role_id'] ?? 0);
        $password = $_POST['password'] ?? '';
        
        if ($id && $name && $username && $roleId) {
            // Check if username already exists for other users
            $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? AND id != ?');
            $checkStmt->execute([$username, $id]);
            
            if ($checkStmt->fetchColumn() > 0) {
                $flashMessage = ['type' => 'danger', 'message' => 'Username already exists. Please choose a different username.'];
            } else {
                if ($password) {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE users SET name = ?, username = ?, password_hash = ?, role_id = ? WHERE id = ?');
                    $stmt->execute([$name, $username, $passwordHash, $roleId, $id]);
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET name = ?, username = ?, role_id = ? WHERE id = ?');
                    $stmt->execute([$name, $username, $roleId, $id]);
                }
                $flashMessage = ['type' => 'success', 'message' => 'User updated successfully!'];
            }
        } else {
            $flashMessage = ['type' => 'danger', 'message' => 'All fields are required.'];
        }
    } elseif ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $pdo->prepare('UPDATE users SET is_active = NOT is_active WHERE id = ?');
            $stmt->execute([$id]);
            $flashMessage = ['type' => 'success', 'message' => 'User status updated!'];
        }
    }
    
    header('Location: users.php');
    exit;
}

// Get all users with their roles and last login info
$users = $pdo->query('
    SELECT u.*, r.name AS role_name, u.created_at,
           (SELECT MAX(created_at) FROM sales WHERE user_id = u.id) as last_activity
    FROM users u 
    JOIN roles r ON u.role_id = r.id 
    ORDER BY u.is_active DESC, u.created_at DESC
')->fetchAll();

// Get all roles for the form
$roles = $pdo->query('SELECT * FROM roles ORDER BY name')->fetchAll();

ob_start();
?>
<div class="page-header">
    <div>
        <h1 class="page-title">👥 User Management</h1>
        <p class="page-subtitle">Manage system users and their access levels</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        ➕ Add New User
    </button>
</div>

<!-- User Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon primary">👥</div>
            <div class="stat-value"><?= number_format($userStats['total_users']) ?></div>
            <div class="stat-label">Total Users</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon success">✅</div>
            <div class="stat-value"><?= number_format($userStats['active_users']) ?></div>
            <div class="stat-label">Active Users</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon warning">👨‍💼</div>
            <div class="stat-value"><?= number_format($userStats['admin_count']) ?></div>
            <div class="stat-label">Administrators</div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon info">👤</div>
            <div class="stat-value"><?= number_format($userStats['cashier_count']) ?></div>
            <div class="stat-label">Cashiers</div>
        </div>
    </div>
</div>

<?php if ($flashMessage): ?>
    <div class="alert alert-<?= $flashMessage['type'] ?> alert-dismissible fade show">
        <strong><?= $flashMessage['type'] === 'success' ? '✓' : '⚠️' ?></strong>
        <?= htmlspecialchars($flashMessage['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">📋 All Users (<?= count($users) ?>)</h5>
            <div class="d-flex gap-2">
                <input type="text" id="userSearch" class="form-control" placeholder="🔍 Search users..." style="width: 250px;">
                <select id="roleFilter" class="form-select" style="width: 150px;">
                    <option value="">All Roles</option>
                    <option value="Admin">👨‍💼 Admin</option>
                    <option value="Cashier">👤 Cashier</option>
                </select>
                <select id="statusFilter" class="form-select" style="width: 150px;">
                    <option value="">All Status</option>
                    <option value="active">✓ Active</option>
                    <option value="inactive">✗ Inactive</option>
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Name & Username</th>
                        <th style="width: 120px;">Role</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 150px;">Last Activity</th>
                        <th style="width: 120px;">Created</th>
                        <th style="width: 200px;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr class="<?= !$user['is_active'] ? 'table-secondary' : '' ?>">
                            <td><span class="badge bg-secondary">#<?= $user['id'] ?></span></td>
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($user['name']) ?></strong>
                                    <?php if ($user['id'] == $currentUser['id']): ?>
                                        <span class="badge bg-primary ms-1">You</span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted">@<?= htmlspecialchars($user['username']) ?></small>
                            </td>
                            <td>
                                <?php if ($user['role_name'] === 'Admin'): ?>
                                    <span class="badge bg-danger">👨‍💼 Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-info">👤 Cashier</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span class="badge bg-success">✓ Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">✗ Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['last_activity']): ?>
                                    <small class="text-success">
                                        <?= date('M d, Y', strtotime($user['last_activity'])) ?><br>
                                        <span class="text-muted"><?= date('g:i A', strtotime($user['last_activity'])) ?></span>
                                    </small>
                                <?php else: ?>
                                    <small class="text-muted">Never</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= date('M d, Y', strtotime($user['created_at'])) ?>
                                </small>
                            </td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editUser(<?= htmlspecialchars(json_encode($user)) ?>)" title="Edit User">
                                        ✏️
                                    </button>
                                    <?php if ($user['id'] != $currentUser['id']): ?>
                                        <form method="POST" style="display: inline;" id="toggleForm<?= $user['id'] ?>">
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                            <button type="button" class="btn btn-sm btn-outline-<?= $user['is_active'] ? 'warning' : 'success' ?>" 
                                                    onclick="customConfirm('Are you sure you want to <?= $user['is_active'] ? 'deactivate' : 'activate' ?> this user?', function(yes) { if(yes) document.getElementById('toggleForm<?= $user['id'] ?>').submit(); })"
                                                    title="<?= $user['is_active'] ? 'Deactivate' : 'Activate' ?> User">
                                                <?= $user['is_active'] ? '🔒' : '🔓' ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary" disabled title="Cannot modify your own account">
                                            🚫
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., John Doe" required>
                        <div class="form-text">Enter the user's full name</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="e.g., johndoe" required>
                        <div class="form-text">Username must be unique and will be used for login</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control" minlength="6" required>
                        <div class="form-text">Password must be at least 6 characters long</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role</label>
                        <select name="role_id" class="form-select" required>
                            <option value="">Select Role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>">
                                    <?= $role['name'] === 'Admin' ? '👨‍💼' : '👤' ?> <?= htmlspecialchars($role['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Admin: Full access | Cashier: POS access only</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required>
                        <div class="form-text">Username must be unique</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password</label>
                        <input type="password" name="password" class="form-control" minlength="6">
                        <div class="form-text">Leave blank to keep current password. Minimum 6 characters if changing.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role</label>
                        <select name="role_id" id="edit_role_id" class="form-select" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>">
                                    <?= $role['name'] === 'Admin' ? '👨‍💼' : '👤' ?> <?= htmlspecialchars($role['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">👤 User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="userDetailsContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function editUser(user) {
    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_role_id').value = user.role_id;
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

function showUserDetails(user) {
    const content = `
        <div class="row">
            <div class="col-md-6">
                <strong>ID:</strong> #${user.id}<br>
                <strong>Name:</strong> ${user.name}<br>
                <strong>Username:</strong> @${user.username}<br>
                <strong>Role:</strong> ${user.role_name === 'Admin' ? '👨‍💼' : '👤'} ${user.role_name}
            </div>
            <div class="col-md-6">
                <strong>Status:</strong> ${user.is_active ? '<span class="badge bg-success">✓ Active</span>' : '<span class="badge bg-secondary">✗ Inactive</span>'}<br>
                <strong>Created:</strong> ${new Date(user.created_at).toLocaleDateString()}<br>
                <strong>Last Activity:</strong> ${user.last_activity ? new Date(user.last_activity).toLocaleDateString() : 'Never'}
            </div>
        </div>
    `;
    document.getElementById('userDetailsContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('userDetailsModal')).show();
}

// Add search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('userSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');
            
            tableRows.forEach(row => {
                const name = row.cells[1].textContent.toLowerCase();
                const username = row.cells[1].textContent.toLowerCase();
                const role = row.cells[2].textContent.toLowerCase();
                
                if (name.includes(searchTerm) || username.includes(searchTerm) || role.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../views/layout.php';
