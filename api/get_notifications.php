<?php
// api/get_notifications.php
include '../includes/config.php';
include '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get unread notifications
$sql = "SELECT * FROM notifications WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT 20";
$result = mysqli_query($conn, $sql);

$notifications = [];
while ($row = mysqli_fetch_assoc($result)) {
    $notifications[] = $row;
}

// Get unread count
$count_sql = "SELECT COUNT(*) as unread FROM notifications WHERE user_id = '$user_id' AND is_read = 0";
$count_res = mysqli_fetch_assoc(mysqli_query($conn, $count_sql));

//Changes 
echo json_encode([
    'notifications' => $notifications,
    'unread_count' => $count_res['unread']
]);
?>
