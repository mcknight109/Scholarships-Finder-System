<?php
session_start();
include '../../config.php';

$user_id = $_SESSION['user_id'] ?? null;
$application_id = $_GET['id'] ?? null;

if (!$user_id || !$application_id) {
    header("Location: view-application.php");
    exit;
}

// Check if the application belongs to the logged-in user
$sql = "SELECT * FROM applications WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $application_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // No application found or doesn't belong to the user
    $_SESSION['error'] = "Invalid application or unauthorized action.";
    header("Location: view-application.php");
    exit;
}

// Proceed to delete the application
$delete_sql = "DELETE FROM applications WHERE id = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("i", $application_id);

if ($delete_stmt->execute()) {
    $_SESSION['success'] = "Application canceled successfully.";
} else {
    $_SESSION['error'] = "Failed to cancel application. Please try again.";
}

header("Location: ../stud-application.php");
exit;
?>
