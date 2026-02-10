<?php
include 'includes/header.php';
requireRole('client');

$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_id = $_SESSION['user_id'];
    $title = sanitize($conn, $_POST['title']);
    $description = sanitize($conn, $_POST['description']);
    $skills = sanitize($conn, $_POST['skills']);
    $budget = floatval($_POST['budget']);
    $deadline = sanitize($conn, $_POST['deadline']);

    $sql = "INSERT INTO jobs (client_id, title, description, skills_req, budget, deadline) VALUES ('$client_id', '$title', '$description', '$skills', '$budget', '$deadline')";
    
    if (mysqli_query($conn, $sql)) {
        // Notify Admins
        include_once 'includes/notification_helper.php';
        broadcastToAdmins($conn, "New Job Posted: " . $title);

        $msg = "<div class='alert alert-success'>Job Posted Successfully! <a href='manage_jobs.php'>View Jobs</a></div>";
    } else {
        $msg = "<div class='alert alert-error'>Error: " . mysqli_error($conn) . "</div>";
    }
}
?>

<div class="container">
    <div class="card" style="max-width: 800px; margin: 20px auto;">
        <h2 class="text-center">Post a New Job</h2>
        <?php echo $msg; ?>
        <form method="POST">
            <div class="form-group">
                <label>Job Title</label>
                <input type="text" name="title" class="form-control" required placeholder="e.g. Build a PHP Website">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label>Required Skills (Comma separated)</label>
                <input type="text" name="skills" class="form-control" required placeholder="PHP, MySQL, HTML">
            </div>
            <div class="form-group">
                <label>Budget (LKR)</label>
                <input type="number" name="budget" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Deadline</label>
                <input type="date" name="deadline" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-full">Post Job</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
