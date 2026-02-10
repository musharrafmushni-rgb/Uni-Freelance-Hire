<?php
include 'includes/config.php';
include 'includes/functions.php';
requireRole('client');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $job_id = intval($_POST['job_id']);
    $student_id = intval($_POST['student_id']);
    $client_id = intval($_POST['client_id']);

    // Start Transaction
    mysqli_begin_transaction($conn);

    try {
        // 1. Create Project
        $proj_sql = "INSERT INTO projects (job_id, student_id, client_id, status) VALUES ('$job_id', '$student_id', '$client_id', 'assigned')";
        mysqli_query($conn, $proj_sql);

        // 2. Update Job Status
        $job_update = "UPDATE jobs SET status = 'assigned' WHERE id = '$job_id'";
        mysqli_query($conn, $job_update);

        // 3. Update Bid Status
        $bid_update = "UPDATE bids SET status = 'accepted' WHERE job_id = '$job_id' AND student_id = '$student_id'";
        mysqli_query($conn, $bid_update);

        // Notify Student
        include_once 'includes/notification_helper.php';
        sendNotification($conn, $student_id, "Congratulations! You have been hired for job ID: #$job_id");
        
        // Reject others
        //$reject_sql = "UPDATE bids SET status = 'rejected' WHERE job_id = '$job_id' AND student_id != '$student_id'";
        //mysqli_query($conn, $reject_sql);

        mysqli_commit($conn);
        echo "<script>alert('Project Assigned Successfully!'); window.location.href='client_projects.php';</script>";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        die("Error assigning project: " . $e->getMessage());
    }
} else {
    redirect('manage_jobs.php');
}
?>
