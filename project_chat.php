<?php
include 'includes/header.php';
requireLogin();

$project_id = intval($_GET['project_id']);
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// 1. Verify access: User must be either the assigned student or the client for this project
$proj_sql = "SELECT projects.*, jobs.title FROM projects JOIN jobs ON projects.job_id = jobs.id WHERE projects.id = '$project_id'";
$proj_res = mysqli_query($conn, $proj_sql);

if (mysqli_num_rows($proj_res) == 0) {
    die("Project not found.");
}

$project = mysqli_fetch_assoc($proj_res);

if ($project['student_id'] != $user_id && $project['client_id'] != $user_id && $role != 'admin') {
    die("Access Denied: You are not part of this project.");
}

// 2. Handle Message Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $message = sanitize($conn, $_POST['message']);
    $type = ($role == 'client') ? sanitize($conn, $_POST['message_type']) : 'two_way';
    
    // Students can NEVER send one-way messages (by rule)
    // Actually, rule says student CANNOT REPLY to one-way messages.
    // If a student tries to send a message but the LAST message or the mode is one-way, we might block it.
    // Simplifying: Students can only send "two_way" messages, and only if allowed.
    
    if ($role == 'student') {
        // Enforce: Student cannot reply if the last client message was 'one_way'?
        // The requirement says: "Student reply input must be disabled in UI" and "Student view messages but CANNOT reply".
        // This implies for specific project modes or specific messages.
        // Rule: "Used for instructions... Student reply input must be disabled"
        // Let's check if the project has a one-way mode or if we check the message history.
        // For now, let's just allow clients to choose per-message or per-project.
        // "Add field: message_type (two_way / one_way)" -> Per message.
        // "System must enforce reply restriction for one_way messages" -> If any message in the project is one_way and comes from client, do we block ALL student replies?
        // Or just show it as a notice?
        // Requirement says "Student reply input must be disabled in UI... Explantion text for student".
        // Let's assume if there's any active one-way instruction, the box is disabled.
    }

    $receiver_id = ($role == 'student') ? $project['client_id'] : $project['student_id'];
    
    $insert_sql = "INSERT INTO messages (project_id, sender_id, receiver_id, message, message_type) 
                  VALUES ('$project_id', '$user_id', '$receiver_id', '$message', '$type')";
    
    if (mysqli_query($conn, $insert_sql)) {
        // Notify Receiver
        include_once 'includes/notification_helper.php';
        $sender_name = $_SESSION['name'];
        sendNotification($conn, $receiver_id, "New message from $sender_name regarding project: '{$project['title']}'");
        
        header("Location: project_chat.php?project_id=$project_id");
        exit;
    }
}

// 3. Fetch Messages
$msg_sql = "SELECT messages.*, users.full_name as sender_name 
            FROM messages 
            JOIN users ON messages.sender_id = users.id 
            WHERE project_id = '$project_id' 
            ORDER BY created_at ASC";
$msg_res = mysqli_query($conn, $msg_sql);

// Check if student is allowed to reply
$can_reply = true;
if ($role == 'student') {
    // If the latest client message is 'one_way', disable reply.
    $check_last_sql = "SELECT message_type FROM messages WHERE project_id='$project_id' AND sender_id='{$project['client_id']}' ORDER BY created_at DESC LIMIT 1";
    $last_res = mysqli_query($conn, $check_last_sql);
    if ($last_res && mysqli_num_rows($last_res) > 0) {
        $last_msg = mysqli_fetch_assoc($last_res);
        if ($last_msg['message_type'] == 'one_way') {
            $can_reply = false;
        }
    }
}
?>

<div class="container mt-3">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Project Chat: <?php echo htmlspecialchars($project['title']); ?></h2>
        <a href="<?php echo ($role == 'student') ? 'my_projects_student.php' : 'client_projects.php'; ?>" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <div class="card mt-3" style="min-height: 400px; display: flex; flex-direction: column;">
        <div id="chat-messages" style="flex: 1; overflow-y: auto; padding: 15px; background: #f9fafb; border-radius: var(--radius-sm); margin-bottom: 20px; border: 1px solid var(--border-light);">
            <?php if (mysqli_num_rows($msg_res) > 0): ?>
                <?php while ($m = mysqli_fetch_assoc($msg_res)): ?>
                    <div style="margin-bottom: 15px; text-align: <?php echo ($m['sender_id'] == $user_id) ? 'right' : 'left'; ?>;">
                        <div style="display: inline-block; max-width: 70%; text-align: left;">
                            <div style="font-size: 0.8rem; font-weight: 700; color: var(--muted-text); margin-bottom: 4px;">
                                <?php echo htmlspecialchars($m['sender_name']); ?> 
                                <?php if ($m['message_type'] == 'one_way'): ?>
                                    <span class="badge badge-info" style="font-size: 0.6rem;">Client Notice (One-Way)</span>
                                <?php endif; ?>
                            </div>
                            <div style="padding: 10px 15px; border-radius: 12px; background: <?php echo ($m['sender_id'] == $user_id) ? 'var(--primary-color)' : '#fff'; ?>; color: <?php echo ($m['sender_id'] == $user_id) ? '#fff' : 'var(--text-color)'; ?>; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border: <?php echo ($m['sender_id'] == $user_id) ? 'none' : '1px solid var(--border-light)'; ?>;">
                                <?php echo nl2br(htmlspecialchars($m['message'])); ?>
                            </div>
                            <div style="font-size: 0.7rem; color: var(--muted-text); margin-top: 4px;">
                                <?php echo date('M d, H:i', strtotime($m['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center text-muted">No messages yet. Start the conversation!</p>
            <?php endif; ?>
        </div>

        <!-- Chat Input -->
        <div class="chat-input-area">
            <?php if ($role == 'client'): ?>
                <form method="POST">
                    <div class="form-group">
                        <textarea name="message" class="form-control" rows="3" placeholder="Type your message here..." required></textarea>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="form-group" style="margin: 0;">
                            <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="message_type" value="two_way" checked> Standard (Two-Way)
                            </label>
                            &nbsp;&nbsp;
                            <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; color: var(--error);">
                                <input type="radio" name="message_type" value="one_way"> <strong>One-Way Notice (No Reply)</strong>
                            </label>
                        </div>
                        <button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
                    </div>
                </form>
            <?php elseif ($role == 'student'): ?>
                <?php if ($can_reply): ?>
                    <form method="POST">
                        <div class="form-group" style="display: flex; gap: 10px;">
                            <textarea name="message" class="form-control" rows="2" placeholder="Reply to client..." required style="flex: 1;"></textarea>
                            <button type="submit" name="send_message" class="btn btn-primary" style="height: fit-content; align-self: flex-end;">Reply</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning" style="margin-bottom: 0;">
                        <strong>One-Way Message Mode:</strong> The client has sent a restricted notice. Replies are disabled for this project's current instruction state.
                    </div>
                    <div class="form-group mt-3">
                        <textarea disabled class="form-control" rows="2" placeholder="Replies are disabled for this notice..."></textarea>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Scroll to bottom of chat
    const chatBox = document.getElementById('chat-messages');
    chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php include 'includes/footer.php'; ?>
