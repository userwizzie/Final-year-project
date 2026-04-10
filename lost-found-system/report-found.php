<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Protect page - must be logged in
require_user();

$message = '';
$success = false;

$item_name_value    = '';
$description_value  = '';
$category_value     = '';
$date_found_value   = '';
$location_value     = '';

$category_labels = [
    'Phone'       => 'Phone / Mobile Device',
    'Wallet'      => 'Wallet / Purse',
    'Document'    => 'Document / ID / Certificate',
    'Bag'         => 'Bag / Backpack',
    'Clothing'    => 'Clothing / Shoes',
    'Electronics' => 'Laptop / Charger / Earphones',
    'Keys'        => 'Keys',
    'Other'       => 'Other'
];

// Upload settings
$upload_dir = 'uploads/items/';
$max_size   = 2000000; // 2MB
$allowed    = ['jpg', 'jpeg', 'png', 'gif'];

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name   = trim($_POST['item_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $date_found  = $_POST['date_found'] ?? '';
    $location    = trim($_POST['location'] ?? '');

    $item_name_value   = htmlspecialchars($item_name, ENT_QUOTES, 'UTF-8');
    $description_value = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
    $category_value    = htmlspecialchars($category, ENT_QUOTES, 'UTF-8');
    $date_found_value  = htmlspecialchars($date_found, ENT_QUOTES, 'UTF-8');
    $location_value    = htmlspecialchars($location, ENT_QUOTES, 'UTF-8');

    // Basic validation
    if (empty($item_name) || empty($description) || empty($category) || empty($date_found)) {
        $message = "Please fill in all required fields.";
    } elseif (strlen($description) > 500) {
        $message = "Description cannot exceed 500 characters.";
    } elseif (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date_found)) {
        $message = "Invalid date format. Use YYYY-MM-DD.";
    } else {
        $image_path = null;

        // Handle image upload if provided
        if (!empty($_FILES['item_image']['name'])) {
            $file_name   = time() . '_' . basename($_FILES['item_image']['name']);
            $target_path = $upload_dir . $file_name;
            $file_type   = strtolower(pathinfo($target_path, PATHINFO_EXTENSION));

            if (!in_array($file_type, $allowed, true)) {
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
                $message = "Found item reported successfully. Thank you for helping.";

                // notify admin of new found report
                if (function_exists('notify_user')) {
                    notify_user(
                        'admin@lostfound.local',
                        'New found item reported',
                        "User #{$_SESSION['user_id']} reported a new item (#$item_name)."
                    );
                }

                $item_name_value = $description_value = $category_value = $date_found_value = $location_value = '';
            } catch (PDOException $e) {
                $message = "Database error: " . $e->getMessage();
            }
        }
    }
}

