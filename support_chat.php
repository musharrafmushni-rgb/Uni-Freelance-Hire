<?php
include 'includes/header.php';
requireLogin();

$user_id = $_SESSION['user_id'];
// For user side, we chat with "an admin". Effectively, messages sent to admin can be picked up by any admin in theory, 
// but our data structure links to specific receiver. 
// For simplicity: When user sends a message, who is receiver? 
// Let's send to Admin ID 1 (System Admin) by default if initiating, 
// OR reply to whoever sent the last message.
// BETTER APPROACH for MVP: 
// 1. If user has existing chat history, get the admin ID from there.
// 2. If new, default to ID 1.

// Message handling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $message = sanitize($conn, $_POST['message']);
    
    // Determine admin to send to
    $admin_id = 1; // Default
    
    // Check last admin who messaged me
    $last_admin_sql = "SELECT sender_id FROM messages WHERE receiver_id='$user_id' AND project_id IS NULL ORDER BY created_at DESC LIMIT 1";
    $last_res = mysqli_query($conn, $last_admin_sql);
    if ($last_res && mysqli_num_rows($last_res) > 0) {
        $row = mysqli_fetch_assoc($last_res);
        $admin_id = $row['sender_id'];
    }
    
    $sql = "INSERT INTO messages (project_id, sender_id, receiver_id, message, message_type) 
            VALUES (NULL, '$user_id', '$admin_id', '$message', 'two_way')";
            
    if (mysqli_query($conn, $sql)) {
         // Notify Admin
         include_once 'includes/notification_helper.php';
         sendNotification($conn, $admin_id, "Support request from " . $_SESSION['name']);
         header("Location: support_chat.php");
         exit;
    }
}

// Fetch Messages
// Get messages where project_id IS NULL and (sender=me AND receiver=admin) OR (sender=admin AND receiver=me)
// Since there could be multiple admins, we just show all project-less messages involving me.
$msg_sql = "SELECT messages.*, users.full_name as sender_name, users.role as sender_role
            FROM messages 
            JOIN users ON messages.sender_id = users.id 
            WHERE project_id IS NULL 
            AND (sender_id='$user_id' OR receiver_id='$user_id')
            ORDER BY created_at ASC";
$msg_res = mysqli_query($conn, $msg_sql);
?>

<div class="container mt-3">
    <h2>Admin Support Chat</h2>
    
    <div class="card mt-3" style="min-height: 400px; display: flex; flex-direction: column;">
        <div id="chat-messages" style="flex: 1; overflow-y: auto; padding: 15px; background: #f9fafb; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ddd;">
            <?php if (mysqli_num_rows($msg_res) > 0): ?>
                <?php while ($m = mysqli_fetch_assoc($msg_res)): ?>
                    <div style="margin-bottom: 15px; text-align: <?php echo ($m['sender_id'] == $user_id) ? 'right' : 'left'; ?>;">
                        <div style="display: inline-block; max-width: 70%; text-align: left;">
                            <div style="font-size: 0.8rem; font-weight: 700; color: #666; margin-bottom: 4px;">
                                <?php echo htmlspecialchars($m['sender_name']); ?>
                                <?php if($m['sender_role'] == 'admin') echo " (Admin)"; ?>
                            </div>
                            <div style="padding: 10px 15px; border-radius: 12px; background: <?php echo ($m['sender_id'] == $user_id) ? '#007bff' : '#fff'; ?>; color: <?php echo ($m['sender_id'] == $user_id) ? '#fff' : '#333'; ?>; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border: <?php echo ($m['sender_id'] == $user_id) ? 'none' : '1px solid #ddd'; ?>;">
                                <?php echo nl2br(htmlspecialchars($m['message'])); ?>
                            </div>
                            <div style="font-size: 0.7rem; color: #999; margin-top: 4px;">
                                <?php echo date('M d, H:i', strtotime($m['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center text-muted">No messages yet. Messages sent here go directly to system administrators.</p>
            <?php endif; ?>
        </div>

        <form method="POST">
            <div class="form-group" style="display: flex; gap: 10px;">
                <textarea name="message" class="form-control" rows="2" placeholder="Type your message..." required style="flex: 1;"></textarea>
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
