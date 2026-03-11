<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($name) || empty($email) || empty($password)) {
        $message = "All fields are required.";
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = "Email already registered.";
        } else {
            // Hash password
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $conn->prepare("
                INSERT INTO users (name, email, password, phone, role)
                VALUES (?, ?, ?, ?, 'user')
            ");
            if ($stmt->execute([$name, $email, $hashed, $phone])) {
                $success = true;
                $message = "Registration successful! You can now log in.";

                // welcome notification
                notify_user($email, 'Welcome to Lost & Found',
                            "Hi $name,\n\nThank you for registering with the Lost & Found system at Kyambogo University.\nYou may now log in and start reporting items.");
            } else {
                $message = "Something went wrong. Try again.";
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
    <title>Register - Kyambogo University Lost & Found System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .logo-section {
            background: #f8f9fa;
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
        }
        .logo-placeholder {
            width: 80px;
            height: 80px;
            background: #2a5298;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 2rem;
            font-weight: bold;
        }
        .university-name {
            color: #2a5298;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }
        .motto {
            color: #6c757d;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }
        .form-section {
            padding: 2rem;
        }
        .btn-login {
            background: #2a5298;
            border: none;
            padding: 0.75rem;
            font-weight: 600;
        }
        .btn-login:hover {
            background: #1e3c72;
        }
        .register-link {
            text-align: center;
            margin-top: 1rem;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="login-container">
                <div class="logo-section">
                    <div class="logo-placeholder">KU</div>
                    <h4 class="university-name">Kyambogo University</h4>
                    <p class="motto">Knowledge and Service</p>
                </div>

                <div class="form-section">
                    <h5 class="text-center mb-4">Create New Account</h5>

                    <?php if ($message): ?>
                        <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show">
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required placeholder="your.email@kyu.ac.ug">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone (optional)</label>
                            <input type="tel" name="phone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-login w-100">Register</button>
                    </form>

                    <div class="register-link">
                        <p class="mb-0">Already have an account? <a href="login.php" class="text-decoration-none">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>