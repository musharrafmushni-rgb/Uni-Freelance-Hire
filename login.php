<?php 
include 'includes/header.php'; 

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        if ($user['status'] == 'deactivated') {
            $error = "Your account has been deactivated. Please contact support.";
        } elseif ($user['status'] == 'pending') {
            $error = "Your account is pending approval. Please wait for an admin to approve it.";
        } elseif (password_verify($password, $user['password'])) {
            // Login Success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['full_name'];
            
            // Redirect based on role
            if ($user['role'] == 'student') {
                redirect('student_profile.php'); // Or jobs.php
            } elseif ($user['role'] == 'client') {
                redirect('manage_jobs.php');
            } else {
                redirect('admin_dashboard.php'); // Admin
            }
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<div class="card" style="max-width: 400px; margin: 0 auto;">
    <h2 class="text-center">Login</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-full">Login</button>
        <p class="text-center mt-3"><a href="register.php">Create an Account</a></p>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
