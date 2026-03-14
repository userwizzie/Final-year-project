<?php
require_once 'includes/config.php';

// No login required for search, but show name if logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_name    = $_SESSION['name'] ?? 'Guest';

$search_term = trim($_GET['q'] ?? '');
$results     = [];
$has_results = false;
$message     = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($search_term)) {
    try {
        $stmt = $conn->prepare("
            SELECT 'lost' AS type, lost_id AS id, item_name, description, category, date_lost AS item_date, location, image_path
            FROM lost_items
            WHERE item_name LIKE ? OR description LIKE ?
            UNION ALL
            SELECT 'found' AS type, found_id AS id, item_name, description, category, date_found AS item_date, location, image_path
            FROM found_items
            WHERE item_name LIKE ? OR description LIKE ?
            ORDER BY item_date DESC
            LIMIT 50
        ");
        
        $like_term = "%$search_term%";
        $stmt->execute([$like_term, $like_term, $like_term, $like_term]);
        $results = $stmt->fetchAll();
        
        $has_results = !empty($results);
        if (!$has_results) {
            $message = "No matching items found for '$search_term'. Try different keywords.";
        }
    } catch (PDOException $e) {
        $message = "Search error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Items - Kyambogo University Lost & Found</title>
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-kyu shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="<?= $is_logged_in ? 'dashboard.php' : 'index.php' ?>">KyU Lost & Found</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#searchNav" aria-controls="searchNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="searchNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="search.php">Search</a></li>
                    <li class="nav-item"><a class="nav-link" href="report-lost.php">Report Lost</a></li>
                    <li class="nav-item"><a class="nav-link" href="report-found.php">Report Found</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <?php if ($is_logged_in): ?>
                        <span class="navbar-text me-3">Hello, <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></span>
                        <a href="dashboard.php" class="btn btn-kyu-alt me-2">Dashboard</a>
                        <a href="logout.php" class="btn btn-outline-light">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-light me-2">Login</a>
                        <a href="register.php" class="btn btn-kyu">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="mb-4 text-center">Search for Lost or Found Items</h2>

        <form method="GET" class="mb-5">
            <div class="input-group input-group-lg">
                <input type="text" name="q" class="form-control" placeholder="e.g., Samsung phone, black wallet, ID card..." 
                       value="<?= htmlspecialchars($search_term) ?>" required autofocus>
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
        </form>

        <?php if ($message && !$has_results): ?>
            <div class="empty-state mx-auto" style="max-width: 560px;">
                <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="32" cy="32" r="30" opacity="0.2" />
                    <path d="M22 24h20M22 32h20M22 40h14" />
                    <path d="M14 14l12 12" />
                    <path d="M24 14l-12 12" />
                </svg>
                <h4 class="mb-2">No results found</h4>
                <p class="text-muted mb-3"><?= htmlspecialchars($message) ?></p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="search.php" class="btn btn-kyu">Try a new search</a>
                    <a href="report-found.php" class="btn btn-kyu-alt">Report a found item</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($has_results): ?>
            <h4 class="mb-3">Found <?= count($results) ?> matching item(s)</h4>
            <div class="row g-4">
                <?php foreach ($results as $item): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm <?= $item['type'] === 'lost' ? 'border-danger' : 'border-success' ?>">
                            
                            <?php if (!empty($item['image_path'])): ?>
                                <img src="<?= htmlspecialchars($item['image_path']) ?>" 
                                     class="card-img-top" alt="Item image">
                            <?php endif; ?>

                            <div class="card-body">
                                <h5 class="card-title">
                                    <?= htmlspecialchars($item['item_name']) ?>
                                    <span class="badge <?= $item['type'] === 'lost' ? 'bg-danger' : 'bg-success' ?> ms-2">
                                        <?= ucfirst($item['type']) ?>
                                    </span>
                                </h5>
                                <p class="card-text text-muted small">
                                    <?= nl2br(htmlspecialchars(substr($item['description'] ?? '', 0, 120))) ?>...
                                </p>
                                <ul class="list-group list-group-flush small">
                                    <li class="list-group-item"><strong>Category:</strong> <?= htmlspecialchars($item['category'] ?? 'N/A') ?></li>
                                    <li class="list-group-item"><strong>Date:</strong> <?= htmlspecialchars($item['item_date'] ?? 'N/A') ?></li>
                                    <li class="list-group-item"><strong>Location:</strong> <?= htmlspecialchars($item['location'] ?? 'Not specified') ?></li>
                                </ul>
                            </div>

                            <div class="card-footer bg-transparent border-0 text-center">
                                <?php if ($is_logged_in && $item['type'] === 'found'): ?>
                                    <a href="claim.php?id=<?= $item['id'] ?>" class="btn btn-outline-primary w-100">
                                        Claim This Item
                                    </a>
                                <?php elseif (!$is_logged_in && $item['type'] === 'found'): ?>
                                    <small class="text-muted">Login to claim found items</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="text-center mt-5">
            <a href="<?= $is_logged_in ? 'dashboard.php' : 'index.php' ?>" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>