<?php
include 'includes/header.php';
requireRole('admin');

// Handle Deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Delete projects related to job first? fk constraint usually handles or error.
    // Assuming CASCADE or manual cleanup. Let's try simple delete.
    mysqli_query($conn, "DELETE FROM jobs WHERE id='$id'");
    echo "<script>alert('Job deleted'); window.location.href='admin_jobs.php';</script>";
}

// Handle Warning
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_warning'])) {
    $client_id = intval($_POST['client_id']);
    $msg = sanitize($conn, $_POST['warning_message']);
    
    include_once 'includes/notification_helper.php';
    sendNotification($conn, $client_id, "WARNING from Admin regarding your job post: " . $msg);
    
    echo "<script>alert('Warning sent to client'); window.location.href='admin_jobs.php';</script>";
}

// Fetch Jobs
$sql = "SELECT jobs.*, users.full_name as client_name, users.id as client_id 
        FROM jobs 
        JOIN users ON jobs.client_id = users.id 
        ORDER BY jobs.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="container mt-3">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Job Management</h2>
        <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <div class="card mt-3">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid #ddd;">
                    <th>ID</th>
                    <th>Title</th>
                    <th>Client</th>
                    <th>Budget</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($job = mysqli_fetch_assoc($result)): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td><?php echo $job['id']; ?></td>
                        <td><?php echo htmlspecialchars($job['title']); ?></td>
                        <td><?php echo htmlspecialchars($job['client_name']); ?></td>
                        <td>Rs. <?php echo number_format($job['budget'], 2); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($job['created_at'])); ?></td>
                        <td>
                            <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                <a href="job_details.php?id=<?php echo $job['id']; ?>" class="btn btn-secondary btn-sm" target="_blank">View</a>
                                <a href="admin_jobs.php?delete=<?php echo $job['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this job post?');">Delete</a>
                                
                                <a href="admin_chat.php?user_id=<?php echo $job['client_id']; ?>" class="btn btn-primary btn-sm">Chat</a>
                                
                                <button type="button" class="btn btn-warning btn-sm" onclick="openWarningModal(<?php echo $job['client_id']; ?>, '<?php echo htmlspecialchars($job['client_name']); ?>')">Warning</button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Warning Modal -->
<div id="warningModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 20px; border-radius: 8px; width: 400px; max-width: 90%;">
        <h3>Warn Client</h3>
        <p>Send a warning to <span id="warningMsgClientName"></span></p>
        <form method="POST">
            <input type="hidden" name="client_id" id="warningClientId">
            <div class="form-group">
                <textarea name="warning_message" class="form-control" rows="3" placeholder="Reason for warning..." required></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeWarningModal()">Cancel</button>
                <button type="submit" name="send_warning" class="btn btn-warning">Send The Warning</button>
            </div>
        </form>
    </div>
</div>

<script>
function openWarningModal(id, name) {
    document.getElementById('warningClientId').value = id;
    document.getElementById('warningMsgClientName').innerText = name;
    document.getElementById('warningModal').style.display = 'flex';
}
function closeWarningModal() {
    document.getElementById('warningModal').style.display = 'none';
}
</script>

<?php include 'includes/footer.php'; ?>
