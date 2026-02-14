<?php
include 'includes/header.php';
requireRole('admin');

// Stats
$stats = [];
$stats['students'] = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users WHERE role='student'"));
$stats['clients'] = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users WHERE role='client'"));
$stats['jobs'] = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM jobs"));
$stats['projects'] = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM projects WHERE status='completed'"));

// Recent Users
$users_res = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2 class="mt-3">Admin Dashboard</h2>
        <a href="admin_add_subadmin.php" class="btn btn-primary">Add New Sub Admin</a>
    </div>
    
    <div style="display: flex; gap: 20px; margin-bottom: 30px;">
        <a href="admin_users.php?role=student" class="card text-center" style="flex: 1; background: #e3f2fd; text-decoration: none; color: inherit; transition: transform 0.2s;">
            <h3><?php echo $stats['students']; ?></h3>
            <p>Students</p>
        </a>
        <a href="admin_users.php?role=client" class="card text-center" style="flex: 1; background: #e8f5e9; text-decoration: none; color: inherit; transition: transform 0.2s;">
            <h3><?php echo $stats['clients']; ?></h3>
            <p>Clients</p>
        </a>
        <a href="admin_jobs.php" class="card text-center" style="flex: 1; background: #fff3e0; text-decoration: none; color: inherit; transition: transform 0.2s;">
            <h3><?php echo $stats['jobs']; ?></h3>
            <p>Jobs Posted</p>
        </a>
        <div class="card text-center" style="flex: 1; background: #fce4ec;">
            <h3><?php echo $stats['projects']; ?></h3>
            <p>Completed Projects</p>
        </div>
    </div>

    <div class="card">
        <h3>Recent Users</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid #ddd;">
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($u = mysqli_fetch_assoc($users_res)): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo ucfirst($u['role']); ?></td>
                        <td><?php echo $u['created_at']; ?></td>
                        <td>
                            <a href="admin_delete_user.php?id=<?php echo $u['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete user?');">Remove</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="mt-3 text-center">
            <a href="admin_users.php" class="btn btn-primary">View All Users</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
