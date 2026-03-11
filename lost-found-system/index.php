<?php
require_once 'includes/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kyambogo Lost & Found System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .hero { padding: 100px 0; text-align: center; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">Lost & Found - KyU</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#homeNav" aria-controls="homeNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="homeNav">
                <div class="ms-auto">
                    <a href="login.php" class="btn btn-outline-primary me-2">Login</a>
                    <a href="register.php" class="btn btn-primary">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="hero">
        <div class="container">
            <h1 class="display-4">Welcome to the Web-Based Lost and Found System</h1>
            <p class="lead">Report lost or found items • Search • Reclaim your property securely</p>
            <div class="mt-4">
                <a href="report-lost.php" class="btn btn-lg btn-danger">I Lost Something</a>
                <a href="report-found.php" class="btn btn-lg btn-success ms-3">I Found Something</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (optional for now) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>