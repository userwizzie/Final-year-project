<?php
require_once 'includes/config.php';

$page_title = 'Home';
require_once 'includes/header.php';
?>

<style>
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
