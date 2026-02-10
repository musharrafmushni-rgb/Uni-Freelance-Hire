<?php
include 'includes/header.php';
requireRole('student');

$student_id = $_SESSION['user_id'];

// Handle Submission
if (isset($_POST['submit_project'])) {
    $project_id = intval($_POST['project_id']);
    $update_sql = "UPDATE projects SET status='submitted' WHERE id='$project_id' AND student_id='$student_id' AND status IN ('assigned', 'in_progress')";
    if (mysqli_query($conn, $update_sql)) {
        // Fetch Client ID for notification
        $client_res = mysqli_query($conn, "SELECT projects.client_id, jobs.title FROM projects JOIN jobs ON projects.job_id = jobs.id WHERE projects.id='$project_id'");
        $client_data = mysqli_fetch_assoc($client_res);
        
        include_once 'includes/notification_helper.php';
        sendNotification($conn, $client_data['client_id'], "Project '{$client_data['title']}' submitted by " . $_SESSION['name']);

        echo "<script>alert('Project submitted for review!'); window.location.href='my_projects_student.php';</script>";
    } else {
        echo "<script>alert('Error submitting project.');</script>";
    }
}

// Handle Message Notification Bridge
if (isset($_POST['send_notif_message'])) {
    $project_id = intval($_POST['project_id']);
    $msg_text = sanitize($conn, $_POST['message_text']);
    
    $client_res = mysqli_query($conn, "SELECT projects.client_id, jobs.title FROM projects JOIN jobs ON projects.job_id = jobs.id WHERE projects.id='$project_id'");
    $client_data = mysqli_fetch_assoc($client_res);
    
    include_once 'includes/notification_helper.php';
    sendNotification($conn, $client_data['client_id'], "New Message from Student regarding '{$client_data['title']}': $msg_text");
    echo "<script>alert('Message sent to client!'); window.location.href='my_projects_student.php';</script>";
}

$sql = "SELECT projects.*, jobs.title, clients.company_name, users.full_name as client_name 
        FROM projects 
        JOIN jobs ON projects.job_id = jobs.id 
        JOIN users ON projects.client_id = users.id 
        LEFT JOIN clients ON users.id = clients.user_id 
        WHERE projects.student_id = '$student_id' 
        ORDER BY projects.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="container">
    <h2 class="mt-3">My Projects</h2>
    
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($proj = mysqli_fetch_assoc($result)): ?>
            <div class="card">
                <div style="display: flex; justify-content: space-between;">
                    <h3><?php echo htmlspecialchars($proj['title']); ?></h3>
                    <span>Status: <strong><?php echo ucfirst($proj['status']); ?></strong></span>
                </div>
                <p><strong>Client:</strong> <?php echo $proj['company_name'] ? $proj['company_name'] : $proj['client_name']; ?></p>
                <p><strong>Started:</strong> <?php echo $proj['created_at']; ?></p>
                
                <?php if ($proj['status'] == 'assigned' || $proj['status'] == 'in_progress'): ?>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to submit this project?');">
                        <input type="hidden" name="project_id" value="<?php echo $proj['id']; ?>">
                        <button type="submit" name="submit_project" class="btn btn-success">Mark as Submitted</button>
                    </form>
                <?php elseif ($proj['status'] == 'submitted'): ?>
                    <div class="alert alert-warning">Waiting for client approval.</div>
                <?php elseif ($proj['status'] == 'completed'): ?>
                    <div class="alert alert-success">Project Completed! Payment Pending.</div>
                <?php elseif ($proj['status'] == 'paid'): ?>
                    <div class="alert alert-success">Payment Received.</div>
                <?php endif; ?>

                <hr>
                <div style="margin-top: 10px; display: flex; gap: 10px;">
                    <a href="project_chat.php?project_id=<?php echo $proj['id']; ?>" class="btn btn-secondary w-full">Open Project Chat</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No active projects.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
