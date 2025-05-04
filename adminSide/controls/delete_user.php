<?php
include '../../config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch user data first
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Insert into users_archive
        $archiveQuery = "INSERT INTO users_archive (name, gender, picture, email, password, role, otp_code, otp_expiry, last_login)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $archiveStmt = $conn->prepare($archiveQuery);
        $archiveStmt->bind_param("sssssssss", 
            $user['name'], 
            $user['gender'], 
            $user['picture'], 
            $user['email'], 
            $user['password'], 
            $user['role'], 
            $user['otp_code'], 
            $user['otp_expiry'], 
            $user['last_login']
        );

        if ($archiveStmt->execute()) {
            // Delete from users
            $deleteQuery = "DELETE FROM users WHERE id = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $id);
            if ($deleteStmt->execute()) {
                header("Location: ../manage-users.php?status=deleted");
                exit();
            } else {
                echo "Error deleting user: " . $deleteStmt->error;
            }
        } else {
            echo "Error archiving user: " . $archiveStmt->error;
        }
    } else {
        echo "User not found.";
    }
}
?>
