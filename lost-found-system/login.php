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
    <title>Login - Lost & Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-header text-center bg-primary text-white">
                    <h4>Sign In</h4>
                </div>
                <div class="card-body">

                    <?php if ($message): ?>
                        <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>">
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email address</label>
                            <input type="email" name="email" class="form-control" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>

                    <p class="text-center mt-3">
                        Don't have an account? <a href="register.php">Register here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>