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
?>