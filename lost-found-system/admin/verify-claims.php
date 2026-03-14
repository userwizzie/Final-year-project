<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';

// Handle approve/reject + reward
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $claim_id = (int)($_POST['claim_id'] ?? 0);
    $action   = $_POST['action'] ?? '';

    if ($claim_id > 0 && in_array($action, ['approve', 'reject'])) {
        $new_status = $action === 'approve' ? 'approved' : 'rejected';

        try {
            $claim_stmt = $conn->prepare("SELECT found_id FROM claims WHERE claim_id = ?");
            $claim_stmt->execute([$claim_id]);
            $claim = $claim_stmt->fetch();

            $conn->prepare("
                UPDATE claims 
                SET status = ?, admin_id = ? 
                WHERE claim_id = ?
            ")->execute([$new_status, $_SESSION['user_id'], $claim_id]);

            if ($action === 'approve' && $claim) {
                $conn->prepare("
                    UPDATE found_items 
                    SET reward_status = 'rewarded', reward_points = 100 
                    WHERE found_id = ?
                ")->execute([$claim['found_id']]);

                $message = "Claim #$claim_id approved! Finder rewarded 100 points.";

                // notify claimant and finder
                if (!empty($claim['claimant_email'])) {
                    notify_user($claim['claimant_email'], 'Your claim was approved',
                                "Hello {$claim['claimant_name']},\n\nYour claim (#$claim_id) has been approved.");
                }
                if (!empty($claim['finder_email'])) {
                    notify_user($claim['finder_email'], 'Reward issued',
                                "Hello {$claim['finder_name']},\n\nYour item claim was approved and 100 points have been awarded.");
                }
            } elseif ($action === 'reject' && $claim) {
                // reset item status so others can claim again
                $conn->prepare(
                    "UPDATE found_items SET reward_status = NULL, reward_points = 0 WHERE found_id = ?"
                )->execute([$claim['found_id']]);

                $message = "Claim #$claim_id rejected and item released.";

                if (!empty($claim['claimant_email'])) {
                    notify_user($claim['claimant_email'], 'Your claim was rejected',
                                "Hello {$claim['claimant_name']},\n\nYour claim (#$claim_id) has been rejected. Feel free to search again.");
                }
            } else {
                $message = "Claim #$claim_id $new_status.";
            }
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Fetch claims with proof
try {
    $stmt = $conn->prepare("
        SELECT 
            c.claim_id, c.claim_date, c.status, c.proof_description,
            f.item_name AS found_name, f.description AS found_desc, f.image_path,
            f.date_found, f.location,
            u.name AS finder_name, u.email AS finder_email,
            claimant.name AS claimant_name, claimant.email AS claimant_email
        FROM claims c
        LEFT JOIN found_items f ON c.found_id = f.found_id
        LEFT JOIN users u ON f.user_id = u.user_id          -- finder
        LEFT JOIN users claimant ON c.user_id = claimant.user_id  -- claimant
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
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .proof-text { max-height: 80px; overflow: hidden; transition: max-height 0.3s; }
        .proof-text.expanded { max-height: 500px; }
        .toggle-proof { cursor: pointer; color: var(--kyu-blue); font-size: 0.9rem; }
    </style>
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
                    <li class="nav-item"><a class="nav-link" href="view-items.php">Items</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage-users.php">Users</a></li>
                </ul>
                <div class="d-flex">
                    <a href="../logout.php" class="btn btn-outline-light">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Claim Verification</h2>

        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if (empty($claims)): ?>
            <div class="alert alert-secondary text-center py-4">
                No claims pending or processed yet.
            </div>
        <?php else: ?>
            <div class="data-grid">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Found Item</th>
                            <th>Finder</th>
                            <th>Claimant</th>
                            <th>Proof (click to expand)</th>
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
                                    <small><?= htmlspecialchars(substr($claim['found_desc'] ?? '', 0, 60)) ?>...</small>
                                    <?php if (!empty($claim['image_path'])): ?>
                                        <br><img src="../<?= htmlspecialchars($claim['image_path']) ?>" alt="Item" style="max-width:80px; margin-top:5px;">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($claim['finder_name'] ?? 'Unknown') ?><br>
                                    <small><?= htmlspecialchars($claim['finder_email'] ?? '') ?></small>
                                </td>
                                <td><?= htmlspecialchars($claim['claimant_name'] ?? 'Unknown') ?><br>
                                    <small><?= htmlspecialchars($claim['claimant_email'] ?? '') ?></small>
                                </td>
                                <td>
                                    <div class="proof-text" id="proof-<?= $claim['claim_id'] ?>">
                                        <?= nl2br(htmlspecialchars(substr($claim['proof_description'] ?? 'No proof provided', 0, 150))) ?>...
                                    </div>
                                    <?php if (strlen($claim['proof_description'] ?? '') > 150): ?>
                                        <span class="toggle-proof" onclick="toggleProof(<?= $claim['claim_id'] ?>)">Show more</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge 
                                        <?= $claim['status'] === 'pending' ? 'bg-warning text-dark' : 
                                            ($claim['status'] === 'approved' ? 'bg-success' : 'bg-danger') ?>">
                                        <?= ucfirst($claim['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($claim['status'] === 'pending'): ?>
                                        <form method="POST" class="d-inline-block">
                                            <input type="hidden" name="claim_id" value="<?= $claim['claim_id'] ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-sm btn-success me-1">Approve</button>
                                            <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
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

        <div class="mt-4 text-center">
            <a href="dashboard.php" class="btn btn-secondary btn-lg px-5">Back to Admin Dashboard</a>
        </div>
    </div>

    <script>
        function toggleProof(id) {
            const el = document.getElementById('proof-' + id);
            if (el.classList.contains('expanded')) {
                el.classList.remove('expanded');
                el.nextElementSibling.textContent = 'Show more';
            } else {
                el.classList.add('expanded');
                el.nextElementSibling.textContent = 'Show less';
            }
        }
    </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>