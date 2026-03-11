<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Protect page - must be logged in to claim
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$found_id = (int)($_GET['id'] ?? 0);
$message  = '';
$success  = false;
$item     = null;
$user_id  = $_SESSION['user_id'];

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
    } elseif ($item['finder_id'] == $user_id) {
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

    if (empty($proof_description)) {
        $message = "Please explain why you believe this is your item.";
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
            notify_user('admin@lostfound.local', 'New claim submitted',
                        "User #$user_id submitted a claim for found item #$found_id.");

            $success = true;
            $message = "Your claim has been submitted successfully! An administrator will review it soon.";
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
    <title>Claim Item - Kyambogo University Lost & Found System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Lost & Found - KyU</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#claimNav" aria-controls="claimNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="claimNav">
                <div class="ms-auto">
                    <span class="text-white me-3">Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></span>
                    <a href="logout.php" class="btn btn-outline-light">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">

                <?php if ($message): ?>
                    <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($item && !$success): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header bg-success text-white">
                            <h4>Claim This Found Item</h4>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($item['image_path'])): ?>
                                <img src="<?= htmlspecialchars($item['image_path']) ?>" 
                                     class="img-fluid rounded mb-3" alt="Found item" style="max-height: 250px; object-fit: contain; width: 100%;">
                            <?php endif; ?>

                            <h5><?= htmlspecialchars($item['item_name']) ?></h5>
                            <p class="text-muted"><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>Category:</strong> <?= htmlspecialchars($item['category'] ?? 'N/A') ?></li>
                                <li class="list-group-item"><strong>Found on:</strong> <?= htmlspecialchars($item['date_found'] ?? 'N/A') ?></li>
                                <li class="list-group-item"><strong>Location:</strong> <?= htmlspecialchars($item['location'] ?? 'Not specified') ?></li>
                            </ul>
                        </div>
                    </div>

                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h5>Your Claim Details</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Why do you believe this is your item? <span class="text-danger">*</span></label>
                                    <textarea name="proof_description" class="form-control" rows="5" required 
                                              placeholder="Describe matching details: exact color, brand, serial number, contents, scratches, purchase date, receipt info, etc..."></textarea>
                                    <small class="form-text text-muted">Be as detailed as possible — this helps the admin verify your claim.</small>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-3">Submit My Claim</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-5">
                    <a href="search.php" class="btn btn-secondary btn-lg px-5">Back to Search</a>
                    <a href="dashboard.php" class="btn btn-outline-secondary btn-lg px-5 ms-3">Dashboard</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>