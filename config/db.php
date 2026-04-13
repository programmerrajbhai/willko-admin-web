<?php
// File: config/db.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = "localhost";
$username = "u479142652_willko_dbuser";
$password = "Rajbhai@101";
$dbname = "u479142652_willko_dbname";

// ডাটাবেস কানেকশন
$conn = new mysqli($servername, $username, $password, $dbname);

// 🔥 FIX 5: Error Leakage বন্ধ করা
if ($conn->connect_error) {
    // এররটি একটি লগে সেভ করুন (ঐচ্ছিক)
    error_log("Database Connection Error: " . $conn->connect_error);
    // ইউজারকে সাধারণ মেসেজ দেখান
    die("Something went wrong. Please try again later.");
}

$conn->set_charset("utf8");

// 🔥 FIX 5: CSRF Token তৈরি করা (ইউনিক টোকেন)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// CSRF ভেরিফিকেশন ফাংশন
function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>