<?php
include_once __DIR__ . '/config.php';
include_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniFreelance - Student Freelancing Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Notification Styles */
        .notification-wrapper { position: relative; display: inline-block; margin-left: 20px; cursor: pointer; }
        .notification-bell { color: white; font-size: 1.2rem; }
        .badge-count { 
            position: absolute; top: -5px; right: -8px; 
            background: #dc3545; color: white; font-size: 0.7rem; 
            padding: 2px 6px; border-radius: 50%; display: none; 
        }
        .notification-dropdown {
            position: absolute; right: 0; top: 40px; width: 300px;
            background: white; border: 1px solid #ddd; border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: none; z-index: 1000;
        }
        .notification-dropdown.show { display: block; }
        .notification-header { padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; color: #333; }
        .notification-list { list-style: none; padding: 0; margin: 0; max-height: 300px; overflow-y: auto; }
        .notification-list li { padding: 10px; border-bottom: 1px solid #eee; cursor: pointer; }
        .notification-list li:hover { background: #f9f9f9; }
        .notification-list li.unread { background: #e3f2fd; }
        .notification-list li .message { font-size: 0.9rem; color: #333; }
        .notification-list li small { color: #888; font-size: 0.8rem; }
    </style>
</head>
<body>

<header>
    <div class="container navbar">
        <a href="index.php" class="logo">UniFreelance Hire</a>
        <nav class="nav-links">
            <?php if (isLoggedIn()): ?>
                <a href="wallet.php">My Wallet</a>
                <?php if ($_SESSION['role'] == 'student'): ?>
                    <a href="jobs.php">Browse Jobs</a>
                    <a href="my_bids.php">My Bids</a>
                    <a href="my_projects_student.php">My Projects</a>
                    <a href="student_profile.php">My Profile</a>
                <?php elseif ($_SESSION['role'] == 'client'): ?>
                    <a href="post_job.php">Post Job</a>
                    <a href="manage_jobs.php">My Jobs</a>
                    <a href="client_projects.php">Hired Projects</a>
                <?php elseif ($_SESSION['role'] == 'admin'): ?>
                    <a href="admin_dashboard.php">Dashboard</a>
                <?php else: ?>
                    <a href="support_chat.php">Support</a>
                <?php endif; ?>
                
                <!-- Notification Bell -->
                <div class="notification-wrapper" id="notification-bell">
                    <span class="notification-bell">🔔</span>
                    <span class="badge-count" id="notification-count">0</span>
                    <div class="notification-dropdown" id="notification-dropdown">
                        <div class="notification-header">Notifications</div>
                        <ul class="notification-list" id="notification-list">
                            <li style="padding:10px; text-align:center;">Loading...</li>
                        </ul>
                    </div>
                </div>

                <a href="profile_settings.php">Settings</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="index.php">Home</a>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<div class="container main-content" style="min-height: 80vh; padding-top: 20px;">
<script src="assets/js/notifications.js"></script>
<script src="assets/js/global.js"></script>
