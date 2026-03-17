<?php
require_once '../includes/config.php';

require_admin();

$page_title = 'Admin Dashboard';
require_once '../includes/header.php';

$show_welcome = isset($_SESSION['just_logged_in']) && $_SESSION['just_logged_in'];
if ($show_welcome) {
    unset($_SESSION['just_logged_in']);
}

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

<style>
    .login-popup {
        position: fixed;
        top: 1rem;
        left: 50%;
        transform: translate(-50%, -140%);
        z-index: 1080;
        width: min(92vw, 700px);
        border-radius: 1rem;
        border: none;
        box-shadow: 0 14px 36px rgba(13, 110, 253, 0.28);
        animation: popupDropIn 0.55s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }

    @keyframes popupDropIn {
        from {
            transform: translate(-50%, -150%);
            opacity: 0;
        }
        to {
            transform: translate(-50%, 0);
            opacity: 1;
        }
    }

    @media (max-width: 576px) {
        .login-popup {
            top: 0.75rem;
            width: calc(100vw - 1rem);
        }
    }
</style>

<?php if ($show_welcome): ?>
    <div class="alert alert-info alert-dismissible fade show login-popup" role="alert">
        <i class="fas fa-user-shield me-2"></i><strong>Welcome back!</strong> Admin session is active.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<h2 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h2>

<!-- Summary Statistics -->
<div class="row mb-5 g-4">
    <div class="col-md-3 col-sm-6">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-clock fa-2x text-warning mb-3"></i>
                <h6 class="card-subtitle mb-2 text-muted">Pending Claims</h6>
                <h3 class="text-warning"><?= number_format($stats['pending_claims'] ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-users fa-2x text-primary mb-3"></i>
                <h6 class="card-subtitle mb-2 text-muted">Total Users</h6>
                <h3 class="text-primary"><?= number_format($stats['total_users'] ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-3"></i>
                <h6 class="card-subtitle mb-2 text-muted">Lost Items</h6>
                <h3 class="text-danger"><?= number_format($stats['lost_count'] ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-plus-circle fa-2x text-success mb-3"></i>
                <h6 class="card-subtitle mb-2 text-muted">Found Items</h6>
                <h3 class="text-success"><?= number_format($stats['found_count'] ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-gift fa-2x text-info mb-3"></i>
                <h6 class="card-subtitle mb-2 text-muted">Rewards Given</h6>
                <h3 class="text-info"><?= number_format($stats['total_rewards'] ?? 0) ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Action Cards -->
<div class="row g-4">
    <div class="col-md-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-check-circle fa-2x text-primary mb-3"></i>
                <h5 class="card-title">Verify Claims</h5>
                <p class="card-text">Review and approve/reject ownership claims.</p>
                <a href="verify-claims.php" class="btn btn-primary"><i class="fas fa-arrow-right me-1"></i>Go to Claims</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-chart-bar fa-2x text-success mb-3"></i>
                <h5 class="card-title">Reports</h5>
                <p class="card-text">View analytics, statistics, and export data.</p>
                <a href="reports.php" class="btn btn-success"><i class="fas fa-arrow-right me-1"></i>View Reports</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-boxes fa-2x text-info mb-3"></i>
                <h5 class="card-title">Manage Items</h5>
                <p class="card-text">View and delete lost and found reports.</p>
                <a href="view-items.php" class="btn btn-info text-white"><i class="fas fa-arrow-right me-1"></i>Go to Items</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-users-cog fa-2x text-warning mb-3"></i>
                <h5 class="card-title">Manage Users</h5>
                <p class="card-text">List all registered accounts.</p>
                <a href="manage-users.php" class="btn btn-warning text-dark"><i class="fas fa-arrow-right me-1"></i>Go to Users</a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Tips -->
<div class="alert alert-info mt-5">
    <i class="fas fa-lightbulb me-2"></i><strong>Tip:</strong> Start by checking pending claims in the "Verify Claims" section to help reunite items with their owners.
</div>

<?php require_once '../includes/footer.php'; ?>