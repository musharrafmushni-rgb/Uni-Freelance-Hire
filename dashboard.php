<?php
include 'includes/header.php';
requireLogin();

$role = $_SESSION['role'];

if ($role == 'student') {
    redirect('student_profile.php');
} elseif ($role == 'client') {
    redirect('manage_jobs.php');
} elseif ($role == 'admin') {
    redirect('admin_dashboard.php');
} else {
    redirect('index.php');
}
?>
