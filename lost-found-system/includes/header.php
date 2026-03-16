<?php
// Enhanced header include for Lost & Found - Kyambogo University
// Note: session_start() is called in config.php, so we don't need it here

$is_admin_page = strpos($_SERVER['PHP_SELF'] ?? '', '/admin/') !== false;
$base_path = $base_path ?? ($is_admin_page ? '../' : '');
$is_logged_in = isset($_SESSION['user_id']);
$is_admin_user = $is_logged_in && (($_SESSION['role'] ?? '') === 'admin');

$admin_dashboard_link = $is_admin_page ? 'dashboard.php' : 'admin/dashboard.php';
$admin_verify_link = $is_admin_page ? 'verify-claims.php' : 'admin/verify-claims.php';
$admin_reports_link = $is_admin_page ? 'reports.php' : 'admin/reports.php';
$admin_items_link = $is_admin_page ? 'view-items.php' : 'admin/view-items.php';
$admin_users_link = $is_admin_page ? 'manage-users.php' : 'admin/manage-users.php';
$current_page     = basename($_SERVER['PHP_SELF'] ?? '');
$show_quick_links = $is_logged_in && $current_page !== 'index.php';
$favicon_version  = file_exists(__DIR__ . '/../assets/images/favicon.svg')
    ? filemtime(__DIR__ . '/../assets/images/favicon.svg')
    : time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Lost & Found - Kyambogo University</title>
    <meta name="description" content="Kyambogo University Lost and Found Management System - Report lost items, claim found items, and help reunite belongings.">
    <meta name="theme-color" content="#0d6efd">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" sizes="any" href="<?php echo $base_path; ?>assets/images/favicon.svg?v=<?php echo $favicon_version; ?>">
    <link rel="icon" type="image/png" sizes="96x96" href="<?php echo $base_path; ?>assets/images/favicon-96x96.png?v=<?php echo $favicon_version; ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $base_path; ?>assets/images/apple-touch-icon.png?v=<?php echo $favicon_version; ?>">
    <link rel="manifest" href="<?php echo $base_path; ?>assets/images/site.webmanifest?v=<?php echo $favicon_version; ?>">

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap 5 CSS -->
    <link href="<?php echo $base_path; ?>assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Shared project styles -->
    <link href="<?php echo $base_path; ?>assets/css/style.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #0d6efd;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #0dcaf0;
            --light-bg: #f5f7fa;
            --shadow: 0 2px 10px rgba(0,0,0,0.1);
            --border-radius: 8px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(circle at 0% 0%, rgba(13,110,253,0.14), transparent 36%),
                radial-gradient(circle at 100% 100%, rgba(13,110,253,0.08), transparent 40%),
                var(--light-bg);
            color: #333;
        }

        .navbar-brand {
            font-weight: 600;
        }

        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .btn {
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.23, 1, 0.320, 1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            border: none;
            box-shadow: 0 6px 16px rgba(13, 110, 253, 0.25);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(13, 110, 253, 0.35);
            background: linear-gradient(135deg, #0d6efd 0%, #084298 100%);
        }

        .btn-outline-primary {
            color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-outline-primary:hover {
            background: #0d6efd;
            border-color: #0d6efd;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(13, 110, 253, 0.25);
        }

        .quicklinks-trigger {
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.45);
            background: rgba(255,255,255,0.12);
            color: #fff;
            font-weight: 600;
            padding: 0.38rem 0.8rem;
        }

        .quicklinks-trigger:hover,
        .quicklinks-trigger:focus {
            color: #fff;
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.7);
        }

        .quicklinks-fab {
            position: fixed;
            right: 1rem;
            bottom: 1rem;
            z-index: 1040;
            border-radius: 999px;
            box-shadow: 0 12px 28px rgba(13, 110, 253, 0.35);
            padding: 0.65rem 0.95rem;
            font-weight: 600;
        }

        .quicklinks-panel {
            width: min(380px, 92vw);
            border-left: 1px solid rgba(13, 110, 253, 0.1);
            box-shadow: -18px 0 38px rgba(8, 16, 38, 0.25);
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .quicklinks-header {
            background: linear-gradient(130deg, #0a2b63 0%, #0d6efd 100%);
            color: #fff;
            padding: 1rem 1.05rem;
        }

        .quicklinks-header .btn-close {
            filter: invert(1) brightness(2);
            opacity: 0.9;
        }

        .quicklinks-list .nav-link {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            border-radius: 0.7rem;
            color: #20314d;
            font-weight: 500;
            padding: 0.72rem 0.8rem;
            margin-bottom: 0.35rem;
            transition: transform 0.15s, background-color 0.15s, color 0.15s;
        }

        .quicklinks-list .nav-link i {
            width: 18px;
            text-align: center;
            color: #47648f;
        }

        .quicklinks-list .nav-link:hover {
            background: rgba(13, 110, 253, 0.08);
            color: #0d6efd;
            transform: translateX(2px);
        }

        .quicklinks-list .nav-link.active {
            background: rgba(13, 110, 253, 0.14);
            color: #0d6efd;
            font-weight: 600;
        }

        .quicklinks-list .nav-link.active i {
            color: #0d6efd;
        }

        .offcanvas-backdrop.show {
            opacity: 0.55;
            backdrop-filter: blur(2px);
        }

        @media (max-width: 991.98px) {
            .quicklinks-panel {
                width: 100vw;
                max-width: 100vw;
                border-left: none;
            }
        }

        /* ── Branded navbar logo ── */
        .brand-badge {
            width: 38px; height: 38px;
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.35);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.05rem; flex-shrink: 0;
        }
        .brand-text small { font-size: 0.58em; letter-spacing: 0.07em; opacity: 0.85; }

        /* ── Active nav-link underline indicator ── */
        .navbar-dark .nav-link { 
            color: rgba(255,255,255,0.8) !important; 
            transition: color 0.3s ease, padding 0.3s ease;
            position: relative;
            padding-bottom: 0.5rem !important;
        }

        .navbar-dark .nav-link:hover { 
            color: #fff !important;
        }

        .navbar-dark .nav-link.active { 
            color: #fff !important; 
            font-weight: 600;
        }

        .navbar-dark .nav-link.active::after {
            content: ''; 
            display: block;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2.5px; 
            background: linear-gradient(90deg, rgba(255,255,255,0.9), rgba(255,255,255,0.6));
            border-radius: 2px 2px 0 0;
            animation: slideIn 0.3s cubic-bezier(0.23, 1, 0.320, 1);
        }

        @keyframes slideIn {
            from { width: 0; left: 50%; }
            to { width: 100%; left: 0; }
        }

        /* ── Sidebar collapse controls ── */
        .sidebar-header {
            display: flex; justify-content: space-between;
            align-items: center; margin-bottom: 0.75rem;
        }
        #sidebarToggleHide { padding: 0.2rem 0.45rem; font-size: 0.75rem; }
        #sidebarShowBtn {
            position: fixed; left: 0; top: 50%;
            transform: translateY(-50%);
            z-index: 1030;
            border-radius: 0 8px 8px 0;
            padding: 0.7rem 0.55rem;
            display: none;
            box-shadow: 2px 0 10px rgba(0,0,0,0.2);
            border-left: none;
        }
        @media (max-width: 991.98px) { #sidebarShowBtn { display: none !important; } }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
        <div class="container">
            <?php if ($is_admin_page && $is_admin_user): ?>
                <a class="navbar-brand d-flex align-items-center gap-2" href="dashboard.php">
                    <div class="brand-badge"><i class="fas fa-shield-alt"></i></div>
                    <div class="brand-text lh-1">
                        <span class="fw-bold d-block">Admin Panel</span>
                        <small>KYAMBOGO UNIVERSITY</small>
                    </div>
                </a>
            <?php else: ?>
                     <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo $base_path; ?><?php echo $is_logged_in ? 'dashboard.php' : 'index.php'; ?>">
                    <div class="brand-badge"><i class="fas fa-university"></i></div>
                    <div class="brand-text lh-1">
                        <span class="fw-bold d-block">Lost &amp; Found</span>
                        <small>KYAMBOGO UNIVERSITY</small>
                    </div>
                </a>
            <?php endif; ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    <?php if ($is_admin_page && $is_admin_user): ?>
                        <li class="nav-item"><a class="nav-link <?php echo $current_page==='dashboard.php'?'active':''; ?>" href="dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo $current_page==='verify-claims.php'?'active':''; ?>" href="verify-claims.php"><i class="fas fa-check-circle me-1"></i>Verify Claims</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo $current_page==='reports.php'?'active':''; ?>" href="reports.php"><i class="fas fa-chart-bar me-1"></i>Reports</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo $current_page==='view-items.php'?'active':''; ?>" href="view-items.php"><i class="fas fa-boxes me-1"></i>Items</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo $current_page==='manage-users.php'?'active':''; ?>" href="manage-users.php"><i class="fas fa-users me-1"></i>Users</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link <?php echo $current_page==='index.php'?'active':''; ?>" href="<?php echo $base_path; ?>index.php"><i class="fas fa-home me-1"></i>Home</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo $current_page==='search.php'?'active':''; ?>" href="<?php echo $base_path; ?>search.php"><i class="fas fa-search me-1"></i>Search</a></li>
                        <?php if ($is_logged_in): ?>
                            <li class="nav-item"><a class="nav-link <?php echo $current_page==='dashboard.php'?'active':''; ?>" href="<?php echo $base_path; ?>dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link <?php echo $current_page==='my-claims.php'?'active':''; ?>" href="<?php echo $base_path; ?>my-claims.php"><i class="fas fa-list me-1"></i>My Claims</a></li>
                            <?php if ($is_admin_user): ?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-cog"></i> Admin
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="<?php echo $admin_dashboard_link; ?>"><i class="fas fa-tachometer-alt"></i> Admin Dashboard</a></li>
                                        <li><a class="dropdown-item" href="<?php echo $admin_verify_link; ?>"><i class="fas fa-check-circle"></i> Verify Claims</a></li>
                                        <li><a class="dropdown-item" href="<?php echo $admin_reports_link; ?>"><i class="fas fa-chart-bar"></i> Reports</a></li>
                                        <li><a class="dropdown-item" href="<?php echo $admin_items_link; ?>"><i class="fas fa-boxes"></i> Manage Items</a></li>
                                        <li><a class="dropdown-item" href="<?php echo $admin_users_link; ?>"><i class="fas fa-users"></i> Manage Users</a></li>
                                    </ul>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <div class="d-flex align-items-center">
                    <?php if ($is_logged_in): ?>
                        <?php if ($show_quick_links): ?>
                            <button class="btn quicklinks-trigger btn-sm me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#quickLinksPanel" aria-controls="quickLinksPanel" aria-label="Open quick links menu">
                                <i class="fas fa-bars me-1"></i>Quick Links
                            </button>
                        <?php endif; ?>
                        <span class="text-light me-3">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>
                        </span>
                        <a href="<?php echo $base_path; ?>logout.php" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $base_path; ?>login.php" class="btn btn-outline-light btn-sm me-2">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="<?php echo $base_path; ?>register.php" class="btn btn-light btn-sm">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <?php if ($show_quick_links): ?>
        <button class="btn btn-primary quicklinks-fab d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#quickLinksPanel" aria-controls="quickLinksPanel" aria-label="Open quick links menu">
            <i class="fas fa-bars me-1"></i>Menu
        </button>

        <div class="offcanvas offcanvas-end quicklinks-panel" tabindex="-1" id="quickLinksPanel" aria-labelledby="quickLinksTitle" data-bs-scroll="false" data-bs-backdrop="true">
            <div class="quicklinks-header d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0" id="quickLinksTitle"><i class="fas fa-compass me-2"></i>Quick Links</h5>
                    <small class="opacity-75">Navigate faster</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <nav class="quicklinks-list nav flex-column" aria-label="Quick links navigation">
                    <?php if ($is_admin_page && $is_admin_user): ?>
                        <a class="nav-link <?php echo $current_page==='dashboard.php' ? 'active' : ''; ?>" href="dashboard.php"><i class="fas fa-home"></i>Dashboard</a>
                        <a class="nav-link <?php echo $current_page==='verify-claims.php' ? 'active' : ''; ?>" href="verify-claims.php"><i class="fas fa-check-circle"></i>Verify Claims</a>
                        <a class="nav-link <?php echo $current_page==='reports.php' ? 'active' : ''; ?>" href="reports.php"><i class="fas fa-chart-line"></i>Reports</a>
                        <a class="nav-link <?php echo $current_page==='view-items.php' ? 'active' : ''; ?>" href="view-items.php"><i class="fas fa-box-open"></i>Manage Items</a>
                        <a class="nav-link <?php echo $current_page==='manage-users.php' ? 'active' : ''; ?>" href="manage-users.php"><i class="fas fa-users"></i>Manage Users</a>
                    <?php else: ?>
                        <a class="nav-link <?php echo $current_page==='dashboard.php' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>dashboard.php"><i class="fas fa-home"></i>Dashboard</a>
                        <a class="nav-link <?php echo $current_page==='report-lost.php' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>report-lost.php"><i class="fas fa-exclamation-triangle"></i>Report Lost Item</a>
                        <a class="nav-link <?php echo $current_page==='report-found.php' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>report-found.php"><i class="fas fa-hand-holding-heart"></i>Report Found Item</a>
                        <a class="nav-link <?php echo $current_page==='search.php' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>search.php"><i class="fas fa-search"></i>Search Items</a>
                        <a class="nav-link <?php echo $current_page==='my-claims.php' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>my-claims.php"><i class="fas fa-clipboard-check"></i>My Claims</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    <?php endif; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">