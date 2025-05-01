<?php
session_start();
include '../../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $applicationId = $_GET['id'];
    $studentId = $_SESSION['user_id'];

    // Ensure that the application belongs to the logged-in student
    $stmt = $conn->prepare("DELETE FROM applications WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $applicationId, $studentId);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Application deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete application.";
    }
}

header("Location: ../stud-applications.php");
exit();
?>
