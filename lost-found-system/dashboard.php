<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$name    = $_SESSION['name'] ?? 'User';

// Check if user just logged in to show welcome message
$show_welcome = isset($_SESSION['just_logged_in']) && $_SESSION['just_logged_in'];
if ($show_welcome) {
    unset($_SESSION['just_logged_in']);
}

$lost_stmt = $conn->prepare("
    SELECT lost_id, item_name, category, date_lost, location, description, image_path
    FROM lost_items WHERE user_id = ? ORDER BY date_lost DESC LIMIT 10
");
$lost_stmt->execute([$user_id]);
$lost_items = $lost_stmt->fetchAll();

$found_stmt = $conn->prepare("
    SELECT found_id, item_name, category, date_found, location, description, image_path,
           reward_status, reward_points
    FROM found_items WHERE user_id = ? ORDER BY date_found DESC LIMIT 10
");
$found_stmt->execute([$user_id]);
$found_items = $found_stmt->fetchAll();

$claim_stmt = $conn->prepare("
    SELECT c.claim_id, c.claim_date, c.status, f.item_name, f.found_id
    FROM claims c JOIN found_items f ON c.found_id = f.found_id
    WHERE c.user_id = ?
    ORDER BY c.claim_date DESC LIMIT 10
");
$claim_stmt->execute([$user_id]);
$claims = $claim_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Kyambogo University Lost & Found</title>
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .section-header { margin-top: 2.5rem; margin-bottom: 1rem; font-weight: bold; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-kyu shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">KyU Lost & Found</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="search.php">Search</a></li>
                    <li class="nav-item"><a class="nav-link" href="report-lost.php">Report Lost</a></li>
                    <li class="nav-item"><a class="nav-link" href="report-found.php">Report Found</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="navbar-text me-3">Hello, <?= htmlspecialchars($name) ?></span>
                    <a href="logout.php" class="btn btn-outline-light">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">My Dashboard</h2>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card text-center border-0 card-modern">
                    <div class="card-body">
                        <h5>Report Lost</h5>
                        <a href="report-lost.php" class="btn btn-kyu w-100 mt-2">Report Lost</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-0 card-modern">
                    <div class="card-body">
                        <h5>Report Found</h5>
                        <a href="report-found.php" class="btn btn-kyu-alt w-100 mt-2">Report Found</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-0 card-modern">
                    <div class="card-body">
                        <h5>Search Items</h5>
                        <a href="search.php" class="btn btn-kyu w-100 mt-2">Search</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-0 card-modern">
                    <div class="card-body">
                        <h5>My Claims</h5>
                        <a href="my-claims.php" class="btn btn-kyu-alt w-100 mt-2">View Claims</a>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="section-header text-danger">My Lost Items</h4>
        <?php if (empty($lost_items)): ?>
            <div class="alert alert-info">No lost items reported.</div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($lost_items as $item): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-danger shadow-sm h-100">
                            <?php if (!empty($item['image_path'])): ?>
                                <img src="<?= htmlspecialchars($item['image_path']) ?>" class="card-img-top" alt="Item">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5><?= htmlspecialchars($item['item_name']) ?></h5>
                                <p class="small text-muted"><?= htmlspecialchars(substr($item['description'] ?? '', 0, 100)) ?>...</p>
                                <p><strong>Category:</strong> <?= htmlspecialchars($item['category'] ?? 'N/A') ?></p>
                                <p><strong>Date Lost:</strong> <?= htmlspecialchars($item['date_lost'] ?? 'N/A') ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h4 class="section-header text-success">My Found Items & Rewards</h4>
        <?php if (empty($found_items)): ?>
            <div class="alert alert-info">No found items reported.</div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($found_items as $item): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-success shadow-sm h-100">
                            <?php if (!empty($item['image_path'])): ?>
                                <img src="<?= htmlspecialchars($item['image_path']) ?>" class="card-img-top" alt="Item">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5><?= htmlspecialchars($item['item_name']) ?></h5>
                                <p class="small text-muted"><?= htmlspecialchars(substr($item['description'] ?? '', 0, 100)) ?>...</p>
                                <p><strong>Category:</strong> <?= htmlspecialchars($item['category'] ?? 'N/A') ?></p>
                                <p><strong>Date Found:</strong> <?= htmlspecialchars($item['date_found'] ?? 'N/A') ?></p>
                                
                                <?php if ($item['reward_status'] === 'rewarded'): ?>
                                    <div class="alert alert-success mt-3 small">
                                        <strong>Reward Earned!</strong> <?= $item['reward_points'] ?> points
                                    </div>
                                <?php elseif ($item['reward_status'] === 'claimed'): ?>
                                    <div class="alert alert-warning mt-3 small">
                                        Claim in review...
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h4 class="section-header text-primary">My Claims (Quick View)</h4>
        <?php if (empty($claims)): ?>
            <div class="alert alert-info">No claims submitted yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead><tr><th>Item</th><th>Date</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($claims as $claim): ?>
                            <tr>
                                <td><?= htmlspecialchars($claim['item_name']) ?></td>
                                <td><?= htmlspecialchars($claim['claim_date']) ?></td>
                                <td>
                                    <span class="badge <?= $claim['status'] === 'approved' ? 'bg-success' : ($claim['status'] === 'rejected' ? 'bg-danger' : 'bg-warning') ?>">
                                        <?= ucfirst($claim['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="my-claims.php" class="btn btn-sm btn-outline-primary">View Full Details</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Welcome Modal Popup -->
    <div class="modal fade" id="welcomeModal" tabindex="-1" aria-labelledby="welcomeLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-header bg-success text-white py-2">
                    <h5 class="modal-title fs-6" id="welcomeLabel">Welcome back, <?= htmlspecialchars($name) ?>! 👋</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="mb-0 small">You're logged in to the Kyambogo University Lost & Found System. Explore, report, and claim items!</p>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-success" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <?php if ($show_welcome): ?>
    <script>
        // Show welcome modal on page load
        document.addEventListener('DOMContentLoaded', function() {
            const welcomeModal = new bootstrap.Modal(document.getElementById('welcomeModal'));
            welcomeModal.show();

            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                welcomeModal.hide();
            }, 5000);
        });
    </script>
    <?php endif; ?>

</body>
</html>