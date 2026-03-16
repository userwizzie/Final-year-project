<?php
require_once 'includes/config.php';

if (is_logged_in()) {
    header(is_admin_user() ? 'Location: admin/dashboard.php' : 'Location: dashboard.php');
    exit;
}

$message       = '';
$message_type  = 'danger';
$prefill_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $prefill_email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

    if (empty($email) || empty($password)) {
        $message = "Please enter both your email and password.";
    } else {
        $stmt = $conn->prepare("SELECT user_id, name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $stmt = $conn->prepare("SELECT admin_id AS user_id, name, email, password, 'admin' AS role FROM administrators WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
        }

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']        = $user['user_id'];
            $_SESSION['name']           = $user['name'];
            $_SESSION['email']          = $user['email'];
            $_SESSION['role']           = $user['role'] ?? 'admin';
            $_SESSION['just_logged_in'] = true;
            header($_SESSION['role'] === 'admin' ? 'Location: admin/dashboard.php' : 'Location: dashboard.php');
            exit;
        } else {
            $message = "Invalid email or password. Please try again.";
        }
    }
}

$logged_out = isset($_GET['logged_out']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Lost &amp; Found — Kyambogo University</title>
    <meta name="description" content="Sign in to the Kyambogo University Lost and Found System.">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 5 -->
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background-color: #060f1e;
            background-image:
                radial-gradient(ellipse 80% 55% at 10%   0%, rgba( 13,110,253,0.50) 0%, transparent 55%),
                radial-gradient(ellipse 55% 45% at 90% 100%, rgba( 13,110,253,0.26) 0%, transparent 55%),
                radial-gradient(ellipse 50% 40% at 50% 110%, rgba( 13,202,240,0.15) 0%, transparent 50%);
            overflow-x: hidden;
        }

        /* Animated background blobs */
        .bg-blob {
            position: fixed; border-radius: 50%;
            filter: blur(90px); opacity: 0.14;
            pointer-events: none; z-index: 0;
            animation: blobDrift 18s ease-in-out infinite alternate;
        }
        @keyframes blobDrift {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(28px, -18px) scale(1.07); }
        }
        .bg-blob-1 { width: 520px; height: 520px; background: #0d6efd; top: -15%; left: -10%; animation-delay: 0s; }
        .bg-blob-2 { width: 420px; height: 420px; background: #2f7fff; bottom: -12%; right:  -8%; animation-delay: -6s; }
        .bg-blob-3 { width: 280px; height: 280px; background: #0dcaf0; top:  45%; right:   6%; animation-delay: -3s; }

        /* Card */
        .auth-card {
            position: relative; z-index: 1;
            width: 100%; max-width: 460px;
            background: #ffffff;
            border-radius: 22px;
            box-shadow:
                0 0 0 1px rgba(255,255,255,0.07),
                0 24px 70px rgba(0,0,0,0.45),
                0 4px 24px rgba(13,110,253,0.22);
            overflow: hidden;
            animation: fadeInUp 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(36px) scale(0.96); }
            to   { opacity: 1; transform: translateY(0)    scale(1);    }
        }

        /* Card header */
        .auth-header {
            background: linear-gradient(135deg, #060f1e 0%, #0a3580 55%, #1573ff 100%);
            padding: 2.6rem 2rem 2.2rem;
            text-align: center; position: relative; overflow: hidden;
        }
        .auth-header::before {
            content: ''; position: absolute; inset: 0;
            background:
                radial-gradient(circle at 80% 15%, rgba(255,255,255,0.09) 0%, transparent 45%),
                radial-gradient(circle at 20% 85%, rgba(255,255,255,0.06) 0%, transparent 45%);
        }
        .auth-logo {
            position: relative;
            width: 82px; height: 82px;
            background: rgba(255,255,255,0.14);
            border: 2px solid rgba(255,255,255,0.30);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.1rem; color: #fff;
            margin: 0 auto 1.1rem;
            backdrop-filter: blur(6px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.28);
        }
        .auth-logo img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.55);
            background: #fff;
        }
        .auth-header h1 {
            position: relative; color: #fff;
            font-size: 1.5rem; font-weight: 700;
            margin-bottom: 0.25rem; letter-spacing: -0.015em;
        }
        .auth-header p {
            position: relative; color: rgba(255,255,255,0.68);
            font-size: 0.78rem; letter-spacing: 0.10em;
            text-transform: uppercase; margin: 0;
        }

        /* Card body */
        .auth-body { padding: 2rem 2rem 1.5rem; }
        .auth-subtitle { font-size: 0.97rem; font-weight: 600; color: #1a1a2e; margin-bottom: 1.4rem; }

        /* Alerts */
        .auth-alert {
            border-radius: 10px; font-size: 0.86rem;
            padding: 0.7rem 1rem; border: none; margin-bottom: 1.2rem;
            display: flex; align-items: flex-start; gap: 0.5rem;
        }
        .auth-alert.alert-danger  { background: #fff0f0; color: #b91c1c; }
        .auth-alert.alert-success { background: #f0fff5; color: #166534; }
        .auth-alert.alert-info    { background: #eff8ff; color: #1d5f8a; }
        .auth-alert .btn-close { margin-left: auto; font-size: 0.68rem; flex-shrink: 0; }

        /* Labels */
        .form-label { font-size: 0.81rem; font-weight: 600; color: #475569; margin-bottom: 0.4rem; letter-spacing: 0.03em; display: block; }

        /* Icon-prefixed inputs */
        .field-wrap { position: relative; }
        .field-wrap .field-icon {
            position: absolute; left: 0.9rem; top: 50%;
            transform: translateY(-50%); color: #94a3b8;
            font-size: 0.88rem; z-index: 5; pointer-events: none; transition: color 0.2s;
        }
        .field-wrap:focus-within .field-icon { color: #0d6efd; }
        .field-wrap .form-control {
            padding-left: 2.7rem; height: 48px;
            border-radius: 11px !important; border: 1.5px solid #e2e8f0;
            font-size: 0.92rem; background: #f8faff;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        .field-wrap .form-control:focus {
            border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,0.13);
            background: #fff; outline: none;
        }
        .field-wrap .form-control.has-toggle { padding-right: 2.8rem; }

        /* Password toggle */
        .pw-toggle {
            position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: #94a3b8;
            cursor: pointer; padding: 0.2rem; z-index: 5; font-size: 0.88rem; transition: color 0.2s;
        }
        .pw-toggle:hover { color: #0d6efd; }

        /* Remember / Forgot row */
        .auth-meta { display: flex; justify-content: space-between; align-items: center; font-size: 0.83rem; }
        .auth-meta .form-check-label { color: #64748b; cursor: pointer; font-size: 0.83rem; }
        .auth-meta a { color: #0d6efd; font-weight: 500; text-decoration: none; }
        .auth-meta a:hover { text-decoration: underline; }
        .form-check-input:checked { background-color: #0d6efd; border-color: #0d6efd; }

        /* Sign-in button */
        .btn-signin {
            height: 50px; font-weight: 700; font-size: 0.96rem; border-radius: 11px;
            background: linear-gradient(135deg, #1573ff, #0a3fc7); border: none;
            letter-spacing: 0.02em; box-shadow: 0 5px 16px rgba(13,110,253,0.38);
            transition: transform 0.15s, box-shadow 0.15s, background 0.2s; color: #fff !important;
        }
        .btn-signin:hover:not(:disabled) {
            background: linear-gradient(135deg, #0a58ca, #073aab);
            transform: translateY(-2px); box-shadow: 0 8px 22px rgba(13,110,253,0.48);
        }
        .btn-signin:active:not(:disabled) { transform: translateY(0); }
        .btn-signin:disabled { opacity: 0.7; cursor: not-allowed; }

        /* OR divider */
        .auth-divider { display: flex; align-items: center; gap: 0.7rem; font-size: 0.74rem; color: #94a3b8; font-weight: 600; letter-spacing: 0.06em; }
        .auth-divider::before, .auth-divider::after { content: ''; flex: 1; height: 1px; background: #e8edf3; }

        /* Google button */
        .btn-google {
            height: 48px; font-size: 0.9rem; font-weight: 500; border-radius: 11px;
            border: 1.5px solid #dde4ef; background: #fff; color: #3c4043;
            transition: background 0.18s, box-shadow 0.18s, transform 0.15s;
            display: flex; align-items: center; justify-content: center; gap: 0.6rem;
        }
        .btn-google:hover { background: #f5f8ff; box-shadow: 0 3px 12px rgba(0,0,0,0.10); transform: translateY(-1px); }

        /* Footer strip */
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
    <!-- Animated background blobs -->
    <div class="bg-blob bg-blob-1"></div>
    <div class="bg-blob bg-blob-2"></div>
    <div class="bg-blob bg-blob-3"></div>

    <div class="auth-card">

        <!-- Brand header -->
        <div class="auth-header">
            <div class="auth-logo">
                <img src="assets/images/kyu.jpg" alt="Kyambogo University Logo"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                <i class="fas fa-university" style="display:none;" aria-hidden="true"></i>
            </div>
            <h1>Lost &amp; Found</h1>
            <p>Kyambogo University</p>
        </div>

        <!-- Form body -->
        <div class="auth-body">
            <p class="auth-subtitle">Sign in to your account</p>

            <?php if ($logged_out): ?>
                <div class="auth-alert alert alert-info" role="alert">
                    <i class="fas fa-check-circle mt-1"></i>
                    <span>You have been signed out successfully.</span>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="auth-alert alert alert-<?php echo htmlspecialchars($message_type); ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?php echo ($message_type === 'success') ? 'check-circle' : 'exclamation-circle'; ?> mt-1"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="" novalidate id="loginForm">

                <!-- Email -->
                <div class="mb-3">
                    <label for="loginEmail" class="form-label">Email Address</label>
                    <div class="field-wrap">
                        <i class="fas fa-envelope field-icon"></i>
                        <input type="email" id="loginEmail" name="email"
                               class="form-control"
                               placeholder="you@kyu.ac.ug"
                               value="<?php echo $prefill_email; ?>"
                               autocomplete="email" required>
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="loginPassword" class="form-label">Password</label>
                    <div class="field-wrap">
                        <i class="fas fa-lock field-icon"></i>
                        <input type="password" id="loginPassword" name="password"
                               class="form-control has-toggle"
                               placeholder="Enter your password"
                               autocomplete="current-password" required>
                        <button type="button" class="pw-toggle" id="pwToggle" aria-label="Toggle password visibility">
                            <i class="fas fa-eye" id="pwIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember me + Forgot password -->
                <div class="auth-meta mb-4">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" id="rememberMe" name="remember_me">
                        <label class="form-check-label" for="rememberMe">Remember me</label>
                    </div>
                    <a href="forgot-password.php"><i class="fas fa-key me-1"></i>Forgot password?</a>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn btn-signin w-100" id="submitBtn">
                    <i class="fas fa-right-to-bracket me-2"></i>Sign In
                </button>

            </form>

            <!-- OR divider -->
            <div class="auth-divider my-4">OR CONTINUE WITH</div>

            <!-- Google Sign-In placeholder -->
            <button type="button" class="btn btn-google w-100"
                    onclick="alert('Google Sign-In is not yet configured.\nPlease use your email and password to sign in.')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20" height="20" aria-hidden="true" style="flex-shrink:0;">
                    <path fill="#4285F4" d="M23.49 12.27c0-.98-.09-1.93-.25-2.84H12v5.38h6.47a5.52 5.52 0 0 1-2.39 3.61v3h3.86c2.26-2.08 3.55-5.15 3.55-9.15z"/>
                    <path fill="#34A853" d="M12 24c3.24 0 5.95-1.07 7.93-2.91l-3.86-3c-1.07.72-2.44 1.14-4.07 1.14-3.13 0-5.78-2.11-6.73-4.96H1.3v3.1A11.99 11.99 0 0 0 12 24z"/>
                    <path fill="#FBBC05" d="M5.27 14.27A7.2 7.2 0 0 1 4.9 12c0-.79.14-1.56.37-2.28V6.62H1.3A11.99 11.99 0 0 0 0 12c0 1.93.46 3.75 1.3 5.38l3.97-3.11z"/>
                    <path fill="#EA4335" d="M12 4.76c1.77 0 3.35.6 4.6 1.8l3.44-3.44C17.95 1.19 15.24 0 12 0A12 12 0 0 0 1.3 6.62l3.97 3.1C6.22 6.87 8.87 4.76 12 4.76z"/>
                </svg>
                Continue with Google
            </button>
        </div><!-- /.auth-body -->

        <!-- Register footer -->
        <div class="auth-footer">
            Don't have an account?&nbsp;<a href="register.php">Create one here</a>
        </div>

    </div><!-- /.auth-card -->

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password show/hide toggle
        const pwToggle = document.getElementById('pwToggle');
        const pwInput  = document.getElementById('loginPassword');
        const pwIcon   = document.getElementById('pwIcon');
        if (pwToggle) {
            pwToggle.addEventListener('click', function () {
                const show      = pwInput.type === 'password';
                pwInput.type    = show ? 'text' : 'password';
                pwIcon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
            });
        }
        // Prevent double-submit
        document.getElementById('loginForm')?.addEventListener('submit', function () {
            const btn = document.getElementById('submitBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing in\u2026';
            }
        });
    </script>
</body>
</html>