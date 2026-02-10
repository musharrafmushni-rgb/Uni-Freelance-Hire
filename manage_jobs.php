<?php
include 'includes/header.php';
requireRole('client');

$client_id = $_SESSION['user_id'];
$sql = "SELECT * FROM jobs WHERE client_id = '$client_id' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2 class="mt-3">Manage Jobs</h2>
        <a href="post_job.php" class="btn btn-primary">Post New Job</a>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($job = mysqli_fetch_assoc($result)): ?>
            <div class="card">
                <div style="display: flex; justify-content: space-between;">
                    <h3><?php echo htmlspecialchars($job['title']); ?> <small>(<?php echo ucfirst($job['status']); ?>)</small></h3>
                    <span>Budget: LKR <?php echo number_format($job['budget'], 2); ?></span>
                </div>
                <p>Deadline: <?php echo $job['deadline']; ?></p>
                <div class="actions">
                    <a href="view_bids.php?job_id=<?php echo $job['id']; ?>" class="btn btn-secondary">View Bids</a>
                    <?php if ($job['status'] == 'open'): ?>
                        <span class="text-muted">Accepting Bids</span>
                    <?php else: ?>
                        <span class="text-success">Project Assigned</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>You haven't posted any jobs yet.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
