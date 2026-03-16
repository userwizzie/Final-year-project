<?php
// Database configuration
$host     = "localhost";
$dbname   = "lost_found";          // ← must match what you created in phpMyAdmin
$username = "root";                 // default XAMPP
$password = "";                     // default XAMPP (empty)

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Set PDO error mode to exception (very helpful during development)
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Optional: better defaults
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In production you'd log this, but for development show error
    die("Connection failed: " . $e->getMessage());
}

// Start session (needed for login/logout later)
session_start();

// Prevent caching of authenticated pages so Back button does not reveal stale content.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

// Keep admin portal independent from user pages.
$current_script = str_replace('\\', '/', $_SERVER['PHP_SELF'] ?? '');
$is_admin_route = strpos($current_script, '/admin/') !== false;
$script_name = basename($current_script);
$is_admin_session = isset($_SESSION['user_id']) && (($_SESSION['role'] ?? '') === 'admin');

if ($is_admin_session && !$is_admin_route && $script_name !== 'logout.php') {
    header('Location: admin/dashboard.php');
    exit;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin_user() {
    return is_logged_in() && (($_SESSION['role'] ?? '') === 'admin');
}

function require_admin() {
    if (!is_admin_user()) {
        header('Location: ../login.php');
        exit;
    }
}

function require_user() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }

    // Keep admin and user portals completely separate.
    if (is_admin_user()) {
        header('Location: admin/dashboard.php');
        exit;
    }
}
?>