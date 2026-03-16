<?php
require_once 'includes/config.php';

require_user();

$page_title = 'Dashboard';
require_once 'includes/header.php';

$user_id = $_SESSION['user_id'];
$name    = $_SESSION['name'] ?? 'User';

// Check if user just logged in to show welcome message
$show_welcome = isset($_SESSION['just_logged_in']) && $_SESSION['just_logged_in'];
if ($show_welcome) {
    unset($_SESSION['just_logged_in']);
}

$lost_stmt = $conn->prepare("
    SELECT lost_id, item_name, category, date_lost, location, description, image_path
    FROM lost_items WHERE user_id = ? ORDER BY date_lost DESC LIMIT 10
");
$lost_stmt->execute([$user_id]);
$lost_items = $lost_stmt->fetchAll();

$found_stmt = $conn->prepare("
    SELECT found_id, item_name, category, date_found, location, description, image_path,
           reward_status, reward_points
    FROM found_items WHERE user_id = ? ORDER BY date_found DESC LIMIT 10
");
$found_stmt->execute([$user_id]);
$found_items = $found_stmt->fetchAll();

$claim_stmt = $conn->prepare("
    SELECT c.claim_id, c.claim_date, c.status, f.item_name, f.found_id
    FROM claims c JOIN found_items f ON c.found_id = f.found_id
    WHERE c.user_id = ?
    ORDER BY c.claim_date DESC LIMIT 10
");
$claim_stmt->execute([$user_id]);
$claims = $claim_stmt->fetchAll();
?>

<!-- Welcome Alert -->
<?php if ($show_welcome): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>Welcome back, <?php echo htmlspecialchars($name); ?>! You're now logged in.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<h2 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>

<!-- Action Cards -->
<div class="row g-4 mb-5">
    <div class="col-6 col-lg-3">
        <div class="card text-center h-100 action-card">
            <div class="card-body py-4">
                <div class="icon-circle icon-circle-warning mb-3">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h6 class="card-title fw-semibold mb-1">Report Lost</h6>
                <p class="card-text text-muted small mb-3">Lost something? Report it here.</p>
                <a href="report-lost.php" class="btn btn-warning btn-sm"><i class="fas fa-plus me-1"></i>Report Lost</a>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card text-center h-100 action-card">
            <div class="card-body py-4">
                <div class="icon-circle icon-circle-success mb-3">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h6 class="card-title fw-semibold mb-1">Report Found</h6>
                <p class="card-text text-muted small mb-3">Found an item? Help reunite it.</p>
                <a href="report-found.php" class="btn btn-success btn-sm"><i class="fas fa-plus me-1"></i>Report Found</a>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card text-center h-100 action-card">
            <div class="card-body py-4">
                <div class="icon-circle icon-circle-primary mb-3">
                    <i class="fas fa-search"></i>
                </div>
                <h6 class="card-title fw-semibold mb-1">Search Items</h6>
                <p class="card-text text-muted small mb-3">Browse all lost &amp; found items.</p>
                <a href="search.php" class="btn btn-primary btn-sm"><i class="fas fa-search me-1"></i>Search</a>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card text-center h-100 action-card">
            <div class="card-body py-4">
                <div class="icon-circle icon-circle-info mb-3">
                    <i class="fas fa-list"></i>
                </div>
                <h6 class="card-title fw-semibold mb-1">My Claims</h6>
                <p class="card-text text-muted small mb-3">Check your claim status.</p>
                <a href="my-claims.php" class="btn btn-info btn-sm"><i class="fas fa-list me-1"></i>View Claims</a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row g-4">
    <!-- Lost Items -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>My Lost Reports</h5>
            </div>
            <div class="card-body">
                <?php if (empty($lost_items)): ?>
                    <p class="text-muted">No lost items reported yet.</p>
                    <a href="report-lost.php" class="btn btn-sm btn-warning">Report First Item</a>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($lost_items as $item): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['item_name']); ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i><?php echo date('M j, Y', strtotime($item['date_lost'])); ?>
                                            <i class="fas fa-map-marker-alt ms-2 me-1"></i><?php echo htmlspecialchars($item['location']); ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-warning">Lost</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Found Items -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>My Found Reports</h5>
            </div>
            <div class="card-body">
                <?php if (empty($found_items)): ?>
                    <p class="text-muted">No found items reported yet.</p>
                    <a href="report-found.php" class="btn btn-sm btn-success">Report First Item</a>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($found_items as $item): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['item_name']); ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i><?php echo date('M j, Y', strtotime($item['date_found'])); ?>
                                            <i class="fas fa-map-marker-alt ms-2 me-1"></i><?php echo htmlspecialchars($item['location']); ?>
                                        </small>
                                        <?php if ($item['reward_status'] === 'rewarded'): ?>
                                            <br><small class="text-success"><i class="fas fa-gift me-1"></i>Rewarded: <?php echo $item['reward_points']; ?> pts</small>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge bg-success">Found</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Claims -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>My Claims</h5>
            </div>
            <div class="card-body">
                <?php if (empty($claims)): ?>
                    <p class="text-muted">No claims made yet.</p>
                    <a href="search.php" class="btn btn-sm btn-info">Browse Items</a>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($claims as $claim): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($claim['item_name']); ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i><?php echo date('M j, Y', strtotime($claim['claim_date'])); ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?php echo $claim['status'] === 'approved' ? 'success' : ($claim['status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                        <?php echo ucfirst($claim['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>