<?php
include 'includes/config.php';
include 'includes/functions.php';
requireRole('admin');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Prevent deleting self
    if ($id == $_SESSION['user_id']) {
        die("Cannot delete yourself.");
    }
    
    $sql = "DELETE FROM users WHERE id='$id'";
    if (mysqli_query($conn, $sql)) {
        header("Location: admin_dashboard.php");
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
} else {
    header("Location: admin_dashboard.php");
}
?>
