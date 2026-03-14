<?php
require_once '../includes/config.php';

// Admin check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';
$success = false;

// Handle delete item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    $item_id = (int)($_POST['item_id'] ?? 0);
    $item_type = $_POST['item_type'] ?? '';

    if ($item_id > 0 && in_array($item_type, ['lost', 'found'])) {
        $table = $item_type === 'lost' ? 'lost_items' : 'found_items';
        $id_column = $item_type === 'lost' ? 'lost_id' : 'found_id';

        $stmt = $conn->prepare("DELETE FROM $table WHERE $id_column = ?");
        if ($stmt->execute([$item_id])) {
            $success = true;
            $message = ucfirst($item_type) . " item deleted successfully.";
        } else {
            $message = "Error deleting item.";
        }
    }
}

// Fetch all items
try {
    $stmt = $conn->query("
        SELECT 'lost' AS type, lost_id AS id, item_name, description, category, date_lost AS item_date, location, image_path, u.name AS reporter_name
        FROM lost_items l
        JOIN users u ON l.user_id = u.user_id
        UNION ALL
        SELECT 'found' AS type, found_id AS id, item_name, description, category, date_found AS item_date, location, image_path, u.name AS reporter_name
        FROM found_items f
        JOIN users u ON f.user_id = u.user_id
        ORDER BY item_date DESC
    ");
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    $items = [];
    $message = "Error loading items: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Items - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-kyu shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">KyU Lost & Found Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav" aria-controls="adminNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="verify-claims.php">Verify Claims</a></li>
                    <li class="nav-item"><a class="nav-link" href="view-items.php">Items</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage-users.php">Users</a></li>
                    <li class="nav-item"><a class="nav-link active" href="manage-items.php">Manage Items</a></li>
                </ul>
                <div class="d-flex">
                    <a href="../logout.php" class="btn btn-outline-light">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Manage Items</h2>

        <?php if ($message): ?>
            <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Items Table -->
        <div class="card card-modern">
            <div class="card-header">
                <h5 class="mb-0">All Items (Lost & Found)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($items)): ?>
                    <p>No items found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Reporter</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><span class="badge <?= $item['type'] === 'lost' ? 'bg-danger' : 'bg-success' ?>"><?= ucfirst($item['type']) ?></span></td>
                                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                                        <td><?= htmlspecialchars($item['category'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($item['item_date']) ?></td>
                                        <td><?= htmlspecialchars($item['location'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($item['reporter_name']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-item-id="<?= $item['id'] ?>" data-item-type="<?= $item['type'] ?>" data-item-name="<?= htmlspecialchars($item['item_name']) ?>">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
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
                    Are you sure you want to delete the <span id="itemType"></span> item "<span id="itemName"></span>"? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="item_id" id="deleteItemId">
                        <input type="hidden" name="item_type" id="deleteItemType">
                        <button type="submit" name="delete_item" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const itemId = button.getAttribute('data-item-id');
            const itemType = button.getAttribute('data-item-type');
            const itemName = button.getAttribute('data-item-name');
            document.getElementById('deleteItemId').value = itemId;
            document.getElementById('deleteItemType').value = itemType;
            document.getElementById('itemType').textContent = itemType;
            document.getElementById('itemName').textContent = itemName;
        });
    </script>
</body>
</html>