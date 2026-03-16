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

<style>
    /* Dashboard Hero Section */
    .dashboard-hero {
        background: linear-gradient(135deg, #081f4d 0%, #0d6efd 100%);
        color: #fff;
        padding: 3rem 2rem;
        border-radius: 1.5rem;
        box-shadow: 0 24px 48px rgba(5, 28, 74, 0.35);
        position: relative;
        overflow: hidden;
        margin-bottom: 3rem;
    }

    .dashboard-hero::before {
        content: '';
        position: absolute;
        width: 400px;
        height: 400px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
        left: -150px;
        bottom: -100px;
        filter: blur(40px);
        z-index: 0;
    }

    .dashboard-hero::after {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background: rgba(88,166,255,0.06);
        right: -80px;
        top: -60px;
        filter: blur(50px);
        z-index: 0;
    }

    .dashboard-hero > * {
        position: relative;
        z-index: 1;
    }

    /* Action Cards */
    .action-card {
        border: none;
        border-radius: 1.1rem;
        box-shadow: 0 8px 20px rgba(13, 110, 253, 0.06);
        transition: transform 0.3s cubic-bezier(0.23, 1, 0.320, 1), 
                    box-shadow 0.3s cubic-bezier(0.23, 1, 0.320, 1),
                    border-color 0.3s ease;
        border: 1px solid transparent;
    }

    .action-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px rgba(13, 110, 253, 0.15);
        border-color: rgba(13, 110, 253, 0.1);
    }

    .action-icon-box {
        width: 62px;
        height: 62px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        background: linear-gradient(135deg, rgba(13,110,253,0.12), rgba(88,166,255,0.08));
        color: #0d6efd;
        margin: 0 auto 0.8rem;
        transition: transform 0.3s ease, background 0.3s ease;
    }

    .action-card:hover .action-icon-box {
        transform: scale(1.08) rotate(5deg);
        background: linear-gradient(135deg, rgba(13,110,253,0.18), rgba(88,166,255,0.12));
    }

    /* Data section cards */
    .data-section {
        border: none;
        border-radius: 1.1rem;
        box-shadow: 0 8px 20px rgba(13, 110, 253, 0.06);
        overflow: hidden;
        border: 1px solid rgba(13,110,253,0.08);
    }

    .data-section .card-header {
        background: linear-gradient(135deg, #0a2b63 0%, #0d6efd 100%);
        color: #fff;
        padding: 1.2rem;
        border: none;
    }

    .data-section .card-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.05rem;
    }

    /* Item List Cards */
    .item-list-group {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .item-list-group li {
        padding: 1rem;
        border-bottom: 1px solid rgba(13,110,253,0.08);
        transition: background-color 0.2s ease;
    }

    .item-list-group li:last-child {
        border-bottom: none;
    }

    .item-list-group li:hover {
        background-color: rgba(13,110,253,0.03);
    }

    .item-title {
        font-weight: 600;
        color: #081f4d;
        margin-bottom: 0.5rem;
        display: block;
    }

    .item-meta {
        font-size: 0.85rem;
        color: #47648f;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .item-badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.3px;
    }

    .item-badge-primary {
        background: rgba(13,110,253,0.1);
        color: #0d6efd;
    }

    .item-badge-success {
        background: rgba(25,135,84,0.1);
        color: #198754;
    }

    .item-badge-pending {
        background: rgba(255,193,7,0.1);
        color: #ff9800;
    }

    .item-badge-approved {
        background: rgba(25,135,84,0.1);
        color: #198754;
    }

    .item-badge-rejected {
        background: rgba(220,53,69,0.1);
        color: #dc3545;
    }

    .empty-state-message {
        text-align: center;
        padding: 2rem;
        color: #47648f;
    }

    .empty-state-message .btn {
        margin-top: 1rem;
    }

    @media (max-width: 768px) {
        .dashboard-hero {
            padding: 2rem 1.5rem;
        }
    }
</style>

<!-- Welcome Alert -->
<?php if ($show_welcome): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 1rem; border: none; box-shadow: 0 4px 12px rgba(25,135,84,0.15);">
        <i class="fas fa-check-circle me-2"></i><strong>Welcome back!</strong> You're now logged in, <?php echo htmlspecialchars($name); ?>.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Dashboard Hero -->
<div class="dashboard-hero">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h1 class="display-5 fw-bold mb-2">Welcome back, <?php echo htmlspecialchars($name); ?>!</h1>
            <p class="lead mb-0 opacity-85">Here's an overview of your reports, claims, and activity across the Lost & Found system.</p>
        </div>
        <div class="col-lg-4 text-lg-end d-none d-lg-block">
            <i class="fas fa-chart-line" style="font-size:4rem; opacity:0.2;"></i>
        </div>
    </div>
</div>

<!-- Action Cards -->
<div class="row g-3 mb-5">
    <div class="col-6 col-lg-3">
        <div class="card action-card text-center">
            <div class="card-body py-4">
                <div class="action-icon-box">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h6 class="fw-semibold mb-2">Report Lost</h6>
                <p class="text-muted small mb-3">Lost something? Report it here.</p>
                <a href="report-lost.php" class="btn btn-primary btn-sm" style="box-shadow: 0 4px 10px rgba(13,110,253,0.2);"><i class="fas fa-plus me-1"></i>Report</a>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card action-card text-center">
            <div class="card-body py-4">
                <div class="action-icon-box">
                    <i class="fas fa-hand-holding-heart"></i>
                </div>
                <h6 class="fw-semibold mb-2">Report Found</h6>
                <p class="text-muted small mb-3">Found an item? Help reunite it.</p>
                <a href="report-found.php" class="btn btn-primary btn-sm" style="box-shadow: 0 4px 10px rgba(13,110,253,0.2);"><i class="fas fa-plus me-1"></i>Report</a>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card action-card text-center">
            <div class="card-body py-4">
                <div class="action-icon-box">
                    <i class="fas fa-search"></i>
                </div>
                <h6 class="fw-semibold mb-2">Search Items</h6>
                <p class="text-muted small mb-3">Browse all lost &amp; found items.</p>
                <a href="search.php" class="btn btn-primary btn-sm" style="box-shadow: 0 4px 10px rgba(13,110,253,0.2);"><i class="fas fa-search me-1"></i>Search</a>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card action-card text-center">
            <div class="card-body py-4">
                <div class="action-icon-box">
                    <i class="fas fa-clock"></i>
                </div>
                <h6 class="fw-semibold mb-2">My Claims</h6>
                <p class="text-muted small mb-3">Check your claim status.</p>
                <a href="my-claims.php" class="btn btn-primary btn-sm" style="box-shadow: 0 4px 10px rgba(13,110,253,0.2);"><i class="fas fa-history me-1"></i>View</a>
            </div>
        </div>
    </div>
</div>

<!-- Data Sections -->
<div class="row g-4 mb-4">
    <!-- Lost Items -->
    <div class="col-lg-4">
        <div class="card data-section h-100">
            <div class="card-header">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>My Lost Reports</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($lost_items)): ?>
                    <div class="empty-state-message">
                        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;"><i class="fas fa-inbox"></i></div>
                        <p>No lost items reported yet.</p>
                        <a href="report-lost.php" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Report First Item</a>
                    </div>
                <?php else: ?>
                    <ul class="item-list-group">
                        <?php foreach ($lost_items as $item): ?>
                            <li>
                                <span class="item-title"><?php echo htmlspecialchars($item['item_name']); ?></span>
                                <div class="item-meta">
                                    <span><i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($item['date_lost'])); ?></span>
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($item['location']); ?></span>
                                </div>
                                <span class="item-badge item-badge-primary mt-2">Lost</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Found Items -->
    <div class="col-lg-4">
        <div class="card data-section h-100">
            <div class="card-header">
                <h5><i class="fas fa-heart me-2"></i>My Found Reports</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($found_items)): ?>
                    <div class="empty-state-message">
                        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;"><i class="fas fa-inbox"></i></div>
                        <p>No found items reported yet.</p>
                        <a href="report-found.php" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Report First Item</a>
                    </div>
                <?php else: ?>
                    <ul class="item-list-group">
                        <?php foreach ($found_items as $item): ?>
                            <li>
                                <span class="item-title"><?php echo htmlspecialchars($item['item_name']); ?></span>
                                <div class="item-meta">
                                    <span><i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($item['date_found'])); ?></span>
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($item['location']); ?></span>
                                </div>
                                <?php if ($item['reward_status'] === 'rewarded'): ?>
                                    <div class="mt-2">
                                        <span class="item-badge item-badge-success"><i class="fas fa-gift me-1"></i>Rewarded: <?php echo $item['reward_points']; ?> pts</span>
                                    </div>
                                <?php else: ?>
                                    <span class="item-badge item-badge-success mt-2">Found</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Claims -->
    <div class="col-lg-4">
        <div class="card data-section h-100">
            <div class="card-header">
                <h5><i class="fas fa-file-check me-2"></i>My Claims</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($claims)): ?>
                    <div class="empty-state-message">
                        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;"><i class="fas fa-inbox"></i></div>
                        <p>No claims made yet.</p>
                        <a href="search.php" class="btn btn-primary btn-sm"><i class="fas fa-search me-1"></i>Browse Items</a>
                    </div>
                <?php else: ?>
                    <ul class="item-list-group">
                        <?php foreach ($claims as $claim): ?>
                            <li>
                                <span class="item-title"><?php echo htmlspecialchars($claim['item_name']); ?></span>
                                <div class="item-meta">
                                    <span><i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($claim['claim_date'])); ?></span>
                                </div>
                                <span class="item-badge item-badge-<?php echo ($claim['status'] === 'approved' ? 'approved' : ($claim['status'] === 'rejected' ? 'rejected' : 'pending')); ?> mt-2">
                                    <?php echo ucfirst($claim['status']); ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

