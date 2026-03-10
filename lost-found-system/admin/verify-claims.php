<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';

// Handle approve/reject + reward on approve
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $claim_id = (int)($_POST['claim_id'] ?? 0);
    $action   = $_POST['action'] ?? '';

    if ($claim_id > 0 && in_array($action, ['approve', 'reject'])) {
        $new_status = $action === 'approve' ? 'approved' : 'rejected';

        try {
            // Get the found_id and finder_id for reward
            $claim_stmt = $conn->prepare("SELECT found_id FROM claims WHERE claim_id = ?");
            $claim_stmt->execute([$claim_id]);
            $claim = $claim_stmt->fetch();

            $conn->prepare("
                UPDATE claims 
                SET status = ?, admin_id = ? 
                WHERE claim_id = ?
            ")->execute([$new_status, $_SESSION['user_id'], $claim_id]);

            if ($action === 'approve' && $claim) {
                // Award reward to finder
                $conn->prepare("
                    UPDATE found_items 
                    SET reward_status = 'rewarded', reward_points = 100 
                    WHERE found_id = ?
                ")->execute([$claim['found_id']]);

                $message = "Claim #$claim_id approved! Finder rewarded with 100 points.";
            } else {
                $message = "Claim #$claim_id has been $new_status.";
            }
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Fetch claims
try {
    $stmt = $conn->prepare("
        SELECT c.claim_id, c.claim_date, c.status, c.proof_description,
               f.item_name AS found_name, f.description AS found_desc, f.date_found,
               l.item_name AS lost_name, l.description AS lost_desc,
               u.name AS finder_name, u.email AS finder_email,
               cu.name AS claimer_name, cu.email AS claimer_email
        FROM claims c
        LEFT JOIN found_items f ON c.found_id = f.found_id
        LEFT JOIN lost_items l ON c.lost_id = l.lost_id
        LEFT JOIN users u ON f.user_id = u.user_id
        LEFT JOIN users cu ON l.user_id = cu.user_id
        ORDER BY c.claim_date DESC
        LIMIT 50
    ");
    $stmt->execute();
    $claims = $stmt->fetchAll();
} catch (PDOException $e) {
    $claims = [];
    $message = "Error loading claims: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Claims - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Admin - Lost & Found</a>
            <div class="ms-auto">
                <a href="dashboard.php" class="btn btn-outline-light me-2">Dashboard</a>
                <a href="../logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Claim Verification</h2>

        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if (empty($claims)): ?>
            <div class="alert alert-secondary text-center">
                No claims found.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Found Item</th>
                            <th>Lost Item</th>
                            <th>Finder / Claimer</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($claims as $claim): ?>
                            <tr>
                                <td><?= $claim['claim_id'] ?></td>
                                <td><?= htmlspecialchars($claim['claim_date']) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($claim['found_name'] ?? 'N/A') ?></strong><br>
                                    <small class="text-muted">Found: <?= htmlspecialchars($claim['date_found'] ?? 'N/A') ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($claim['lost_name'] ?? 'N/A') ?></strong><br>
                                    <small class="text-muted">Claimer: <?= htmlspecialchars($claim['claimer_name'] ?? 'Unknown') ?></small>
                                </td>
                                <td>
                                    <strong>Finder:</strong> <?= htmlspecialchars($claim['finder_name'] ?? 'Unknown') ?><br>
                                    <strong>Claimer:</strong> <?= htmlspecialchars($claim['claimer_name'] ?? 'Unknown') ?>
                                </td>
                                <td>
                                    <span class="badge 
                                        <?= $claim['status'] === 'pending' ? 'bg-warning' : 
                                            ($claim['status'] === 'approved' ? 'bg-success' : 'bg-danger') ?>">
                                        <?= ucfirst($claim['status']) ?>
                                    </span>
                                    <?php if (!empty($claim['proof_description'])): ?>
                                        <br><small class="text-muted">Has proof</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($claim['status'] === 'pending'): ?>
                                        <form method="POST" class="d-inline-block">
                                            <input type="hidden" name="claim_id" value="<?= $claim['claim_id'] ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-sm btn-success me-1">Approve</button>
                                            <button type="submit" name="action" value="reject"  class="btn btn-sm btn-danger">Reject</button>
                                        </form>
                                    <?php else: ?>
                                        <small>Processed</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Back to Admin Dashboard</a>
        </div>
    </div>

</body>
</html>