<?php
require_once 'includes/config.php';

// Protect page - must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$name    = $_SESSION['name'] ?? 'User';

// Fetch claims submitted by the user (as a finder), including proof_description
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
    WHERE c.user_id = ?
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
    <title>My Claims - Kyambogo University Lost & Found</title>
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .proof-section { background: #fff; border-radius: 0.75rem; padding: 1rem; margin-top: 1rem; border: 1px solid rgba(0,0,0,0.08); }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-kyu shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">KyU Lost & Found</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#claimsNav" aria-controls="claimsNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="claimsNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="search.php">Search</a></li>
                    <li class="nav-item"><a class="nav-link" href="report-found.php">Report Found</a></li>
                    <li class="nav-item"><a class="nav-link" href="report-lost.php">Report Lost</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="navbar-text me-3">Hello, <?= htmlspecialchars($name) ?></span>
                    <a href="logout.php" class="btn btn-outline-light">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Claims</h2>
            <a href="search.php" class="btn btn-primary">Search More Items</a>
        </div>

        <?php if (empty($claims)): ?>
            <div class="empty-state mx-auto" style="max-width: 540px;">
                <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="32" cy="32" r="30" opacity="0.18" />
                    <path d="M22 26h20" />
                    <path d="M22 34h20" />
                    <path d="M22 42h12" />
                    <path d="M18 18l10 10" />
                    <path d="M28 18l-10 10" />
                </svg>
                <h4 class="mb-3">No claims found</h4>
                <p class="text-muted mb-3">You haven't submitted any claims yet. Try searching for found items to claim.</p>
                <a href="search.php" class="btn btn-kyu">Search Found Items</a>
            </div>
        <?php else: ?>
            <?php foreach ($claims as $claim): 
                $status = $claim['status'];
                $step2Class = $status === 'pending' ? 'active' : 'done';
                $step3Class = $status === 'approved' ? 'done' : ($status === 'rejected' ? 'rejected' : '');
            ?>
                <div class="card card-modern shadow mb-4 <?= $status === 'pending' ? 'border-warning' : ($status === 'approved' ? 'border-success' : 'border-danger') ?>">
                    <div class="card-body">
                        <div class="stepper">
                            <div class="step done">
                                <div class="bullet">1</div>
                                <div>
                                    <div class="fw-semibold">Submitted</div>
                                    <div class="small text-muted">Claim sent</div>
                                </div>
                            </div>
                            <div class="step <?= $step2Class ?>">
                                <div class="bullet">2</div>
                                <div>
                                    <div class="fw-semibold">Under Review</div>
                                    <div class="small text-muted">Admin is checking</div>
                                </div>
                            </div>
                            <div class="step <?= $step3Class ?>">
                                <div class="bullet">3</div>
                                <div>
                                    <div class="fw-semibold"><?= $status === 'approved' ? 'Approved' : ($status === 'rejected' ? 'Rejected' : 'Final Decision') ?></div>
                                    <div class="small text-muted"><?= $status === 'approved' ? 'Collect your item' : ($status === 'rejected' ? 'Try again' : '') ?></div>
                                </div>
                            </div>
                        </div>

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

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>