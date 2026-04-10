<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$token        = trim($_GET['token'] ?? '');
$message      = '';
$message_type = 'danger';
$valid_token  = false;
$token_email  = '';

// ── Validate token from URL or POST ───────────────────────────────────────────
function load_token(PDO $conn, string $token): ?array {
    if (strlen($token) !== 64 || !ctype_xdigit($token)) {
        return null;
    }
    $stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
    $stmt->execute([$token]);
    $row = $stmt->fetch();
    if (!$row) return null;
    if (strtotime($row['expires_at']) < time()) {
        // Expired — clean it up
        $del = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
        $del->execute([$token]);
        return null;
    }
    return $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token       = trim($_POST['token'] ?? '');
    $password    = $_POST['password'] ?? '';
    $confirm     = $_POST['confirm_password'] ?? '';

    $row = load_token($conn, $token);
    if (!$row) {
        $message = "This reset link is invalid or has expired. Please request a new one.";
    } elseif (strlen($password) < 8) {
        $message     = "Password must be at least 8 characters.";
        $valid_token = true;
        $token_email = $row['email'];
    } elseif ($password !== $confirm) {
        $message     = "Passwords do not match.";
        $valid_token = true;
        $token_email = $row['email'];
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Try users table first, then administrators
        $upd = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $upd->execute([$hashed, $row['email']]);

        if ($upd->rowCount() === 0) {
            $upd2 = $conn->prepare("UPDATE administrators SET password = ? WHERE email = ?");
            $upd2->execute([$hashed, $row['email']]);
        }

        // Delete used token
        $del = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
        $del->execute([$token]);

        // Redirect with success flag so login page shows message
        header('Location: login.php?reset=1');
        exit;
    }
} else {
    // GET request — just validate the token
    $row = load_token($conn, $token);
    if (!$row) {
        $message = "This reset link is invalid or has expired. Please request a new one.";
    } else {
        $valid_token = true;
        $token_email = $row['email'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Lost &amp; Found — Kyambogo University</title>
    <link rel="icon" type="image/svg+xml" sizes="any" href="assets/images/favicon.svg?v=20260317">
    <link rel="icon" type="image/png" sizes="96x96" href="assets/images/favicon-96x96.png?v=20260317">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/apple-touch-icon.png?v=20260317">
    <link rel="manifest" href="assets/images/site.webmanifest?v=20260317">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/local-icons.css" rel="stylesheet">
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
            to   { opacity: 1; transform: translateY(0)    scale(1); }
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
            border-radius: 12px; font-size: 0.86rem; padding: 0.8rem 1rem;
            border: 1px solid transparent; margin-bottom: 1.2rem;
            display: flex; align-items: flex-start; gap: 0.5rem;
            border-left-width: 4px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }
        .auth-alert.alert-danger  { background: #fff5f5; color: #b42318; border-color: rgba(180, 35, 24, 0.18); }
        .auth-alert.alert-success { background: #f0fdf4; color: #166534; border-color: rgba(22, 101, 52, 0.16); }
        .auth-alert.alert-info    { background: #eff6ff; color: #1d4ed8; border-color: rgba(29, 78, 216, 0.16); }
        .auth-form-summary { display: none; margin-bottom: 1rem; }
        .auth-inline-feedback {
            display: none;
            margin-top: 0.45rem;
            font-size: 0.8rem;
            color: #b42318;
            font-weight: 600;
        }
        .auth-inline-feedback.show { display: block; }
        .field-wrap .form-control.is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 3px rgba(220,53,69,0.12) !important;
            background: #fff;
        }

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
        .field-wrap .form-control.has-toggle { padding-right: 2.8rem; }

        .pw-toggle {
            position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: #94a3b8;
            cursor: pointer; padding: 0.2rem; z-index: 5; font-size: 0.88rem; transition: color 0.2s;
        }
        .pw-toggle:hover { color: #0d6efd; }

        /* Password strength bar */
        .strength-bar-wrap { height: 4px; background: #e2e8f0; border-radius: 4px; margin-top: 8px; overflow: hidden; }
        .strength-bar { height: 100%; width: 0%; border-radius: 4px; transition: width 0.3s, background 0.3s; }

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

            <?php if (!$valid_token): ?>
                <!-- Invalid / expired token -->
                <p class="auth-subtitle">Link Invalid or Expired</p>
                <div class="auth-alert alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle mt-1"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
                <a href="forgot-password.php" class="btn btn-primary-action w-100">
                    <i class="fas fa-redo me-2"></i>Request a New Link
                </a>

            <?php else: ?>
                <!-- Valid token — show new password form -->
                <p class="auth-subtitle">Set a New Password</p>
                <p class="auth-hint">Choose a strong password for <strong><?php echo htmlspecialchars($token_email); ?></strong>.</p>

                <?php if ($message): ?>
                    <div class="auth-alert alert alert-<?php echo htmlspecialchars($message_type); ?>" role="alert">
                        <i class="fas fa-exclamation-circle mt-1"></i>
                        <span><?php echo htmlspecialchars($message); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" novalidate id="resetForm">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <!-- New password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <div class="field-wrap">
                            <i class="fas fa-lock field-icon"></i>
                            <input type="password" id="password" name="password"
                                   class="form-control has-toggle"
                                   placeholder="At least 8 characters"
                                   autocomplete="new-password" required>
                            <button type="button" class="pw-toggle" id="pw1Toggle" aria-label="Show/hide password">
                                <i class="fas fa-eye" id="pw1Icon"></i>
                            </button>
                        </div>
                        <div class="strength-bar-wrap mt-2">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                        <small id="strengthLabel" class="text-muted" style="font-size:0.75rem;"></small>
                    </div>

                    <!-- Confirm password -->
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <div class="field-wrap">
                            <i class="fas fa-lock field-icon"></i>
                            <input type="password" id="confirm_password" name="confirm_password"
                                   class="form-control has-toggle"
                                   placeholder="Repeat your new password"
                                   autocomplete="new-password" required>
                            <button type="button" class="pw-toggle" id="pw2Toggle" aria-label="Show/hide password">
                                <i class="fas fa-eye" id="pw2Icon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-action w-100" id="submitBtn">
                        <i class="fas fa-key me-2"></i>Reset Password
                    </button>
                </form>
            <?php endif; ?>

        </div>

        <div class="auth-footer">
            <a href="login.php"><i class="fas fa-arrow-left me-1"></i>Back to Sign In</a>
        </div>

    </div>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/auth-feedback.js"></script>
    <script>
        // Password toggle helpers
        function makeToggle(btnId, iconId, inputId) {
            const btn   = document.getElementById(btnId);
            const icon  = document.getElementById(iconId);
            const input = document.getElementById(inputId);
            if (!btn) return;
            btn.addEventListener('click', function () {
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                icon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
            });
        }
        makeToggle('pw1Toggle', 'pw1Icon', 'password');
        makeToggle('pw2Toggle', 'pw2Icon', 'confirm_password');

        // Strength meter
        const pwInput      = document.getElementById('password');
        const strengthBar  = document.getElementById('strengthBar');
        const strengthLbl  = document.getElementById('strengthLabel');
        if (pwInput) {
            pwInput.addEventListener('input', function () {
                const val = this.value;
                let score = 0;
                if (val.length >= 8)  score++;
                if (/[A-Z]/.test(val)) score++;
                if (/[0-9]/.test(val)) score++;
                if (/[^A-Za-z0-9]/.test(val)) score++;
                const colors = ['#ef4444','#f97316','#eab308','#22c55e'];
                const labels = ['Weak','Fair','Good','Strong'];
                const widths = ['25%','50%','75%','100%'];
                if (val.length === 0) {
                    strengthBar.style.width = '0%';
                    strengthLbl.textContent = '';
                } else {
                    const i = Math.max(0, score - 1);
                    strengthBar.style.width      = widths[i];
                    strengthBar.style.background = colors[i];
                    strengthLbl.textContent      = labels[i];
                    strengthLbl.style.color      = colors[i];
                }
            });
        }

        AuthFeedback.attachValidation({
            formId: 'resetForm',
            summaryMessage: 'Please fix the highlighted password fields and try again.',
            rules: [
                {
                    field: 'password',
                    test: value => value !== '',
                    message: 'New password is required.'
                },
                {
                    field: 'password',
                    test: value => value.length >= 8,
                    message: 'Password must be at least 8 characters long.'
                },
                {
                    field: 'confirm_password',
                    test: value => value !== '',
                    message: 'Please confirm your new password.'
                },
                {
                    field: 'confirm_password',
                    test: value => value === document.getElementById('password').value,
                    message: 'Passwords do not match.'
                }
            ],
            onValidSubmit: function () {
                const btn = document.getElementById('submitBtn');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving…';
                }
            }
        });
    </script>
</body>
</html>
