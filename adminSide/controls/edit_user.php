<?php
include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id     = $_POST['id'];
    $name   = $_POST['name'];
    $email  = $_POST['email'];
    $role   = $_POST['role'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Handle profile picture upload
    if (!empty($_FILES['picture']['name'])) {
        $pictureName = basename($_FILES["picture"]["name"]);
        $targetDir = "../../uploads/";
        $targetFilePath = $targetDir . $pictureName;

        if (move_uploaded_file($_FILES["picture"]["tmp_name"], $targetFilePath)) {
            $updatePicture = ", picture='$pictureName'";
        } else {
            $updatePicture = ""; // If upload fails, don’t change
        }
    } else {
        $updatePicture = ""; // If no new picture uploaded
    }

    // Handle password update
    $updatePassword = $password ? ", password='$password'" : "";

    // Final SQL query
    $sql = "UPDATE users SET name='$name', email='$email', role='$role' $updatePicture $updatePassword WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../manage-users.php?update=success");
        exit();
    } else {
        echo "Error updating user: " . $conn->error;
    }
}
?>
