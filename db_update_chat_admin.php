<?php
include 'includes/config.php';

// Allow project_id to be NULL for admin-user chats (support chats)
$sql = "ALTER TABLE messages MODIFY project_id INT NULL";

if (mysqli_query($conn, $sql)) {
    echo "Successfully updated 'messages' table: project_id is now nullable.<br>";
} else {
    echo "Error updating table: " . mysqli_error($conn) . "<br>";
}

echo "Database update complete.";
?>
