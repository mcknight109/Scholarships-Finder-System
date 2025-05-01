<?php
include '../../config.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the data from POST
    $application_id = $_POST['id'];
    $status = $_POST['status'];

    // Check if the status is either 'approved' or 'rejected'
    if (in_array($status, ['approved', 'rejected'])) {
        // Prepare the query to update the application status
        $updateQuery = "UPDATE applications SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("si", $status, $application_id);

        // Execute the query and check if the update was successful
        if ($stmt->execute()) {
            // Redirect back to the view-application page after updating
            header("Location: view-application.php?id=$application_id");
            exit(); // Ensure no further code is executed
        } else {
            // Handle error
            echo "Error updating application status.";
        }
        $stmt->close();
    } else {
        // If the status is not valid
        echo "Invalid status.";
    }
} else {
    // If not POST request
    echo "Invalid request method.";
}
?>
