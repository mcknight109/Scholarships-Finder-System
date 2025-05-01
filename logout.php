<?php
session_start(); // Start session

include 'config.php'; // <-- Add this line to connect to database

if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];

    // Update status to offline
    $update_status = "UPDATE users SET status = 'offline' WHERE id = '$user_id'";
    mysqli_query($conn, $update_status);
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login
header("location: login.php");
exit();
?>
