<?php
require_once '../includes/config.php';

// Protect: must be logged in AND admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$name = $_SESSION['name'] ?? 'Admin';

// gather some summary stats
try {
    $stats = [];
    // pending claims
    $stmt = $conn->query("SELECT COUNT(*) FROM claims WHERE status='pending'");
    $stats['pending_claims'] = $stmt->fetchColumn();
    // total users
    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    $stats['total_users'] = $stmt->fetchColumn();
    // lost items
    $stmt = $conn->query("SELECT COUNT(*) FROM lost_items");
    $stats['lost_count'] = $stmt->fetchColumn();
    // found items
    $stmt = $conn->query("SELECT COUNT(*) FROM found_items");
    $stats['found_count'] = $stmt->fetchColumn();
    // total rewards given
    $stmt = $conn->query("SELECT IFNULL(SUM(reward_points),0) FROM found_items WHERE reward_status='rewarded'");
    $stats['total_rewards'] = $stmt->fetchColumn();
} catch (PDOException $e) {
    $stats = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Lost & Found</title>
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
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
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="verify-claims.php">Verify Claims</a></li>
                    <li class="nav-item"><a class="nav-link" href="reports.php">Reports</a></li>
                    <li class="nav-item"><a class="nav-link" href="view-items.php">Manage Items</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage-users.php">Users</a></li>
                </ul>
                <div class="d-flex">
                    <a href="../logout.php" class="btn btn-outline-light">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="mb-4">Admin Dashboard</h2>

        <!-- summary statistics -->
        <div class="row mb-5 g-4">
            <div class="col-md-3 col-sm-6">
                <div class="card text-center border-secondary shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">Pending Claims</h6>
                        <h3><?= number_format($stats['pending_claims'] ?? 0) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card text-center border-secondary shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">Users</h6>
                        <h3><?= number_format($stats['total_users'] ?? 0) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card text-center border-secondary shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">Lost Items</h6>
                        <h3><?= number_format($stats['lost_count'] ?? 0) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card text-center border-secondary shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">Found Items</h6>
                        <h3><?= number_format($stats['found_count'] ?? 0) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card text-center border-secondary shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">Rewards Given</h6>
                        <h3><?= number_format($stats['total_rewards'] ?? 0) ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card text-center border-primary shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title">Verify Claims</h5>
                        <p class="card-text">Review and approve/reject ownership claims.</p>
                        <a href="verify-claims.php" class="btn btn-primary">Go to Claims</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center border-success shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title">Reports</h5>
                        <p class="card-text">View analytics, statistics, and export data.</p>
                        <a href="reports.php" class="btn btn-success">View Reports</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center border-info shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title">Manage Items</h5>
                        <p class="card-text">View and delete lost and found reports.</p>
                        <a href="view-items.php" class="btn btn-info text-white">Go to Items</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center border-warning shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title">Manage Users</h5>
                        <p class="card-text">List all registered accounts.</p>
                        <a href="manage-users.php" class="btn btn-warning text-dark">Go to Users</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info mt-5">
            <strong>Tip:</strong> Start by checking pending claims in the "Verify Claims" section.
        </div>
    </div>

    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>