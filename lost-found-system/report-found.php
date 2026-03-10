<?php
require_once 'includes/config.php';

// Protect page - must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = '';
$success = false;

// Upload settings
$upload_dir = 'uploads/items/';
$max_size   = 2000000; // 2MB
$allowed    = ['jpg', 'jpeg', 'png', 'gif'];

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name     = trim($_POST['item_name'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $category      = trim($_POST['category'] ?? '');
    $date_found    = $_POST['date_found'] ?? '';
    $location      = trim($_POST['location'] ?? '');

    // Basic validation
    if (empty($item_name) || empty($description) || empty($category) || empty($date_found)) {
        $message = "Please fill in all required fields.";
    } elseif (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date_found)) {
        $message = "Invalid date format. Use YYYY-MM-DD.";
    } else {
        $image_path = null;

        // Handle image upload if provided
        if (!empty($_FILES['item_image']['name'])) {
            $file_name   = time() . '_' . basename($_FILES['item_image']['name']);
            $target_path = $upload_dir . $file_name;
            $file_type   = strtolower(pathinfo($target_path, PATHINFO_EXTENSION));

            if (!in_array($file_type, $allowed)) {
                $message = "Only JPG, JPEG, PNG & GIF files are allowed.";
            } elseif ($_FILES['item_image']['size'] > $max_size) {
                $message = "File is too large (max 2MB).";
            } elseif ($_FILES['item_image']['error'] !== UPLOAD_ERR_OK) {
                $message = "Upload error: " . $_FILES['item_image']['error'];
            } elseif (!move_uploaded_file($_FILES['item_image']['tmp_name'], $target_path)) {
                $message = "Failed to save the image. Check folder permissions.";
            } else {
                $image_path = $target_path;
            }
        }

        // Only proceed if no upload error occurred
        if (empty($message)) {
            try {
                $stmt = $conn->prepare("
                    INSERT INTO found_items 
                    (item_name, description, image_path, category, date_found, location, user_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $item_name,
                    $description,
                    $image_path,
                    $category,
                    $date_found,
                    $location ?: null,
                    $_SESSION['user_id']
                ]);

                $success = true;
                $message = "Found item reported successfully! Thank you for helping.";
            } catch (PDOException $e) {
                $message = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Found Item - Lost & Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Lost & Found - KyU</a>
            <div class="ms-auto">
                <span class="text-white me-3">Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></span>
                <a href="logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-success text-white text-center">
                        <h4>Report a Found Item</h4>
                    </div>
                    <div class="card-body">

                        <?php if ($message): ?>
                            <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>">
                                <?= htmlspecialchars($message) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                <input type="text" name="item_name" class="form-control" required 
                                       placeholder="e.g., Black Leather Wallet, iPhone 13">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="4" required 
                                          placeholder="Color, brand, condition, any unique features or contents..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Upload Photo of Item (optional)</label>
                                <input type="file" name="item_image" class="form-control" accept="image/*">
                                <small class="form-text text-muted">JPG, JPEG, PNG, GIF – max 2MB</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-select" required>
                                    <option value="">-- Select Category --</option>
                                    <option value="Phone">Phone / Mobile Device</option>
                                    <option value="Wallet">Wallet / Purse</option>
                                    <option value="Document">ID / Passport / Certificate</option>
                                    <option value="Bag">Bag / Backpack</option>
                                    <option value="Clothing">Clothing / Shoes</option>
                                    <option value="Electronics">Laptop / Charger / Earphones</option>
                                    <option value="Keys">Keys</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date Found <span class="text-danger">*</span></label>
                                <input type="date" name="date_found" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Location Found</label>
                                <input type="text" name="location" class="form-control" 
                                       placeholder="e.g., Main Gate, Library Reading Room, Kikoni Stage">
                            </div>

                            <button type="submit" class="btn btn-success w-100">Submit Found Item Report</button>
                        </form>

                        <div class="text-center mt-4">
                            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>