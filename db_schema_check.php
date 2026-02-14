<?php
include 'includes/config.php';

function describeTable($conn, $table) {
    echo "Table: $table\n";
    $res = mysqli_query($conn, "DESCRIBE $table");
    if (!$res) {
        echo "Error: " . mysqli_error($conn) . "\n";
        return;
    }
    while ($row = mysqli_fetch_assoc($res)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    echo "\n";
}

describeTable($conn, 'messages');
describeTable($conn, 'projects');
?>
