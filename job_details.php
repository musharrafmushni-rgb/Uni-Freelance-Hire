<?php
include 'includes/header.php';
requireRole('student');

if (!isset($_GET['id'])) {
    redirect('jobs.php');
}

$job_id = intval($_GET['id']);
$student_id = $_SESSION['user_id'];

// Fetch Job Info
$sql = "SELECT jobs.*, clients.company_name, users.full_name as client_name 
        FROM jobs 
        JOIN users ON jobs.client_id = users.id 
        LEFT JOIN clients ON users.id = clients.user_id 
        WHERE jobs.id = $job_id";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) == 0) {
    echo "Job not found.";
    include 'includes/footer.php';
    exit;
}
$job = mysqli_fetch_assoc($result);

// Handle Bid Submission
$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if already bid
    $check_sql = "SELECT id FROM bids WHERE job_id = '$job_id' AND student_id = '$student_id'";
    $check_res = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_res) > 0) {
        $msg = "<div class='alert alert-error'>You have already placed a bid on this job.</div>";
    } else {
        $proposal = sanitize($conn, $_POST['proposal']);
        $price = floatval($_POST['price']);
        $time_est = sanitize($conn, $_POST['time_est']);
        
        $bid_sql = "INSERT INTO bids (job_id, student_id, proposal, price, time_est) VALUES ('$job_id', '$student_id', '$proposal', '$price', '$time_est')";
        
        if (mysqli_query($conn, $bid_sql)) {
            // Notify Client
            include_once 'includes/notification_helper.php';
            sendNotification($conn, $job['client_id'], "New Bid on '{$job['title']}' by " . $_SESSION['name']);

            $msg = "<div class='alert alert-success'>Bid placed successfully! <a href='my_bids.php'>View My Bids</a></div>";
        } else {
            $msg = "<div class='alert alert-error'>Error: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>

<div class="container">
    <div class="card mt-3">
        <h2><?php echo htmlspecialchars($job['title']); ?></h2>
        <p><strong>Posted by:</strong> <?php echo $job['company_name'] ? $job['company_name'] : $job['client_name']; ?> on <?php echo $job['created_at']; ?></p>
        <hr>
        <p><strong>Description:</strong></p>
        <p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
        <hr>
        <div style="display: flex; gap: 50px;">
            <p><strong>Budget:</strong> LKR <?php echo number_format($job['budget'], 2); ?></p>
            <p><strong>Deadline:</strong> <?php echo $job['deadline']; ?></p>
            <p><strong>Status:</strong> <?php echo ucfirst($job['status']); ?></p>
        </div>
        <p><strong>Skills Required:</strong> <?php echo htmlspecialchars($job['skills_req']); ?></p>
    </div>

    <?php echo $msg; ?>

    <?php if ($job['status'] == 'open'): ?>
        <div class="card">
            <h3>Place Your Bid</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Proposal / Cover Letter</label>
                    <textarea name="proposal" class="form-control" rows="5" required placeholder="Explain why you are the best fit..."></textarea>
                </div>
                <div style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Your Price (LKR)</label>
                        <input type="number" name="price" class="form-control" required value="<?php echo $job['budget']; ?>">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Estimated Delivery Time (e.g., 3 Days)</label>
                        <input type="text" name="time_est" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Submit Bid</button>
            </form>
        </div>
    <?php else: ?>
        <div class="alert alert-error">This job is no longer accepting bids.</div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
