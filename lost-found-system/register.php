<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (is_logged_in()) {
    header(is_admin_user() ? 'Location: admin/dashboard.php' : 'Location: dashboard.php');
    exit;
}

$message       = '';
$message_type  = 'danger';
$success       = false;

$name_value  = '';
$email_value = '';
$phone_value = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    $name_value  = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $email_value = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $phone_value = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');

    if (empty($name) || empty($email) || empty($password)) {
        $message = "Please fill in full name, email, and password.";
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (!empty($phone) && !preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
        $message = "Please enter a valid phone number or leave it empty.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
    } else {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $message = "That email is already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                INSERT INTO users (name, email, password, phone, role)
                VALUES (?, ?, ?, ?, 'user')
            ");

            if ($stmt->execute([$name, $email, $hashed, $phone])) {
                $success      = true;
                $message_type = 'success';
                $message      = "Registration successful. Your account is ready.";

                if (function_exists('notify_user')) {
                    notify_user(
                        $email,
                        'Welcome to Lost & Found',
                        "Hi $name,\n\nThank you for registering with the Lost & Found system at Kyambogo University.\nYou may now log in and start reporting items."
                    );
                }

                $name_value = $email_value = $phone_value = '';
            } else {
                $message = "Something went wrong while creating your account. Please try again.";
            }
        }
    }
}

