<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        die("Access Denied: You do not have permission to view this page.");
    }
}

function sanitize($conn, $input) {
    return mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($input))));
}

function redirect($url) {
    header("Location: " . $url);
    exit;
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}
?>
