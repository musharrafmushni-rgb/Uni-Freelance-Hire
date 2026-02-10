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
                                <a href="admin_users.php?delete=<?php echo $u['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure? This will delete all their data.');">Delete</a>
                                
                                <?php if ($u['status'] == 'deactivated'): ?>
                                    <a href="admin_users.php?reactivate=<?php echo $u['id']; ?>" class="btn btn-success">Reactivate</a>
                                <?php elseif ($u['status'] == 'pending'): ?>
                                    <a href="admin_users.php?approve=<?php echo $u['id']; ?>" class="btn btn-primary">Approve</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
