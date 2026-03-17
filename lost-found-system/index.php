<?php
require_once 'includes/config.php';

$is_guest_view = !is_logged_in();
$logged_out = isset($_GET['logged_out']);
$is_logout_view = $logged_out && $is_guest_view;
$minimal_header_view = $is_logout_view;

$page_title = 'Home';
require_once 'includes/header.php';
?>

<style>
    .top-popup-alert {
        position: fixed;
        top: 1rem;
        left: 50%;
        transform: translate(-50%, -140%);
        z-index: 1080;
        width: min(92vw, 760px);
        border-radius: 1rem;
        border: none;
        box-shadow: 0 14px 36px rgba(13, 110, 253, 0.2);
        animation: popupDropIn 0.55s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }

    @keyframes popupDropIn {
        from {
            transform: translate(-50%, -150%);
            opacity: 0;
        }
        to {
            transform: translate(-50%, 0);
            opacity: 1;
        }
    }

    .guest-split {
        border-radius: 1.5rem;
        overflow: hidden;
        box-shadow: 0 26px 56px rgba(5, 28, 74, 0.22);
        border: 1px solid rgba(13,110,253,0.1);
        background: #fff;
    }

    .guest-showcase {
        padding: 3rem 2.2rem;
        color: #fff;
        background:
            radial-gradient(circle at 15% 20%, rgba(255,255,255,0.18), transparent 30%),
            radial-gradient(circle at 80% 80%, rgba(255,255,255,0.14), transparent 35%),
            linear-gradient(135deg, #061b47 0%, #0a3580 50%, #0d6efd 100%);
    }

    .guest-pill {
        display: inline-block;
        border-radius: 999px;
        padding: 0.38rem 0.85rem;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-weight: 700;
        background: rgba(255,255,255,0.16);
        border: 1px solid rgba(255,255,255,0.3);
        margin-bottom: 1rem;
    }

    .guest-mini {
        margin-top: 1.3rem;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.65rem;
    }

    .guest-mini div {
        background: rgba(255,255,255,0.13);
        border: 1px solid rgba(255,255,255,0.22);
        border-radius: 0.8rem;
        padding: 0.75rem 0.7rem;
        text-align: center;
    }

    .guest-mini strong {
        display: block;
        font-size: 1.05rem;
        line-height: 1.1;
    }

    .guest-mini span {
        font-size: 0.78rem;
        opacity: 0.9;
    }

    .guest-login-wrap {
        background: linear-gradient(180deg, #ffffff 0%, #f7faff 100%);
        padding: 1.8rem;
        display: flex;
        align-items: center;
    }

    .guest-login-card {
        width: 100%;
        border-radius: 1.05rem;
        border: 1px solid rgba(13,110,253,0.14);
        background: #fff;
        padding: 1.45rem;
        box-shadow: 0 10px 26px rgba(13,110,253,0.08);
    }

    .guest-login-card .form-control {
        border-radius: 0.7rem;
        border: 1.5px solid #dbe7f6;
        padding: 0.68rem 0.78rem;
    }

    .guest-login-card .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13,110,253,0.12);
    }

    .guest-divider {
        text-align: center;
        font-size: 0.8rem;
        color: #6b7f9f;
        margin: 0.9rem 0;
    }

    .logout-home-panel {
        min-height: 100%;
    }

    .logout-login-panel {
        border-radius: 1rem;
        border: 1px solid rgba(13,110,253,0.14);
        background: #fff;
        padding: 1.15rem;
        box-shadow: 0 10px 26px rgba(13,110,253,0.08);
    }

    .logout-login-panel .form-control {
        border-radius: 0.7rem;
        border: 1.5px solid #dbe7f6;
        padding: 0.62rem 0.74rem;
        font-size: 0.9rem;
    }

    .logout-login-panel .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13,110,253,0.12);
    }

    /* ─── Hero Section ─── */
    .home-hero {
        border-radius: 1.5rem;
        background: linear-gradient(135deg, #081f4d 0%, #0d6efd 100%);
        color: #fff;
        padding: 3rem 2rem;
        box-shadow: 0 24px 48px rgba(5, 28, 74, 0.35);
        position: relative;
        overflow: hidden;
    }

    /* Hero background patterns */
    .home-hero::before {
        content: '';
        position: absolute;
        width: 400px;
        height: 400px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
        left: -150px;
        bottom: -100px;
        filter: blur(40px);
        z-index: 0;
    }

    .home-hero::after {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background: rgba(88,166,255,0.06);
        right: -80px;
        top: -60px;
        filter: blur(50px);
        z-index: 0;
    }

    /* Feature Cards */
    .feature-card {
        border: 0;
        border-radius: 1.1rem;
        box-shadow: 0 8px 20px rgba(13, 110, 253, 0.06);
        height: 100%;
        transition: transform 0.3s cubic-bezier(0.23, 1, 0.320, 1), 
                    box-shadow 0.3s cubic-bezier(0.23, 1, 0.320, 1),
                    border-color 0.3s ease;
        border: 1px solid transparent;
    }

    .feature-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px rgba(13, 110, 253, 0.15);
        border-color: rgba(13, 110, 253, 0.1);
    }

    .feature-icon {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.5rem;
        background: linear-gradient(135deg, rgba(13,110,253,0.12), rgba(88,166,255,0.08));
        color: #0d6efd;
        transition: transform 0.3s ease, background 0.3s ease;
    }

    .feature-card:hover .feature-icon {
        transform: scale(1.08) rotate(5deg);
        background: linear-gradient(135deg, rgba(13,110,253,0.18), rgba(88,166,255,0.12));
    }

    /* CTA Buttons */
    .btn-hero {
        font-weight: 600;
        padding: 0.6rem 1.3rem;
        border-radius: 0.75rem;
        transition: all 0.3s cubic-bezier(0.23, 1, 0.320, 1);
        box-shadow: 0 6px 16px rgba(13, 110, 253, 0.25);
    }

    .btn-hero:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(13, 110, 253, 0.35);
    }

    .btn-hero.btn-light {
        background: #fff;
        color: #0d6efd;
        border: 1px solid #fff;
    }

    .btn-hero.btn-light:hover {
        background: #f0f6ff;
        color: #081f4d;
        border-color: #f0f6ff;
    }

    .btn-hero.btn-outline-light {
        color: #fff;
        border: 1.5px solid rgba(255,255,255,0.7);
    }

    .btn-hero.btn-outline-light:hover {
        background: rgba(255,255,255,0.12);
        border-color: #fff;
        color: #fff;
    }

    /* CTA Strip */
    .cta-strip {
        border-radius: 1.1rem;
        background: linear-gradient(135deg, #fff 0%, #f8fbff 100%);
        border: 1px solid rgba(13,110,253,0.08);
        box-shadow: 0 12px 32px rgba(13, 110, 253, 0.1);
        padding: 1.8rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .cta-strip:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 40px rgba(13, 110, 253, 0.15);
    }

    /* Stats Section */
    .stats-section {
        background: linear-gradient(135deg, #f8fbff 0%, #f0f6ff 100%);
        border-radius: 1.5rem;
        padding: 3rem 2rem;
        margin: 3rem 0;
        border: 1px solid rgba(13,110,253,0.08);
    }

    .stat-card {
        text-align: center;
        padding: 1.5rem;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #0d6efd;
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        font-size: 0.95rem;
        color: #47648f;
        font-weight: 500;
        letter-spacing: 0.3px;
    }

    /* Hero content z-index fix */
    .home-hero > div {
        position: relative;
        z-index: 1;
    }

    @media (max-width: 768px) {
        .top-popup-alert {
            top: 0.75rem;
            width: calc(100vw - 1rem);
        }

        .guest-showcase,
        .guest-login-wrap {
            padding: 1.5rem;
        }

        .guest-mini {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .logout-login-panel {
            padding: 1.1rem;
        }

        .home-hero {
            padding: 2rem 1.5rem;
        }

        .stat-number {
            font-size: 2rem;
        }
    }

    /* Float animation for hero icon */
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
    }
</style>

<?php if ($logged_out): ?>
    <div class="alert alert-info alert-dismissible fade show top-popup-alert" role="alert">
        <i class="fas fa-check-circle me-2"></i><strong>Signed out.</strong> You can log in again anytime.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($is_logout_view): ?>
    <div class="row g-4 mb-5 align-items-start">
        <div class="col-lg-8">
            <div class="home-hero logout-home-panel">
                <div class="row align-items-center g-4">
                    <div class="col-12">
                        <h1 class="display-5 fw-bold mb-3">Welcome to Lost &amp; Found KyU</h1>
                        <p class="lead mb-4 opacity-85">Report lost or found items, search reports quickly, and reunite belongings with confidence. Kyambogo University's trusted platform for community support.</p>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="report-lost.php" class="btn btn-light btn-hero"><i class="fas fa-exclamation-triangle me-2"></i>I Lost Something</a>
                            <a href="report-found.php" class="btn btn-outline-light btn-hero"><i class="fas fa-hand-holding-heart me-2"></i>I Found Something</a>
                            <a href="search.php" class="btn btn-outline-light btn-hero"><i class="fas fa-search me-2"></i>Search Items</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="logout-login-panel">
                <h5 class="fw-bold mb-1">Log in</h5>
                <p class="text-muted small mb-3">Continue with your account.</p>
                <form method="POST" action="login.php" class="row g-3" novalidate>
                    <div class="col-12">
                        <label for="logoutLoginEmail" class="form-label small fw-semibold text-muted">Email</label>
                        <input type="email" class="form-control" id="logoutLoginEmail" name="email" placeholder="you@kyu.ac.ug" autocomplete="email" required>
                    </div>
                    <div class="col-12">
                        <label for="logoutLoginPassword" class="form-label small fw-semibold text-muted">Password</label>
                        <input type="password" class="form-control" id="logoutLoginPassword" name="password" placeholder="Enter password" autocomplete="current-password" required>
                    </div>
                    <div class="col-12 d-grid">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-right-to-bracket me-2"></i>Log In</button>
                    </div>
                </form>
                <div class="guest-divider">or</div>
                <div class="d-grid gap-2">
                    <a href="register.php" class="btn btn-outline-primary btn-sm">Create a new account</a>
                    <a href="forgot-password.php" class="text-decoration-none small text-center">Forgot your password?</a>
                </div>
            </div>
        </div>
    </div>
<?php elseif ($is_guest_view): ?>
    <div class="guest-split mb-5">
        <div class="row g-0">
            <div class="col-lg-7">
                <div class="guest-showcase h-100">
                    <span class="guest-pill">KyU Lost and Found</span>
                    <h1 class="display-5 fw-bold mb-3">Find lost items faster, all in one place.</h1>
                    <p class="lead mb-4 opacity-85">Track reports, post found belongings, and reconnect owners with confidence. This is your campus feed for things that matter.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="search.php" class="btn btn-light btn-hero"><i class="fas fa-search me-2"></i>Explore Items</a>
                        <a href="register.php" class="btn btn-outline-light btn-hero"><i class="fas fa-user-plus me-2"></i>Create Account</a>
                        <a href="report-found.php" class="btn btn-outline-light btn-hero"><i class="fas fa-hand-holding-heart me-2"></i>Report Found</a>
                    </div>
                    <div class="guest-mini">
                        <div>
                            <strong>2.4K+</strong>
                            <span>Recovered</span>
                        </div>
                        <div>
                            <strong>1.8K+</strong>
                            <span>Members</span>
                        </div>
                        <div>
                            <strong>98%</strong>
                            <span>Success</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="guest-login-wrap h-100">
                    <div class="guest-login-card">
                        <h4 class="fw-bold mb-1">Welcome back</h4>
                        <p class="text-muted small mb-3">Log in to manage reports and claims.</p>
                        <form method="POST" action="login.php" class="row g-3" novalidate>
                            <div class="col-12">
                                <label for="homeLoginEmail" class="form-label small fw-semibold text-muted">Email</label>
                                <input type="email" class="form-control" id="homeLoginEmail" name="email" placeholder="you@kyu.ac.ug" autocomplete="email" required>
                            </div>
                            <div class="col-12">
                                <label for="homeLoginPassword" class="form-label small fw-semibold text-muted">Password</label>
                                <input type="password" class="form-control" id="homeLoginPassword" name="password" placeholder="Enter password" autocomplete="current-password" required>
                            </div>
                            <div class="col-12 d-grid">
                                <button type="submit" class="btn btn-primary btn-hero"><i class="fas fa-right-to-bracket me-2"></i>Log In</button>
                            </div>
                        </form>
                        <div class="guest-divider">or</div>
                        <div class="d-grid gap-2">
                            <a href="register.php" class="btn btn-outline-primary btn-sm">Create a new account</a>
                            <a href="forgot-password.php" class="text-decoration-none small text-center">Forgot your password?</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="home-hero mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold mb-3">Welcome to Lost &amp; Found KyU</h1>
                <p class="lead mb-4 opacity-85">Report lost or found items, search reports quickly, and reunite belongings with confidence. Kyambogo University's trusted platform for community support.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="report-lost.php" class="btn btn-light btn-hero"><i class="fas fa-exclamation-triangle me-2"></i>I Lost Something</a>
                    <a href="report-found.php" class="btn btn-outline-light btn-hero"><i class="fas fa-hand-holding-heart me-2"></i>I Found Something</a>
                    <a href="search.php" class="btn btn-outline-light btn-hero"><i class="fas fa-search me-2"></i>Search Items</a>
                </div>
            </div>
            <div class="col-lg-4 text-lg-center d-none d-lg-block">
                <div style="font-size:6rem; opacity:0.25; animation: float 3s ease-in-out infinite;">
                    <i class="fas fa-university"></i>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card feature-card p-4">
            <div class="feature-icon"><i class="fas fa-bolt"></i></div>
            <h5 class="fw-semibold mb-2">Search Fast</h5>
            <p class="text-muted mb-0">Find lost and found items instantly using smart filters, categories, and keywords across the entire community network.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card feature-card p-4">
            <div class="feature-icon"><i class="fas fa-pen-to-square"></i></div>
            <h5 class="fw-semibold mb-2">Report Easily</h5>
            <p class="text-muted mb-0">Submit detailed reports with photos, descriptions, and metadata in just a few simple steps. Real-time notifications keep you updated.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card feature-card p-4">
            <div class="feature-icon"><i class="fas fa-certificate"></i></div>
            <h5 class="fw-semibold mb-2">Verified &amp; Secure</h5>
            <p class="text-muted mb-0">Admin-verified claims ensure items reach rightful owners. Reward system motivates finders to return valuable belongings.</p>
        </div>
    </div>
</div>

<div class="cta-strip d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4 mb-5">
    <div>
        <h5 class="mb-1 fw-bold">Ready to recover your item?</h5>
        <p class="text-muted mb-0">Join thousands of KyU students and staff. Create an account to track reports, submit claims, and receive instant updates.</p>
    </div>
    <div class="d-flex flex-wrap gap-2 flex-shrink-0">
        <a href="register.php" class="btn btn-primary btn-hero"><i class="fas fa-user-plus me-2"></i>Register Now</a>
        <a href="login.php" class="btn btn-outline-primary btn-hero"><i class="fas fa-right-to-bracket me-2"></i>Login</a>
    </div>
</div>

<!-- Trust Signals Section -->
<div class="stats-section">
    <div class="text-center mb-4">
        <h3 class="fw-bold mb-2">Trusted by the KyU Community</h3>
        <p class="text-muted">Helping students and staff reunite with their lost belongings every day.</p>
    </div>
    <div class="row g-4">
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-number">2.4K+</div>
                <div class="stat-label">Items Recovered</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-number">1.8K+</div>
                <div class="stat-label">Active Members</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-number">98%</div>
                <div class="stat-label">Claim Success Rate</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-number">247K</div>
                <div class="stat-label">Reward Points Earned</div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
