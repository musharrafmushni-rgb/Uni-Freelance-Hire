<?php 
include 'includes/header.php'; 

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = sanitize($conn, $_POST['role']);
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
            $sql = "INSERT INTO users (full_name, email, password, role) VALUES ('$full_name', '$email', '$hashed_password', '$role')";
            
            if (mysqli_query($conn, $sql)) {
                $user_id = mysqli_insert_id($conn);
                
                if ($role == 'student') {
                    $university = sanitize($conn, $_POST['university']);
                    $degree = sanitize($conn, $_POST['degree']);
                    $student_sql = "INSERT INTO students (user_id, university, degree) VALUES ('$user_id', '$university', '$degree')";
                    mysqli_query($conn, $student_sql);
                    $wallet_sql = "INSERT INTO wallets (user_id, balance) VALUES ('$user_id', 0)";
                    mysqli_query($conn, $wallet_sql);

                } elseif ($role == 'client') {
                    $company = sanitize($conn, $_POST['company_name']);
                    $client_sql = "INSERT INTO clients (user_id, company_name) VALUES ('$user_id', '$company')";
                    mysqli_query($conn, $client_sql);
                    $wallet_sql = "INSERT INTO wallets (user_id, balance) VALUES ('$user_id', 0)";
                    mysqli_query($conn, $wallet_sql);
                }
                
                // Notify Admins
                include_once 'includes/notification_helper.php';
                broadcastToAdmins($conn, "New " . ucfirst($role) . " registered (ID: $user_id): " . $full_name . ". Pending approval.");

                $success = "Registration successful! <a href='login.php'>Login here</a>";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<div class="card" style="max-width: 500px; margin: 0 auto;">
    <h2 class="text-center">Register</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>I am a:</label>
            <select name="role" id="role-select" class="form-control" onchange="toggleFields()">
                <option value="student" <?php echo (isset($_GET['role']) && $_GET['role'] == 'student') ? 'selected' : ''; ?>>Student</option>
                <option value="client" <?php echo (isset($_GET['role']) && $_GET['role'] == 'client') ? 'selected' : ''; ?>>Client</option>
            </select>
        </div>

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

        <!-- Student Fields -->
        <div id="student-fields">
            <div class="form-group">
                <label>University</label>
                <input type="text" name="university" class="form-control">
            </div>
            <div class="form-group">
                <label>Degree Program</label>
                <input type="text" name="degree" class="form-control">
            </div>
        </div>

        <!-- Client Fields -->
        <div id="client-fields" style="display: none;">
            <div class="form-group">
                <label>Company Name (Optional)</label>
                <input type="text" name="company_name" class="form-control">
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-full">Register</button>
        <p class="text-center mt-3"><a href="login.php">Already have an account? Login</a></p>
    </form>
</div>

<script>
function toggleFields() {
    var role = document.getElementById('role-select').value;
    if (role === 'student') {
        document.getElementById('student-fields').style.display = 'block';
        document.getElementById('client-fields').style.display = 'none';
    } else {
        document.getElementById('student-fields').style.display = 'none';
        document.getElementById('client-fields').style.display = 'block';
    }
}
// Run on load
toggleFields();
</script>

<?php include 'includes/footer.php'; ?>
