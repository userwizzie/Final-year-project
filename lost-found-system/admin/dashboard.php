<?php
require_once '../includes/config.php';

// Protect: must be logged in AND admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$name = $_SESSION['name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Lost & Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Admin - Lost & Found KyU</a>
            <div class="ms-auto">
                <span class="text-white me-3">Welcome, <?= htmlspecialchars($name) ?></span>
                <a href="../logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="mb-4">Admin Dashboard</h2>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card text-center border-primary shadow">
                    <div class="card-body">
                        <h5 class="card-title">Verify Claims</h5>
                        <p class="card-text">Review and approve/reject ownership claims.</p>
                        <a href="verify-claims.php" class="btn btn-primary">Go to Claims</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center border-info shadow">
                    <div class="card-body">
                        <h5 class="card-title">View All Items</h5>
                        <p class="card-text">Browse reported lost & found items.</p>
                        <a href="#" class="btn btn-info text-white disabled">Coming Soon</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center border-warning shadow">
                    <div class="card-body">
                        <h5 class="card-title">Manage Users</h5>
                        <p class="card-text">View or manage registered users.</p>
                        <a href="#" class="btn btn-warning text-dark disabled">Coming Soon</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info mt-5">
            <strong>Tip:</strong> Start by checking pending claims in the "Verify Claims" section.
        </div>
    </div>

</body>
</html>