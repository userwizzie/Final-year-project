<?php
require_once '../includes/config.php';

// admin check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

try {
    $stmt = $conn->query("SELECT 'lost' AS type, lost_id AS id, item_name, description, category, date_lost AS item_date, location, image_path, u.name AS reporter_name FROM lost_items l JOIN users u ON l.user_id = u.user_id");
    $lost = $stmt->fetchAll();
    $stmt = $conn->query("SELECT 'found' AS type, found_id AS id, item_name, description, category, date_found AS item_date, location, image_path, u.name AS reporter_name FROM found_items f JOIN users u ON f.user_id = u.user_id");
    $found = $stmt->fetchAll();
    $items = array_merge($lost, $found);
} catch (PDOException $e) {
    $items = [];
    $error = $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Items - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Admin - Lost & Found KyU</a>
            <div class="ms-auto">
                <a href="dashboard.php" class="btn btn-outline-light me-2">Dashboard</a>
                <a href="../logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>All Reported Items</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">Error loading items: <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (empty($items)): ?>
            <div class="alert alert-secondary">No items to display.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Reported By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td><?= htmlspecialchars(ucfirst($it['type'])) ?></td>
                                <td><?= htmlspecialchars($it['item_name']) ?></td>
                                <td><?= htmlspecialchars($it['category'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($it['item_date']) ?></td>
                                <td><?= htmlspecialchars($it['location'] ?? '') ?></td>
                                <td><?= htmlspecialchars($it['reporter_name'] ?? 'Unknown') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>