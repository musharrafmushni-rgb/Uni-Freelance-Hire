<?php
include 'includes/header.php';
requireRole('client');

$client_id = $_SESSION['user_id'];

// Handle Completion Approval (Simulated Payment)
if (isset($_POST['approve_project'])) {
    $project_id = intval($_POST['project_id']);
    
    // In a real app, we check wallet balance here.
    // For simulation, we assume unlimited funds or just transfer numbers.
    
    // Get project details to know the cost (from bid price or job budget? Usually bid price)
    // We need to fetch the accepted bid price.
    $price_sql = "SELECT price FROM bids WHERE job_id = (SELECT job_id FROM projects WHERE id='$project_id') AND status='accepted'";
    $price_res = mysqli_query($conn, $price_sql);
    $price_row = mysqli_fetch_assoc($price_res);
    $amount = $price_row['price'];

    mysqli_begin_transaction($conn);
    try {
        // 1. Update Project Status
        mysqli_query($conn, "UPDATE projects SET status='completed', completed_at=NOW() WHERE id='$project_id'");
        
        // 2. Transfer Money
        // Deduct from Client
        mysqli_query($conn, "UPDATE wallets SET balance = balance - $amount WHERE user_id='$client_id'");
        // Add to Student
        $student_id_sql = mysqli_query($conn, "SELECT student_id FROM projects WHERE id='$project_id'");
        $student_id = mysqli_fetch_assoc($student_id_sql)['student_id'];
        mysqli_query($conn, "UPDATE wallets SET balance = balance + $amount WHERE user_id='$student_id'");

        // 3. Record Transaction
        mysqli_query($conn, "INSERT INTO transactions (from_user, to_user, amount, description) VALUES ('$client_id', '$student_id', '$amount', 'Payment for Project #$project_id')");

        // Notify Student
        include_once 'includes/notification_helper.php';
        sendNotification($conn, $student_id, "Payment of LKR " . number_format($amount, 2) . " has been released to your wallet for Project #$project_id.");

        mysqli_commit($conn);
        
        // Redirect to Rating Page
        echo "<script>window.location.href='rate_student.php?project_id=$project_id';</script>";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Error: " . $e->getMessage();
    }
}

$sql = "SELECT projects.*, jobs.title, users.full_name as student_name 
        FROM projects 
        JOIN jobs ON projects.job_id = jobs.id 
        JOIN users ON projects.student_id = users.id 
        WHERE projects.client_id = '$client_id' 
        ORDER BY projects.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="container">
    <h2 class="mt-3">My Projects (Hired)</h2>
    
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($proj = mysqli_fetch_assoc($result)): ?>
            <div class="card">
                <h3><?php echo htmlspecialchars($proj['title']); ?></h3>
                <p><strong>Freelancer:</strong> <?php echo $proj['student_name']; ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst($proj['status']); ?></p>
                
                <?php if ($proj['status'] == 'submitted'): ?>
                    <div class="alert alert-info">Student has submitted the work. Please review.</div>
                    <form method="POST" onsubmit="return confirm('Approve completion and release payment?');">
                        <input type="hidden" name="project_id" value="<?php echo $proj['id']; ?>">
                        <button type="submit" name="approve_project" class="btn btn-success">Approve & Pay</button>
                    </form>
                <?php elseif ($proj['status'] == 'completed'): ?>
                    <div class="alert alert-success">Project Completed and Paid.</div>
                <?php endif; ?>
                <hr>
                <div style="margin-top: 10px;">
                    <a href="project_chat.php?project_id=<?php echo $proj['id']; ?>" class="btn btn-secondary w-full">Project Chat / Instructions</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No active projects.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