$signed_out = isset($_GET['logged_out']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Lost &amp; Found KyU</title>
    <meta name="description" content="Create your Lost and Found KyU account to report and claim items securely.">
    <link rel="icon" type="image/svg+xml" sizes="any" href="assets/images/favicon.svg?v=20260317">
    <link rel="icon" type="image/png" sizes="96x96" href="assets/images/favicon-96x96.png?v=20260317">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-touch-icon.png?v=20260317">
    <link rel="manifest" href="assets/images/site.webmanifest?v=20260317">

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

        .bg-blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.14;
            pointer-events: none;
            z-index: 0;
            animation: blobDrift 18s ease-in-out infinite alternate;
        }
        @keyframes blobDrift {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(28px, -18px) scale(1.07); }
        }
        .bg-blob-1 { width: 520px; height: 520px; background: #0d6efd; top: -15%; left: -10%; animation-delay: 0s; }
        .bg-blob-2 { width: 420px; height: 420px; background: #2f7fff; bottom: -12%; right:  -8%; animation-delay: -6s; }
        .bg-blob-3 { width: 280px; height: 280px; background: #0dcaf0; top:  45%; right:   6%; animation-delay: -3s; }

        .auth-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 500px;
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

        .auth-header {
            background: linear-gradient(135deg, #060f1e 0%, #0a3580 55%, #1573ff 100%);
            padding: 2.4rem 2rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .auth-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 80% 15%, rgba(255,255,255,0.09) 0%, transparent 45%),
                radial-gradient(circle at 20% 85%, rgba(255,255,255,0.06) 0%, transparent 45%);
        }
        .auth-logo {
            position: relative;
            width: 82px;
            height: 82px;
            margin: 0 auto 1rem;
            background: rgba(255,255,255,0.14);
            border: 2px solid rgba(255,255,255,0.30);
            border-radius: 50%;
            box-shadow: 0 8px 24px rgba(0,0,0,0.28);
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(6px);
        }
        .auth-logo img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.55);
            background: #fff;
        }
        .auth-header h1 {
            position: relative;
            color: #fff;
            font-size: 1.45rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            letter-spacing: -0.015em;
        }
        .auth-header p {
            position: relative;
            margin: 0;
            color: rgba(255,255,255,0.68);
            font-size: 0.78rem;
            letter-spacing: 0.10em;
            text-transform: uppercase;
        }

        .auth-body {
            padding: 2rem;
        }
        .auth-subtitle {
            font-size: 0.97rem;
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 1.2rem;
        }

        .auth-alert {
            border-radius: 10px;
            font-size: 0.86rem;
            padding: 0.7rem 1rem;
            border: none;
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }
        .auth-alert.alert-danger  { background: #fff0f0; color: #b91c1c; }
        .auth-alert.alert-success { background: #f0fff5; color: #166534; }
        .auth-alert.alert-info    { background: #eff8ff; color: #1d5f8a; }
        .auth-alert .btn-close {
            margin-left: auto;
            font-size: 0.68rem;
            flex-shrink: 0;
        }

        .form-label {
            font-size: 0.81rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.4rem;
            letter-spacing: 0.03em;
            display: block;
        }

        .field-wrap { position: relative; }
        .field-wrap .field-icon {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.88rem;
            z-index: 5;
            pointer-events: none;
            transition: color 0.2s;
        }
        .field-wrap:focus-within .field-icon { color: #0d6efd; }

        .field-wrap .form-control {
            padding-left: 2.7rem;
            height: 48px;
            border-radius: 11px !important;
            border: 1.5px solid #e2e8f0;
            font-size: 0.92rem;
            background: #f8faff;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        .field-wrap .form-control.has-toggle { padding-right: 2.8rem; }
        .field-wrap .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13,110,253,0.13);
            background: #fff;
            outline: none;
        }

        .pw-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 0.2rem;
            z-index: 5;
            font-size: 0.88rem;
            transition: color 0.2s;
        }
        .pw-toggle:hover { color: #0d6efd; }

        .password-hint {
            font-size: 0.78rem;
            color: #64748b;
            margin-top: 0.4rem;
        }

        .btn-register {
            height: 50px;
            font-weight: 700;
            font-size: 0.96rem;
            border-radius: 11px;
            background: linear-gradient(135deg, #1573ff, #0a3fc7);
            border: none;
            letter-spacing: 0.02em;
            box-shadow: 0 5px 16px rgba(13,110,253,0.38);
            transition: transform 0.15s, box-shadow 0.15s, background 0.2s;
            color: #fff !important;
        }
        .btn-register:hover:not(:disabled) {
            background: linear-gradient(135deg, #0a58ca, #073aab);
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(13,110,253,0.48);
        }
        .btn-register:disabled { opacity: 0.7; cursor: not-allowed; }

        .auth-divider {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            font-size: 0.74rem;
            color: #94a3b8;
            font-weight: 600;
            letter-spacing: 0.06em;
        }
        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e8edf3;
        }

        .btn-google {
            height: 48px;
            font-size: 0.9rem;
            font-weight: 500;
            border-radius: 11px;
            border: 1.5px solid #dde4ef;
            background: #fff;
            color: #3c4043;
            transition: background 0.18s, box-shadow 0.18s, transform 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
        }
        .btn-google:hover {
            background: #f5f8ff;
            box-shadow: 0 3px 12px rgba(0,0,0,0.10);
            transform: translateY(-1px);
        }

        .auth-footer {
            background: #f5f8ff;
            border-top: 1px solid #edf1f8;
            padding: 1.15rem 2rem;
            text-align: center;
            font-size: 0.87rem;
            color: #64748b;
        }
        .auth-footer a {
            color: #0d6efd;
            font-weight: 600;
            text-decoration: none;
        }
        .auth-footer a:hover { text-decoration: underline; }

        /* ── Celebration Screen ── */
        .celebration-screen {
            text-align: center;
            padding: 2.2rem 1rem 1.6rem;
            animation: fadeInUp 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        .celebrate-icon {
            width: 84px; height: 84px;
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.2rem; color: #16a34a;
            margin: 0 auto 1.1rem;
            animation: celebPop 0.55s cubic-bezier(0.22, 1, 0.36, 1) 0.15s both;
            box-shadow: 0 0 0 12px rgba(22,163,74,.10), 0 0 0 28px rgba(22,163,74,.05);
        }
        @keyframes celebPop {
            from { transform: scale(0); opacity: 0; }
            to   { transform: scale(1); opacity: 1; }
        }
        .celebrate-name { font-size: 1.4rem; font-weight: 800; color: #0f172a; margin-bottom: 0.35rem; letter-spacing: -0.02em; }
        .celebrate-msg  { font-size: 0.93rem; color: #475569; margin-bottom: 1.5rem; }
        .progress-wrap  { background: #e2e8f0; border-radius: 999px; height: 6px; overflow: hidden; }
        .progress-fill  {
            height: 100%; border-radius: 999px;
            background: linear-gradient(90deg, #0d6efd, #0dcaf0);
            animation: fillBar 3.2s linear forwards;
        }
        @keyframes fillBar { from { width: 0; } to { width: 100%; } }
        .progress-label { font-size: 0.76rem; color: #94a3b8; margin-top: 0.5rem; }

        /* ── Confetti ── */
        .confetti-wrap {
            position: fixed; inset: 0;
            pointer-events: none; z-index: 2000; overflow: hidden;
        }
        .confetto {
            position: absolute; top: -14px; border-radius: 3px; opacity: 0;
            animation: confettiFall linear forwards;
        }
        @keyframes confettiFall {
            0%   { opacity: 1; transform: translateY(-20px) rotate(0deg); }
            100% { opacity: 0; transform: translateY(110vh) rotate(720deg); }
        }

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

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-xl-5">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-logo">
                        <img src="assets/images/kyu.jpg" alt="Kyambogo University Logo"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                        <i class="fas fa-university" style="display:none; color:#fff; font-size:2rem;" aria-hidden="true"></i>
                    </div>
                    <h1>Lost &amp; Found KyU</h1>
                    <p>Kyambogo University</p>
                </div>

                <div class="auth-body">
                    <?php if ($success): ?>
                        <div class="confetti-wrap" id="confettiWrap"></div>
                        <div class="celebration-screen">
                            <div class="celebrate-icon"><i class="fas fa-check"></i></div>
                            <h3 class="celebrate-name">You're in, <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>!</h3>
                            <p class="celebrate-msg">Your account is all set. Taking you to sign in&hellip;</p>
                            <div class="progress-wrap"><div class="progress-fill"></div></div>
                            <p class="progress-label">Redirecting in 3 seconds&hellip;</p>
                        </div>
                        <script>
                            (function () {
                                var wrap   = document.getElementById('confettiWrap');
                                var colors = ['#0d6efd','#16a34a','#f59e0b','#ef4444','#8b5cf6','#0dcaf0','#fb923c'];
                                for (var i = 0; i < 56; i++) {
                                    var el = document.createElement('div');
                                    el.className = 'confetto';
                                    el.style.cssText = [
                                        'left:'  + (Math.random() * 100) + '%',
                                        'width:' + (6 + Math.random() * 8) + 'px',
                                        'height:'+ (8 + Math.random() * 12) + 'px',
                                        'background:' + colors[Math.floor(Math.random() * colors.length)],
                                        'border-radius:' + (Math.random() > 0.5 ? '50%' : '2px'),
                                        'animation-duration:' + (2.2 + Math.random() * 2.4) + 's',
                                        'animation-delay:'    + (Math.random() * 1.6) + 's'
                                    ].join(';');
                                    wrap.appendChild(el);
                                }
                                setTimeout(function () {
                                    window.location.href = 'login.php?registered=1';
                                }, 3200);
                            })();
                        </script>
                    <?php else: ?>
                        <p class="auth-subtitle">Create your secure account</p>

                        <?php if ($signed_out): ?>
                            <div class="auth-alert alert alert-info" role="alert">
                                <i class="fas fa-check-circle mt-1"></i>
                                <span>You signed out successfully. Register a new account or sign back in.</span>
                            </div>
                        <?php endif; ?>

                        <?php if ($message): ?>
                            <div class="auth-alert alert alert-<?php echo htmlspecialchars($message_type, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show" role="alert">
                                <i class="fas fa-triangle-exclamation mt-1"></i>
                                <span><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></span>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                    <?php endif; ?>

                    <?php if (!$success): ?>
                    <form method="POST" action="" id="registerForm" novalidate>
                        <div class="mb-3">
                            <label class="form-label" for="name">Full Name</label>
                            <div class="field-wrap">
                                <i class="fas fa-user field-icon"></i>
                                <input type="text" id="name" name="name" class="form-control" required
                                       placeholder="Enter your full name"
                                       value="<?php echo $name_value; ?>" autocomplete="name">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="email">Email Address</label>
                            <div class="field-wrap">
                                <i class="fas fa-envelope field-icon"></i>
                                <input type="email" id="email" name="email" class="form-control" required
                                       placeholder="your.email@kyu.ac.ug"
                                       value="<?php echo $email_value; ?>" autocomplete="email">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="phone">Phone (Optional)</label>
                            <div class="field-wrap">
                                <i class="fas fa-phone field-icon"></i>
                                <input type="tel" id="phone" name="phone" class="form-control"
                                       placeholder="+256 7xx xxx xxx"
                                       value="<?php echo $phone_value; ?>" autocomplete="tel">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">
                                Password
                                <i class="fas fa-circle-info text-primary ms-1"
                                   data-bs-toggle="tooltip"
                                   data-bs-placement="top"
                                   title="Use at least 8 characters. A mix of upper/lowercase letters, numbers, and symbols is recommended."></i>
                            </label>
                            <div class="field-wrap">
                                <i class="fas fa-lock field-icon"></i>
                                <input type="password" id="password" name="password" class="form-control has-toggle" required
                                       placeholder="Create a strong password" autocomplete="new-password">
                                <button type="button" class="pw-toggle" id="togglePassword" aria-label="Show or hide password">
                                    <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            <div class="password-hint">
                                Tip: make it at least 8 characters and avoid common words.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="confirm_password">Confirm Password</label>
                            <div class="field-wrap">
                                <i class="fas fa-lock field-icon"></i>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control has-toggle" required
                                       placeholder="Re-enter password" autocomplete="new-password">
                                <button type="button" class="pw-toggle" id="toggleConfirmPassword" aria-label="Show or hide confirm password">
                                    <i class="fas fa-eye" id="toggleConfirmPasswordIcon"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-register w-100" id="registerBtn">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </button>
                    </form>

                    <div class="auth-divider my-4">OR CONTINUE WITH</div>

                    <button type="button" class="btn btn-google w-100"
                            onclick="alert('Google Sign-Up is not yet configured.\nPlease use the registration form for now.')">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20" height="20" aria-hidden="true" style="flex-shrink:0;">
                            <path fill="#4285F4" d="M23.49 12.27c0-.98-.09-1.93-.25-2.84H12v5.38h6.47a5.52 5.52 0 0 1-2.39 3.61v3h3.86c2.26-2.08 3.55-5.15 3.55-9.15z"/>
                            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.07 7.93-2.91l-3.86-3c-1.07.72-2.44 1.14-4.07 1.14-3.13 0-5.78-2.11-6.73-4.96H1.3v3.1A11.99 11.99 0 0 0 12 24z"/>
                            <path fill="#FBBC05" d="M5.27 14.27A7.2 7.2 0 0 1 4.9 12c0-.79.14-1.56.37-2.28V6.62H1.3A11.99 11.99 0 0 0 0 12c0 1.93.46 3.75 1.3 5.38l3.97-3.11z"/>
                            <path fill="#EA4335" d="M12 4.76c1.77 0 3.35.6 4.6 1.8l3.44-3.44C17.95 1.19 15.24 0 12 0A12 12 0 0 0 1.3 6.62l3.97 3.1C6.22 6.87 8.87 4.76 12 4.76z"/>
                        </svg>
                        Continue with Google
                    </button>
                    <?php endif; ?>
                </div>

                <div class="auth-footer">
                    Already have an account? <a href="login.php">Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    // Enable tooltip for strong password hint
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el);
    });

    // Password visibility toggles
    function wirePasswordToggle(inputId, buttonId, iconId) {
        const input = document.getElementById(inputId);
        const btn   = document.getElementById(buttonId);
        const icon  = document.getElementById(iconId);
        if (!input || !btn || !icon) return;

        btn.addEventListener('click', function () {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
        });
    }

    wirePasswordToggle('password', 'togglePassword', 'togglePasswordIcon');
    wirePasswordToggle('confirm_password', 'toggleConfirmPassword', 'toggleConfirmPasswordIcon');

    // Prevent accidental double-submit
    document.getElementById('registerForm')?.addEventListener('submit', function () {
        const btn = document.getElementById('registerBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating account...';
        }
    });
</script>
</body>
</html>