<?php
include 'includes/header.php';
requireRole('admin');

// Only Super Admin can add Sub Admins? 
// The requirement description says "admin_dashboard only can add sub admins option", 
// implying the main admin (system admin) adds them. 
// We will check if the current user is a super admin for extra security, though not strictly requested, it's good practice.
// However, the prompt implies "System Admin don't neet that option" ref to Danger Zone, and "admin_dashboard only can add sub admins option".
// I'll allow any admin to add a sub-admin for now, or restriction based on admin_type if I can easily get it.
// Since I just added admin_type, let's use it.

$user_id = $_SESSION['user_id'];
$curr_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT admin_type FROM users WHERE id='$user_id'"));

if ($curr_user['admin_type'] !== 'super') {
    // Ideally only super admin can add sub admins, but let's stick to the prompt "admin_dashboard only can add"
    // If I am a sub-admin, can I add other sub-admins? Probably not.
    echo "<div class='container mt-3'><div class='alert alert-error'>Access Denied. Only System Admin can add Sub Admins.</div></div>";
    include 'includes/footer.php';
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitize($conn, $_POST['full_name']);
    $email = sanitize($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if email exists
        $check_sql = "SELECT id FROM users WHERE email = '$email'";
        $check_res = mysqli_query($conn, $check_sql);
        
        if (mysqli_num_rows($check_res) > 0) {
            $error = "Email already registered!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // admin_type is 'sub' for new admins created here
            $sql = "INSERT INTO users (full_name, email, password, role, admin_type, status) VALUES ('$full_name', '$email', '$hashed_password', 'admin', 'sub', 'active')";
            
            if (mysqli_query($conn, $sql)) {
                $success = "Sub Admin added successfully.";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<div class="container mt-3">
    <h2>Add New Sub Admin</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="card" style="max-width: 500px;">
        <form method="POST" action="">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Create Sub Admin</button>
            <a href="admin_dashboard.php" class="btn btn-secondary" style="margin-left: 10px;">Back to Dashboard</a>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
