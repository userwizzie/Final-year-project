<?php
require_once 'includes/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kyambogo University Lost & Found</title>
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        header.hero {
            background: url('assets/images/hero-bg.jpg') no-repeat center center;
            background-size: cover;
            height: 78vh;
            color: #fff;
        }
        header.hero .overlay { background-color: rgba(0,0,0,.5); position: absolute; top:0; left:0; right:0; bottom:0; }
        section.features .bi { font-size: 3rem; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-kyu shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-compass-fill me-2"></i>KyU Lost & Found</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#homeNav" aria-controls="homeNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="homeNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="search.php">Search</a></li>
                    <li class="nav-item"><a class="nav-link" href="report-lost.php">Report Lost</a></li>
                    <li class="nav-item"><a class="nav-link" href="report-found.php">Report Found</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <a href="login.php" class="btn btn-outline-light me-2">Login</a>
                    <a href="register.php" class="btn btn-kyu">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <header class="hero d-flex align-items-center justify-content-center text-center position-relative">
        <div class="overlay"></div>
        <div class="container position-relative">
            <h1 class="display-4 fw-bold">Welcome to KyU Lost &amp; Found</h1>
            <p class="lead mb-4">Report lost or found items, search the database, and reclaim your property with confidence.</p>
            <a href="report-lost.php" class="btn btn-lg btn-danger me-3">I Lost Something</a>
            <a href="report-found.php" class="btn btn-lg btn-success">I Found Something</a>
        </div>
    </header>

    <section class="features py-5">
        <div class="container">
            <div class="row text-center g-4">
                <div class="col-md-4">
                    <i class="bi bi-search text-primary"></i>
                    <h5 class="mt-3">Search</h5>
                    <p>Quickly locate items that have been reported lost or found.</p>
                </div>
                <div class="col-md-4">
                    <i class="bi bi-pencil-square text-success"></i>
                    <h5 class="mt-3">Report</h5>
                    <p>Submit a new lost or found report in just a few clicks.</p>
                </div>
                <div class="col-md-4">
                    <i class="bi bi-shield-lock text-danger"></i>
                    <h5 class="mt-3">Secure</h5>
                    <p>Manage your claims securely with a personal account.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-light py-3 text-center mt-auto">
        <div class="container">
            <small class="text-muted">&copy; <?= date('Y') ?> Kyambogo University Lost &amp; Found System</small>
        </div>
    </footer>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>