$category_button_label = $category_value && isset($category_labels[$category_value])
    ? $category_labels[$category_value]
    : 'Select category';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Found Item | Lost & Found KyU</title>
    <link rel="icon" type="image/svg+xml" sizes="any" href="assets/images/favicon.svg?v=20260317">
    <link rel="icon" type="image/png" sizes="96x96" href="assets/images/favicon-96x96.png?v=20260317">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-touch-icon.png?v=20260317">
    <link rel="manifest" href="assets/images/site.webmanifest?v=20260317">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/local-icons.css" rel="stylesheet">
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">

    <style>
        :root {
            --theme-color: #0d6efd;
            --theme-dark: #0a58ca;
            --theme-soft: rgba(13, 110, 253, 0.1);
            --panel-shadow: 0 24px 60px rgba(9, 13, 38, 0.22);
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background:
                radial-gradient(circle at 0% 0%, rgba(13,110,253,0.16), transparent 38%),
                radial-gradient(circle at 100% 100%, rgba(13,110,253,0.14), transparent 40%),
                #f4f7fb;
        }

        .report-navbar {
            background: linear-gradient(120deg, #05214a 0%, #0d6efd 100%);
        }

        .report-wrap {
            max-width: 920px;
            margin: 2rem auto 2.5rem;
        }

        .report-card {
            border: 0;
            border-radius: 1.1rem;
            box-shadow: var(--panel-shadow);
            overflow: hidden;
        }

        .report-header {
            background: linear-gradient(140deg, #0a2b63 0%, #0d6efd 100%);
            color: #fff;
            padding: 1.4rem 1.5rem;
        }

        .header-icon {
            width: 62px;
            height: 62px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.38);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 0.75rem;
        }

        .stepper .step.active {
            background: var(--theme-soft);
            border-color: rgba(13, 110, 253, 0.25);
        }

        .stepper .step.done {
            background: rgba(25, 135, 84, 0.16);
            border-color: rgba(25, 135, 84, 0.35);
        }

        .progress {
            height: 8px;
            background: rgba(13, 110, 253, 0.08);
            border-radius: 999px;
        }

        .progress-bar {
            background: linear-gradient(90deg, #0d6efd, #58a6ff);
            transition: width 0.25s ease;
        }

        .input-shell {
            position: relative;
        }

        .input-shell .field-icon {
            position: absolute;
            top: 50%;
            left: 0.85rem;
            transform: translateY(-50%);
            color: #8a95a5;
            z-index: 3;
            pointer-events: none;
            transition: color 0.2s;
        }

        .input-shell:focus-within .field-icon {
            color: var(--theme-color);
        }

        .input-shell input,
        .input-shell textarea {
            padding-left: 2.55rem;
            border-radius: 0.7rem !important;
            border: 1.4px solid #d7deea;
            background: #f8fbff;
        }

        .input-shell textarea {
            min-height: 160px;
            resize: vertical;
        }

        .input-shell input:focus,
        .input-shell textarea:focus,
        .category-dropdown .dropdown-toggle:focus {
            border-color: var(--theme-color);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.17);
            background: #fff;
        }

        .category-dropdown .dropdown-toggle {
            height: 48px;
            border-radius: 0.7rem;
            border: 1.4px solid #d7deea;
            background: #f8fbff;
            text-align: left;
            width: 100%;
        }

        .category-dropdown .dropdown-item i {
            width: 20px;
        }

        .hint-text {
            color: #667085;
            font-size: 0.8rem;
        }

        .char-counter {
            font-size: 0.78rem;
            color: #64748b;
        }

        .char-counter.limit-near {
            color: #b45309;
        }

        .char-counter.limit-hit {
            color: #0a58ca;
            font-weight: 600;
        }

        .preview-box {
            border: 1px dashed #c8d2e1;
            border-radius: 0.75rem;
            padding: 0.75rem;
            background: #f8fbff;
            display: inline-block;
        }

        .preview-box img {
            width: 170px;
            height: 130px;
            object-fit: cover;
            border-radius: 0.6rem;
            display: block;
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }

        .btn-theme {
            background: linear-gradient(130deg, #0d6efd 0%, #58a6ff 100%);
            border: none;
            color: #fff;
            border-radius: 0.7rem;
            font-weight: 600;
            box-shadow: 0 10px 24px rgba(13,110,253,0.3);
        }

        .btn-theme:hover {
            color: #fff;
            background: linear-gradient(130deg, #0a58ca 0%, #3d8eff 100%);
            transform: translateY(-1px);
        }

        .alert-pop {
            animation: popIn 0.45s ease;
        }

        @keyframes popIn {
            from { transform: translateY(-8px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @media (max-width: 576px) {
            .report-header {
                padding: 1.15rem 1rem;
            }

            .header-icon {
                width: 54px;
                height: 54px;
                font-size: 1.2rem;
            }

            .preview-box img {
                width: 130px;
                height: 110px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark report-navbar shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php"><i class="fas fa-search-location me-2"></i>Lost & Found KyU</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#foundNav" aria-controls="foundNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="foundNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="search.php">Search</a></li>
                    <li class="nav-item"><a class="nav-link" href="my-claims.php">My Claims</a></li>
                </ul>
                <div class="d-flex align-items-center text-white gap-2">
                    <span class="small"><i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span>
                    <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container report-wrap">
        <div class="card report-card">
            <div class="report-header">
                <div class="header-icon"><i class="fas fa-hand-holding-heart"></i></div>
                <h3 class="h5 mb-1 fw-bold">Report a Found Item</h3>
                <p class="mb-0 small opacity-75">Help reunite someone with their belongings by reporting clear and accurate details.</p>
            </div>

            <div class="card-body p-4 p-lg-5">
                <div class="stepper mb-3" id="foundStepper">
                    <div class="step active" id="found-step-1">
                        <div class="bullet">1</div>
                        <div><strong>Item details</strong><br><small class="text-muted">Name, type, date, place</small></div>
                    </div>
                    <div class="step" id="found-step-2">
                        <div class="bullet">2</div>
                        <div><strong>Photo</strong><br><small class="text-muted">Attach a clear picture</small></div>
                    </div>
                    <div class="step" id="found-step-3">
                        <div class="bullet">3</div>
                        <div><strong>Submit</strong><br><small class="text-muted">Review and send report</small></div>
                    </div>
                </div>

                <div class="progress mb-4" role="progressbar" aria-label="Form progress" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar" id="foundProgressBar" style="width: 30%"></div>
                </div>

                <?php if ($message): ?>
                    <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show alert-pop" role="alert">
                        <i class="fas <?php echo $success ? 'fa-circle-check' : 'fa-circle-exclamation'; ?> me-2"></i>
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" id="foundForm" novalidate>
                    <div class="form-section">
                        <h5 class="mb-3"><i class="fas fa-clipboard-check me-2 text-primary"></i>Item Details</h5>

                        <div class="mb-3">
                            <label for="item_name" class="form-label">Item Name <span class="text-danger">*</span>
                                <i class="fas fa-circle-info text-muted ms-1" data-bs-toggle="tooltip" title="Use a clear name for easier owner recognition."></i>
                            </label>
                            <div class="input-shell">
                                <i class="fas fa-box-open field-icon"></i>
                                <input type="text" id="item_name" name="item_name" class="form-control" required maxlength="120"
                                       placeholder="e.g., Black Leather Wallet, iPhone 13"
                                       value="<?php echo $item_name_value; ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span>
                                <i class="fas fa-circle-info text-muted ms-1" data-bs-toggle="tooltip" title="Include brand, condition, unique marks, and where/how it was found."></i>
                            </label>
                            <div class="input-shell">
                                <i class="fas fa-align-left field-icon" style="top: 1.1rem; transform:none;"></i>
                                <textarea id="description" name="description" class="form-control" required maxlength="500"
                                          placeholder="Describe color, condition, unique features, and other helpful details."><?php echo $description_value; ?></textarea>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <span class="hint-text">Maximum 500 characters.</span>
                                <span class="char-counter" id="descriptionCounter">0 / 500</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span>
                                <i class="fas fa-circle-info text-muted ms-1" data-bs-toggle="tooltip" title="Choose the category that best matches the found item."></i>
                            </label>
                            <input type="hidden" name="category" id="categoryInput" value="<?php echo $category_value; ?>">
                            <div class="dropdown category-dropdown">
                                <button class="btn dropdown-toggle" type="button" id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-tags me-2 text-muted"></i>
                                    <span id="categoryLabel"><?php echo htmlspecialchars($category_button_label); ?></span>
                                </button>
                                <ul class="dropdown-menu w-100" aria-labelledby="categoryDropdown">
                                    <li><button class="dropdown-item category-option" type="button" data-value="Phone"><i class="fas fa-mobile-screen-button text-primary"></i> Phone / Mobile Device</button></li>
                                    <li><button class="dropdown-item category-option" type="button" data-value="Wallet"><i class="fas fa-wallet text-warning"></i> Wallet / Purse</button></li>
                                    <li><button class="dropdown-item category-option" type="button" data-value="Document"><i class="fas fa-id-card text-info"></i> Document / ID / Certificate</button></li>
                                    <li><button class="dropdown-item category-option" type="button" data-value="Bag"><i class="fas fa-briefcase text-secondary"></i> Bag / Backpack</button></li>
                                    <li><button class="dropdown-item category-option" type="button" data-value="Clothing"><i class="fas fa-shirt text-success"></i> Clothing / Shoes</button></li>
                                    <li><button class="dropdown-item category-option" type="button" data-value="Electronics"><i class="fas fa-laptop text-dark"></i> Laptop / Charger / Earphones</button></li>
                                    <li><button class="dropdown-item category-option" type="button" data-value="Keys"><i class="fas fa-key text-danger"></i> Keys</button></li>
                                    <li><button class="dropdown-item category-option" type="button" data-value="Other"><i class="fas fa-ellipsis text-muted"></i> Other</button></li>
                                </ul>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="date_found" class="form-label">Date Found <span class="text-danger">*</span></label>
                                <div class="input-shell">
                                    <i class="fas fa-calendar-days field-icon"></i>
                                    <input type="date" id="date_found" name="date_found" class="form-control" required
                                           max="<?php echo date('Y-m-d'); ?>"
                                           value="<?php echo $date_found_value; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="location" class="form-label">Location Found
                                    <i class="fas fa-circle-info text-muted ms-1" data-bs-toggle="tooltip" title="Examples: Main Gate, Library Reading Room, Bus Park."></i>
                                </label>
                                <div class="input-shell">
                                    <i class="fas fa-location-dot field-icon"></i>
                                    <input type="text" id="location" name="location" class="form-control" maxlength="140"
                                           placeholder="e.g., Main Gate, Library"
                                           value="<?php echo $location_value; ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h5 class="mb-3"><i class="fas fa-camera me-2 text-primary"></i>Photo (Optional)</h5>
                        <div class="mb-3">
                            <label for="item_image" class="form-label">Upload Item Photo</label>
                            <input type="file" id="item_image" name="item_image" class="form-control" accept="image/*">
                            <small class="hint-text d-block mt-2">Accepted: JPG, JPEG, PNG, GIF. Maximum size: 2MB.</small>
                        </div>

                        <div id="previewWrapper" class="d-none mt-3">
                            <div class="preview-box">
                                <img id="previewImage" src="" alt="Selected image preview">
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="removeImageBtn">
                                    <i class="fas fa-xmark me-1"></i>Remove image
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-md-row gap-2 mt-4">
                        <button type="submit" class="btn btn-theme flex-grow-1" id="submitFoundBtn">
                            <i class="fas fa-paper-plane me-2"></i>Submit Found Item Report
                        </button>
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const foundForm = document.getElementById('foundForm');
        const descriptionInput = document.getElementById('description');
        const counter = document.getElementById('descriptionCounter');
        const itemImageInput = document.getElementById('item_image');
        const previewWrapper = document.getElementById('previewWrapper');
        const previewImage = document.getElementById('previewImage');
        const removeImageBtn = document.getElementById('removeImageBtn');
        const categoryInput = document.getElementById('categoryInput');
        const categoryLabel = document.getElementById('categoryLabel');
        const progressBar = document.getElementById('foundProgressBar');

        const step1 = document.getElementById('found-step-1');
        const step2 = document.getElementById('found-step-2');
        const step3 = document.getElementById('found-step-3');

        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
            new bootstrap.Tooltip(el);
        });

        function updateCounter() {
            const length = descriptionInput.value.length;
            counter.textContent = length + ' / 500';
            counter.classList.remove('limit-near', 'limit-hit');
            if (length >= 430 && length < 500) counter.classList.add('limit-near');
            if (length >= 500) counter.classList.add('limit-hit');
        }

        function updateSteps() {
            const itemNameFilled = document.getElementById('item_name').value.trim() !== '';
            const descriptionFilled = descriptionInput.value.trim() !== '';
            const categoryFilled = categoryInput.value.trim() !== '';
            const dateFilled = document.getElementById('date_found').value.trim() !== '';
            const locationFilled = document.getElementById('location').value.trim() !== '';
            const hasPhoto = itemImageInput.files && itemImageInput.files.length > 0;

            const requiredFilledCount = [itemNameFilled, descriptionFilled, categoryFilled, dateFilled]
                .filter(Boolean).length;
            const requiredRatio = requiredFilledCount / 4;

            // Weighted progress: base + required fields + optional location + optional photo.
            const progressValue = Math.min(
                100,
                Math.round(15 + (requiredRatio * 65) + (locationFilled ? 10 : 0) + (hasPhoto ? 10 : 0))
            );

            progressBar.style.width = progressValue + '%';
            progressBar.setAttribute('aria-valuenow', String(progressValue));

            step1.classList.remove('active', 'done');
            step2.classList.remove('active', 'done');
            step3.classList.remove('active', 'done');

            if (requiredFilledCount < 4) {
                step1.classList.add('active');
                return;
            }

            step1.classList.add('done');
            step2.classList.add('active');

            if (hasPhoto) {
                step2.classList.remove('active');
                step2.classList.add('done');
                step3.classList.add('active');
            }
        }

        function showPreview(file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                previewImage.src = e.target.result;
                previewWrapper.classList.remove('d-none');
                updateSteps();
            };
            reader.readAsDataURL(file);
        }

        document.querySelectorAll('.category-option').forEach((btn) => {
            btn.addEventListener('click', function () {
                categoryInput.value = this.dataset.value;
                categoryLabel.textContent = this.textContent.trim();
                updateSteps();
            });
        });

        itemImageInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                showPreview(this.files[0]);
            } else {
                previewWrapper.classList.add('d-none');
            }
            updateSteps();
        });

        removeImageBtn.addEventListener('click', function () {
            itemImageInput.value = '';
            previewImage.src = '';
            previewWrapper.classList.add('d-none');
            updateSteps();
        });

        ['item_name', 'description', 'date_found', 'location'].forEach((id) => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', updateSteps);
        });

        descriptionInput.addEventListener('input', updateCounter);

        foundForm.addEventListener('submit', function () {
            step1.classList.add('done');
            step2.classList.add('done');
            step3.classList.add('done');
            progressBar.style.width = '100%';

            const submitBtn = document.getElementById('submitFoundBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting report...';
            }
        });

        updateCounter();
        updateSteps();

        if (categoryInput.value && categoryLabel.textContent.trim() === 'Select category') {
            const selected = Array.from(document.querySelectorAll('.category-option')).find((opt) => opt.dataset.value === categoryInput.value);
            if (selected) categoryLabel.textContent = selected.textContent.trim();
        }
    </script>
</body>
</html>
