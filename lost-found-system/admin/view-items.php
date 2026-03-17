<?php
require_once '../includes/config.php';

require_admin();

$message = '';
$success = false;

// Handle delete item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    $item_id = (int)($_POST['item_id'] ?? 0);
    $item_type = $_POST['item_type'] ?? '';

    if ($item_id > 0 && in_array($item_type, ['lost', 'found'])) {
        $table = $item_type === 'lost' ? 'lost_items' : 'found_items';
        $id_column = $item_type === 'lost' ? 'lost_id' : 'found_id';

        $stmt = $conn->prepare("DELETE FROM $table WHERE $id_column = ?");
        if ($stmt->execute([$item_id])) {
            $success = true;
            $message = ucfirst($item_type) . " item deleted successfully.";
        } else {
            $message = "Error deleting item.";
        }
    }
}

try {
    $stmt = $conn->query("SELECT 'lost' AS type, lost_id AS id, item_name, description, category, date_lost AS item_date, location, image_path, u.name AS reporter_name FROM lost_items l JOIN users u ON l.user_id = u.user_id");
    $lost = $stmt->fetchAll();
    $stmt = $conn->query("SELECT 'found' AS type, found_id AS id, item_name, description, category, date_found AS item_date, location, image_path, u.name AS reporter_name FROM found_items f JOIN users u ON f.user_id = u.user_id");
    $found = $stmt->fetchAll();
    $items = array_merge($lost, $found);
    // Sort by date descending
    usort($items, function($a, $b) {
        return strtotime($b['item_date']) <=> strtotime($a['item_date']);
    });
} catch (PDOException $e) {
    $items = [];
    $error = $e->getMessage();
}

$page_title = 'Manage Items - Admin';
require_once '../includes/header.php';
?>
<h2 class="mb-4"><i class="fas fa-boxes me-2"></i>Manage Items</h2>

<?php if ($message): ?>
    <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">Error loading items: <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (empty($items)): ?>
    <div class="alert alert-secondary">No items to display.</div>
<?php else: ?>
    <div class="data-grid">
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Name</th>
                    <th>Category</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Reported By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td><?= htmlspecialchars(ucfirst($it['type'])) ?></td>
                                <td><?= htmlspecialchars($it['item_name']) ?></td>
                                <td><?= htmlspecialchars($it['category'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($it['item_date']) ?></td>
                                <td><?= htmlspecialchars($it['location'] ?? '') ?></td>
                                <td><?= htmlspecialchars($it['reporter_name'] ?? 'Unknown') ?></td>
                                <td>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-item-id="<?= $it['id'] ?>" data-item-type="<?= $it['type'] ?>" data-item-name="<?= htmlspecialchars($it['item_name']) ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete the <span id="itemType"></span> item "<span id="itemName"></span>"? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="item_id" id="deleteItemId">
                        <input type="hidden" name="item_type" id="deleteItemType">
                        <input type="hidden" name="delete_item" value="">
                        <button type="submit" class="btn btn-danger" onclick="this.form.elements['delete_item'].value='1';">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Handle delete modal
        const deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const itemId = button.getAttribute('data-item-id');
            const itemType = button.getAttribute('data-item-type');
            const itemName = button.getAttribute('data-item-name');

            document.getElementById('deleteItemId').value = itemId;
            document.getElementById('deleteItemType').value = itemType;
            document.getElementById('itemType').textContent = itemType;
            document.getElementById('itemName').textContent = itemName;
        });
    </script>

<?php require_once '../includes/footer.php'; ?>