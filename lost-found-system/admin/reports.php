<?php
require_once '../includes/config.php';

require_admin();

$message = '';
$from_date = $_POST['from_date'] ?? date('Y-m-d', strtotime('-30 days'));
$to_date = $_POST['to_date'] ?? date('Y-m-d');

// Function to export CSV
function exportCSV($data, $filename) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit;
}

// Handle CSV export
if (isset($_POST['export'])) {
    $export_type = $_POST['export'];
    $data = [];

    if ($export_type === 'items') {
        $stmt = $conn->prepare("
            SELECT category, COUNT(*) as count
            FROM (
                SELECT category FROM lost_items WHERE date_lost BETWEEN ? AND ?
                UNION ALL
                SELECT category FROM found_items WHERE date_found BETWEEN ? AND ?
            ) AS combined
            GROUP BY category
        ");
        $stmt->execute([$from_date, $to_date, $from_date, $to_date]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($export_type === 'claims') {
        $stmt = $conn->prepare("
            SELECT status, COUNT(*) as count
            FROM claims
            WHERE claim_date BETWEEN ? AND ?
            GROUP BY status
        ");
        $stmt->execute([$from_date, $to_date]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($export_type === 'finders') {
        $stmt = $conn->prepare("
            SELECT u.name, COUNT(f.found_id) as found_count
            FROM users u
            JOIN found_items f ON u.user_id = f.user_id
            WHERE f.date_found BETWEEN ? AND ?
            GROUP BY u.user_id
            ORDER BY found_count DESC
            LIMIT 10
        ");
        $stmt->execute([$from_date, $to_date]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($export_type === 'activity') {
        $stmt = $conn->prepare("
            SELECT c.claim_id, c.claim_date, c.status, u.name as claimant, f.item_name, admin.name as approver
            FROM claims c
            LEFT JOIN users u ON c.user_id = u.user_id
            LEFT JOIN found_items f ON c.found_id = f.found_id
            LEFT JOIN users admin ON c.admin_id = admin.user_id
            WHERE c.claim_date BETWEEN ? AND ?
            ORDER BY c.claim_date DESC
            LIMIT 10
        ");
        $stmt->execute([$from_date, $to_date]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($export_type === 'rewards') {
        $stmt = $conn->prepare("
            SELECT u.name, SUM(f.reward_points) as total_points
            FROM users u
            JOIN found_items f ON u.user_id = f.user_id
            WHERE f.reward_status = 'rewarded' AND f.date_found BETWEEN ? AND ?
            GROUP BY u.user_id
            ORDER BY total_points DESC
        ");
        $stmt->execute([$from_date, $to_date]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    exportCSV($data, $export_type . '_report.csv');
}

// Fetch data for reports
$items_data = [];
$claims_data = [];
$finders_data = [];
$activity_data = [];
$rewards_data = [];
$total_rewards = 0;

try {
    // Items by category
    $stmt = $conn->prepare("
        SELECT category, COUNT(*) as count
        FROM (
            SELECT category FROM lost_items WHERE date_lost BETWEEN ? AND ?
            UNION ALL
            SELECT category FROM found_items WHERE date_found BETWEEN ? AND ?
        ) AS combined
        GROUP BY category
    ");
    $stmt->execute([$from_date, $to_date, $from_date, $to_date]);
    $items_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Claims statistics
    $stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM claims WHERE claim_date BETWEEN ? AND ? GROUP BY status");
    $stmt->execute([$from_date, $to_date]);
    $claims_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $claims_data = array_column($claims_raw, 'count', 'status');
    $total_claims = array_sum($claims_data);
    $approved_rate = $total_claims > 0 ? round(($claims_data['approved'] ?? 0) / $total_claims * 100, 1) : 0;

    // Top finders
    $stmt = $conn->prepare("
        SELECT u.name, COUNT(f.found_id) as found_count
        FROM users u
        JOIN found_items f ON u.user_id = f.user_id
        WHERE f.date_found BETWEEN ? AND ?
        GROUP BY u.user_id
        ORDER BY found_count DESC
        LIMIT 10
    ");
    $stmt->execute([$from_date, $to_date]);
    $finders_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent activity
    $stmt = $conn->prepare("
        SELECT c.claim_id, c.claim_date, c.status, u.name as claimant, f.item_name, admin.name as approver
        FROM claims c
        LEFT JOIN users u ON c.user_id = u.user_id
        LEFT JOIN found_items f ON c.found_id = f.found_id
        LEFT JOIN users admin ON c.admin_id = admin.user_id
        WHERE c.claim_date BETWEEN ? AND ?
        ORDER BY c.claim_date DESC
        LIMIT 10
    ");
    $stmt->execute([$from_date, $to_date]);
    $activity_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Rewards
    $stmt = $conn->prepare("
        SELECT u.name, SUM(f.reward_points) as total_points
        FROM users u
        JOIN found_items f ON u.user_id = f.user_id
        WHERE f.reward_status = 'rewarded' AND f.date_found BETWEEN ? AND ?
        GROUP BY u.user_id
        ORDER BY total_points DESC
    ");
    $stmt->execute([$from_date, $to_date]);
    $rewards_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_rewards = array_sum(array_column($rewards_data, 'total_points'));

} catch (PDOException $e) {
    $message = "Error loading reports: " . $e->getMessage();
}

$page_title = 'Reports - Admin';
require_once '../includes/header.php';
?>

<script src="../assets/js/chart.umd.min.js"></script>
<style>
    .chart-container { position: relative; height: 300px; }
</style>

<h2 class="mb-4"><i class="fas fa-chart-bar"></i> Reports</h2>

        <?php if ($message): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Date Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="<?= $from_date ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="<?= $to_date ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter"></i> Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Items Report -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-boxes"></i> Items by Category</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <canvas id="itemsChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items_data as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['category'] ?? 'Uncategorized') ?></td>
                                        <td><?= $item['count'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <form method="POST" class="mt-3">
                            <input type="hidden" name="export" value="items">
                            <button type="submit" class="btn btn-success"><i class="fas fa-download"></i> Export CSV</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Claims Report -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-handshake"></i> Claims Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h3 class="text-primary"><?= $total_claims ?></h3>
                        <p>Total Claims</p>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-warning"><?= $claims_data['pending'] ?? 0 ?></h3>
                        <p>Pending</p>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-success"><?= $claims_data['approved'] ?? 0 ?></h3>
                        <p>Approved</p>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-danger"><?= $claims_data['rejected'] ?? 0 ?></h3>
                        <p>Rejected</p>
                    </div>
                </div>
                <div class="mt-3">
                    <h5>Success Rate: <span class="badge bg-info"><?= $approved_rate ?>%</span></h5>
                </div>
                <form method="POST" class="mt-3">
                    <input type="hidden" name="export" value="claims">
                    <button type="submit" class="btn btn-success"><i class="fas fa-download"></i> Export CSV</button>
                </form>
            </div>
        </div>

        <!-- Top Finders -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-trophy"></i> Top Finders</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Finder</th>
                            <th>Items Found</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rank = 1; foreach ($finders_data as $finder): ?>
                            <tr>
                                <td><?= $rank++ ?></td>
                                <td><?= htmlspecialchars($finder['name']) ?></td>
                                <td><?= $finder['found_count'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <form method="POST" class="mt-3">
                    <input type="hidden" name="export" value="finders">
                    <button type="submit" class="btn btn-success"><i class="fas fa-download"></i> Export CSV</button>
                </form>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-history"></i> Recent Activity</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Claim ID</th>
                            <th>Date</th>
                            <th>Item</th>
                            <th>Claimant</th>
                            <th>Status</th>
                            <th>Approved By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activity_data as $activity): ?>
                            <tr>
                                <td><?= $activity['claim_id'] ?></td>
                                <td><?= htmlspecialchars($activity['claim_date']) ?></td>
                                <td><?= htmlspecialchars($activity['item_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($activity['claimant'] ?? 'N/A') ?></td>
                                <td><span class="badge bg-<?= $activity['status'] === 'approved' ? 'success' : ($activity['status'] === 'rejected' ? 'danger' : 'warning') ?>"><?= ucfirst($activity['status']) ?></span></td>
                                <td><?= htmlspecialchars($activity['approver'] ?? 'N/A') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <form method="POST" class="mt-3">
                    <input type="hidden" name="export" value="activity">
                    <button type="submit" class="btn btn-success"><i class="fas fa-download"></i> Export CSV</button>
                </form>
            </div>
        </div>

        <!-- Rewards -->
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-gift"></i> Rewards Issued</h5>
            </div>
            <div class="card-body">
                <h4 class="text-center mb-3">Total Points Awarded: <span class="badge bg-primary fs-5"><?= $total_rewards ?></span></h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Finder</th>
                            <th>Total Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rewards_data as $reward): ?>
                            <tr>
                                <td><?= htmlspecialchars($reward['name']) ?></td>
                                <td><?= $reward['total_points'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <form method="POST" class="mt-3">
                    <input type="hidden" name="export" value="rewards">
                    <button type="submit" class="btn btn-success"><i class="fas fa-download"></i> Export CSV</button>
                </form>
            </div>
        </div>

    <script>
        // Items Pie Chart
        const itemsCtx = document.getElementById('itemsChart').getContext('2d');
        const itemsData = <?= json_encode($items_data) ?>;
        const itemsLabels = itemsData.map(item => item.category || 'Uncategorized');
        const itemsCounts = itemsData.map(item => item.count);

        new Chart(itemsCtx, {
            type: 'pie',
            data: {
                labels: itemsLabels,
                datasets: [{
                    data: itemsCounts,
                    backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>

<?php require_once '../includes/footer.php'; ?>