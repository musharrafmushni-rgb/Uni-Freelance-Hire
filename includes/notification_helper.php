<?php
// includes/notification_helper.php

/**
 * Send a notification to a specific user
 *
 * @param mysqli $conn Database connection
 * @param int $user_id User ID to receive the notification
 * @param string $message content
 * @return bool True on success
 */
function sendNotification($conn, $user_id, $message) {
    $message = sanitize($conn, $message);
    $sql = "INSERT INTO notifications (user_id, message) VALUES ('$user_id', '$message')";
    return mysqli_query($conn, $sql);
}

/**
 * Broadcast notification to all admins
 *
 * @param mysqli $conn Database connection
 * @param string $message content
 * @return void
 */
function broadcastToAdmins($conn, $message) {
    $sql = "SELECT id FROM users WHERE role='admin'";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        sendNotification($conn, $row['id'], $message);
    }
}
?>
