<?php
include 'includes/config.php';

echo "Running Database Updates...\n";

// 1. Add profile_photo column
$check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'profile_photo'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) DEFAULT 'default_avatar.png'";
    if (mysqli_query($conn, $sql)) {
        echo "[SUCCESS] Column 'profile_photo' added.\n";
    } else {
        echo "[ERROR] Failed to add 'profile_photo': " . mysqli_error($conn) . "\n";
    }
} else {
    echo "[INFO] Column 'profile_photo' already exists.\n";
}

// 2. Add status column
$check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'status'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE users ADD COLUMN status ENUM('active', 'deactivated') DEFAULT 'active'";
    if (mysqli_query($conn, $sql)) {
        echo "[SUCCESS] Column 'status' added.\n";
    } else {
        echo "[ERROR] Failed to add 'status': " . mysqli_error($conn) . "\n";
    }
} else {
    echo "[INFO] Column 'status' already exists.\n";
}

echo "Database updates completed.\n";
?>
