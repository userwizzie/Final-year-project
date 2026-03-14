<?php
require_once 'includes/config.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $message = "Email and password are required.";
    } else {
        // Check if user exists (we'll check both users and administrators tables)
        $stmt = $conn->prepare("SELECT user_id, name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            // Try administrators table
            $stmt = $conn->prepare("SELECT admin_id AS user_id, name, email, password FROM administrators WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
        }

        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['name']      = $user['name'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['role']      = $user['role'] ?? 'admin'; // admins don't have role field, so default to 'admin'

            $_SESSION['just_logged_in'] = true; // Flag for welcome message

            $success = true;
            session_regenerate_id(true);
            $message = "Login successful! Redirecting...";

            // // Redirect based on role (simple for now)
            // if ($_SESSION['role'] === 'admin') {
            //     header("Refresh: 2; url=admin/dashboard.php");
            // } else {
            //     header("Refresh: 2; url=dashboard.php");
            // }
            // exit;
            // In login.php, inside the successful login block, replace the redirect with:
           
            if ($_SESSION['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            exit;
            } else {
                header("Location: dashboard.php");
            exit;
}
        } else {
            $message = "Invalid email or password.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kyambogo University Lost & Found System</title>
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
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
                    <!-- <div class="logo-placeholder">KU</div> -->
                     <img src="assets/images/kyu.jpg" alt="Kyambogo University Logo" class="mb-3" style="width: 80px; height: 80px;">
                    <h4 class="university-name">Kyambogo University</h4>
                    <p class="motto">Knowledge and Skills for Service</p>
                </div>

                <div class="form-section">
                    <h5 class="text-center mb-4">Login</h5>

                    <?php if ($message): ?>
                        <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show">
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email address</label>
                            <input type="email" name="email" class="form-control">                         </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-login w-100">Login</button>
                    </form>

                    <div class="register-link">
                        <p class="mb-0">Don't have an account? <a href="register.php" class="text-decoration-none">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>