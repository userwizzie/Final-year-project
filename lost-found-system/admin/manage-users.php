<?php
require_once '../includes/config.php';

require_admin();

$message = '';
$success = false;

// Handle create user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'user';

    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = "Email already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $phone, $hashed_password, $role])) {
                $success = true;
                $message = "User created successfully.";
            } else {
                $message = "Error creating user.";
            }
        }
    }
}

// Handle delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = (int)($_POST['user_id'] ?? 0);
    if ($user_id > 0) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        if ($stmt->execute([$user_id])) {
            $success = true;
            $message = "User deleted successfully.";
        } else {
            $message = "Error deleting user.";
        }
    }
}

// Fetch all users
$stmt = $conn->query("SELECT user_id, name, email, phone, role FROM users ORDER BY name");
$users = $stmt->fetchAll();

$page_title = 'Manage Users - Admin';
require_once '../includes/header.php';
?>

<h2 class="mb-4"><i class="fas fa-users me-2"></i>Manage Users</h2>

        <?php if ($message): ?>
            <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Create User Form -->
        <div class="card card-modern mb-4">
            <div class="card-header">
                <h5 class="mb-0">Create New User</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" name="create_user" class="btn btn-kyu mt-3">Create User</button>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card card-modern">
            <div class="card-header">
                <h5 class="mb-0">All Users</h5>
            </div>
            <div class="card-body">
                <?php if (empty($users)): ?>
                    <p>No users found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= $user['user_id'] ?></td>
                                        <td><?= htmlspecialchars($user['name']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= htmlspecialchars($user['phone']) ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($user['role']) ?></span></td>
                                        <td>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-user-id="<?= $user['user_id'] ?>" data-user-name="<?= htmlspecialchars($user['name']) ?>">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete user "<span id="userName"></span>"? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="user_id" id="deleteUserId">
                        <button type="submit" name="delete_user" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const userName = button.getAttribute('data-user-name');
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('userName').textContent = userName;
        });
    </script>

<?php require_once '../includes/footer.php'; ?>