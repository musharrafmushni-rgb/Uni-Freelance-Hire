<?php
include 'includes/header.php';
requireRole('client');

if (!isset($_GET['project_id'])) {
    redirect('client_projects.php');
}

$project_id = intval($_GET['project_id']);
$client_id = $_SESSION['user_id'];

// Initial Checks
$sql = "SELECT * FROM projects WHERE id='$project_id' AND client_id='$client_id' AND status='completed'"; // Assuming status is 'completed' (paid) before rating
// Actually the previous step updated to 'completed' then redirected here.
// Let's check if already rated?
$check_rating = "SELECT id FROM reviews WHERE project_id='$project_id'";
if (mysqli_num_rows(mysqli_query($conn, $check_rating)) > 0) {
    echo "<div class='container'><div class='alert alert-info'>You have already rated this project. <a href='client_projects.php'>Go Back</a></div></div>";
    include 'includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = intval($_POST['rating']);
    $comment = sanitize($conn, $_POST['comment']);
    
    // Get Student ID
    $proj_res = mysqli_query($conn, "SELECT student_id FROM projects WHERE id='$project_id'");
    $proj_row = mysqli_fetch_assoc($proj_res);
    $student_id = $proj_row['student_id'];

    mysqli_begin_transaction($conn);
    try {
        // 1. Insert Review
        $sql = "INSERT INTO reviews (project_id, from_user, to_user, rating, comment) VALUES ('$project_id', '$client_id', '$student_id', '$rating', '$comment')";
        mysqli_query($conn, $sql);

        // 2. Update Student Worth Score
        // Recalculate average
        $avg_sql = "SELECT AVG(rating) as avg_rating FROM reviews WHERE to_user='$student_id'";
        $avg_res = mysqli_query($conn, $avg_sql);
        $avg_row = mysqli_fetch_assoc($avg_res);
        $new_score = number_format($avg_row['avg_rating'], 2);

        mysqli_query($conn, "UPDATE students SET worth_score='$new_score' WHERE user_id='$student_id'");

        // Notify Student
        include_once 'includes/notification_helper.php';
        sendNotification($conn, $student_id, "You received a new review! Rating: $rating/5");

        mysqli_commit($conn);
        echo "<script>alert('Review Submitted! Thank you.'); window.location.href='client_projects.php';</script>";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Error: " . $e->getMessage();
    }
}
?>

<div class="container">
    <div class="card" style="max-width: 600px; margin: 30px auto;">
        <h2 class="text-center">Rate Student</h2>
        <form method="POST">
            <div class="form-group">
                <label>Rating (1-5 Stars)</label>
                <select name="rating" class="form-control">
                    <option value="5">⭐⭐⭐⭐⭐ (Excellent)</option>
                    <option value="4">⭐⭐⭐⭐ (Good)</option>
                    <option value="3">⭐⭐⭐ (Average)</option>
                    <option value="2">⭐⭐ (Poor)</option>
                    <option value="1">⭐ (Terrible)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Comment / Review</label>
                <textarea name="comment" class="form-control" rows="4" required placeholder="Describe your experience working with this student..."></textarea>
            </div>
            <button type="submit" class="btn">Submit Review</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
