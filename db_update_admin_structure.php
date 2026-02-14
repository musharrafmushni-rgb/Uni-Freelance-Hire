<?php
include 'includes/config.php';

// Add admin_type column if it doesn't exist
$check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'admin_type'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE users ADD COLUMN admin_type VARCHAR(20) DEFAULT 'sub' AFTER role";
    if (mysqli_query($conn, $sql)) {
        echo "Successfully added 'admin_type' column to users table.<br>";
    } else {
        echo "Error adding column: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "'admin_type' column already exists.<br>";
}

// Set User ID 1 as Super Admin
$sql = "UPDATE users SET admin_type='super' WHERE id=1";
if (mysqli_query($conn, $sql)) {
    echo "Successfully set User ID 1 as Super Admin.<br>";
} else {
    echo "Error updating User ID 1: " . mysqli_error($conn) . "<br>";
}

echo "Database update complete.";
?>
