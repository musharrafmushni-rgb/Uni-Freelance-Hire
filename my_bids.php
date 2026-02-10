<?php
include 'includes/header.php';
requireRole('student');

$student_id = $_SESSION['user_id'];
$sql = "SELECT bids.*, jobs.title, jobs.status as job_status 
        FROM bids 
        JOIN jobs ON bids.job_id = jobs.id 
        WHERE bids.student_id = '$student_id' 
        ORDER BY bids.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="container">
    <h2 class="mt-3">My Bids</h2>
    <div class="card">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f4f4f4; text-align: left;">
                    <th style="padding: 10px;">Job Title</th>
                    <th style="padding: 10px;">My Price</th>
                    <th style="padding: 10px;">Date</th>
                    <th style="padding: 10px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($bid = mysqli_fetch_assoc($result)): ?>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 10px;"><a href="job_details.php?id=<?php echo $bid['job_id']; ?>"><?php echo htmlspecialchars($bid['title']); ?></a></td>
                            <td style="padding: 10px;">LKR <?php echo number_format($bid['price'], 2); ?></td>
                            <td style="padding: 10px;"><?php echo $bid['created_at']; ?></td>
                            <td style="padding: 10px;">
                                <?php 
                                    if ($bid['status'] == 'accepted') echo '<span class="badge badge-success">Accepted</span>';
                                    elseif ($bid['status'] == 'rejected') echo '<span class="badge badge-danger">Rejected</span>';
                                    else echo '<span class="badge badge-warning">Pending</span>';
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="padding: 20px; text-align: center;">No bids found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
