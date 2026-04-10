<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Protect page - must be logged in to claim
require_user();

$found_id = (int)($_GET['id'] ?? 0);
$message  = '';
$success  = false;
$item     = null;
$user_id  = $_SESSION['user_id'];

$proof_description_value = '';

if ($found_id <= 0) {
    $message = "Invalid item ID.";
} else {
    // Fetch the found item
    $stmt = $conn->prepare("
        SELECT found_id, item_name, description, category, date_found, location, image_path, user_id AS finder_id
        FROM found_items
        WHERE found_id = ?
    ");
    $stmt->execute([$found_id]);
    $item = $stmt->fetch();

    if (!$item) {
        $message = "Found item not found or no longer available.";
    } elseif ((int)$item['finder_id'] === (int)$user_id) {
        $message = "You cannot claim an item you reported as found yourself.";
    } else {
        // Check for duplicate claim by the same user on this found item
        $check_stmt = $conn->prepare("
            SELECT claim_id FROM claims
            WHERE found_id = ? AND user_id = ?
            LIMIT 1
        ");
        $check_stmt->execute([$found_id, $user_id]);
        if ($check_stmt->fetch()) {
            $message = "You have already submitted a claim for this item.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $item && empty($message)) {
    $proof_description = trim($_POST['proof_description'] ?? '');
    $proof_description_value = htmlspecialchars($proof_description, ENT_QUOTES, 'UTF-8');

    if (empty($proof_description)) {
        $message = "Please explain why you believe this is your item.";
    } elseif (strlen($proof_description) > 500) {
        $message = "Proof description cannot exceed 500 characters.";
    } else {
        try {
            $stmt = $conn->prepare("
                INSERT INTO claims
                (claim_date, status, lost_id, found_id, admin_id, proof_description, user_id)
                VALUES (CURDATE(), 'pending', NULL, ?, NULL, ?, ?)
            ");
            $stmt->execute([$found_id, $proof_description, $user_id]);

            // mark item as claimed so the finder sees the review status
            $conn->prepare("UPDATE found_items SET reward_status = 'claimed' WHERE found_id = ?")
                 ->execute([$found_id]);

            // notify admin about new claim (simulated email)
            if (function_exists('notify_user')) {
                notify_user(
                    'admin@lostfound.local',
                    'New claim submitted',
                    "User #$user_id submitted a claim for found item #$found_id."
                );
            }

            $success = true;
            $message = "Your claim has been submitted successfully. An administrator will review it soon.";
            $proof_description_value = '';
        } catch (PDOException $e) {
            $message = "Error submitting claim: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Item | Lost & Found KyU</title>
    <link rel="icon" type="image/svg+xml" sizes="any" href="assets/images/favicon.svg?v=20260317">
    <link rel="icon" type="image/png" sizes="96x96" href="assets/images/favicon-96x96.png?v=20260317">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-touch-icon.png?v=20260317">
    <link rel="manifest" href="assets/images/site.webmanifest?v=20260317">

    <!-- Removed Google Fonts CDN, using local Inter font -->
    <link href="assets/css/local-icons.css" rel="stylesheet">
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">

    <style>
        :root {
            --claim-primary: #0d6efd;
            --claim-accent: #58a6ff;
            --claim-soft: rgba(13, 110, 253, 0.1);
            --claim-bg: #f4f7fc;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background:
                radial-gradient(circle at 0% 0%, rgba(13,110,253,0.16), transparent 38%),
                radial-gradient(circle at 100% 100%, rgba(13,110,253,0.1), transparent 40%),
                var(--claim-bg);
        }

        .claim-navbar {
            background: linear-gradient(120deg, #05214a 0%, #0d6efd 100%);
        }

        .claim-wrap {
            max-width: 1100px;
            margin: 2rem auto 2.5rem;
        }

        .glass-panel {
            border: 0;
            border-radius: 1.1rem;
            box-shadow: 0 24px 60px rgba(9,13,38,0.2);
            overflow: hidden;
            background: #fff;
        }

        .claim-header {
            background: linear-gradient(140deg, #0a2b63 0%, #0d6efd 100%);
            color: #fff;
            padding: 1.3rem 1.5rem;
        }

        .badge-icon {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.35);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 0.7rem;
        }

        .stepper .step.active {
            background: var(--claim-soft);
            border-color: rgba(13, 110, 253, 0.25);
        }

        .stepper .step.done {
            background: rgba(25, 135, 84, 0.12);
            border-color: rgba(25, 135, 84, 0.3);
        }

        .progress {
            height: 8px;
            border-radius: 999px;
            background: rgba(13,110,253,0.08);
        }

        .progress-bar {
            background: linear-gradient(90deg, #0d6efd, #58a6ff);
            transition: width 0.25s ease;
        }

        .item-image {
            width: 100%;
            max-height: 280px;
            object-fit: cover;
            border-radius: 0.85rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 12px 26px rgba(0,0,0,0.12);
        }

        .meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.3rem 0.65rem;
            border-radius: 999px;
            background: #f1f5fb;
            color: #334155;
            font-size: 0.78rem;
            margin-right: 0.35rem;
            margin-bottom: 0.35rem;
        }

        .proof-wrap {
            position: relative;
        }

        .proof-wrap i {
            position: absolute;
            top: 1rem;
            left: 0.85rem;
            color: #8894a7;
        }

        .proof-wrap textarea {
            min-height: 180px;
            border-radius: 0.8rem !important;
            border: 1.4px solid #d7deea;
            background: #f8fbff;
            padding: 0.95rem 0.95rem 0.95rem 2.5rem;
            resize: vertical;
        }

        .proof-wrap textarea:focus {
            border-color: var(--claim-primary);
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.17);
            background: #fff;
        }

        .char-counter {
            font-size: 0.8rem;
            color: #64748b;
        }

        .char-counter.near {
            color: #b45309;
        }

        .char-counter.limit {
            color: #b42332;
            font-weight: 600;
        }

        .btn-claim {
            border: none;
            border-radius: 0.75rem;
            background: linear-gradient(130deg, #0d6efd 0%, #58a6ff 100%);
            color: #fff;
            font-weight: 600;
            box-shadow: 0 10px 24px rgba(13,110,253,0.3);
        }

        .btn-claim:hover {
            color: #fff;
            background: linear-gradient(130deg, #0a58ca 0%, #3d8eff 100%);
            transform: translateY(-1px);
        }

        .alert-float {
            animation: appearUp 0.45s ease;
        }

        @keyframes appearUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .claim-wrap {
                margin-top: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark claim-navbar shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php"><i class="fas fa-shield-heart me-2"></i>Lost & Found KyU</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#claimNav" aria-controls="claimNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="claimNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="search.php">Search</a></li>
                    <li class="nav-item"><a class="nav-link" href="report-lost.php">Report Lost</a></li>
                    <li class="nav-item"><a class="nav-link" href="report-found.php">Report Found</a></li>
                </ul>
                <div class="d-flex align-items-center text-white gap-2">
                    <span class="small"><i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span>
                    <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container claim-wrap">
        <div class="card glass-panel">
            <div class="claim-header">
                <div class="badge-icon"><i class="fas fa-hand-holding-heart"></i></div>
                <h2 class="h5 mb-1 fw-bold">Claim Found Item</h2>
                <p class="small mb-0 opacity-75">Provide strong identifying details so administrators can verify ownership safely.</p>
            </div>

            <div class="card-body p-4 p-lg-5">
                <div class="stepper mb-3" id="claimStepper">
                    <div class="step active" id="claim-step-1">
                        <div class="bullet">1</div>
                        <div><strong>Review item</strong><br><small class="text-muted">Confirm key details</small></div>
                    </div>
                    <div class="step" id="claim-step-2">
                        <div class="bullet">2</div>
                        <div><strong>Your proof</strong><br><small class="text-muted">Add identifying details</small></div>
                    </div>
                    <div class="step" id="claim-step-3">
                        <div class="bullet">3</div>
                        <div><strong>Submit</strong><br><small class="text-muted">Send for admin review</small></div>
                    </div>
                </div>

                <div class="progress mb-4" role="progressbar" aria-label="Claim progress" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar" id="claimProgress" style="width: 33%"></div>
                </div>

                <?php if ($message): ?>
                    <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show alert-float" role="alert">
                        <i class="fas <?php echo $success ? 'fa-circle-check' : 'fa-circle-exclamation'; ?> me-2"></i>
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($item && !$success): ?>
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                <h5 class="mb-3"><i class="fas fa-box-open me-2 text-primary"></i>Found Item</h5>

                                <?php if (!empty($item['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" class="item-image mb-3" alt="Found item image">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center text-muted border rounded-3 mb-3" style="height:220px; background:#f8fbff;">
                                        <div class="text-center">
                                            <i class="fas fa-image fa-2x mb-2"></i>
                                            <div class="small">No image provided</div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <h6 class="fw-bold mb-2"><?php echo htmlspecialchars($item['item_name']); ?></h6>
                                <p class="small text-muted mb-3"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>

                                <div>
                                    <span class="meta-chip"><i class="fas fa-tags"></i><?php echo htmlspecialchars($item['category'] ?? 'N/A'); ?></span>
                                    <span class="meta-chip"><i class="fas fa-calendar-days"></i><?php echo htmlspecialchars($item['date_found'] ?? 'N/A'); ?></span>
                                    <span class="meta-chip"><i class="fas fa-location-dot"></i><?php echo htmlspecialchars($item['location'] ?? 'Not specified'); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="border rounded-4 p-3 p-md-4 h-100">
                                <h5 class="mb-3"><i class="fas fa-clipboard-check me-2 text-primary"></i>Your Claim Details</h5>

                                <div class="small text-muted mb-3">
                                    <i class="fas fa-circle-info me-1"></i>
                                    Include details only the true owner would know (serial numbers, hidden marks, exact contents, etc.).
                                </div>

                                <form method="POST" id="claimForm" novalidate>
                                    <div class="mb-2">
                                        <label for="proof_description" class="form-label fw-semibold">
                                            Why do you believe this is your item? <span class="text-danger">*</span>
                                            <i class="fas fa-circle-info text-muted ms-1" data-bs-toggle="tooltip" title="Detailed, specific evidence increases verification success."></i>
                                        </label>
                                        <div class="proof-wrap">
                                            <i class="fas fa-pen-to-square"></i>
                                            <textarea id="proof_description" name="proof_description" class="form-control" required maxlength="500"
                                                      placeholder="Describe exact matching details: color shade, scratches, stickers, contents, purchase date, serial number, etc."><?php echo $proof_description_value; ?></textarea>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <small class="text-muted">Maximum 500 characters.</small>
                                        <span class="char-counter" id="proofCounter">0 / 500</span>
                                    </div>

                                    <div class="d-flex gap-2 flex-column flex-md-row">
                                        <button type="submit" class="btn btn-claim flex-grow-1" id="submitClaimBtn">
                                            <i class="fas fa-paper-plane me-2"></i>Submit My Claim
                                        </button>
                                        <a href="search.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left me-1"></i>Back to Search
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!$item): ?>
                    <div class="text-center py-4">
                        <a href="search.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Search
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const proofInput = document.getElementById('proof_description');
        const proofCounter = document.getElementById('proofCounter');
        const claimProgress = document.getElementById('claimProgress');

        const step1 = document.getElementById('claim-step-1');
        const step2 = document.getElementById('claim-step-2');
        const step3 = document.getElementById('claim-step-3');

        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
            new bootstrap.Tooltip(el);
        });

        function updateCounterAndSteps() {
            if (!proofInput || !proofCounter) return;
            const len = proofInput.value.length;
            proofCounter.textContent = len + ' / 500';
            proofCounter.classList.remove('near', 'limit');
            if (len >= 430 && len < 500) proofCounter.classList.add('near');
            if (len >= 500) proofCounter.classList.add('limit');

            step1.classList.add('done');
            step2.classList.remove('active', 'done');
            step3.classList.remove('active', 'done');

            if (len === 0) {
                step2.classList.add('active');
                claimProgress.style.width = '60%';
            } else {
                step2.classList.add('done');
                step3.classList.add('active');
                claimProgress.style.width = '90%';
            }
        }

        if (proofInput) {
            updateCounterAndSteps();
            proofInput.addEventListener('input', updateCounterAndSteps);

            document.getElementById('claimForm')?.addEventListener('submit', function () {
                step1.classList.add('done');
                step2.classList.add('done');
                step3.classList.add('done');
                claimProgress.style.width = '100%';

                const submitBtn = document.getElementById('submitClaimBtn');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting claim...';
                }
            });
        }
    </script>
</body>
</html>
