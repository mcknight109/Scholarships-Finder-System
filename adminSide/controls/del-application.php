<?php
include '../config.php';

if (isset($_GET['id'])) {
    $applicationId = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM applications WHERE id = ?");
    $stmt->bind_param("i", $applicationId);

    if ($stmt->execute()) {
        header("Location: ../applications.php?deleted=success");
        exit();
    } else {
        echo "Error deleting application: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}
?>
