<?php
include 'includes/header.php';
requireRole('admin');

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if (!$user_id) {
    die("Invalid User ID");
}

$chat_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT full_name, email FROM users WHERE id='$user_id'"));
if (!$chat_user) {
    die("User not found");
}

// Handle Message Send
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $message = sanitize($conn, $_POST['message']);
    $sender_id = $_SESSION['user_id'];
    
    // project_id is NULL for admin support chat
    $sql = "INSERT INTO messages (project_id, sender_id, receiver_id, message, message_type) 
            VALUES (NULL, '$sender_id', '$user_id', '$message', 'two_way')";
    
    if (mysqli_query($conn, $sql)) {
        // Notify User
        include_once 'includes/notification_helper.php';
        sendNotification($conn, $user_id, "New support message from Admin");
        // Redirect to avoid form resubmission
        header("Location: admin_chat.php?user_id=$user_id");
        exit;
    }
}

// Fetch Messages
$msg_sql = "SELECT messages.*, users.full_name as sender_name 
            FROM messages 
            JOIN users ON messages.sender_id = users.id 
            WHERE project_id IS NULL 
            AND ((sender_id='{$_SESSION['user_id']}' AND receiver_id='$user_id') 
              OR (sender_id='$user_id' AND receiver_id='{$_SESSION['user_id']}'))
            ORDER BY created_at ASC";
$msg_res = mysqli_query($conn, $msg_sql);
?>

<div class="container mt-3">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Chat with <?php echo htmlspecialchars($chat_user['full_name']); ?></h2>
        <a href="admin_users.php" class="btn btn-secondary">Back to Users</a>
    </div>

    <div class="card mt-3" style="min-height: 400px; display: flex; flex-direction: column;">
        <div id="chat-messages" style="flex: 1; overflow-y: auto; padding: 15px; background: #f9fafb; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ddd;">
            <?php if (mysqli_num_rows($msg_res) > 0): ?>
                <?php while ($m = mysqli_fetch_assoc($msg_res)): ?>
                    <div style="margin-bottom: 15px; text-align: <?php echo ($m['sender_id'] == $_SESSION['user_id']) ? 'right' : 'left'; ?>;">
                        <div style="display: inline-block; max-width: 70%; text-align: left;">
                            <div style="font-size: 0.8rem; font-weight: 700; color: #666; margin-bottom: 4px;">
                                <?php echo htmlspecialchars($m['sender_name']); ?>
                            </div>
                            <div style="padding: 10px 15px; border-radius: 12px; background: <?php echo ($m['sender_id'] == $_SESSION['user_id']) ? '#007bff' : '#fff'; ?>; color: <?php echo ($m['sender_id'] == $_SESSION['user_id']) ? '#fff' : '#333'; ?>; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border: <?php echo ($m['sender_id'] == $_SESSION['user_id']) ? 'none' : '1px solid #ddd'; ?>;">
                                <?php echo nl2br(htmlspecialchars($m['message'])); ?>
                            </div>
                            <div style="font-size: 0.7rem; color: #999; margin-top: 4px;">
                                <?php echo date('M d, H:i', strtotime($m['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center text-muted">No messages yet.</p>
            <?php endif; ?>
        </div>

        <form method="POST">
            <div class="form-group" style="display: flex; gap: 10px;">
                <textarea name="message" class="form-control" rows="2" placeholder="Type a message..." required style="flex: 1;"></textarea>
                <button type="submit" name="send_message" class="btn btn-primary" style="height: fit-content; align-self: flex-end;">Send</button>
            </div>
        </form>
    </div>
</div>

<script>
    const chatBox = document.getElementById('chat-messages');
    chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php include 'includes/footer.php'; ?>
