<?php
require_once 'includes/config.php';

// Protect page - must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$name    = $_SESSION['name'] ?? 'User';

// Fetch claims related to the user's lost items, including proof_description
$stmt = $conn->prepare("
    SELECT 
        c.claim_id,
        c.claim_date,
        c.status,
        c.proof_description,
        f.item_name AS found_item_name,
        f.description AS found_description,
        f.image_path AS found_image,
        f.date_found,
        f.location AS found_location,
        f.category AS found_category
    FROM claims c
    JOIN found_items f ON c.found_id = f.found_id
    JOIN lost_items l ON c.lost_id = l.lost_id
    WHERE l.user_id = ?
    ORDER BY c.claim_date DESC
");
$stmt->execute([$user_id]);
$claims = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Claims - Lost & Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .claim-card { border-left: 6px solid; margin-bottom: 1.5rem; }
        .claim-pending  { border-left-color: #ffc107; background-color: #fff8e1; }
        .claim-approved { border-left-color: #198754; background-color: #d4edda; }
        .claim-rejected { border-left-color: #dc3545; background-color: #f8d7da; }
        .card-img-top { height: 180px; object-fit: cover; border-radius: 0.375rem 0.375rem 0 0; }
        .proof-section { background: #f8f9fa; border-radius: 0.375rem; padding: 1rem; margin-top: 1rem; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Lost & Found - KyU</a>
            <div class="ms-auto">
                <span class="text-white me-3">Welcome, <?= htmlspecialchars($name) ?></span>
                <a href="logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Claims</h2>
            <a href="search.php" class="btn btn-primary">Search More Items</a>
        </div>

        <?php if (empty($claims)): ?>
            <div class="alert alert-info text-center py-5">
                <h4 class="mb-3">No Claims Yet</h4>
                <p>You haven't submitted any claims on found items.</p>
                <a href="search.php" class="btn btn-lg btn-primary mt-3">Go Search for Lost Items →</a>
            </div>
        <?php else: ?>
            <?php foreach ($claims as $claim): ?>
                <div class="claim-card shadow rounded <?= 
                    $claim['status'] === 'pending' ? 'claim-pending' : 
                    ($claim['status'] === 'approved' ? 'claim-approved' : 'claim-rejected') 
                ?>">
                    <div class="card-body">
                        <?php if (!empty($claim['found_image'])): ?>
                            <img src="<?= htmlspecialchars($claim['found_image']) ?>" 
                                 class="card-img-top mb-3" alt="Found item">
                        <?php endif; ?>

                        <h4 class="card-title mb-3"><?= htmlspecialchars($claim['found_item_name']) ?></h4>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Category:</strong> <?= htmlspecialchars($claim['found_category'] ?? 'N/A') ?><br>
                                <strong>Found on:</strong> <?= htmlspecialchars($claim['date_found'] ?? 'N/A') ?><br>
                                <strong>Location:</strong> <?= htmlspecialchars($claim['found_location'] ?? 'Not specified') ?>
                            </div>
                            <div class="col-md-6 text-end">
                                <strong>Claim submitted:</strong> <?= htmlspecialchars($claim['claim_date']) ?><br><br>
                                <span class="badge fs-6 px-4 py-2 
                                    <?= $claim['status'] === 'pending' ? 'bg-warning text-dark' : 
                                        ($claim['status'] === 'approved' ? 'bg-success' : 'bg-danger') ?>">
                                    <?= strtoupper($claim['status']) ?>
                                </span>
                            </div>
                        </div>

                        <hr>

                        <h6 class="mt-4 mb-2">Your Proof / Explanation:</h6>
                        <div class="proof-section border">
                            <?= nl2br(htmlspecialchars($claim['proof_description'] ?? 'No proof provided')) ?>
                        </div>

                        <?php if ($claim['status'] === 'approved'): ?>
                            <div class="alert alert-success mt-4">
                                <strong>Approved!</strong> This claim was accepted by an administrator.
                            </div>
                        <?php elseif ($claim['status'] === 'rejected'): ?>
                            <div class="alert alert-danger mt-4">
                                <strong>Rejected.</strong> The administrator did not approve this claim.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="text-center mt-5">
            <a href="dashboard.php" class="btn btn-outline-secondary btn-lg px-5">Back to Dashboard</a>
        </div>
    </div>

</body>
</html>