<?php
include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id    = $_POST['id'];
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $role  = $_POST['role'];

    $query = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";
    $stmt  = $conn->prepare($query);
    $stmt->bind_param("sssi", $name, $email, $role, $id);

    if ($stmt->execute()) {
        header("Location: ../manage-users.php?status=updated");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
