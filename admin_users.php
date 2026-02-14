<?php
include 'includes/header.php';
requireRole('admin');

// Handle Deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM users WHERE id='$id'");
    echo "<script>alert('User deleted'); window.location.href='admin_users.php';</script>";
}

// Handle Reactivation
if (isset($_GET['reactivate'])) {
    $id = intval($_GET['reactivate']);
    mysqli_query($conn, "UPDATE users SET status='active' WHERE id='$id'");
    echo "<script>alert('User reactivated'); window.location.href='admin_users.php';</script>";
}

// Handle Approval
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    mysqli_query($conn, "UPDATE users SET status='active' WHERE id='$id'");
    
    // Notify User
    include_once 'includes/notification_helper.php';
    sendNotification($conn, $id, "Your account has been approved! You can now access all features.");
    
    echo "<script>alert('User approved'); window.location.href='admin_users.php';</script>";
}

// Handle Warning
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_warning'])) {
    $user_id = intval($_POST['user_id']);
    $msg = sanitize($conn, $_POST['warning_message']);
    
    include_once 'includes/notification_helper.php';
    sendNotification($conn, $user_id, "WARNING from Admin: " . $msg);
    
    echo "<script>alert('Warning sent successfully'); window.location.href='admin_users.php';</script>";
}

$role_filter = isset($_GET['role']) ? sanitize($conn, $_GET['role']) : '';
$where = "WHERE 1";
if ($role_filter) {
    $where .= " AND role='$role_filter'";
}

$sql = "SELECT * FROM users $where ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2 class="mt-3">User Management</h2>
        <div>
            Filter: 
            <a href="admin_users.php" class="btn btn-secondary">All</a>
            <a href="admin_users.php?role=student" class="btn btn-secondary">Students</a>
            <a href="admin_users.php?role=client" class="btn btn-secondary">Clients</a>
        </div>
    </div>

    <div class="card mt-3">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid #ddd;">
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($u = mysqli_fetch_assoc($result)): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo ucfirst($u['role']); ?></td>
                        <td>
                            <?php 
                                $status_class = 'badge-success';
                                if($u['status'] == 'deactivated') $status_class = 'badge-danger';
                                if($u['status'] == 'pending') $status_class = 'badge-warning';
                            ?>
                            <span class="badge <?php echo $status_class; ?>">
                                <?php echo ucfirst($u['status']); ?>
                            </span>
                        </td>
                        <td><?php echo $u['created_at']; ?></td>
                        <td>
                            <?php if ($u['role'] != 'admin'): ?>
                                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                    <a href="admin_users.php?delete=<?php echo $u['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure? This will delete all their data.');">Delete</a>
                                    
                                    <?php if ($u['status'] == 'deactivated'): ?>
                                        <a href="admin_users.php?reactivate=<?php echo $u['id']; ?>" class="btn btn-success btn-sm">Reactivate</a>
                                    <?php elseif ($u['status'] == 'pending'): ?>
                                        <a href="admin_users.php?approve=<?php echo $u['id']; ?>" class="btn btn-primary btn-sm">Approve</a>
                                    <?php endif; ?>

                                    <a href="admin_chat.php?user_id=<?php echo $u['id']; ?>" class="btn btn-primary btn-sm">Chat</a>
                                    
                                    <button type="button" class="btn btn-warning btn-sm" onclick="openWarningModal(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['full_name']); ?>')">Warning</button>
                                </div>
                            <?php endif; ?>
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
        <h3>Send Warning</h3>
        <p>Send a warning to <span id="warningUserName"></span></p>
        <form method="POST">
            <input type="hidden" name="user_id" id="warningUserId">
            <div class="form-group">
                <textarea name="warning_message" class="form-control" rows="3" placeholder="Enter warning message..." required></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeWarningModal()">Cancel</button>
                <button type="submit" name="send_warning" class="btn btn-warning">Send Warning</button>
            </div>
        </form>
    </div>
</div>

<script>
function openWarningModal(id, name) {
    document.getElementById('warningUserId').value = id;
    document.getElementById('warningUserName').innerText = name;
    document.getElementById('warningModal').style.display = 'flex';
}
function closeWarningModal() {
    document.getElementById('warningModal').style.display = 'none';
}
</script>

<?php include 'includes/footer.php'; ?>
