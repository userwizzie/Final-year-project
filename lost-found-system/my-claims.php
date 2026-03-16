<?php
require_once 'includes/config.php';

// Protect page - must be logged in
require_user();

$user_id = $_SESSION['user_id'];
$name    = $_SESSION['name'] ?? 'User';

// Fetch claims submitted by the user (as claimant), including proof_description
$stmt = $conn->prepare("
    SELECT
        c.claim_id,
        c.claim_date,
        c.status,
        c.proof_description,
        f.item_name AS found_item_name,
        f.description AS found_description,
        f.image_path AS found_image,
        f.date_found,
        f.location AS found_location,
        f.category AS found_category
    FROM claims c
    JOIN found_items f ON c.found_id = f.found_id
    WHERE c.user_id = ?
    ORDER BY c.claim_date DESC
");
$stmt->execute([$user_id]);
$claims = $stmt->fetchAll();

$page_title = 'My Claims';
require_once 'includes/header.php';
?>

<style>
    .claims-hero {
        border-radius: 1rem;
        background: linear-gradient(135deg, #0a2b63 0%, #0d6efd 100%);
        color: #fff;
        padding: 1.25rem 1.2rem;
        box-shadow: 0 16px 36px rgba(5, 28, 74, 0.24);
        margin-bottom: 1.2rem;
    }

    .claim-status-badge {
        font-size: 0.74rem;
        letter-spacing: 0.04em;
        padding: 0.4rem 0.65rem;
        border-radius: 999px;
    }

    .claim-proof {
        background: #f8fbff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 0.75rem;
        padding: 0.85rem;
    }

    .claim-card {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 12px 28px rgba(9, 13, 38, 0.12);
        overflow: hidden;
    }

    .claim-card .card-img-top {
        height: 220px;
        object-fit: cover;
    }
</style>

<div class="claims-hero d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div>
        <h2 class="h4 fw-bold mb-1"><i class="fas fa-clipboard-check me-2"></i>My Claims</h2>
        <p class="mb-0 opacity-75">Track submitted claims and their verification status.</p>
    </div>
    <a href="search.php" class="btn btn-light btn-sm"><i class="fas fa-search me-1"></i>Search More Items</a>
</div>

<?php if (empty($claims)): ?>
    <div class="empty-state mx-auto" style="max-width: 560px;">
        <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="32" cy="32" r="30" opacity="0.18" />
            <path d="M22 26h20" />
            <path d="M22 34h20" />
            <path d="M22 42h12" />
            <path d="M18 18l10 10" />
            <path d="M28 18l-10 10" />
        </svg>
        <h4 class="mb-3">No Claims Yet</h4>
        <p class="text-muted mb-3">You have not submitted any claims yet. Search found items to submit your first claim.</p>
        <a href="search.php" class="btn btn-primary"><i class="fas fa-search me-1"></i>Search Found Items</a>
    </div>
<?php else: ?>
    <?php foreach ($claims as $claim):
        $status = strtolower($claim['status'] ?? 'pending');
        $step2Class = $status === 'pending' ? 'active' : 'done';
        $step3Class = $status === 'approved' ? 'done' : ($status === 'rejected' ? 'rejected' : '');
    ?>
        <div class="card claim-card mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
                    <h3 class="h5 mb-0 fw-bold"><?php echo htmlspecialchars($claim['found_item_name']); ?></h3>
                    <span class="claim-status-badge <?php echo $status === 'pending' ? 'bg-warning text-dark' : ($status === 'approved' ? 'bg-success text-white' : 'bg-danger text-white'); ?>">
                        <?php echo strtoupper($status); ?>
                    </span>
                </div>

                <div class="stepper mb-3">
                    <div class="step done">
                        <div class="bullet">1</div>
                        <div>
                            <div class="fw-semibold">Submitted</div>
                            <div class="small text-muted">Claim sent</div>
                        </div>
                    </div>
                    <div class="step <?php echo $step2Class; ?>">
                        <div class="bullet">2</div>
                        <div>
                            <div class="fw-semibold">Under Review</div>
                            <div class="small text-muted">Admin verification</div>
                        </div>
                    </div>
                    <div class="step <?php echo $step3Class; ?>">
                        <div class="bullet">3</div>
                        <div>
                            <div class="fw-semibold"><?php echo $status === 'approved' ? 'Approved' : ($status === 'rejected' ? 'Rejected' : 'Final Decision'); ?></div>
                            <div class="small text-muted"><?php echo $status === 'approved' ? 'Proceed to collection' : ($status === 'rejected' ? 'Review feedback' : 'Awaiting outcome'); ?></div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-4">
                        <?php if (!empty($claim['found_image'])): ?>
                            <img src="<?php echo htmlspecialchars($claim['found_image']); ?>" class="card-img-top rounded" alt="Found item image">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center border rounded p-4 text-muted h-100">
                                <div class="text-center">
                                    <i class="fas fa-image fa-2x mb-2"></i>
                                    <div>No image available</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-lg-8">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6"><strong>Category:</strong> <?php echo htmlspecialchars($claim['found_category'] ?? 'N/A'); ?></div>
                            <div class="col-md-6"><strong>Found Date:</strong> <?php echo htmlspecialchars($claim['date_found'] ?? 'N/A'); ?></div>
                            <div class="col-md-6"><strong>Location:</strong> <?php echo htmlspecialchars($claim['found_location'] ?? 'Not specified'); ?></div>
                            <div class="col-md-6"><strong>Claim Date:</strong> <?php echo htmlspecialchars($claim['claim_date']); ?></div>
                        </div>

                        <h6 class="mb-2">Your Proof / Explanation</h6>
                        <div class="claim-proof mb-3">
                            <?php echo nl2br(htmlspecialchars($claim['proof_description'] ?? 'No proof provided')); ?>
                        </div>

                        <?php if ($status === 'approved'): ?>
                            <div class="alert alert-success mb-0"><i class="fas fa-circle-check me-1"></i>Approved by administrator. Please proceed with collection guidance.</div>
                        <?php elseif ($status === 'rejected'): ?>
                            <div class="alert alert-danger mb-0"><i class="fas fa-circle-xmark me-1"></i>This claim was not approved. You may submit a new claim with stronger details.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="text-center mt-4">
    <a href="dashboard.php" class="btn btn-outline-secondary btn-lg px-4"><i class="fas fa-arrow-left me-1"></i>Back to Dashboard</a>
</div>

<?php require_once 'includes/footer.php'; ?>
