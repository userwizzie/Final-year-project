<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$message      = '';
$message_type = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } else {
        // Check users table
        $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $found = $stmt->fetch();

        // Check administrators table if not found in users
        if (!$found) {
            $stmt = $conn->prepare("SELECT email FROM administrators WHERE email = ?");
            $stmt->execute([$email]);
            $found = $stmt->fetch();
        }

        // Always show the same message (prevent user enumeration)
        if ($found) {
            // Delete any existing token for this email
            $del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $del->execute([$email]);

            // Generate a secure token
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $ins = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $ins->execute([$email, $token, $expires]);

            $reset_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                . '://' . $_SERVER['HTTP_HOST']
                . dirname($_SERVER['PHP_SELF']) . '/reset-password.php?token=' . $token;

            $subject = "Reset Your Lost & Found Password";
            $body    = "Hello,\n\nYou requested a password reset for your Lost & Found account.\n\n"
                     . "Click the link below to set a new password (valid for 1 hour):\n\n"
                     . $reset_link . "\n\n"
                     . "If you did not request this, you can safely ignore this email.\n\n"
                     . "— Kyambogo University Lost & Found";

            notify_user($email, $subject, $body);
        }

        // Same message whether email exists or not
        $message      = "If that email is registered, a password-reset link has been sent. Check your email (or email_log.txt if on the demo server).";
        $message_type = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Lost &amp; Found — Kyambogo University</title>
    <link rel="icon" type="image/svg+xml" sizes="any" href="assets/images/favicon.svg?v=20260317">
    <link rel="icon" type="image/png" sizes="96x96" href="assets/images/favicon-96x96.png?v=20260317">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-touch-icon.png?v=20260317">
    <link rel="manifest" href="assets/images/site.webmanifest?v=20260317">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 1.5rem;
            background-color: #060f1e;
            background-image:
                radial-gradient(ellipse 80% 55% at 10%   0%, rgba(13,110,253,0.50) 0%, transparent 55%),
                radial-gradient(ellipse 55% 45% at 90% 100%, rgba(13,110,253,0.26) 0%, transparent 55%),
                radial-gradient(ellipse 50% 40% at 50% 110%, rgba(13,202,240,0.15) 0%, transparent 50%);
            overflow-x: hidden;
        }

        .bg-blob { position: fixed; border-radius: 50%; filter: blur(90px); opacity: 0.14;
                   pointer-events: none; z-index: 0; animation: blobDrift 18s ease-in-out infinite alternate; }
        @keyframes blobDrift {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(28px, -18px) scale(1.07); }
        }
        .bg-blob-1 { width: 520px; height: 520px; background: #0d6efd; top: -15%; left: -10%; animation-delay: 0s; }
        .bg-blob-2 { width: 420px; height: 420px; background: #2f7fff; bottom: -12%; right: -8%; animation-delay: -6s; }
        .bg-blob-3 { width: 280px; height: 280px; background: #0dcaf0; top: 45%; right: 6%; animation-delay: -3s; }

        .auth-card {
            position: relative; z-index: 1;
            width: 100%; max-width: 460px;
            background: #ffffff; border-radius: 22px;
            box-shadow: 0 0 0 1px rgba(255,255,255,0.07),
                        0 24px 70px rgba(0,0,0,0.45),
                        0 4px 24px rgba(13,110,253,0.22);
            overflow: hidden;
            animation: fadeInUp 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(36px) scale(0.96); }
            to   { opacity: 1; transform: translateY(0)    scale(1);    }
        }

        .auth-header {
            background: linear-gradient(135deg, #060f1e 0%, #0a3580 55%, #1573ff 100%);
            padding: 2.6rem 2rem 2.2rem; text-align: center; position: relative; overflow: hidden;
        }
        .auth-header::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(circle at 80% 15%, rgba(255,255,255,0.09) 0%, transparent 45%),
                        radial-gradient(circle at 20% 85%, rgba(255,255,255,0.06) 0%, transparent 45%);
        }
        .auth-logo {
            position: relative; width: 82px; height: 82px;
            background: rgba(255,255,255,0.14); border: 2px solid rgba(255,255,255,0.30);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 2.1rem; color: #fff; margin: 0 auto 1.1rem;
            backdrop-filter: blur(6px); box-shadow: 0 8px 24px rgba(0,0,0,0.28);
        }
        .auth-logo img { width: 70px; height: 70px; object-fit: cover; border-radius: 50%;
                         border: 2px solid rgba(255,255,255,0.55); background: #fff; }
        .auth-header h1 { position: relative; color: #fff; font-size: 1.5rem; font-weight: 700;
                          margin-bottom: 0.25rem; letter-spacing: -0.015em; }
        .auth-header p  { position: relative; color: rgba(255,255,255,0.68); font-size: 0.78rem;
                          letter-spacing: 0.10em; text-transform: uppercase; margin: 0; }

        .auth-body { padding: 2rem 2rem 1.5rem; }
        .auth-subtitle { font-size: 0.97rem; font-weight: 600; color: #1a1a2e; margin-bottom: 0.5rem; }
        .auth-hint { font-size: 0.84rem; color: #64748b; margin-bottom: 1.4rem; line-height: 1.5; }

        .auth-alert {
            border-radius: 10px; font-size: 0.86rem; padding: 0.7rem 1rem;
            border: none; margin-bottom: 1.2rem;
            display: flex; align-items: flex-start; gap: 0.5rem;
        }
        .auth-alert.alert-danger  { background: #fff0f0; color: #b91c1c; }
        .auth-alert.alert-success { background: #f0fff5; color: #166534; }

        .form-label { font-size: 0.81rem; font-weight: 600; color: #475569; margin-bottom: 0.4rem;
                      letter-spacing: 0.03em; display: block; }

        .field-wrap { position: relative; }
        .field-wrap .field-icon { position: absolute; left: 0.9rem; top: 50%; transform: translateY(-50%);
                                   color: #94a3b8; font-size: 0.88rem; z-index: 5;
                                   pointer-events: none; transition: color 0.2s; }
        .field-wrap:focus-within .field-icon { color: #0d6efd; }
        .field-wrap .form-control {
            padding-left: 2.7rem; height: 48px; border-radius: 11px !important;
            border: 1.5px solid #e2e8f0; font-size: 0.92rem; background: #f8faff;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        .field-wrap .form-control:focus {
            border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,0.13);
            background: #fff; outline: none;
        }

        .btn-primary-action {
            height: 50px; font-weight: 700; font-size: 0.96rem; border-radius: 11px;
            background: linear-gradient(135deg, #1573ff, #0a3fc7); border: none;
            letter-spacing: 0.02em; box-shadow: 0 5px 16px rgba(13,110,253,0.38);
            transition: transform 0.15s, box-shadow 0.15s; color: #fff !important;
        }
        .btn-primary-action:hover:not(:disabled) {
            background: linear-gradient(135deg, #0a58ca, #073aab);
            transform: translateY(-2px); box-shadow: 0 8px 22px rgba(13,110,253,0.48);
        }
        .btn-primary-action:active:not(:disabled) { transform: translateY(0); }
        .btn-primary-action:disabled { opacity: 0.7; cursor: not-allowed; }

        .auth-footer {
            background: #f5f8ff; border-top: 1px solid #edf1f8;
            padding: 1.15rem 2rem; text-align: center; font-size: 0.87rem; color: #64748b;
        }
        .auth-footer a { color: #0d6efd; font-weight: 600; text-decoration: none; }
        .auth-footer a:hover { text-decoration: underline; }

        @media (max-width: 480px) {
            body { padding: 0.75rem; }
            .auth-header { padding: 2rem 1.5rem 1.75rem; }
            .auth-body   { padding: 1.5rem; }
            .auth-footer { padding: 1rem 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="bg-blob bg-blob-1"></div>
    <div class="bg-blob bg-blob-2"></div>
    <div class="bg-blob bg-blob-3"></div>

    <div class="auth-card">

        <div class="auth-header">
            <div class="auth-logo">
                <img src="assets/images/kyu.jpg" alt="Kyambogo University Logo"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                <i class="fas fa-university" style="display:none;" aria-hidden="true"></i>
            </div>
            <h1>Lost &amp; Found</h1>
            <p>Kyambogo University</p>
        </div>

        <div class="auth-body">
            <p class="auth-subtitle">Forgot your password?</p>
            <p class="auth-hint">Enter the email address linked to your account and we'll send you a reset link valid for 1 hour.</p>

            <?php if ($message): ?>
                <div class="auth-alert alert alert-<?php echo htmlspecialchars($message_type); ?>" role="alert">
                    <i class="fas fa-<?php echo ($message_type === 'success') ? 'check-circle' : 'exclamation-circle'; ?> mt-1"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($message_type !== 'success'): ?>
            <form method="POST" action="" novalidate id="forgotForm">
                <div class="mb-4">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="field-wrap">
                        <i class="fas fa-envelope field-icon"></i>
                        <input type="email" id="email" name="email"
                               class="form-control"
                               placeholder="you@kyu.ac.ug"
                               autocomplete="email" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary-action w-100" id="submitBtn">
                    <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                </button>
            </form>
            <?php endif; ?>
        </div>

        <div class="auth-footer">
            <a href="login.php"><i class="fas fa-arrow-left me-1"></i>Back to Sign In</a>
        </div>

    </div>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('forgotForm')?.addEventListener('submit', function () {
            const btn = document.getElementById('submitBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending…';
            }
        });
    </script>
</body>
</html>
