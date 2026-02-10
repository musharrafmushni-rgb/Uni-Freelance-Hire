<?php
include 'includes/header.php';
requireRole('student');

// Basic Search
$search = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';
$where_clause = "jobs.status = 'open'";
if ($search) {
    $where_clause .= " AND (title LIKE '%$search%' OR skills_req LIKE '%$search%')";
}

$sql = "SELECT jobs.*, clients.company_name, users.full_name as client_name 
        FROM jobs 
        JOIN users ON jobs.client_id = users.id 
        LEFT JOIN clients ON users.id = clients.user_id 
        WHERE $where_clause 
        ORDER BY jobs.created_at DESC";

$result = mysqli_query($conn, $sql);
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
        <h2>Available Jobs</h2>
        <form method="GET" style="display: flex; gap: 10px;">
            <input type="text" name="search" class="form-control" placeholder="Search jobs..." value="<?php echo $search; ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <div class="job-list" style="margin-top: 20px;">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($job = mysqli_fetch_assoc($result)): ?>
                <div class="card">
                    <div style="display: flex; justify-content: space-between;">
                        <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                        <span style="color: green; font-weight: bold;">LKR <?php echo number_format($job['budget'], 2); ?></span>
                    </div>
                    <p><strong>Client:</strong> <?php echo $job['company_name'] ? $job['company_name'] : $job['client_name']; ?></p>
                    <p><?php echo substr(htmlspecialchars($job['description']), 0, 150); ?>...</p>
                    <p><small><strong>Skills:</strong> <?php echo htmlspecialchars($job['skills_req']); ?></small></p>
                    <p><small><strong>Deadline:</strong> <?php echo $job['deadline']; ?></small></p>
                    <a href="job_details.php?id=<?php echo $job['id']; ?>" class="btn btn-primary">View & Bid</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No jobs found.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
