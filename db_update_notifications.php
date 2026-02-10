<?php
include 'includes/config.php';

echo "Running Notification System Updates...\n";

// Create notifications table
$sql = "CREATE TABLE IF NOT EXISTS `notifications` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `message` text NOT NULL,
    `is_read` tinyint(1) DEFAULT 0,
    `created_at` timestamp DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($conn, $sql)) {
    echo "[SUCCESS] Table 'notifications' created/exists.\n";
} else {
    echo "[ERROR] Failed to create 'notifications': " . mysqli_error($conn) . "\n";
}

echo "Database updates completed.\n";
?>
