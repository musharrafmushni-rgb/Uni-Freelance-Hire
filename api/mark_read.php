<?php
// api/mark_read.php
include '../includes/config.php';
include '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    // Mark specific notification
    $notif_id = intval($data['id']);
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = '$notif_id' AND user_id = '$user_id'";
} else {
    // Mark all as read
    $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = '$user_id'";
}

if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Database error']);
}
?>
