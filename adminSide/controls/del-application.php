<?php
include '../../config.php';

if (isset($_GET['id'])) {
    $applicationId = $_GET['id'];

    // Step 1: Fetch the application data to be archived
    $stmt = $conn->prepare("SELECT * FROM applications WHERE id = ?");
    $stmt->bind_param("i", $applicationId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $application = $result->fetch_assoc();

        // Step 2: Insert the data into the applications_archive table
        $stmt_archive = $conn->prepare("INSERT INTO applications_archive (user_id, scholarship_id, age, contact, address, school, document, applied_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_archive->bind_param("iisssssss", $application['user_id'], $application['scholarship_id'], $application['age'], $application['contact'], $application['address'], $application['school'], $application['document'], $application['applied_at'], $application['status']);

        if ($stmt_archive->execute()) {
            // Step 3: Delete the application from the applications table
            $stmt_delete = $conn->prepare("DELETE FROM applications WHERE id = ?");
            $stmt_delete->bind_param("i", $applicationId);

            if ($stmt_delete->execute()) {
                header("Location: ../applicants.php?deleted=success");
                exit();
            } else {
                echo "Error deleting application: " . $conn->error;
            }

            $stmt_delete->close();
        } else {
            echo "Error archiving application: " . $conn->error;
        }

        $stmt_archive->close();
    } else {
        echo "Application not found.";
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}
?>
