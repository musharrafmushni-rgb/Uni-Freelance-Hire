<?php
include 'includes/config.php';

$sql = "ALTER TABLE users MODIFY COLUMN status ENUM('active', 'deactivated', 'pending') DEFAULT 'pending'";
if (mysqli_query($conn, $sql)) {
    echo "[SUCCESS] Column 'status' modified to include 'pending'.\n";
} else {
    echo "[ERROR] Failed to modify 'status': " . mysqli_error($conn) . "\n";
}
?>
