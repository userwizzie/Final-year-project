<?php
require_once 'includes/config.php';

// No login required for search, but show name if logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_name    = $_SESSION['name'] ?? 'Guest';

$search_term = trim($_GET['q'] ?? '');
$results     = [];
$has_results = false;
$message     = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $search_term !== '') {
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
            $message = "No matching items found for '" . htmlspecialchars($search_term, ENT_QUOTES, 'UTF-8') . "'. Try different keywords.";
        }
    } catch (PDOException $e) {
        $message = "Search error: " . $e->getMessage();
    }
}

$lost_count = 0;
$found_count = 0;
foreach ($results as $result_item) {
    if (($result_item['type'] ?? '') === 'lost') {
        $lost_count++;
    } elseif (($result_item['type'] ?? '') === 'found') {
        $found_count++;
    }
}

function short_desc(?string $text, int $len = 145): string {
    $text = trim((string)$text);
    if ($text === '') {
        return 'No description provided.';
    }
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($text) > $len ? mb_substr($text, 0, $len) . '...' : $text;
    }
    return strlen($text) > $len ? substr($text, 0, $len) . '...' : $text;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Items | Lost & Found KyU</title>
    <link rel="icon" type="image/svg+xml" sizes="any" href="assets/images/favicon.svg?v=20260317">
    <link rel="icon" type="image/png" sizes="96x96" href="assets/images/favicon-96x96.png?v=20260317">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-touch-icon.png?v=20260317">
    <link rel="manifest" href="assets/images/site.webmanifest?v=20260317">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">

    <style>
        :root {
            --search-blue: #0d6efd;
            --search-teal: #58a6ff;
            --search-red: #dc3545;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background:
                radial-gradient(circle at 0% 0%, rgba(13,110,253,0.16), transparent 42%),
                radial-gradient(circle at 100% 100%, rgba(13,110,253,0.1), transparent 42%),
                #f4f7fb;
        }

        .search-navbar {
            background: linear-gradient(120deg, #05214a 0%, #0d6efd 100%);
        }

        .hero {
            margin-top: 2rem;
            margin-bottom: 1.3rem;
            border-radius: 1.2rem;
            background: linear-gradient(130deg, #0a2b63 0%, #0d6efd 100%);
            color: #fff;
            padding: 1.7rem;
            box-shadow: 0 18px 46px rgba(5, 28, 74, 0.28);
        }

        .hero h1 {
            font-size: 1.45rem;
            font-weight: 700;
            margin-bottom: 0.35rem;
        }

        .hero p {
            margin-bottom: 0;
            opacity: 0.86;
        }

        .search-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 16px 42px rgba(9,13,38,0.12);
            overflow: hidden;
        }

        .search-form-wrap {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 8px 26px rgba(12, 39, 93, 0.08);
            padding: 1rem;
        }

        .search-input-shell {
            position: relative;
        }

        .search-input-shell i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #8090a6;
            z-index: 3;
        }

        .search-input {
            height: 56px;
            border-radius: 0.8rem !important;
            border: 1.5px solid #d7deea;
            background: #f8fbff;
            padding-left: 2.8rem;
        }

        .search-input:focus {
            border-color: var(--search-blue);
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.18);
            background: #fff;
        }

        .btn-search {
            height: 56px;
            border-radius: 0.8rem;
            border: none;
            background: linear-gradient(130deg, #0d6efd 0%, #58a6ff 100%);
            box-shadow: 0 10px 24px rgba(13,110,253,0.28);
            font-weight: 600;
        }

        .btn-search:hover {
            background: linear-gradient(130deg, #0a58ca 0%, #3d8eff 100%);
        }

        .quick-tags .btn {
            border-radius: 999px;
            font-size: 0.76rem;
            padding: 0.35rem 0.65rem;
        }

        .stats-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.72rem;
            border-radius: 999px;
            background: #eef3fb;
            color: #334155;
            font-size: 0.8rem;
            margin-right: 0.4rem;
            margin-bottom: 0.4rem;
        }

        .result-card {
            border: 0;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 10px 26px rgba(9,13,38,0.11);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .result-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 32px rgba(9,13,38,0.16);
        }

        .result-card .thumb {
            height: 190px;
            width: 100%;
            object-fit: cover;
            background: #f3f6fb;
        }

        .fallback-thumb {
            height: 190px;
            background: #f3f6fb;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #93a0b2;
            border-bottom: 1px solid #e6ebf2;
        }

        .type-pill {
            border-radius: 999px;
            font-size: 0.72rem;
            padding: 0.35rem 0.65rem;
            font-weight: 600;
        }

        .type-pill.lost {
            background: rgba(220,53,69,0.12);
            color: #b42332;
        }

        .type-pill.found {
            background: rgba(25,135,84,0.12);
            color: #12693f;
        }

        .result-meta {
            font-size: 0.82rem;
            color: #556377;
            margin-bottom: 0.4rem;
        }

        .result-meta i {
            width: 16px;
            margin-right: 0.25rem;
            color: #7586a1;
        }

        .empty-card {
            border: 0;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 10px 26px rgba(9,13,38,0.1);
            padding: 2rem 1.2rem;
            text-align: center;
        }

        @media (max-width: 576px) {
            .hero {
                padding: 1.2rem;
            }
            .hero h1 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark search-navbar shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo $is_logged_in ? 'dashboard.php' : 'index.php'; ?>">
                <i class="fas fa-magnifying-glass-location me-2"></i>Lost & Found KyU
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#searchNav" aria-controls="searchNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="searchNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="search.php">Search</a></li>
                    <li class="nav-item"><a class="nav-link" href="report-lost.php">Report Lost</a></li>
                    <li class="nav-item"><a class="nav-link" href="report-found.php">Report Found</a></li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <?php if ($is_logged_in): ?>
                        <span class="text-white small"><i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($user_name); ?></span>
                        <a href="dashboard.php" class="btn btn-light btn-sm">Dashboard</a>
                        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-light btn-sm">Login</a>
                        <a href="register.php" class="btn btn-light btn-sm">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-5">
        <section class="hero">
            <h1><i class="fas fa-compass me-2"></i>Search Lost & Found Items</h1>
            <p>Find reported belongings quickly using names, descriptions, brands, locations, or unique clues.</p>
        </section>

        <section class="search-form-wrap mb-4">
            <form method="GET" class="row g-2 align-items-center" id="searchForm">
                <div class="col-md-9">
                    <div class="search-input-shell">
                        <i class="fas fa-search"></i>
                        <input type="text" name="q" class="form-control search-input" required
                               placeholder="e.g., Samsung phone, black wallet, student ID"
                               value="<?php echo htmlspecialchars($search_term); ?>" autofocus>
                    </div>
                </div>
                <div class="col-md-3 d-grid">
                    <button class="btn btn-search text-white" type="submit">
                        <i class="fas fa-magnifying-glass me-2"></i>Search
                    </button>
                </div>
            </form>

            <div class="quick-tags mt-3">
                <span class="small text-muted me-2">Quick ideas:</span>
                <button type="button" class="btn btn-outline-secondary btn-sm quick-tag" data-q="phone">Phone</button>
                <button type="button" class="btn btn-outline-secondary btn-sm quick-tag" data-q="wallet">Wallet</button>
                <button type="button" class="btn btn-outline-secondary btn-sm quick-tag" data-q="ID card">ID card</button>
                <button type="button" class="btn btn-outline-secondary btn-sm quick-tag" data-q="bag">Bag</button>
                <button type="button" class="btn btn-outline-secondary btn-sm quick-tag" data-q="keys">Keys</button>
            </div>
        </section>

        <?php if ($search_term !== '' && $has_results): ?>
            <div class="mb-3">
                <h2 class="h6 mb-2">Search results for <span class="text-primary">"<?php echo htmlspecialchars($search_term); ?>"</span></h2>
                <div>
                    <span class="stats-chip"><i class="fas fa-list"></i><?php echo count($results); ?> total</span>
                    <span class="stats-chip"><i class="fas fa-triangle-exclamation text-danger"></i><?php echo $lost_count; ?> lost</span>
                    <span class="stats-chip"><i class="fas fa-hand-holding-heart text-success"></i><?php echo $found_count; ?> found</span>
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($results as $item): ?>
                    <div class="col-md-6 col-lg-4">
                        <article class="card result-card h-100">
                            <?php if (!empty($item['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($item['image_path']); ?>" class="thumb" alt="Item image">
                            <?php else: ?>
                                <div class="fallback-thumb">
                                    <div class="text-center">
                                        <i class="fas fa-image fa-2x mb-2"></i>
                                        <div class="small">No image available</div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h3 class="h6 mb-0 fw-bold pe-2"><?php echo htmlspecialchars($item['item_name']); ?></h3>
                                    <span class="type-pill <?php echo $item['type'] === 'lost' ? 'lost' : 'found'; ?>">
                                        <i class="fas <?php echo $item['type'] === 'lost' ? 'fa-triangle-exclamation' : 'fa-hand-holding-heart'; ?> me-1"></i>
                                        <?php echo ucfirst($item['type']); ?>
                                    </span>
                                </div>

                                <p class="small text-muted mb-3"><?php echo nl2br(htmlspecialchars(short_desc($item['description'] ?? ''))); ?></p>

                                <div class="result-meta"><i class="fas fa-tags"></i><?php echo htmlspecialchars($item['category'] ?? 'N/A'); ?></div>
                                <div class="result-meta"><i class="fas fa-calendar-days"></i><?php echo htmlspecialchars($item['item_date'] ?? 'N/A'); ?></div>
                                <div class="result-meta mb-3"><i class="fas fa-location-dot"></i><?php echo htmlspecialchars($item['location'] ?? 'Not specified'); ?></div>

                                <div class="mt-auto">
                                    <?php if ($is_logged_in && $item['type'] === 'found'): ?>
                                        <a href="claim.php?id=<?php echo (int)$item['id']; ?>" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-hand-holding-heart me-1"></i>Claim This Item
                                        </a>
                                    <?php elseif (!$is_logged_in && $item['type'] === 'found'): ?>
                                        <a href="login.php" class="btn btn-outline-secondary w-100">
                                            <i class="fas fa-right-to-bracket me-1"></i>Login to Claim
                                        </a>
                                    <?php else: ?>
                                        <span class="btn btn-light w-100 disabled">No claim action required</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php elseif ($search_term !== '' && !$has_results): ?>
            <div class="empty-card">
                <i class="fas fa-face-frown-open fa-2x text-muted mb-3"></i>
                <h3 class="h5">No matching results</h3>
                <p class="text-muted mb-3"><?php echo $message; ?></p>
                <div class="d-flex justify-content-center flex-wrap gap-2">
                    <a href="search.php" class="btn btn-primary"><i class="fas fa-rotate-left me-1"></i>Try another search</a>
                    <a href="report-found.php" class="btn btn-outline-success"><i class="fas fa-plus me-1"></i>Report Found Item</a>
                    <a href="report-lost.php" class="btn btn-outline-danger"><i class="fas fa-plus me-1"></i>Report Lost Item</a>
                </div>
            </div>

        <?php else: ?>
            <div class="empty-card">
                <i class="fas fa-search fa-2x text-primary mb-3"></i>
                <h3 class="h5">Start by searching for an item</h3>
                <p class="text-muted mb-0">Use the search bar above with item names, brands, categories, or locations.</p>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="<?php echo $is_logged_in ? 'dashboard.php' : 'index.php'; ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const searchInput = document.querySelector('input[name="q"]');
        const searchForm = document.getElementById('searchForm');

        document.querySelectorAll('.quick-tag').forEach((btn) => {
            btn.addEventListener('click', function () {
                if (!searchInput) return;
                searchInput.value = this.dataset.q || '';
                searchForm?.submit();
            });
        });
    </script>
</body>
</html>
