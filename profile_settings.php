<?php
include 'includes/header.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Update Basic Info
    if (isset($_POST['update_info'])) {
        $name = sanitize($conn, $_POST['full_name']);
        
        // Handle File Upload
        // Handle File Upload
        $photo_sql = "";
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            
            if ($_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['profile_photo']['name'];
                $filetype = $_FILES['profile_photo']['type'];
                $filesize = $_FILES['profile_photo']['size'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (!in_array($ext, $allowed)) {
                    $error = "Invalid file type. Only JPG, PNG, GIF allowed.";
                } elseif ($filesize > 5 * 1024 * 1024) { // 5MB Limit
                    $error = "File size too large. Max 5MB.";
                } else {
                    $new_filename = "user_" . $user_id . "_" . time() . "." . $ext;
                    $upload_dir = __DIR__ . '/uploads/';
                    
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_dir . $new_filename)) {
                        $photo_sql = ", profile_photo='$new_filename'";
                    } else {
                        $error = "Failed to move uploaded file. Check directory permissions.";
                    }
                }
            } else {
                // Map error codes to messages
                switch ($_FILES['profile_photo']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                        $error = "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $error = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $error = "The uploaded file was only partially uploaded.";
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $error = "Missing a temporary folder.";
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $error = "Failed to write file to disk.";
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $error = "A PHP extension stopped the file upload.";
                        break;
                    default:
                        $error = "Unknown upload error.";
                        break;
                }
            }
        }

        if (!$error) {
            $sql = "UPDATE users SET full_name='$name' $photo_sql WHERE id='$user_id'";
            if (mysqli_query($conn, $sql)) {
                $success = "Profile updated successfully.";
                $_SESSION['name'] = $name; // Update session
            } else {
                $error = "Error updating profile.";
            }
        }
    }

    // 2. Update Email
    if (isset($_POST['update_email'])) {
        $email = sanitize($conn, $_POST['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } else {
            // Check if email exists
            $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email' AND id != '$user_id'");
            if (mysqli_num_rows($check) > 0) {
                $error = "Email already in use.";
            } else {
                mysqli_query($conn, "UPDATE users SET email='$email' WHERE id='$user_id'");
                $success = "Email updated.";
            }
        }
    }

    // 3. Change Password
    if (isset($_POST['change_password'])) {
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT password FROM users WHERE id='$user_id'"));
        
        if (!password_verify($current_pass, $u['password'])) {
            $error = "Incorrect current password.";
        } elseif (strlen($new_pass) < 6) {
            $error = "New password must be at least 6 characters.";
        } elseif ($new_pass !== $confirm_pass) {
            $error = "New passwords do not match.";
        } else {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET password='$hash' WHERE id='$user_id'");
            $success = "Password changed successfully.";
        }
    }

    // 4. Deactivate Account
    if (isset($_POST['deactivate_account'])) {
        mysqli_query($conn, "UPDATE users SET status='deactivated' WHERE id='$user_id'");
        session_destroy();
        redirect('login.php');
    }
}

// Fetch Current User Data
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'"));
?>

<div class="container mt-3">
    <h2>Account Settings</h2>
    
    <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
        
        <!-- Basic Info -->
        <div class="card" style="flex: 1; min-width: 300px;">
            <h3>Basic Profile</h3>
            <div style="text-align: center; margin-bottom: 20px;">
                <?php 
                    $photo = isset($user['profile_photo']) && $user['profile_photo'] ? 'uploads/' . $user['profile_photo'] : 'assets/img/default.png';
                    // Fallback if file doesn't exist locally but DB has it
                    if (!file_exists($photo) && isset($user['profile_photo'])) $photo = 'uploads/' . $user['profile_photo']; 
                ?>
                <img src="<?php echo $photo; ?>" alt="Profile" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd;">
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Profile Photo</label>
                    <input type="file" name="profile_photo" class="form-control" accept="image/*">
                </div>
                <button type="submit" name="update_info" class="btn btn-primary">Update Info</button>
            </form>
        </div>

        <!-- Security & Email -->
        <div class="card" style="flex: 1; min-width: 300px;">
            <h3>Security</h3>
            
            <form method="POST" class="mb-3">
                <h4>Change Email</h4>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <button type="submit" name="update_email" class="btn btn-secondary">Update Email</button>
            </form>
            <hr>
            <form method="POST">
                <h4>Change Password</h4>
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" name="change_password" class="btn btn-secondary">Change Password</button>
            </form>
        </div>

    </div>

    <!-- Danger Zone -->
    <div class="card mt-3" style="border: 1px solid #dc3545;">
        <h3 style="color: #dc3545;">Danger Zone</h3>
        <p>Deactivating your account will disable your access. You will not be able to log in until an admin reactivates your account.</p>
        <form method="POST" onsubmit="return confirm('Are you sure you want to deactivate your account?');">
            <button type="submit" name="deactivate_account" class="btn btn-danger">Deactivate Account</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
