<?php
include '../../config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: ../manage-users.php?status=deleted");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
