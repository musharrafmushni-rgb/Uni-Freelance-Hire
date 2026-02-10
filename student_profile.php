<?php
include 'includes/header.php';
requireRole('student');

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $university = sanitize($conn, $_POST['university']);
    $degree = sanitize($conn, $_POST['degree']);
    $skills = sanitize($conn, $_POST['skills']); // Comma separated
    $portfolio = sanitize($conn, $_POST['portfolio_link']);
    $rate = floatval($_POST['hourly_rate']);
    $bio = sanitize($conn, $_POST['bio']);

    $sql = "UPDATE students SET university='$university', degree='$degree', skills='$skills', portfolio_link='$portfolio', hourly_rate='$rate', bio='$bio' WHERE user_id='$user_id'";
    
    if (mysqli_query($conn, $sql)) {
        $success = "Profile updated successfully!";
    } else {
        $error = "Error updating profile: " . mysqli_error($conn);
    }
}

// Fetch current data
$sql = "SELECT * FROM students JOIN users ON students.user_id = users.id WHERE students.user_id = '$user_id'";
$result = mysqli_query($conn, $sql);
$student = mysqli_fetch_assoc($result);

// Wallet
$wallet_sql = "SELECT balance FROM wallets WHERE user_id = '$user_id'";
$wallet_res = mysqli_query($conn, $wallet_sql);
$wallet = mysqli_fetch_assoc($wallet_res);
?>

<div class="container">
    <h2 class="mt-3">My Profile</h2>

    <div style="display: flex; gap: 20px;">
        <!-- Left Column: Info & Stats -->
        <div class="card" style="flex: 1;">
            <div style="text-align: center; margin-bottom: 15px;">
                <?php 
                $photo = !empty($student['profile_photo']) ? 'uploads/' . $student['profile_photo'] : 'https://ui-avatars.com/api/?name=' . urlencode($student['full_name']);
                ?>
                <img src="<?php echo $photo; ?>" alt="Profile" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            </div>
            <h3 class="text-center"><?php echo $student['full_name']; ?></h3>
            <p class="text-center text-muted"><?php echo $student['degree']; ?> @ <?php echo $student['university']; ?></p>
            
            <hr>
            <p><strong>Worth Score:</strong> ⭐ <?php echo $student['worth_score']; ?> / 5.0</p>
            <p><strong>Wallet Balance:</strong> LKR <?php echo number_format($wallet['balance'], 2); ?></p>
            <hr>
            
            <h4>Skills</h4>
            <p><?php echo $student['skills'] ? $student['skills'] : 'No skills listed'; ?></p>
        </div>

        <!-- Right Column: Edit Form -->
        <div class="card" style="flex: 2;">
            <h4>Edit Profile</h4>
            <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>University</label>
                    <input type="text" name="university" class="form-control" value="<?php echo $student['university']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Degree Program</label>
                    <input type="text" name="degree" class="form-control" value="<?php echo $student['degree']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Skills (Comma separated)</label>
                    <input type="text" name="skills" class="form-control" value="<?php echo $student['skills']; ?>" placeholder="e.g. PHP, MySQL, CSS">
                </div>
                <div class="form-group">
                    <label>Portfolio Link</label>
                    <input type="url" name="portfolio_link" class="form-control" value="<?php echo $student['portfolio_link']; ?>">
                </div>
                <div class="form-group">
                    <label>Expected Hourly Rate (LKR)</label>
                    <input type="number" name="hourly_rate" class="form-control" value="<?php echo $student['hourly_rate']; ?>">
                </div>
                <div class="form-group">
                    <label>Bio</label>
                    <textarea name="bio" class="form-control" rows="4"><?php echo $student['bio']; ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
