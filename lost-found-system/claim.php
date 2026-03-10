<?php
require_once 'includes/config.php';

echo "<pre>DEBUG MODE ACTIVE\n";
print_r($_SESSION);
echo "</pre>";

if (!isset($_SESSION['user_id'])) {
    echo "Not logged in → redirecting";
    header("Location: login.php");
    exit;
}

$found_id = (int)($_GET['id'] ?? 0);
echo "Requested found_id: $found_id\n";

if ($found_id <= 0) {
    die("Invalid item ID.");
}

$stmt = $conn->prepare("
    SELECT found_id, item_name, description, category, date_found, location, image_path, user_id AS finder_id
    FROM found_items 
    WHERE found_id = ?
");
$stmt->execute([$found_id]);
$item = $stmt->fetch();

if (!$item) {
    die("Found item not found.");
}

echo "Found item loaded: " . $item['item_name'] . "\n";
echo "Finder ID: " . $item['finder_id'] . " | Current user: " . $_SESSION['user_id'] . "\n";

if ($item['finder_id'] == $_SESSION['user_id']) {
    die("You cannot claim your own found item.");
}

// Rest of your original claim.php code here...
// (paste your existing form + submit logic after this debug part)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Claim Debug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2>Claim Debug Page</h2>
    <p>If you see this + item details above → form should appear below.</p>
    
    <!-- Your original form goes here -->
    <div class="card">
        <div class="card-header">Claim Item: <?= htmlspecialchars($item['item_name']) ?></div>
        <div class="card-body">
            <form method="POST">
                <textarea name="proof_description" class="form-control mb-3" rows="5" required></textarea>
                <button type="submit" class="btn btn-primary">Submit Claim</button>
            </form>
        </div>
    </div>
</body>
</html>