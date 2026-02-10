<?php
include 'includes/header.php';
requireRole('client');

$job_id = intval($_GET['job_id']);
$client_id = $_SESSION['user_id'];

// Handle Rejection
if (isset($_POST['reject_bid_id'])) {
    $bid_id = intval($_POST['reject_bid_id']);
    $student_id = intval($_POST['student_id']);
    
    if (mysqli_query($conn, "UPDATE bids SET status='rejected' WHERE id='$bid_id'")) {
        include_once 'includes/notification_helper.php';
        sendNotification($conn, $student_id, "Your bid for job: '{$job['title']}' was rejected by the client.");
        echo "<script>alert('Bid rejected.'); window.location.href='view_bids.php?job_id=$job_id';</script>";
    }
}

// Verify Ownership
$job_sql = "SELECT * FROM jobs WHERE id = '$job_id' AND client_id = '$client_id'";
$job_res = mysqli_query($conn, $job_sql);
if (mysqli_num_rows($job_res) == 0) {
    die("Job not found or access denied.");
}
$job = mysqli_fetch_assoc($job_res);

// Fetch Bids
$sql = "SELECT bids.*, users.full_name, students.university, students.degree, students.worth_score 
        FROM bids 
        JOIN users ON bids.student_id = users.id 
        JOIN students ON users.id = students.user_id 
        WHERE bids.job_id = '$job_id'";
$result = mysqli_query($conn, $sql);
?>

<div class="container">
    <h2>Bids for: <?php echo htmlspecialchars($job['title']); ?></h2>
    <a href="manage_jobs.php">&larr; Back to Jobs</a>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($bid = mysqli_fetch_assoc($result)): ?>
            <div class="card" style="border-left: 5px solid var(--primary-color);">
                <h4><?php echo htmlspecialchars($bid['full_name']); ?> <small>(⭐ <?php echo $bid['worth_score']; ?>)</small></h4>
                <p><strong>University:</strong> <?php echo $bid['university']; ?></p>
                <div style="background: #f9f9f9; padding: 10px; margin: 10px 0;">
                    <em>"<?php echo nl2br(htmlspecialchars($bid['proposal'])); ?>"</em>
                </div>
                <p><strong>Bid Price:</strong> LKR <?php echo number_format($bid['price'], 2); ?> | <strong>Time:</strong> <?php echo $bid['time_est']; ?></p>
                
                <?php if ($job['status'] == 'open' && $bid['status'] == 'pending'): ?>
                    <div style="display: flex; gap: 10px;">
                        <form action="assign_project.php" method="POST" onsubmit="return confirm('Are you sure you want to hire this student?');">
                            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                            <input type="hidden" name="student_id" value="<?php echo $bid['student_id']; ?>">
                            <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
                            <button type="submit" class="btn btn-success">Accept & Hire</button>
                        </form>
                        
                        <form method="POST" onsubmit="return confirm('Are you sure you want to reject this bid?');">
                            <input type="hidden" name="reject_bid_id" value="<?php echo $bid['id']; ?>">
                            <input type="hidden" name="student_id" value="<?php echo $bid['student_id']; ?>">
                            <button type="submit" class="btn btn-secondary" style="background: #dc3545; color: white;">Reject</button>
                        </form>
                    </div>
                <?php elseif ($bid['status'] == 'rejected'): ?>
                    <span class="alert alert-error" style="padding: 5px 10px;">Rejected</span>
                <?php elseif ($bid['status'] == 'accepted'): ?>
                    <span class="alert alert-success" style="padding: 5px 10px;">Accepted</span>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No bids received yet.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
